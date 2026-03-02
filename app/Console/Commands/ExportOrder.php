<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Config;
class ExportOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ExportOrder:exportOrder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It will download order file';

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
        
        $data_h = "H";
        $data_p = "P";
        $data_p_product_code = "DCT";

        $data_client_code = getConfigValue('CLIENT_CODE_FOR_ORDER_EXPORT_FILE');
        $data_client_account_no = "0683431869";

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
        $export_date = date('Y-m-d',strtotime("-1 days"));

        $tot_order = \App\Order::where(DB::raw('date(end_shopping_date)'),$export_date)
                            ->where('order_status','!=',4)
                            ->where('payment_status',1)
                            ->count();

        $seller_order_data = \App\OrderShop::where(DB::raw('date(end_shopping_date)'),$export_date)
                            ->where('order_status','!=',4)
                            ->where('payment_status',1)
                            ->select(DB::raw('sum(total_final_price) as totPrice ,count(order_id) as totorder'),'shop_user_id','end_shopping_date','shop_json')
                            ->with('getSellerDetail')
                            ->groupBy('shop_id')
                            ->get();
       
        $total_order_amt = 0;
        $i_data = '';
        if($seller_order_data && count($seller_order_data)){
            foreach ($seller_order_data as $key => $value) {
                $total_order_amt = $total_order_amt + $value->totPrice;

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

                $benef_bank_code = str_pad($data_benef_bank_code, 10, " ", STR_PAD_RIGHT);
                $benef_branch_code = str_pad($data_benef_branch_code, 10, " ", STR_PAD_RIGHT);

                $benef_bank_acc_no = str_pad($value->getSellerDetail->account_no, 20, " ", STR_PAD_RIGHT);

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
        $main_data = iconv('utf8', 'tis620', $main_data);

        if($tot_order){
            $exp_log_count = \App\OrderExportLog::where(DB::raw('date(order_date)'),$export_date)->count();
            $exp_no = $exp_log_count ? $exp_log_count : 0;
            $ref_no = sprintf("%03d", $exp_no);

            $file_path = Config::get('constants.public_path');

        
            $client_code = $data_client_code;
            $date = str_replace('-', '', $export_date);
            $file_name = 'P-'.$client_code.'-'.$date.'-'.$ref_no;
            $file = $file_path.'/seller-payment/'.$file_name.'.txt';
            \File::put($file,$main_data);

            $log_obj = new \App\OrderExportLog;
            $log_obj->total_order = $tot_order;
            $log_obj->file_name = $file_name;
            $log_obj->total_seller = count($seller_order_data);
            $log_obj->total_amount = $total_order_amt;
            $log_obj->status = 'pending';
            $log_obj->order_date = date('Y-m-d H:i:s',strtotime($export_date.date('H:i:s')));
            $log_obj->save();
        }

    }
  
} 