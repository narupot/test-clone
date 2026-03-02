<?php

namespace App\Jobs;

use App\Order;
use App\OrderShop;
use App\OrderDetail;
use App\LogisticLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Config;
use Illuminate\Support\Facades\Log;

class SendOrderMovemaxJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $order;
    public $tries = 3;
    public $timeout = 300;
    public $backoff = [60, 120, 300];

    public function __construct(Order $order)
    {
        $this->order = $order;
        
        // กำหนด unique ID สำหรับ job นี้
        $this->onQueue('movemax');
    }

    // เพิ่ม method นี้เพื่อกำหนด unique ID
    public function uniqueId()
    {
        return 'movemax_order_' . $this->order->id;
    }

    // กำหนดเวลา unique สำหรับป้องกัน duplicate
    public function uniqueFor()
    {
        return 3600; // 1 ชั่วโมง
    }

    public function handle()
    {
        // ตรวจสอบว่า order นี้ถูกประมวลผลแล้วหรือไม่
        $current_status = Order::where('id', $this->order->id)
                            ->value('logistic_status');
        
        if ($current_status != '0') {
            \Log::info('Order already processed, skipping: ' . $this->order->id);
            return; // ข้ามการทำงาน
        }
        
        // อัปเดตสถานะเป็น processing ทันที
        
        $this->order->logistic_status = '3';
        $this->order->save();


        set_time_limit(300);
        $main_order = $this->order;
        \Log::info('SendOrderMovemaxJob started for order: '.$main_order->id);

        // Decode order JSON
        $json_arr = json_decode($main_order->order_json, true);

        // Prepare shipping note
        $shippingNote = $this->prepareShippingNoteData($main_order, $json_arr);

        $full_order_json = json_encode(['shippingNoteList' => [$shippingNote]]);

        // ส่ง order ไป MoveMax
        $resp = $this->sendOrderToMoveMax($full_order_json);

        // อัปเดตสถานะ
        $update_status = '2';
        if(isset($resp['status']) && $resp['status'] == 'Success'){
            $update_status = '1';
        } elseif(isset($resp['ret']) && $resp['ret'] == '0'){
            $update_status = '1';
        } elseif(isset($resp['success']) && $resp['success'] === true){
            $update_status = '1';
        }

        if($update_status=='2'){
            $msg = is_array($resp) || is_object($resp) ? json_encode($resp) : $resp;
            LogisticLog::insertLog($main_order->id, 'movemax '.$msg, $full_order_json);
        }

        
        $main_order->logistic_status = $update_status;
        $main_order->save();


        \Log::info('SendOrderMovemaxJob finished for order: '.$main_order->id);
    }

    public function failed(\Exception $exception)
    {
        Log::error('SendOrderMovemaxJob failed for order: ' . $this->order->id, [
            'error' => $exception->getMessage(),
            'uuid' => $this->job->getJobId()
        ]);
        
        // อัปเดตสถานะ order
        $this->order->logistic_status = '2';
        $this->order->movemax_response = 'Job failed: ' . $exception->getMessage();
        $this->order->save();

        // ไม่ต้อง throw exception อีกเพราะนี่คือ failed handler
    }

    private function prepareShippingNoteData($main_order, $json_arr)
    {
        $key_arr = ['first_name','last_name','provice','district','address','road','zip_code','ph_number','company_name','branch','tax_id','company_address','name','location','contact','estimate'];
        $arr_json = [];

        if($main_order->shipping_method == 3){
            foreach ($key_arr as $smvalue) {
                $arr_json[$smvalue] = $json_arr['shipping_address'][$smvalue] ?? '';
            }
            $arr_json['name'] = $json_arr['shipping_address']['title'] ?? '';
        } else {
            foreach ($key_arr as $smvalue) {
                $arr_json[$smvalue] = $json_arr[$smvalue] ?? '';
            }
        }

        // เรียกข้อมูลจาก order_detail โดยตรง ไม่ต้องผ่าน order_shop
        $order_details = OrderDetail::leftJoin('wh_master_product_weights as pw', 'order_detail.sku', '=', 'pw.sku')
            ->select('order_detail.*', 'pw.mode_revised_weight')
            ->where('order_detail.order_id', $main_order->id)
            ->get();

        $total_weight = 0;
        $productList = [];

        foreach ($order_details as $dvalue) {
            $detail_arr = json_decode($dvalue['order_detail_json'], true);

            $item_weight_per_unit = !empty($dvalue['mode_revised_weight']) 
                                    ? $dvalue['mode_revised_weight'] 
                                    : $dvalue['total_weight'];
            $item_weight = $item_weight_per_unit * $dvalue['quantity'];
            $total_weight += $item_weight;

            \Log::info('Check mode_revised_weight', [
                'sku' => $dvalue['sku'] ?? '',
                'mode_revised_weight' => $dvalue['mode_revised_weight'] ?? 'not found',
                'quantity' => $dvalue['quantity'] ?? 0,
                'total_weight' => $item_weight,
            ]);

            $productList[] = [
                'name' => $detail_arr['name'][0] ?? '',
                'orderNo' => (string)$main_order->id,
                'packageNo' => (string)($dvalue['id'] ?? ''),
                'cod' => 0,
                'description' => (float)$item_weight_per_unit.' '.$dvalue['base_unit'].'/'.$dvalue['package_name'],
                'qty' => (float)$dvalue['quantity'],
                'unitCode' => $dvalue['base_unit'] ?? 'PCS',
                'hasCalWeightPerUnit' => true,
                'weightPerUnit' => (float)$item_weight_per_unit,
                'volumeType' => 1,
                'boxSizeWidth' => 0.1,
                'boxSizeLength' => 0.1,
                'boxSizeHeight' => 0.1,
                'hasCalVolumePerUnit' => false,
                'volumePerUnit' => 0.1
            ];
        }

        $delivery_date = $this->calculateDeliveryDate($main_order);
        $full_address = $this->buildFullAddress($arr_json);

        return [
            'code' => '',
            'orderNo' => (string)$main_order->formatted_id,
            'referenceData' => (string)$main_order->id,
            'documentType' => 'ORDER',
            'inDraftProcess' => false,
            'startDeliveryDate' => $delivery_date['start'],
            'endDeliveryDate' => $delivery_date['end'],
            'paymentType' => 1,
            'merchantCode' => Config::get('constants.movemax_merchant_code', 'DEFAULT_MERCHANT'),
            'deliverProjectCode' => Config::get('constants.movemax_project_code', ''),
            'distributionCenterCode' => Config::get('constants.movemax_distribution_center', ''),
            'description' => 'Order from e-commerce system',
            'sender' => $this->prepareSenderData(),
            'receiver' => [
                'name' => trim(($arr_json['first_name'] ?? '') . ' ' . ($arr_json['last_name'] ?? '')),
                'code' => $arr_json['shipping_address_id'] ?? '',
                'addressDescription' => $arr_json['road'] ?? '',
                'address' => $full_address,
                'lat' => 0.1,
                'lng' => 0.1,
                'contactTel' => $arr_json['ph_number'] ?? ''
            ],
            'shipmentPriceType' => 1,
            'deliveryPrice' => (float)($json_arr['total_logistic_cost'] ?? 0),
            'pickUp' => $this->preparePickupData(),
            'delivery' => [
                'addressDescription' => $arr_json['road'] ?? '',
                'address' => $full_address,
                'lat' => 0.1,
                'lng' => 0.1,
                'contactName' => trim(($arr_json['first_name'] ?? '') . ' ' . ($arr_json['last_name'] ?? '')),
                'contactTel' => $arr_json['ph_number'] ?? ''
            ],
            'productList' => $productList
        ];
    }

    private function sendOrderToMoveMax($order_json)
    {
        $server_url = Config::get('constants.movemax_api_url');
        $api_key = Config::get('constants.movemax_api_key');

        if (!$server_url || !$api_key) {
            return ['ret' => 'error', 'message' => 'MoveMax API configuration missing'];
        }

        try {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $server_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $order_json,
                CURLOPT_HTTPHEADER => [
                    "Authorization: " . trim($api_key),
                    "Content-Type: application/json",
                    "User-Agent: Laravel-Queue"
                ],
                CURLOPT_SSL_VERIFYPEER => true,
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            \Log::info('MoveMax API Response', [
                'http_code' => $http_code,
                'curl_error' => $err,
                'response_length' => strlen($response),
            ]);

            if ($err) {
                return ['ret'=>'error','message'=>'CURL Error: '.$err,'http_code'=>$http_code];
            }

            $decoded = json_decode($response,true);
            if(json_last_error() !== JSON_ERROR_NONE){
                return ['ret'=>'error','message'=>'Invalid JSON: '.json_last_error_msg(),'raw_response'=>$response];
            }

            return $decoded;

        } catch (\Exception $e) {
            return ['ret'=>'error','message'=>'Exception: '.$e->getMessage()];
        }
    }

    // --- ฟังก์ชันช่วยเหลือ ---
    private function calculateDeliveryDate($main_order)
    {
        if (!empty($main_order->pickup_time) && strtotime($main_order->pickup_time)) {
            $pickup_time = strtotime($main_order->pickup_time);
            return [
                'start' => date('c', $pickup_time),
                'end' => date('c', strtotime('+2 hours', $pickup_time))
            ];
        }
        return [
            'start' => date('c', strtotime('+1 day')),
            'end' => date('c', strtotime('+2 days'))
        ];
    }

    private function buildFullAddress($arr_json)
    {
        $address_parts = [$arr_json['address'] ?? '', $arr_json['road'] ?? '', $arr_json['district'] ?? '', $arr_json['provice'] ?? '', $arr_json['zip_code'] ?? ''];
        return trim(implode(' ', array_filter($address_parts)));
    }

    private function prepareSenderData()
    {
        return [
            'name' => Config::get('constants.movemax_sender_name', 'Default Sender'),
            'code' => Config::get('constants.movemax_sender_code', 'SENDER001'),
            'addressDescription' => Config::get('constants.movemax_sender_address_desc', ''),
            'address' => Config::get('constants.movemax_sender_address', ''),
            'lat' => 0.1,
            'lng' => 0.1,
            'contactTel' => Config::get('constants.movemax_sender_phone', '')
        ];
    }

    private function preparePickupData()
    {
        return [
            'addressDescription' => Config::get('constants.movemax_pickup_address_desc', ''),
            'address' => Config::get('constants.movemax_pickup_address', ''),
            'lat' => 0.1,
            'lng' => 0.1,
            'contactName' => Config::get('constants.movemax_pickup_contact', ''),
            'contactTel' => Config::get('constants.movemax_pickup_phone', '')
        ];
    }
}
