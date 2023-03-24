<?php 

namespace App\Http\Controllers\Admin\Transaction;

use App\Http\Controllers\MarketPlace;
use App\Helpers\GeneralFunctions;
use App\Helpers\CustomHelpers;
use App\Helpers\EmailHelpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Auth;
use App\Order;
use App\OrderDetail;
use App\OrderShop;
use App\OrderExportLog;
use App\Product;
use Lang;
use Config;
use Excel;

class ExportOrderController extends MarketPlace
{
    public function __construct(){
       $this->middleware('admin.user');
    }
    
    public function index(){

        $permission = $this->checkUrlPermission('export_order_log');
        if($permission === true) {
            
            $filter = $this->getFilter('export_order_log');

            return view('admin.transaction.exportOrder', ['filter'=>$filter]);
        }      
    }

    public function listExportOrderData(Request $request){
        $perpage = !empty($request->pq_rpp) ? $request->pq_rpp : 10;
        $request->page = $current_page = !empty($request->pq_curpage)?$request->pq_curpage:0;

        $start_index = ($current_page - 1) * $perpage;
        //dd($perpage,$request->page);
        
        $order_by = 'id';
        $order_by_val = 'desc';
        if(isset($request->pq_sort)){
            $sort_data = jsonDecodeArr($request->pq_sort);
            $order_by = $sort_data[0]['dataIndx'];
            $order_by_val = ($sort_data[0]['dir']=='up')?'asc':'desc';
        }

        try{
            
            $query = OrderExportLog::select('*');
            
            if(isset($request->pq_filter)){
                $filter_req = json_decode($request->pq_filter,true);
                if(!empty($filter_req['data'])){
                    $filter_arr = $filter_req['data'];
                    foreach ($filter_arr as $fkey => $fvalue) {

                        $searchval = $fvalue['value'];
                        switch ($fvalue['dataIndx']) {
                            case 'status':$query->where('formatted_id','like', '%'.$searchval.'%'); break;
                            case 'file_name':$query->where('file_name','like', '%'.$searchval.'%'); break;
                            case 'created_at':
                                $from_date = $fvalue['value']??'';
                                $to_date = $fvalue['value2']??'';
                                createDateFilter($query,'created_at',$from_date,$to_date);
                            break;
                            case 'order_date':
                                $from_date = $fvalue['value']??'';
                                $to_date = $fvalue['value2']??'';
                                createDateFilter($query,'order_date',$from_date,$to_date);
                            break;
                            
                        }
                        
                    }
                }
            }
            $response = $query->orderBy($order_by,$order_by_val)->paginate($perpage,['*'],'page',$current_page);
            $totrec = $response->total();
            //dd($response);
            if($start_index >= $totrec) {
                $current_page = ceil($totrec/$perpage);
                
                $response = $query->orderBy($order_by,$order_by_val)->paginate($perpage,['*'],'page',$current_page);
            }

            foreach ($response as $key => $value) {

                $response[$key]->dwn_url = action('Admin\Transaction\ExportOrderController@downloadExport',$value->id);
                //$response[$key]->dwn_url = Config::get('constants.public_url').'seller-payment/'.$value->file_name.'.txt';;
            }

            /***save filter****/
            $this->setFilter('export_order_log',$request);

            
        }catch(QueryException $e){
            $response = ['status'=>'fail','msg'=>$e->getMessage()];
        }
        

        return $response;
    }

    public function downloadExport(Request $request,$id=null){

        if($id){
            $file_data = OrderExportLog::where('id',$id)->first();
            if($file_data){
                $file_path = Config::get('constants.public_path').'/seller-payment/';
                $filename = $file_data->file_name;
                $file_full_path = $file_path.$filename;

                if(file_exists($file_full_path)){

                    header("Content-Type: application/octet-stream");
                    header("Content-Disposition: attachment; filename=" . Urlencode($filename));   
                    header("Content-Type: application/force-download");
                    header("Content-Type: application/octet-stream");
                    header("Content-Type: application/download");
                    header("Content-Description: File Transfer");            
                    header("Content-Length: " . Filesize($file_full_path));
                    flush(); // this doesn't really matter.
                    $fp = fopen($file_full_path, "r");
                    while (!feof($fp))
                    {
                        echo fread($fp, 65536);
                        flush(); // this is essential for large downloads
                    } 
                    fclose($fp);
                    
                }
                
            }
        }
    }

