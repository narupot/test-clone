<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Config;

class SendOrderWMS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sendOrderWMS:sendOrderWMS';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It will send order data to WMS';

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
        $order_data = \App\Order::whereIn('shipping_method',[1,3])->where('wms_status','0')->where('end_shopping_date','!=',null)->limit(10)->get();
        
        if(count($order_data)){
            foreach ($order_data as $ordkey => $ordvalue) {
                $main_order = $ordvalue;
                $end_shopping_date = $ordvalue->end_shopping_date;
                $json_arr = json_decode($main_order->order_json,true);
                $key_arr = ['shipping_address_id','title','first_name','last_name','provice','district','address','road','zip_code','ph_number','company_name','branch','tax_id','company_address','name','location','contact','estimate'];
                $arr_json = [];
                
                if($main_order->shipping_method == 3){
                    foreach ($key_arr as $smkey => $smvalue) {
                        $arr_json[$smvalue] = $json_arr['shipping_address'][$smvalue]??'';
                    }
                    $arr_json['name'] = $json_arr['shipping_address']['title']??'';
                }else{
                    foreach ($key_arr as $smkey => $smvalue) {
                        $arr_json[$smvalue] = $json_arr[$smvalue]??'';
                    }
                }
                
                if(isset($json_arr['total_logistic_cost']) && $json_arr['total_logistic_cost']>0){
                    $total_logistic_cost = $json_arr['total_logistic_cost'];
                }else{
                    $total_logistic_cost = 0;
                }

                $seller_send_prodcut_time = null;
                $del_type = \App\DeliveryTime::getDeliverYType($main_order->shipping_method);
                $delivery_time = \App\DeliveryTime::getDeliveryTime($del_type);
                $prepare_time_before = $delivery_time->prepare_time_before;
                
                if($prepare_time_before && strtotime($main_order->pickup_time)){
                    $seller_send_prodcut_time = date('Y-m-d H:i:s',strtotime('-'.$prepare_time_before.' hours', strtotime($main_order->pickup_time)));
                }

                $total_weight = 0;
                $order_shop = \App\OrderShop::where('order_id',$main_order->id)->get()->toArray();
                
                if (!empty($main_order->pickup_time)) {
                    $finish_date = date('Y-m-d H:i:s', strtotime($main_order->pickup_time . ' +2 hours'));
                }

                // สร้างโครงสร้าง JSON ใหม่
                $json_output = [
                    'mainOrder' => [
                        'OrderNo' => (string)$main_order->formatted_id, 
                        'OrderDate' => $end_shopping_date ?? '',
                        'DeliveryDate' => $main_order->pickup_time ?? '',
                        'Shift' => '',
                        'FinishDate' => $finish_date ?? '',
                        'BuyerID' => (string)$main_order->user_id,
                        'BuyerName' => $main_order->user_name ?? '',
                        'ShippingAdrID' => (string)$arr_json['shipping_address_id'] ?? '',
                        'BuyerShippingTitle' => $arr_json['title'] ?? '',
                        'Adr1' => $this->buildAddress($arr_json),
                        'Adr2' => '',
                        'Contact' => trim(($arr_json['first_name'] ?? '') . ' ' . ($arr_json['last_name'] ?? '')),
                        'Phone' => $arr_json['ph_number'] ?? '',
                        'TotalWgt' => 0, // จะคำนวณทีหลัง
                        'OrderType' => $main_order->shipping_method == 3 ? 'จัดส่งตามที่อยู่' : 'มารับที่ศูนย์',
                        'OrderOption' => ''
                    ],
                    'shopOrder' => []
                ];

                if(count($order_shop)){
                    foreach ($order_shop as $key => $value) {                        
                        
                        $shop_arr = json_decode($value['shop_json'],true);
                        $shop_name_raw = $shop_arr['shop_name'] ?? '';

                        // ถ้าเป็น array ให้ดึงค่าแรกออกมา, ถ้าไม่ใช่ array ให้ใช้ค่าเดิม
                        $shop_name = is_array($shop_name_raw) ? ($shop_name_raw[0] ?? '') : $shop_name_raw;

                        
                        $panel_no = $shop_arr['panel_no'] ?? '';
                        $market = $shop_arr['market'] ?? '';
                        $seller_name = $shop_arr['seller_name'] ?? '';
                        $seller_ph_number = $shop_arr['seller_ph_number'] ?? '';
                        $shop_data_output = [
                            'ShopID' => (string)$value['shop_id'],
                            'ShopName' => $shop_name,
                            'Adr1' => $panel_no,   
                            'Adr2' => $market,
                            'Contact' => $seller_name,
                            'Phone' => !empty($seller_ph_number) ? $seller_ph_number : ($arr_json['ph_number'] ?? ''),
                            'DeliveryType' => '',
                            'productOrder' => []
                        ];

                        // $order_detail = \App\OrderDetail::where(['order_shop_id'=>$value['id']])->get()->toArray();
                        $order_detail = \App\OrderDetail::query()
                            ->select('od.*', 'pw.mode_revised_weight') // หรือระบุ column ที่ต้องการเฉพาะ
                            ->from('order_detail as od')
                            ->leftJoin('wh_master_product_weights as pw', 'od.sku', '=', 'pw.sku')
                            ->where('od.order_shop_id', $value['id'])
                            ->where('od.status', 2)
                            ->get()
                            ->toArray();

                        $line = 0;
                        foreach ($order_detail as $dkey => $dvalue) {
                            
                            $detail_arr = json_decode($dvalue['order_detail_json'], true);
                                                     
                            if(!empty($dvalue['mode_revised_weight'])){
                                $item_weight = $dvalue['mode_revised_weight'] * $dvalue['quantity'];
                            }else{
                                $item_weight = $dvalue['total_weight'] * $dvalue['quantity'];
                            }
                           
                            $total_weight += $item_weight;
                            
                            // ดึงข้อมูลภาษาไทยโดยตรง
                            $product_name_raw = $detail_arr['name'] ?? '';
                            $product_name = is_array($product_name_raw) ? ($product_name_raw[0] ?? '') : $product_name_raw;
                            
                            $package_name = $detail_arr['package'] ?? '';
                            
                            $grade = '';
                            $size = '';
                            if (isset($detail_arr['badge']) && is_array($detail_arr['badge'])) {

                                // --- จัดการข้อมูล Size ---
                                $size_text = $detail_arr['badge']['size'] ?? '';
                                // ค้นหารูปแบบ "ข้อความในวงเล็บ"
                                if (preg_match('/\((.*?)\)/', $size_text, $size_matches)) {
                                    // ถ้าเจอ ให้ใช้ข้อความที่อยู่ในวงเล็บ (ผลลัพธ์จะอยู่ที่ index 1)
                                    $size = $size_matches[1]; 
                                } else {
                                    // ถ้าไม่เจอ (หรือไม่มีวงเล็บ) ให้ใช้ค่าเดิมทั้งหมด
                                    $size = $size_text;
                                }

                                // --- จัดการข้อมูล Grade ---
                                $grade_text = $detail_arr['badge']['grade'] ?? '';
                                // ค้นหารูปแบบ "ข้อความในวงเล็บ"
                                if (preg_match('/\((.*?)\)/', $grade_text, $grade_matches)) {
                                    // ถ้าเจอ ให้ใช้ข้อความที่อยู่ในวงเล็บ (ผลลัพธ์จะอยู่ที่ index 1)
                                    $grade = $grade_matches[1];
                                } else {
                                    // ถ้าไม่เจอ (หรือไม่มีวงเล็บ) ให้ใช้ค่าเดิมทั้งหมด
                                    $grade = $grade_text;
                                }

                                // (ถ้าต้องการ) ดึงข้อมูลส่วนอื่นของ badge มาด้วย
                                $detail_arr['badge_title'] = $detail_arr['badge']['title'] ?? '';

                                // ลบ key 'badge' เดิมทิ้งไป
                                unset($detail_arr['badge']);
                            }

                            // แก้ไขส่วน AmountText อย่างปลอดภัย
                            $base_unit = $dvalue['base_unit'] ?? '';
                            $package_name_text = $package_name;
                            
                            // ถ้า base_unit หรือ package_name เป็น array ให้แปลงเป็น string
                            if (is_array($base_unit)) {
                                $base_unit = implode('', $base_unit);
                            }
                            if (is_array($package_name_text)) {
                                $package_name_text = implode('', $package_name_text);
                            }
                            
                            $amount_text = (float)$dvalue['total_weight'] . ' ' . $base_unit . '/' . $package_name_text;
                            
                            $product_data = [
                                'ProductCode' => $dvalue['sku'] ?? '',
                                'ProductName' => $product_name,
                                'ID' => 0,
                                'Brand' => '',
                                'GradeTH' => $grade,
                                'SizeTH' => $size,
                                'OrderQty' => (int)$dvalue['quantity'],
                                'PackageName' => $package_name_text,
                                'AmountText' => $amount_text,
                                'TotalWgt' => $item_weight,
                                'TotalWgtInMainOrder' => 0, // จะอัพเดททีหลัง
                                'OrderPackQty' => 0,
                                'PackUOM' => '',
                                'SalePrice' => (float)$dvalue['total_price'],
                                'SaleAmount' => (float)($dvalue['total_price'] * $dvalue['quantity']),
                                'ShipCost' => (float)$total_logistic_cost,
                                'BarcodeTag' => (int)$dvalue['quantity']
                            ];

                            $shop_data_output['productOrder'][] = $product_data;
                        }
                        
                        $json_output['shopOrder'][] = $shop_data_output;
                    }
                }

                // อัพเดทน้ำหนักรวม
                $json_output['mainOrder']['TotalWgt'] = $total_weight;
                
                // อัพเดทน้ำหนักรวมในแต่ละสินค้า
                foreach ($json_output['shopOrder'] as &$shop) {
                    foreach ($shop['productOrder'] as &$product) {
                        $product['TotalWgtInMainOrder'] = $total_weight;
                    }
                }

                // ใช้ JSON_UNESCAPED_UNICODE เพื่อรักษาภาษาไทย
                $full_order_json = json_encode($json_output, JSON_UNESCAPED_UNICODE);

                $resp = $this->sendOrderJson($full_order_json);

                $update_status = '2';

                if(isset($resp['status']) && $resp['status'] == 'Success'){
                    $update_status = '1'; // สำเร็จ (รูปแบบใหม่)
                } elseif(isset($resp['ret']) && $resp['ret'] == '0'){
                    $update_status = '1'; // สำเร็จ (รูปแบบเก่า)
                } elseif(isset($resp['success']) && $resp['success'] === true){
                    $update_status = '1'; // สำเร็จ (รูปแบบอื่น)
                }

                if($update_status=='2'){

                    $msg = is_array($resp) || is_object($resp) ? json_encode($resp) : $resp;

                    $update_log = \App\LogisticLog::insertLog($main_order->id, 'wms '.$msg, $full_order_json);
                }
                $update_ord = \App\Order::where('id', $main_order->id)->update(['wms_status' => $update_status]);
            }
        }
    }

    /**
     * สร้างที่อยู่จากข้อมูลใน array
     */
    private function buildAddress($address_data)
    {
        $address_parts = [];
        if (!empty($address_data['address'])) $address_parts[] = $address_data['address'];
        if (!empty($address_data['road'])) $address_parts[] = $address_data['road'];
        if (!empty($address_data['district'])) $address_parts[] = $address_data['district'];
        if (!empty($address_data['provice'])) $address_parts[] = $address_data['provice'];
        if (!empty($address_data['zip_code'])) $address_parts[] = $address_data['zip_code'];
        
        return implode(', ', $address_parts);
    }

    private function sendOrderJson($order_json){
        $server_url = trim(Config::get('constants.send_order_wms_url'));
        $api_key = Config::get('constants.wms_api_key');

        // ตรวจสอบ URL เบื้องต้น
        if (empty($server_url)) {
            return ['status' => 'failed', 'message' => 'WMS URL is empty'];
        }
        if (!filter_var($server_url, FILTER_VALIDATE_URL)) {
            $hex = '';
            foreach (str_split($server_url) as $ch) { $hex .= sprintf('%02x ', ord($ch)); }
            return ['status' => 'failed', 'message' => 'Malformed WMS URL', 'url' => $server_url, 'hex' => trim($hex)];
        }

        // $this->info('WMS URL: ' . $server_url);
        
        // *** เพิ่มส่วนนี้: แสดงข้อมูลที่ส่งไป ***
        // $this->info('Request Data: ' . substr($order_json, 0, 500) . '...'); // แสดง 500 ตัวอักษรแรก

        $post_data = $order_json;
        try{
            $curl = curl_init();
            $verbose = fopen('php://temp', 'w+');

            curl_setopt_array($curl, array(
                CURLOPT_URL => $server_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $post_data,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_VERBOSE => true,
                CURLOPT_STDERR => $verbose,
                CURLOPT_HTTPHEADER => array(
                    "cache-control: no-cache",
                    "content-type: application/json",
                    "Accept: application/json",
                    "wms-key: " . $api_key,
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $redirect_url = curl_getinfo($curl, CURLINFO_REDIRECT_URL);

            rewind($verbose);
            $verboseLog = stream_get_contents($verbose);
            fclose($verbose);
            curl_close($curl);

            // $this->info('HTTP Status: ' . $http_code);
            if ($redirect_url) $this->info('Redirected to: ' . $redirect_url);
            // $this->info('cURL Error: ' . ($err ?: 'None'));
            
            // *** เพิ่มส่วนนี้: แสดง Response Body เต็ม ***
            // $this->info('Response Body: ' . $response);
            
            // *** Optional: แสดง verbose เฉพาะเมื่อมี error ***
            // $this->info('cURL verbose: ' . $verboseLog);

            if ($err) {
                $returnResponse = ['status' => 'failed', 'message' => $err, 'http_code' => $http_code, 'verbose' => $verboseLog];
            } else {
                if ($response) {
                    if (strpos($response, '<!DOCTYPE') !== false || strpos($response, '<html') !== false) {
                        $returnResponse = ['status' => 'failed', 'message' => 'Received HTML instead of JSON. Possible API endpoint issue.', 'http_code' => $http_code, 'response' => $response, 'verbose' => $verboseLog];
                    } else {
                        $decoded = json_decode($response, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $returnResponse = ['status' => 'failed', 'message' => 'Invalid JSON response', 'response' => $response, 'http_code' => $http_code, 'verbose' => $verboseLog];
                        } else {
                            // *** เพิ่มส่วนนี้: แสดง error details ถ้ามี ***
                            if (isset($decoded['success']) && !$decoded['success']) {
                                $this->error('WMS Error: ' . ($decoded['message'] ?? 'Unknown error'));
                                if (isset($decoded['errors'])) {
                                    $this->error('Error Details: ' . json_encode($decoded['errors'], JSON_UNESCAPED_UNICODE));
                                }
                                if (isset($decoded['data'])) {
                                    $this->error('Response Data: ' . json_encode($decoded['data'], JSON_UNESCAPED_UNICODE));
                                }
                            }
                            $returnResponse = $decoded;
                        }
                    }
                } else {
                    $returnResponse = ['status' => 'failed', 'message' => 'Empty response', 'http_code' => $http_code, 'verbose' => $verboseLog];
                }
            }
        } catch(Exception $e) {
            $returnResponse = ['status' => 'failed', 'message' => $e->getMessage()];
        }

        return $returnResponse;
    }

}