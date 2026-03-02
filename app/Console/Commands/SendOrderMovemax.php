<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Config;

class SendOrderMovemax extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sendOrderMovemax:sendOrderMovemax';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It will send order data to logistic MoveMax';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $order_data = \App\Order::whereIn('shipping_method',[3])->where('logistic_status','0')->where('end_shopping_date','!=',null)->limit(10)->get();
        
        if(count($order_data)){
            foreach ($order_data as $ordkey => $ordvalue) {
                $main_order = $ordvalue;
                $end_shopping_date = $ordvalue->end_shopping_date;
                $json_arr = json_decode($main_order->order_json,true);
                
                // Prepare shipping note data for MoveMax
                $shippingNote = $this->prepareShippingNoteData($main_order, $json_arr, $end_shopping_date);
                
                $full_order_json = json_encode([
                    'shippingNoteList' => [$shippingNote]
                ]);

                $resp = $this->sendOrderToMoveMax($full_order_json);

                $update_status = '2'; // เริ่มต้นถือว่าล้มเหลว

                $msg = is_array($resp) || is_object($resp) ? json_encode($resp) : $resp;

                // ตรวจสอบกรณีสำเร็จ
                if (
                    (isset($resp['status']) && $resp['status'] == 'Success') ||
                    (isset($resp['ret']) && $resp['ret'] == '0') ||
                    (isset($resp['success']) && $resp['success'] === true) ||
                    (isset($resp['dataList']) && is_array($resp['dataList']) && count($resp['dataList']) > 0)
                ) {
                    $update_status = '1';
                }else{
                    // Log error message
                    $error_msg = $this->formatErrorMessage($msg);
                    $full_send_json = $this->formatErrorMessage($full_order_json);
                    \App\LogisticLog::insertLog($main_order->id, 'movemax '.$error_msg, $full_send_json);
                }

                // ตรวจสอบกรณีเคยส่งแล้ว (conflict แต่ถือว่าสำเร็จ)
                if (
                    isset($resp['businessLogicError']['code']) &&
                    $resp['businessLogicError']['code'] === 'SHIPPING_NOTE_03'
                    
                ) {
                    $update_status = '1';
                }elseif (
                    isset($resp['errorCode']) &&
                    $resp['errorCode'] === 'SHIPPING_NOTE_03'
                ) {
                    $update_status = '1';
                }else{
                    $error_msg = $this->formatErrorMessage($msg);
                    $full_send_json = $this->formatErrorMessage($full_order_json);
                    \App\LogisticLog::insertLog($main_order->id, 'movemax '.$error_msg, $full_send_json);
                }

                // บันทึก log และอัปเดตสถานะ
                
                \App\Order::where('id', $main_order->id)->update(['logistic_status' => $update_status]);
            }
        }
    }

    private function prepareShippingNoteData($main_order, $json_arr, $end_shopping_date)
    {
        // Extract address information based on shipping method
        $json_arr = json_decode($main_order->order_json,true);
        $key_arr = ['shipping_address_id','first_name','last_name','provice','district','address','road','zip_code','ph_number','company_name','branch','tax_id','company_address','name','location','contact','estimate'];
        $arr_json = [];
        
        if($main_order->shipping_method == 3){
            foreach ($key_arr as $smkey => $smvalue) {
                $arr_json[$smvalue] = $json_arr['shipping_address'][$smvalue]??'';
            }
            $arr_json['name'] = $json_arr['shipping_address']['title']??'';
            $arr_json['shipping_address_id'] = $json_arr['shipping_address']['shipping_address_id'] ?? '';
        }else{
            foreach ($key_arr as $smkey => $smvalue) {
                $arr_json[$smvalue] = $json_arr[$smvalue]??'';
            }
        }

        // Calculate total weight and prepare product list
        $total_weight = 0;
        $productList = [];
        $order_shop = \App\OrderShop::where('order_id', $main_order->id)->get()->toArray();
        
        if(count($order_shop)){
            foreach ($order_shop as $shop_key => $shop_value) {
                // $order_detail = \App\OrderDetail::query()
                //             ->select('od.*', 'pw.mode_revised_weight') 
                //             ->from('order_detail as od')
                //             ->leftJoin('wh_master_product_weights as pw', 'od.sku', '=', 'pw.sku')
                //             ->where('od.order_shop_id', $shop_value['id'])
                //             ->get()
                //             ->toArray();

                $order_detail = \App\OrderDetail::query()
                    ->select('od.*', 'pw.mode_revised_weight')
                    ->from('order_detail as od')
                    ->leftJoin('wh_master_product_weights as pw', function ($join) {
                        $join->on('od.cat_id', '=', 'pw.cat_id')
                            ->on('od.package_name', '=', 'pw.package_title');
                            //->on('od.sku', '=', 'pw.sku');
                    })
                    ->where('od.order_shop_id', $shop_value['id'])
                    ->get()
                    ->toArray();

                foreach ($order_detail as $dkey => $dvalue) {
                    $detail_arr = json_decode($dvalue['order_detail_json'],true);
                    $item_weight_per_unit = !empty($dvalue['mode_revised_weight']) 
                                    ? $dvalue['mode_revised_weight'] 
                                    : $dvalue['total_weight'];

                    if(empty($dvalue['mode_revised_weight']) || $dvalue['mode_revised_weight'] <= 0) {
                        if ($dvalue['base_unit'] == 'กรัม') {
                            $item_weight_per_unit = $item_weight_per_unit * 0.001;
                        } elseif ($dvalue['base_unit'] == 'ขีด') {
                            $item_weight_per_unit = $item_weight_per_unit * 0.1;
                        }elseif ($dvalue['base_unit'] == 'มิลลิลิตร') {
                            $item_weight_per_unit = $item_weight_per_unit * 0.001;
                        } 
                    }               
                    $item_weight = $item_weight_per_unit * $dvalue['quantity'];
                    $total_weight += $item_weight;
                    
                    $productList[] = [
                        
                        'name' => $detail_arr['name'][0] ?? '',
                        'orderNo' => (string)$main_order->id,
                        'packageNo' => (string)($dvalue['id'] ?? ''),
                        'cod' => (float)($dvalue['total_final_price'] ?? 0),
                        'description' => (float)$dvalue['total_weight'].' '.$dvalue['base_unit'].'/'.$dvalue['package_name'],
                        'qty' => (float)$dvalue['quantity'],
                        'unitCode' => $dvalue['package_name'] ?? 'PCS',
                        'hasCalWeightPerUnit' => false,
                        'weightPerUnit' => (float)$item_weight_per_unit,
                        // 'volumeType' => 1,
                        // 'boxSizeWidth' => 0.1,
                        // 'boxSizeLength' => 0.1,
                        // 'boxSizeHeight' => 0.1,
                        // 'hasCalVolumePerUnit' => false,
                        // 'volumePerUnit' => 0.1
                    ];
                }
            }
        }

        // Prepare delivery dates
        $delivery_date = $this->calculateDeliveryDate($main_order);

        // Build complete address
        $full_address = $this->buildFullAddress($arr_json);

        // Prepare shipping note data
        $shippingNote = [
            // 'code' => (string)$main_order->formatted_id.'-test', // Let MoveMax generate automatically
            'code' =>(string)$main_order->formatted_id,
            'orderNo' => (string)$main_order->formatted_id,
            'referenceData' => (string)$main_order->id,
            'documentType' => 'ORDER',
            'inDraftProcess' => false,
            'startDeliveryDate' => $delivery_date['start'],
            'endDeliveryDate' => $delivery_date['end'],
            'paymentType' => 1, // Default to 1 as per API
            'merchantCode' => Config::get('constants.movemax_merchant_code', 'DEFAULT_MERCHANT'),
            'deliverProjectCode' => Config::get('constants.movemax_project_code', ''),
            'distributionCenterCode' => Config::get('constants.movemax_distribution_center', ''),
            'description' => 'Order from e-commerce system',
            'sender' => $this->prepareSenderData(),
            'receiver' => [
                // 'name' => trim(($arr_json['first_name'] ?? '') . ' ' . ($arr_json['last_name'] ?? '')),
                'code' => $arr_json['shipping_address_id'] ?? '',
                // 'addressDescription' => $arr_json['road'] ?? '',
                // 'address' => $full_address,
                // 'lat' => 0.1,
                // 'lng' => 0.1,
                // 'contactTel' => $arr_json['ph_number'] ?? ''
            ],
            'shipmentPriceType' => 1,
            'deliveryPrice' => (float)($json_arr['total_logistic_cost'] ?? 0),
            // 'pickUp' => $this->preparePickupData(),
            // 'delivery' => [
            //     'addressDescription' => $arr_json['road'] ?? '',
            //     'address' => $full_address,
            //     'lat' => 0.1,
            //     'lng' => 0.1,
            //     'contactName' => trim(($arr_json['first_name'] ?? '') . ' ' . ($arr_json['last_name'] ?? '')),
            //     'contactTel' => $arr_json['ph_number'] ?? ''
            // ],
            'productList' => $productList
        ];

        return $shippingNote;
    }

    private function calculateDeliveryDate($main_order)
    {
        // ดึงประเภทการจัดส่งและเวลาการจัดส่ง
        $del_type = \App\DeliveryTime::getDeliverYType($main_order->shipping_method);
        $delivery_time = \App\DeliveryTime::getDeliveryTime($del_type);

        // ตรวจสอบว่า pickup_time มีค่าและเป็นเวลาที่ถูกต้อง
        if (!empty($main_order->pickup_time) && strtotime($main_order->pickup_time)) {
            $pickup_time = strtotime($main_order->pickup_time);

            // เริ่มจัดส่ง = pickup_time
            $start_delivery = date('c', $pickup_time);

            // สิ้นสุดจัดส่ง = pickup_time + 2 ชั่วโมง
            $end_delivery = date('c', strtotime('+3 hours', $pickup_time));
        } else {
            // fallback กรณีไม่มี pickup_time
            $start_delivery = date('c', strtotime('+1 day'));
            $end_delivery = date('c', strtotime('+3 days'));
        }

        return [
            'start' => $start_delivery,
            'end' => $end_delivery
        ];
    }

    private function buildFullAddress($arr_json)
    {
        $address_parts = [
            $arr_json['address'] ?? '',
            $arr_json['road'] ?? '',
            $arr_json['district'] ?? '',
            $arr_json['provice'] ?? '',
            $arr_json['zip_code'] ?? ''
        ];
        
        return trim(implode(' ', array_filter($address_parts)));
    }

    private function prepareSenderData()
    {
        // You should configure these values in your config file
        return [
            // 'name' => Config::get('constants.movemax_sender_name', 'Default Sender'),
            'code' => Config::get('constants.movemax_sender_code', 'SENDER001'),
            // 'addressDescription' => Config::get('constants.movemax_sender_address_desc', ''),
            // 'address' => Config::get('constants.movemax_sender_address', ''),
            // 'lat' => 0.1,
            // 'lng' => 0.1,
            // 'contactTel' => Config::get('constants.movemax_sender_phone', '')
        ];
    }

    private function preparePickupData()
    {
        // You should configure these values in your config file
        return [
            'addressDescription' => Config::get('constants.movemax_pickup_address_desc', ''),
            'address' => Config::get('constants.movemax_pickup_address', ''),
            // 'lat' => 0.1,
            // 'lng' => 0.1,
            'contactName' => Config::get('constants.movemax_pickup_contact', ''),
            'contactTel' => Config::get('constants.movemax_pickup_phone', '')
        ];
    }

    private function formatErrorMessage($msg)
    {
        // ลองแปลง JSON string เป็น array เพื่ออ่านง่ายขึ้น
        $decoded = json_decode($msg, true);
        
        if (is_array($decoded)) {
            // ประมาณการดึงข้อความข้อผิดพลาดที่สำคัญ
            $error_details = [];
            
            if (isset($decoded['message'])) {
                $error_details[] = 'Message: ' . $decoded['message'];
            }
            if (isset($decoded['error'])) {
                $error_details[] = 'Error: ' . $decoded['error'];
            }
            if (isset($decoded['description'])) {
                $error_details[] = 'Description: ' . $decoded['description'];
            }
            if (isset($decoded['errorCode'])) {
                $error_details[] = 'Error Code: ' . $decoded['errorCode'];
            }
            if (isset($decoded['businessLogicError']['message'])) {
                $error_details[] = 'Business Logic: ' . $decoded['businessLogicError']['message'];
            }
            
            // ถ้ามีรายละเอียด ให้ใช้มัน ถ้าไม่มีให้ใช้ JSON ทั้งหมด
            if (!empty($error_details)) {
                return implode(' | ', $error_details);
            }
        }
        
        return $msg;
    }

    private function sendOrderToMoveMax($order_json)
    {
        $server_url = Config::get('constants.movemax_api_url');
        $api_key = Config::get('constants.movemax_api_key');

        if (!$server_url || !$api_key) {
            return ['status' => 'failed', 'message' => 'MoveMax API configuration missing'];
        }

        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $server_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 1,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $order_json,
                CURLOPT_HTTPHEADER => array(
                    "Authorization: " . $api_key,
                    "cache-control: no-cache",
                    "content-type: application/json"
                ),
            ));
            
            $response = curl_exec($curl);
            $err = curl_error($curl);
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            
            if ($err) {
                return ['status' => 'failed', 'message' => $err, 'http_code' => $http_code];
            } else {
                if($response) {
                    return json_decode($response, true) ?? ['status' => 'success', 'raw_response' => $response];
                } else {
                    return ['status' => 'failed', 'message' => 'Empty response from MoveMax API'];
                }
            }
        } catch(Exception $e) {
            return ['status' => 'failed', 'message' => $e->getMessage()];
        }
    }
}