    public function changeStatus(Request $request){

        if(!empty($request->export_id) && !empty($request->status)){
            $status = $request->status;
            OrderExportLog::where('id',$request->export_id)->update(['status'=>$status]);
            return ['status'=>'success','msg'=>\Lang::get('admin_common.records_updated_successfully')];
        }else{
            return ['status'=>'fail','msg'=>\Lang::get('admin_common.something_went_wrong')];
        }
    }
    
    public function generateTxt(Request $request)
    {
        $order_date = $request->order_date;
        $shop_ids = $request->shop_ids;
        $shop_id_arr = explode(',',$shop_ids);
        if(!count($shop_id_arr) || !strtotime($order_date)){
            return false;
        }

        $shop_data = $query = \DB::table(with(new \App\Shop)->getTable().' as shop')
                  ->join(with(new \App\Seller)->getTable().' as seller', 'shop.user_id', '=', 'seller.user_id')
                  ->select('shop.user_id','seller.bank_id','shop.id')
                  ->whereIn('shop.id',$shop_id_arr)
                  ->get();
        $bank_arr = [];
        foreach ($shop_data as $shopkey => $shopvalue) {
            if($shopvalue->bank_id==3){
                $bank_arr['kbank'][] = $shopvalue->id;
            }else{
                $bank_arr['other'][] = $shopvalue->id;
            }
            
        }
        $log_id_arr = [];
        foreach ($bank_arr as $bankkey => $bankvalue) {
            $shop_id_arr = [];
            $bank_name_key = $bankkey;
            $shop_id_arr = $bankvalue;

            $eny_type = $request->eny_type;
            $data_h = "H";
            $data_p = "P";
            $data_p_product_code = "DCT";

            $data_client_code = getConfigValue('CLIENT_CODE_FOR_ORDER_EXPORT_FILE');
            $data_client_account_no = "3731037174";

            /*****H row data****/
            $record_identifier = $data_h;
            $no_use_2 = str_repeat(' ', 12);
            $no_use_3 = str_repeat(' ', 10);
            $no_use_4 = str_repeat(' ', 20);
            $no_use_5 = str_repeat(' ', 10);
            $no_use_6 = str_repeat(' ', 10);
            $no_use_7 = str_repeat(' ', 10);
            $no_use_8 = str_repeat(' ', 10);
            $no_use_9 = str_repeat(' ', 20);

            $h_data = $record_identifier . $no_use_2 . $no_use_3 . $no_use_4 . $no_use_5 . $no_use_6 . $no_use_7 . $no_use_8 . $no_use_9;

            /*****P row data************/

            $record_identifier = $data_p;

            $product_code = str_pad($data_p_product_code, 10, " ", STR_PAD_RIGHT);

            $no_use_3 = str_repeat(' ', 10);
            $no_use_4 = str_repeat(' ', 10);
            $no_use_5 = str_repeat(' ', 10);
            $no_use_6 = str_repeat(' ', 5);
            $no_use_7 = str_repeat(' ', 20);

            $client_code = str_pad($data_client_code, 20, " ", STR_PAD_RIGHT);

            $no_use_9 = str_repeat(' ', 10);
            
            $client_account_id = str_pad($data_client_account_no, 20, " ", STR_PAD_RIGHT);
            $no_use_11 = str_repeat(' ', 10);
            $no_use_12 = str_repeat(' ', 10);
            $no_use_13 = str_repeat(' ', 10);
            $no_use_14 = str_repeat(' ', 10);
            $no_use_15 = str_repeat(' ', 20);
            $no_use_16 = str_repeat(' ', 10);
            $no_use_17 = str_repeat(' ', 10);
            $no_use_18 = str_repeat(' ', 10);
            $no_use_19 = str_repeat(' ', 255);
            $no_use_20 = str_repeat(' ', 10);

            $p_data = $record_identifier . $product_code . $no_use_3 . $no_use_4 . $no_use_5 . $no_use_6 . $no_use_7 . $client_code . $no_use_9 . $client_account_id . $no_use_11 . $no_use_12 . $no_use_13 . $no_use_14 . $no_use_15 . $no_use_16 . $no_use_17 . $no_use_18 . $no_use_19 . $no_use_20;
            //echo strlen($p_data);exit;
            /*****I row data************/
            $data_benef_bank_code = "004";
            $data_benef_branch_code = "0040745";
            $record_identifier = $data_i = "I";
            $export_date = $order_date;

            $tot_order = \App\OrderShop::where(DB::raw('date(end_shopping_date)'),$export_date)
                                ->where('order_status','!=',4)
                                ->where('payment_status',1)
                                ->whereIn('shop_id',$shop_id_arr)
                                ->count();

            $seller_order_data = \App\OrderShop::where(DB::raw('date(end_shopping_date)'),$export_date)
                                ->where('order_status','!=',4)
                                ->where('payment_status',1)
                                ->select(DB::raw('sum(total_final_price) as totPrice ,count(order_id) as totorder'),'shop_user_id','end_shopping_date','shop_json')
                                ->with('getSellerDetail')
                                ->groupBy('shop_id')
                                ->whereIn('shop_id',$shop_id_arr)
                                ->get();
           
            $total_order_amt = 0;
            $i_data = '';
            if($seller_order_data && count($seller_order_data)){
                foreach ($seller_order_data as $key => $value) {
                    $total_order_amt = $total_order_amt + $value->totPrice;
                    $shop_user_id = $value->shop_user_id;
                    $bank_id = $value->getSellerDetail->bank_id;
                    $bank_branch_id = $value->getSellerDetail->bank_branch_id;
                    $bank_code = $bank_branch_code = '';
                    if($bank_id){
                        $bank_code = \App\PaymentBank::where('id',$bank_id)->value('bank_code');
                    }

                    $bank_branch_code = $value->getSellerDetail->branch_code;
                    $record_identifier = $data_i = "I";
                    $no_use_2 = str_repeat(' ', 20);
                    $no_use_3 = str_repeat(' ', 10);

                    $shop_json_arr = json_decode($value->shop_json,true);
                    $seller_name = $value->getSellerDetail->account_name;
                    $seller_name_len = mb_strlen($seller_name,'UTF-8');
                    $rest_seller_name = str_repeat(' ', 80-$seller_name_len);
                    $benef_desc = $seller_name.$rest_seller_name;
                    
                    $no_use_5 = str_repeat(' ', 10);
                    $no_use_6 = str_repeat(' ', 10);
                    $no_use_7 = str_repeat(' ', 10);
                    $no_use_8 = str_repeat(' ', 20);
                    
                    $inst_payment_amnt = sprintf("%'020.2f", $value->totPrice);

                    $no_use_10 = str_repeat(' ', 20);
                    
                    $inst_date = str_pad(date('d/m/Y',strtotime($value->end_shopping_date)), 10, " ", STR_PAD_RIGHT);
                    $branch_code_format = $bank_code.sprintf('%04d', $bank_branch_code);
                    $benef_bank_code = str_pad($bank_code, 10, " ", STR_PAD_RIGHT);
                    $benef_branch_code = str_pad($branch_code_format, 10, " ", STR_PAD_RIGHT);
                    $acc_no = str_replace('-', '', $value->getSellerDetail->account_no);
                    $benef_bank_acc_no = str_pad($acc_no, 20, " ", STR_PAD_RIGHT);

                    $no_use_15 = str_repeat(' ', 16);
                    $no_use_16 = str_repeat(' ', 4);
                    $no_use_17 = str_repeat(' ', 150);
                    $no_use_18 = str_repeat(' ', 150);
                    $no_use_19 = str_repeat(' ', 255);
                    $delivery_mode = str_repeat(' ', 10);
                    $no_use_21 = str_repeat(' ', 10);
                    $no_use_22 = str_repeat(' ', 10);
                    $no_use_23 = str_repeat(' ', 1);
                    $no_use_24 = str_repeat(' ', 1);
                    $no_use_25 = str_repeat(' ', 20);
                    $no_use_26 = str_repeat(' ', 20);
                    $no_use_27 = str_repeat(' ', 10);
                    $no_use_28 = str_repeat(' ', 24);
                    $no_use_29 = str_repeat(' ', 20);
                    $no_use_30 = str_repeat(' ', 20);
                    $no_use_31 = str_repeat(' ', 20);
                    
                    //$payee_name = str_pad($value->getSellerDetail->account_name, 120, " ", STR_PAD_RIGHT);

                    $payee_len = 120 - mb_strlen($value->getSellerDetail->account_name, 'UTF-8');
                    $rest_space_name = str_repeat(' ', $payee_len);
                    $payee_name = $value->getSellerDetail->account_name.$rest_space_name;
                    
                    $no_use_33 = str_repeat(' ', 20);
                    $no_use_34 = str_repeat(' ', 54);
                    $no_use_35 = str_repeat(' ', 2);
                    $no_use_36 = str_repeat(' ', 1720);
                    $no_use_37 = str_repeat(' ', 1);
                    $no_use_38 = str_repeat(' ', 255);
                    $no_use_39 = str_repeat(' ', 1);
                    $no_use_40 = str_repeat(' ', 10);
                    $no_use_41 = str_repeat(' ', 20);
                    
                    $beneficiary_pickup_location_code = str_repeat(' ', 30);

                    $no_use_43 = str_repeat(' ', 50);
                    $no_use_44 = str_repeat(' ', 50);

                    $i_data .= $record_identifier . $no_use_2 . $no_use_3 . $benef_desc . $no_use_5 . $no_use_6 . $no_use_7 . $no_use_8 . $inst_payment_amnt . $no_use_10 . $inst_date   . $benef_bank_code . $benef_branch_code . $benef_bank_acc_no .$no_use_15. $no_use_16 . $no_use_17 . $no_use_18 . $no_use_19 . $delivery_mode . $no_use_21 . $no_use_22 . $no_use_23 . $no_use_24 . $no_use_25 . $no_use_26 . $no_use_27 . $no_use_28 . $no_use_29 . $no_use_30 . $no_use_31 . $payee_name . $no_use_33 . $no_use_34 . $no_use_35 . $no_use_36 . $no_use_37 . $no_use_38 . $no_use_39 . $no_use_40 . $no_use_41 . $beneficiary_pickup_location_code . $no_use_43 . $no_use_44;

                    $i_data .="\n";
                }
            }
            
            /******T row data*****************/
            $data_t = "T";

            $record_identifier = $data_t;
            $no_use_2 = str_repeat(' ', 5);
            $no_use_3 = str_repeat(' ', 20);
            $no_use_4 = str_repeat(' ', 5);
            $no_use_5 = str_repeat(' ', 20);
            $t_data = $record_identifier . $no_use_2 . $no_use_3 . $no_use_4 . $no_use_5;

            $main_data = $h_data."\n".$p_data."\n".$i_data.$t_data;
            if($eny_type=='with_enc'){
                $main_data = iconv('utf8', 'tis620', $main_data);
                $pub_path = Config::get('constants.public_path');
                $key_path = @file_get_contents($pub_path.'/KBankH2HPGPProdKey.asc');
                $gpg = new \gnupg();
                $gpg->seterrormode(\gnupg::ERROR_EXCEPTION);
                $info_key = $gpg->import($key_path);
                $fingerprint = $info_key['fingerprint'];
                $gpg->addencryptkey($fingerprint);
                $main_data = $gpg->encrypt($main_data);

                /*$dec_check = $gpg_dep->adddecryptkey($fingerprint,'');
                $dec =  $gpg_dep->decrypt($main_data);*/
            }
            
            if($tot_order){
                $exp_log_count = \App\OrderExportLog::where(DB::raw('date(order_date)'),$export_date)->count();
                $exp_no = $exp_log_count ? $exp_log_count : 0;
                $ref_no = sprintf("%03d", $exp_no);

                $file_path = Config::get('constants.public_path');

            
                $client_code = $data_client_code;
                $date = str_replace('-', '', $export_date);
                $file_name = 'P-'.$client_code.'-'.$date.'-'.$ref_no.'.txt.gpg';
                $file = $file_path.'/seller-payment/'.$file_name;
                \File::put($file,$main_data);
                $all_file_path[] = $file;

                $log_obj = new \App\OrderExportLog;
                $log_obj->total_order = $tot_order;
                if($tot_order){
                    $log_obj->shop_ids = implode(',', $shop_id_arr);
                    $log_obj->bank_type = $bank_name_key;
                }
                $log_obj->file_name = $file_name;
                $log_obj->total_seller = count($seller_order_data);
                $log_obj->total_amount = $total_order_amt;
                $log_obj->status = 'pending';
                $log_obj->order_date = date('Y-m-d',strtotime($export_date));
                $log_obj->save();
                $log_id = $log_obj->id;
                $log_id_arr[] = $log_id;
            }
            
        }//end bank_arr foreach
        if($all_file_path){
            $zip = new \ZipArchive();
            $zip_file = uniqid().'.zip';
            $file_path = Config::get('constants.public_path').'/seller-payment/';
            if ($zip->open(Config::get('constants.public_path') . '/seller-payment/' . $zip_file, \ZipArchive::CREATE) == TRUE) {
                
                foreach ($all_file_path as $file_data) {
                    $file_full_path = $file_data;
                    $relativeName = basename($file_full_path);
                    if(file_exists($file_full_path)){
                        $zip->addFile($file_full_path, $relativeName);
                    }
                    
                }
                $zip->close();
                
                return redirect()->action('Admin\Transaction\ShopOrderController@sellerOrder', array('filter_date' => $order_date, 'zip_file' => $zip_file))->with('succMsg', 'Order exported successfully!');
                
            }
        }
        
    }
    
