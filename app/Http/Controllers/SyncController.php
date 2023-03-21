<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\AESEncription;
use Image;
use Session;
use Lang;
use DB;
use App\SyncLog;

class SyncController extends MarketPlace
{

    
    public function __construct(){

    }

    public function itemcsv(Request $request){
        exit;
        $public_path = \Config('constants.public_path');
        $item_csv = $public_path.'/seller_csv/Customer.xlsxItem.csv';
        $row = 1;
        if (($handle = fopen($item_csv, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if($row == 1){ $row++; continue; }
                $row++;
                $insert_arr = [];
                $insert_arr['panel_id'] = $data[0];
                $insert_arr['seller_unique_id'] = $data[7];
                $insert_arr['description'] = $data[1];
                $insert_arr['base_unit_of_measure'] = $data[2];
                $insert_arr['type'] = $data[3];
                $insert_arr['unit_price'] = $data[4];
                $insert_arr['property'] = $data[5];
                $insert_arr['sales_status'] = $data[6];
                $insert_arr['customer_name'] = $data[8];
                $insert_arr['property_item_type'] = $data[9];
                $insert_arr['area_code'] = $data[10];
                $insert_arr['area_group_code'] = $data[11];
                $insert_arr['unit_size'] = $data[12];
                $insert_arr['cust_no_name'] = $data[13];

                $insert = DB::table('seller_data_item')->insert($insert_arr);
                
            }
        }
        dd('ff');
    }

    public function customercsv(Request $request){
        exit;
        $public_path = \Config('constants.public_path');
        $item_csv = $public_path.'/seller_csv/Customer.xlsxSeller.csv';
        $row = 1;
        if (($handle = fopen($item_csv, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                
                if($row == 1){ $row++; continue; }
                $row++;

                $insert_arr = [];
                $insert_arr['seller_unique_id'] = $data[0];
                $insert_arr['citizen_id'] = $row;//$data[43];
                $insert_arr['name'] = $data[1];
                $insert_arr['search_name'] = $data[2];
                $insert_arr['name_two'] = $data[3];
                $insert_arr['address'] = $data[4];
                $insert_arr['address_two'] = $data[5];
                $insert_arr['city'] = $data[6];
                $insert_arr['contact'] = $data[7];
                $insert_arr['phone_no'] = $data[8];
                $insert_arr['country'] = $data[9];
                $insert_arr['price_inc_vat'] = $data[10];
                $insert_arr['location_code'] = $data[11];
                $insert_arr['fax_no'] = $data[12];
                $insert_arr['mobile_phone_no'] = $data[13];
                $insert_arr['gen_bus_posting_group'] = $data[14];
                $insert_arr['post_code'] = $data[15];
                $insert_arr['date_of_birth'] = trim($data[16])?date('Y-m-d',strtotime($data[16])):null;
                $insert_arr['birthday'] = $data[17];
                $insert_arr['sex'] = $data[18];
                $insert_arr['race'] = $data[19];
                $insert_arr['nationality'] = $data[20];
                $insert_arr['religion'] = $data[21];
                $insert_arr['place_of_birth'] = $data[22];
                $insert_arr['date_of_issue'] = trim($data[23])?date('Y-m-d',strtotime($data[23])):null;
                $insert_arr['date_of_expiry'] = trim($data[24])?date('Y-m-d',strtotime($data[24])):null;
                $insert_arr['marital_status'] = $data[25];
                $insert_arr['spouse'] = $data[26];
                $insert_arr['father_name'] = $data[27];
                $insert_arr['mother_name'] = $data[28];
                $insert_arr['ref_person'] = $data[29];
                $insert_arr['ref_person_address'] = $data[30];
                $insert_arr['ref_person_address_two'] = $data[31];
                $insert_arr['black_list'] = $data[32];
                $insert_arr['age'] = $data[33];
                $insert_arr['weight'] = $data[34];
                $insert_arr['height'] = $data[35];
                $insert_arr['member_no'] = $data[36];
                $insert_arr['bar_code'] = $data[37];
                $insert_arr['address_second'] = $data[38];
                $insert_arr['address_second_three'] = $data[39];
                $insert_arr['address_three'] = $data[40];
                $insert_arr['city_four'] = $data[41];
                $insert_arr['post_code_five'] = $data[42];
                $insert_arr['status'] = $data[44];
                $insert_arr['customer_group'] = $data[45];
                $insert_arr['customer_phone_no'] = $data[46];
                

                $insert = DB::table('seller_data_customer')->insert($insert_arr);
                //dd($data);
            }
        }
        dd('ff');
    }

    public function sellerdata(Request $request){
        exit;
        $item_data = DB::table('seller_data_item')->get();
        $public_path = \Config('constants.public_path');
        $item_csv = $public_path.'/seller_csv/Customer.xlsxSeller.csv';
        $row = 1;
            foreach ($item_data as $key => $value) {
                # code...
                $insert_arr = [];
                $insert_arr['panel_id'] = $value->panel_id;
                $insert_arr['seller_unique_id'] = $value->seller_unique_id;
                $insert_arr['description'] = $value->description;
                $insert_arr['base_unit_of_measure'] = $value->base_unit_of_measure;
                $insert_arr['type'] = $value->type;
                $insert_arr['unit_price'] = $value->unit_price;
                $insert_arr['property'] = $value->property;
                $insert_arr['sales_status'] = $value->sales_status;
                $insert_arr['customer_name'] = $value->customer_name;
                $insert_arr['property_item_type'] = $value->property_item_type;
                $insert_arr['area_code'] = $value->area_code;
                $insert_arr['area_group_code'] = $value->area_group_code;
                $insert_arr['unit_size'] = $value->unit_size;
                $insert_arr['cust_no_name'] = $value->cust_no_name;

                $seller_data = DB::table('seller_data_customer')->where('seller_unique_id',$value->seller_unique_id)->first();
                
                if(!empty($seller_data)){
                    $insert_arr['citizen_id'] = $seller_data->citizen_id;
                    $insert_arr['name'] = $seller_data->name;
                    $insert_arr['search_name'] = $seller_data->search_name;
                    $insert_arr['name_two'] = $seller_data->name_two;
                    $insert_arr['address'] = $seller_data->address;
                    $insert_arr['address_two'] = $seller_data->address_two;
                    $insert_arr['city'] = $seller_data->city;
                    $insert_arr['contact'] = $seller_data->contact;
                    $insert_arr['phone_no'] = $seller_data->phone_no;
                    $insert_arr['country'] = $seller_data->country;
                    $insert_arr['price_inc_vat'] = $seller_data->price_inc_vat;
                    $insert_arr['location_code'] = $seller_data->location_code;
                    $insert_arr['fax_no'] = $seller_data->fax_no;
                    $insert_arr['mobile_phone_no'] = $seller_data->mobile_phone_no;
                    $insert_arr['gen_bus_posting_group'] = $seller_data->gen_bus_posting_group;
                    $insert_arr['post_code'] = $seller_data->post_code;
                    $insert_arr['date_of_birth'] = $seller_data->date_of_birth;
                    $insert_arr['birthday'] = $seller_data->birthday;
                    $insert_arr['sex'] = $seller_data->sex;
                    $insert_arr['race'] = $seller_data->race;
                    $insert_arr['nationality'] = $seller_data->nationality;
                    $insert_arr['religion'] = $seller_data->religion;
                    $insert_arr['place_of_birth'] = $seller_data->place_of_birth;
                    $insert_arr['date_of_issue'] = $seller_data->date_of_issue;
                    $insert_arr['date_of_expiry'] = $seller_data->date_of_expiry;
                    $insert_arr['marital_status'] = $seller_data->marital_status;
                    $insert_arr['spouse'] = $seller_data->spouse;
                    $insert_arr['father_name'] = $seller_data->father_name;
                    $insert_arr['mother_name'] = $seller_data->mother_name;
                    $insert_arr['ref_person'] = $seller_data->ref_person;
                    $insert_arr['ref_person_address'] = $seller_data->ref_person_address;
                    $insert_arr['ref_person_address_two'] = $seller_data->ref_person_address_two;
                    $insert_arr['black_list'] = $seller_data->black_list;
                    $insert_arr['age'] = $seller_data->age;
                    $insert_arr['weight'] = $seller_data->weight;
                    $insert_arr['height'] = $seller_data->height;
                    $insert_arr['member_no'] = $seller_data->member_no;
                    $insert_arr['bar_code'] = $seller_data->bar_code;
                    $insert_arr['address_second'] = $seller_data->address_second;
                    $insert_arr['address_second_three'] = $seller_data->address_second_three;
                    $insert_arr['address_three'] = $seller_data->address_three;
                    $insert_arr['city_four'] = $seller_data->city_four;
                    $insert_arr['post_code_five'] = $seller_data->post_code_five;
                    $insert_arr['status'] = $seller_data->status;
                    $insert_arr['customer_group'] = $seller_data->customer_group;
                    $insert_arr['customer_phone_no'] = $seller_data->customer_phone_no;
                }
                

                $insert = DB::table('seller_data')->insert($insert_arr);
                
            }
            dd('ff');    //dd($data);

    }

    public function newsellerdata(Request $request){
        
        $public_path = \Config('constants.public_path');
        $item_csv = $public_path.'/seller_csv/insert_seller_data_new.csv';
        $row = 1;
        $fp = fopen('duplicate_seller.csv', 'w');
        if (($handle = fopen($item_csv, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if($row == 1){ $row++; continue; }
                $row++;
                $panel_id = $data[3];
                $citizen_id = $data[7];
                $check_duplicate = \App\SellerData::where(['panel_id'=>$panel_id,'citizen_id'=>$citizen_id])->count();
                if($check_duplicate){
                    fputcsv($fp, $data);
                }else{
                    $insert_arr = [];
                    $insert_arr['sales_status'] = $data[0];
                    $insert_arr['area_code'] = $data[1];
                    $insert_arr['area_group_code'] = $data[2];
                    $insert_arr['panel_id'] = $data[3];
                    $insert_arr['description'] = $data[4];
                    $insert_arr['customer_name'] = $data[5];
                    $insert_arr['seller_unique_id'] = $data[6];
                    $insert_arr['citizen_id'] = $data[7];
                    
                    $insert = \App\SellerData::insert($insert_arr);
                }
            }
        }
        fclose($fp);
        dd('success');
    }
             
}