    public function importTxt(Request $request){
        $file_data = $request->import_file;
        if($file_data){
            $file_path = Config::get('constants.public_path').'/seller-payment-response';
            if(!is_dir($file_path)) {               
                mkdir($file_path, 0777, true);               
            }
            $ext = pathinfo($file_data->getClientOriginalName(), PATHINFO_EXTENSION);
            if($ext){
                $ret_url = action('Admin\Transaction\ShopOrderController@sellerOrder');
                if($request->filter_date){
                    $ret_url .='?filter_date='.$request->filter_date;
                }
                return redirect($ret_url)->with('errorMsg', Lang::get('admin_order.invalid_file'));
            }
            $file_name = $file_data->getClientOriginalName().'_'.date('Ymdhis');

            $file_data->move($file_path, $file_name);

            $handle = fopen($file_path."/".$file_name, "r");
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    $line = str_replace("\n", "", $line);
                    $part_identifier = substr($line, 0,1);
                    echo 'part_identifier : '.$part_identifier.'<br>';
                    $credit_detail_no = substr($line, 1,20);
                    echo 'credit_detail_no : '.$credit_detail_no.'<br>';
                    $payee_name = substr($line, 21,120);
                    echo 'payee_name : '.$payee_name.'<br>';
                    $credit_amount = substr($line, 141,13);
                    echo 'credit_amount : '.$credit_amount.'<br>';
                    $amount_ccy = substr($line, 154,3);
                    echo 'amount_ccy : '.$amount_ccy.'<br>';
                    $account_no = substr($line, 157,20);
                    echo 'account_no : '.$account_no.'<br>';
                    $ref_no = substr($line, 177,16);
                    echo 'ref_no : '.$ref_no.'<br>';
                    $status = substr($line, 193,2);
                    echo 'status : '.$status.'<br>';
                    $status_desc = substr($line, 195,35);
                    echo 'status_desc : '.$status_desc.'<br>';
                    $batch_ref = substr($line, 303,16);
                    echo 'batch_ref : '.$batch_ref.'<br>';
                    $client_code = substr($line, 319,10);
                    echo 'client_code : '.$client_code.'<br>';
                    $product_code = substr($line, 329,3);
                    echo 'product_code : '.$product_code.'<br>';
                    exit;
                }
                fclose($handle);
                
            }

            $log_obj = new \App\OrderExportLog;
            $log_obj->file_name = $file_name;
            $log_obj->file_type = 'response';
            $log_obj->status = 'response';
            $log_obj->save();
            $log_id = $log_obj->id;

            return redirect($ret_url)->with('succMsg', Lang::get('admin_order.file_import_successfully'));

            dd($file_data);
        }
    }
    
    function update(Request $request){
    }
    
}
