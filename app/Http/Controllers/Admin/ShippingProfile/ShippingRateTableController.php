<?php    

namespace App\Http\Controllers\Admin\ShippingProfile;

use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\AESEncription;
use App\ShippingProfile;
use App\Language;
use App\ShippingProfileDesc;
use App\ShippingProfileRates;
use App\ShippingProfileRatesDesc;
use App\ShippingProfileCountry;
use App\ShipppingProfileProvince;
use App\ShipppingProfileProduct;
use App\ShippingProfileLog;
use App\Country;
use App\CountryDesc;
use App\CountryProvinceState;
use App\CountryProvinceStateDesc;
use App\CountryCityDistrict;
use App\CountryCityDistrictDesc;
use App\CountrySubDistrict;
use App\CountrySubDistrictDesc;
use Auth;
use Lang; 
use Config;
use File;
use Exception;

class ShippingRateTableController extends MarketPlace {

    public $tableShippingDesc;
    public $tableShipping;
    public $server_url;
    public $server_port;
    public $client_key;

    public function __construct() {
        //$CategoryDesc->getTable();    
        $this->middleware('admin.user');
        $this->tableShipping = with(new ShippingProfile)->getTable();
        $this->tableShippingDesc = with(new ShippingProfileDesc)->getTable();
    }

    public function index(){

        $permission = $this->checkUrlPermission('shipping_profile_list');
        if($permission === true) {
            $permission_arr['add'] = $this->checkMenuPermission('add_shipping_profile');
            $permission_arr['edit'] = $this->checkMenuPermission('edit_shipping_profile');
            $permission_arr['delete'] = $this->checkMenuPermission('delete_shipping_profile');
            
            $fielddata = $this->setTableGrid();
           
            $type = "shipping-rate-table";
            $shippingRateDataList = ShippingProfile::where('shipping_type',$type)->with('getShippingProfileDesc')->get();
            
            //dd($shippingRateDataList);
            return view('admin.shippingProfile.shipping_rate_list', ['shippingRateDataList'=>$shippingRateDataList, 'permission_arr'=>$permission_arr,'session_data'=>session('additional_msg'),'fielddata'=>$fielddata]);

            //return view('admin.shippingProfile.listShippingRateTableProfile', ['shippingRateDataList'=>$shippingRateDataList, 'permission_arr'=>$permission_arr,'session_data'=>session('additional_msg'),'fielddata'=>$fielddata]);
        }

    }

    public function listData() {

        $data_res = array();
        $data_lists = ShippingProfile::select('id', 'shipping_key', 'shipping_type', 'status', 'item_paticipant', 'updated_at')->orderBy('id','Desc')->paginate();
        $mainarray = [];
        foreach ($data_lists as $key => $val) {

            if ($val->shipping_type == 'table-rate') {
                $shipType = 'Table rate';
            } elseif ($val->shipping_type == 'flat-rate') {
                $shipType = 'Flat rate';
            } else {
                $shipType = 'Free Shipping';
            }

            $data_lists[$key]->shipping_name = $val->getShippingProfileDesc->name;

            $data_lists[$key]->shipping_type = $shipType;
            //$data_lists[$key]->updated_at = getDateFormat($val->updated_at, '1');
            $data_lists[$key]->edit_url = action('Admin\ShippingProfile\ShippingProfileController@edit', $val->id);
            $data_lists[$key]->delete_url = action('Admin\ShippingProfile\ShippingProfileController@destroy', $val->id);

        }
        return $data_lists;
        // echo json_encode($mainarray);
        // die;
    }

    public function deliveryAtAddress(Request $request){
        $error = false;
        $id = '1';
        $shippingRateData = ShippingProfile::with(
            ['getAllShippingProfileRates'=> function ($query) use ($id){
                $query->where('shipping_profile_id', $id)->with(['getRatesDescription']);
            },'getShippingProfileDesc']
        )->first();
        //dd($shippingRateData);
        
        // The following code will get all the active profiles of customer | Start
        $lang_id = session('default_lang');
        $costomerProfiles = \App\CustomerGroup::select('id')->where('status','1')->with(['getCustGroupDesc'=> function ($query) use ($lang_id) {
                                $query->where('lang_id', $lang_id);
        }])->get();

        $custGroup = array();
        foreach($costomerProfiles as $cust_key => $customer){
            $custGroup[$cust_key]['id'] = $customer->id;
            $custGroup[$cust_key]['group_name'] = isset($customer->getCustGroupDesc->group_name)?$customer->getCustGroupDesc->group_name:'';
        }
        // The following code will get all the active profiles of customer | End
        $rates = array();
        
        foreach($shippingRateData->getAllShippingProfileRates as $key => $rate){
            if($rate->country_id!="*"){
                $countryData = CountryDesc::select('country_name')->where(['country_id'=>$rate->country_id,'lang_id'=>1])->first();
                $rates[$key]['country_name'] = (isset($countryData->country_name)) ? $countryData->country_name : '';
            }else{
                $rates[$key]['country_name'] = $rate->country_id;
            }

            if(isset($rate->getRatesDescription) && $rate->getRatesDescription!=null){
                $rates[$key]['state_name'] = $rate->getRatesDescription->province_state;
                $rates[$key]['disctrict_name'] = $rate->getRatesDescription->district_city;
                $rates[$key]['sub_district_name'] = $rate->getRatesDescription->sub_district;
            }else{
                $rates[$key]['state_name'] = '';
                $rates[$key]['disctrict_name'] = '';
                $rates[$key]['sub_district_name'] = '';
            }
            
            
            $rates[$key]['zip_from'] = $rate->zip_from;
            $rates[$key]['zip_to'] = $rate->zip_to;
            $rates[$key]['weight_from'] = $rate->weight_from;
            $rates[$key]['weight_to'] = $rate->weight_to;
            $rates[$key]['qty_from'] = $rate->qty_from;
            $rates[$key]['qty_to'] = $rate->qty_to;
            $rates[$key]['price_from'] = $rate->price_from;
            $rates[$key]['price_to'] = $rate->price_to;
            $rates[$key]['product_type_id'] = $rate->product_type_id;
            $rates[$key]['base_rate_for_order'] = $rate->base_rate_for_order;
            $rates[$key]['percentage_rate_per_product'] = $rate->percentage_rate_per_product;
            $rates[$key]['fixed_rate_per_product'] = $rate->fixed_rate_per_product;
            $rates[$key]['fixed_rate_per_unit_weight'] = $rate->fixed_rate_per_unit_weight;
            $rates[$key]['priority'] = $rate->priority;
            $rates[$key]['id'] = $rate->id;
        }

       //dd($rates);

        $ratesFieldData = $this->setRatesTableGrid();
        $delivery_time = \App\DeliveryTime::getDeliveryTime();
        if($delivery_time){
            $delivery_time->time_slot = explode(',', $delivery_time->time_slot);
        }

        $filter = $this->getFilter('shipping-profile-updateMethod');
        return view('admin.shippingProfile.updateMethod', ['shippingRateData'=>$shippingRateData,'rates'=>$rates,'custGroup'=>$custGroup,'fielddata'=>$ratesFieldData,'session_data'=>session('additional_msg'),'session_rates'=>session('additional_rate_msg'),'delivery_time'=>$delivery_time,'search'=>isset($request->searchtxt)?$request->searchtxt:'', 'search_type'=>isset($request->search_type)?$request->search_type:'','filter'=>$filter]);
    }

    public function saveShippingRateProfile(Request $request){
        $shippingProfileData = ShippingProfile::find($request->shipping_profile_id);
        $sp_old = (object)$shippingProfileData->toArray();
        $update_arr = [];
        if(isset($request->shipping_logo)){
            $fileobject  = $request->shipping_logo;
            $original_file_name = $fileobject->getClientOriginalName();
            $extension = $fileobject->getClientOriginalExtension();
            $dir_path = Config::get('constants.files_path');  
            $folder_name = "shipping_profile_logo";
            $shipping_logo_name = 'ship_'.md5(microtime()).'_'.$original_file_name;
            $shipping_logo_withpath = $dir_path.'/'.$folder_name.'/'.$shipping_logo_name;                                 
            $this->checkDirectoryExists($folder_name,$dir_path);   
            $this->UploadImage($shipping_logo_name, $fileobject, $dir_path.'/'.$folder_name);
            $shippingProfileData->logo = Config::get('constants.public_url')."/files/".$folder_name.'/'.$shipping_logo_name;
        }
        $shippingProfileData->created_by = Auth::guard('admin_user')->user()->id;
        $shippingProfileData->updated_by = Auth::guard('admin_user')->user()->id;
        $shippingProfileData->comment = $request->comment;
        $shippingProfileData->status = $request->status;
        $shippingProfileData->minimal_rate = $request->minimal_rate;
        $shippingProfileData->maximal_rate = $request->maximal_rate;
        $shippingProfileData->customer_group = (isset($request->customer_group)) ? implode(',', $request->customer_group) :'';
        $shippingProfileData->shipping_calculation_type = $request->shipping_calculation_type;
        $shippingProfileData->use_dimension_weight = isset($request->use_dimension_weight)?'1':'0';
        $shippingProfileData->dimension_factor = $request->dimension_factor;
        $shippingProfileData->updated_at = date('Y-m-d H:i:s');

        if($sp_old->minimal_rate != $shippingProfileData->minimal_rate) {
            $update_arr['minimal_rate'] = $sp_old->minimal_rate.' => '.$shippingProfileData->minimal_rate;
        }
        if($sp_old->maximal_rate != $shippingProfileData->maximal_rate) {
            $update_arr['maximal_rate'] = $sp_old->maximal_rate.' => '.$shippingProfileData->maximal_rate;
        }
        if($sp_old->shipping_calculation_type != $shippingProfileData->shipping_calculation_type) {
            $update_arr['shipping_calculation_type'] = $sp_old->shipping_calculation_type.' => '.$shippingProfileData->shipping_calculation_type;
        }
        if($sp_old->use_dimension_weight != $shippingProfileData->use_dimension_weight) {
            $update_arr['use_dimension_weight'] = $sp_old->use_dimension_weight.' => '.$shippingProfileData->use_dimension_weight;
        }
        if($sp_old->dimension_factor != $shippingProfileData->dimension_factor) {
            $update_arr['dimension_factor'] = $sp_old->dimension_factor.' => '.$shippingProfileData->dimension_factor;
        }

        //dd($shippingProfileData,$request->all());
        // 
        if(isset($request->csv_rates) && !empty($request->csv_rates)){
            $is_csv_imported = $this->uploadCsvRates($request->csv_rates,$request->delete_existing,$shippingProfileData->id);
        }else{
            $is_csv_imported = array();
        }

        if($shippingProfileData->update()){

            ShippingProfileDesc::where('shipping_profile_id',$request->shipping_profile_id)->delete();
            $languages = Language::select('id')->get();

            foreach($languages as $lng_key => $lang){
                $data[$lng_key] = ["shipping_profile_id" => $request->shipping_profile_id, "lang_id" => $lang->id, "name" => $request->name];
            }
            ShippingProfileDesc::insert($data);

            $request->delivery_type = 'buyer_address';
            $update_delivery_time = \App\DeliveryTime::updateDeliveryTime($request);

            if($update_arr){
                $change_log = ['shipping_profile_id'=>$shippingProfileData->id,  'update_detail'=>$update_arr];
                ShippingProfileLog::updateShippingChangeLog($change_log);
            }

            /*update activity log start*/
            $action_type = "Edit";
            $module_name = "Shipping Table Rates";   //Changes module name like : blog etc         
            $logdetails = "Admin has updated shipping rate profile"; //Change update message as requirement 
            $old_data = ""; //Optional old data in json format key and value as per requirement 
            $new_data = ""; //Optional new data json format key and value as per requirement 

            //Prepaire array for send data
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails,'old_data' =>$old_data,'new_data' =>$new_data );

            //Call method in module
            $this->updateLogActivity($logdata);
            /*update activity log end*/
            
            $return_url = redirect()->action('Admin\ShippingProfile\ShippingRateTableController@deliveryAtAddress')->with(['succMsg'=> 'Shipping rate has been saved.','additional_msg'=>$is_csv_imported]); 
        }
        return $return_url;
    }

    /**
    * This function will upload csv files to add shipping Table rates | Start 
    * $csv_file is a csv file variable which contains rates data and $option is it delete old data or have old data
    */
    protected function uploadCsvRates($csv_file, $option, $shipping_profile_id){

            $temp_path = $csv_file->getRealPath();
           
            $file = fopen($temp_path, "r");
            $csvDataArray = array();
            while (($getData = fgetcsv($file, 10000, ",")) !== FALSE)
            {
                $csvDataArray[] = $getData;
                
            }

            $header_row = array(); 
            $data_rows = array();
            foreach($csvDataArray as $key => $csv_row){

                if($key==0){
                    $header_row = $csv_row;
                }else{
                    $data_rows[($key-1)] = $csv_row;
                }
            }
            //dd($header_row);
            // This function will validate country, state , district, and sub district with system database
            //dd($data_rows,$header_row);
            $validatedDaata = $this->validateCsvData($data_rows,$header_row); 
            $saveCsvData = $this->processSaveCsvData($validatedDaata,$data_rows,$shipping_profile_id,$option);

            /*update activity log start*/
            $action_type = "Import";
            $module_name = "Shipping Table Rates";   //Changes module name like : blog etc         
            $logdetails = "Admin has imported  shipping table rate csv "; //Change update message as requirement 
            $old_data = ""; //Optional old data in json format key and value as per requirement 
            $new_data = ""; //Optional new data json format key and value as per requirement 

            //Prepaire array for send data
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails,'old_data' =>$old_data,'new_data' =>$new_data );

            //Call method in module
            $this->updateLogActivity($logdata);
            /*update activity log end*/

            return $saveCsvData;
    }

    /**
    * This function will upload csv files to add shipping Table rates |
    * $csv_file is a csv file variable which contains rates data and $option is it delete old data or have old data
    */

    protected function processSaveCsvData($validatedDaata, $original_data,$shipping_profile_id, $delete_old_option){
        //dd($validatedDaata, $original_data,$shipping_profile_id, $delete_old_option);
        $skippedRows = array();
        $savedRows = array();
        $alreadyExist = array();

        if($delete_old_option=='yes'){
            ShippingProfileRates::where('shipping_profile_id',$shipping_profile_id)->delete();
        }

        if(!empty($validatedDaata)){
            //dd($validatedDaata,$original_data);
            foreach($validatedDaata as $key =>$data){
                $dataResp = $data;
                $match_data['country_id'] = $data['country_id'];
                $match_data['zip_from'] = $data['zip_from'];
                $match_data['zip_to'] = $data['zip_to'];
                $match_data['weight_from'] = $data['weight_from'];
                $match_data['weight_to'] = $data['weight_to'];
                $match_data['qty_from'] = $data['qty_from'];
                $match_data['qty_to'] = $data['qty_to'];
                $match_data['price_from'] = $data['price_from'];
                $match_data['price_to'] = $data['price_to'];
                $match_data['product_type_id'] = $data['product_type_id'];
                $match_data['base_rate_for_order'] = $data['base_rate_for_order'];
                $match_data['logistic_base_rate_for_order'] = $data['logistic_base_rate_for_order'];
                $match_data['fixed_rate_per_product'] = $data['fixed_rate_per_product'];
                $match_data['logistic_fixed_rate_per_product'] = $data['logistic_fixed_rate_per_product'];
                $match_data['percentage_rate_per_product'] = $data['percentage_rate_per_product'];
                $match_data['logistic_percentage_rate_per_product'] = $data['logistic_percentage_rate_per_product'];
                $match_data['fixed_rate_per_unit_weight'] = $data['fixed_rate_per_unit_weight'];
                $match_data['logistic_fixed_rate_per_unit_weight'] = $data['logistic_fixed_rate_per_unit_weight'];
                $match_data['estimate_shipping'] = $data['estimate_shipping'];
                $match_data['priority'] = $data['priority'];

                $dataResp['country_id'] = $original_data[$key][0];
                // Push row into skipped if something is wrong in row
                if($data['country_id']=='' && $data['province_state']=='' && $data['district_city']=='' && $data['sub_district']==''){
                    array_push($skippedRows, $data);

                }else{
                    $match_data['shipping_profile_id'] = $shipping_profile_id;
                    // to protect duplicacy we need to check if rate not exist
            
            // ** Request change 10 Oct 2021
            // **from client, no need to check duplicate
            // ** because sometime they need all same but different zipcode
                    //$is_exist = ShippingProfileRates::select('id')->where($match_data)->get();
            //$is_exist = 0;
                    //if(count($is_exist) == 0){
                        $inserted_rate_id = ShippingProfileRates::insertGetId($match_data);
                        $rateDescData['province_state'] = $data['province_state'];
                        $rateDescData['district_city'] = $data['district_city'];
                        $rateDescData['sub_district'] = $data['sub_district'];
                        $rateDescData['rate_id'] = $inserted_rate_id;
                        $rateDescData['ship_profile_id'] = $shipping_profile_id;
                        $lang_lists = Language::getLangugeDetails();
                        foreach($lang_lists as $key => $language){
                            $rateDescData['lang_id'] = $language->id;
                            \App\ShippingProfileRatesDesc::insert($rateDescData);
                        }
                        // Push row into saved response
                        array_push($savedRows,$dataResp);
                    //}else{
                        // Push row into already exist
                        //array_push($alreadyExist,$dataResp);  
                    //}   
                }
             }
        }

        $return['Saved Data']  = $savedRows;
        $return['Skipped Data']  = $skippedRows;
        $return['Already Exist']  = $alreadyExist;
        return $return;

    }

    protected function validateCsvData($data_rows,$header_row){
        //dd($data_rows,$header_row);
        $lang_id = session('default_lang');
        //$columns = (new ShippingProfileRates)->getTableColumns();
        $columns = array('country','province_state','district_city','sub_district','zip_from','zip_to','weight_from','weight_to','qty_from','qty_to','price_from','price_to','product_type','base_rate_for_order','logistic_base_rate_for_order','percentage_rate_per_product','logistic_percentage_rate_per_product','logistic_fixed_rate_per_product','fixed_rate_per_product','fixed_rate_per_unit_weight','logistic_fixed_rate_per_unit_weight','estimate_shipping','priority');
        
        // unset($columns[0]);
        // unset($columns[1]);
        // unset($columns[21]);
        // unset($columns[22]);
        //dd($columns,$header_row);
        $columns_array = [];
        foreach($columns as $col){
           $columns_array[] =  str_replace('_id', '', $col);
        }
        
        if(empty(array_diff($columns_array, $header_row))){

            $validData = array();
            foreach($data_rows as $key =>$data){
                if($data[0]!='*'){ // if $data[0] is * means it apply for all countries
                    $validCountry = CountryDesc::select('country_id')->where(['lang_id'=>$lang_id,'country_name'=>$data[0]])->first();
                    if(!empty($validCountry)){
                        $validData[$key]['country_id'] = $validCountry->country_id;
                    }else{
                        $validData[$key]['country_id'] = '*';
                    }  
                }else{
                    $validData[$key]['country_id'] = '*';
                }

                $validData[$key]['province_state'] = $data[1];
                $validData[$key]['district_city'] = $data[2];
                $validData[$key]['sub_district'] = $data[3];
                $validData[$key]['zip_from'] = $data[4];
                $validData[$key]['zip_to'] = $data[5];
                $validData[$key]['weight_from'] = $data[6];
                $validData[$key]['weight_to'] = $data[7];
                $validData[$key]['qty_from'] = $data[8];
                $validData[$key]['qty_to'] = $data[9];
                $validData[$key]['price_from'] = $data[10];
                $validData[$key]['price_to'] = $data[11];
                $validData[$key]['product_type_id'] = $data[12];
                $validData[$key]['base_rate_for_order'] = $data[13];
                $validData[$key]['logistic_base_rate_for_order'] = $data[14];
                $validData[$key]['percentage_rate_per_product'] = $data[15];
                $validData[$key]['logistic_percentage_rate_per_product'] = $data[16];
                $validData[$key]['fixed_rate_per_product'] = $data[17];
                $validData[$key]['logistic_fixed_rate_per_product'] = $data[18];
                $validData[$key]['fixed_rate_per_unit_weight'] = $data[19];
                $validData[$key]['logistic_fixed_rate_per_unit_weight'] = $data[20];
                $validData[$key]['estimate_shipping'] = $data[21];
                $validData[$key]['priority'] = $data[22];
            }
        }else{
            $validData = [];
        }
        // echo "<pre>"; print_r($validData); die;
        return $validData;
    }

    /**
    * This function will export all the rates from db | Start 
    */
    public function export_rates(Request $request){
        
        $shippinsRatesData = ShippingProfileRates::where('shipping_profile_id','1')->with('getRatesDescription')->get();
        $delimiter = ",";
        $filename = "rate_csv-" . date('Y-m-d-H-i-s') . ".csv";
        //create a file pointer
        $f = fopen('php://memory', 'w');
        //set column headers
        $fields = array('country','province_state','district_city','sub_district','zip_from','zip_to','weight_from','weight_to','qty_from','qty_to','price_from','price_to','product_type','base_rate_for_order','logistic_base_rate_for_order','percentage_rate_per_product','logistic_percentage_rate_per_product','fixed_rate_per_product','logistic_fixed_rate_per_product','fixed_rate_per_unit_weight','logistic_fixed_rate_per_unit_weight','estimate_shipping','priority');
        fputcsv($f, $fields, $delimiter);

        if(count($shippinsRatesData) > 0){

            foreach($shippinsRatesData as $key => $rate){
                $rate_country = CountryDesc::select('country_name')->where(['country_id'=>$rate->country_id,'lang_id'=>1])->first();
                $rates['country'] = (!empty($rate_country)) ? $rate_country->country_name : '*';
                $rates['state'] = (isset($rate->getRatesDescription->province_state)) ? $rate->getRatesDescription->province_state :'NA';
                $rates['district'] = (isset($rate->getRatesDescription->district_city)) ? $rate->getRatesDescription->district_city :'NA';
                $rates['sub_district'] = (isset($rate->getRatesDescription->sub_district)) ? $rate->getRatesDescription->sub_district :'NA';
                $rates['zip_from'] = $rate->zip_from;
                $rates['zip_to'] = $rate->zip_to;
                $rates['weight_from'] = sprintf("%.2f", $rate->weight_from);
                $rates['weight_to'] = sprintf("%.2f", $rate->weight_to);
                $rates['qty_from'] =  sprintf("%.2f", $rate->qty_from);
                $rates['qty_to'] = sprintf("%.2f", $rate->qty_to);
                $rates['price_from'] = sprintf("%.2f", $rate->price_from);
                $rates['price_to'] = sprintf("%.2f", $rate->price_to);
                $rates['product_type_id'] = $rate->product_type_id; 
                $rates['base_rate_for_order'] = sprintf("%.2f", $rate->base_rate_for_order);
                $rates['logistic_base_rate_for_order'] = sprintf("%.2f", $rate->logistic_base_rate_for_order);
                $rates['percentage_rate_per_product'] = sprintf("%.2f", $rate->percentage_rate_per_product);
                $rates['logistic_percentage_rate_per_product'] = sprintf("%.2f", $rate->logistic_percentage_rate_per_product);
                $rates['fixed_rate_per_product'] = sprintf("%.2f", $rate->fixed_rate_per_product);
                $rates['logistic_fixed_rate_per_product'] = sprintf("%.2f", $rate->logistic_fixed_rate_per_product);
                $rates['fixed_rate_per_unit_weight'] = sprintf("%.2f", $rate->fixed_rate_per_unit_weight);
                $rates['logistic_fixed_rate_per_unit_weight'] = sprintf("%.2f", $rate->logistic_fixed_rate_per_unit_weight);
                $rates['estimate_shipping'] = $rate->estimate_shipping;
                $rates['priority'] = $rate->priority;
                fputcsv($f, $rates, $delimiter);
            }

        }else{
            // Provide Sample data if no real rates data will be there
                $rates['country'] = 'Thailand';
                $rates['state'] = 'Bangkok';
                $rates['district'] = 'Phaya Thai';
                $rates['sub_district'] = 'Sam Sen Nai';
                $rates['zip_from'] = '*';
                $rates['zip_to'] = '*';
                $rates['weight_from'] = sprintf("%.2f", 1);
                $rates['weight_to'] = sprintf("%.2f", 99999999);
                $rates['qty_from'] =  sprintf("%.2f", 1);
                $rates['qty_to'] = sprintf("%.2f", 99999999);
                $rates['price_from'] = sprintf("%.2f", 1);
                $rates['price_to'] = sprintf("%.2f", 999999999);
                $rates['product_type_id'] = 'All'; 
                $rates['base_rate_for_order'] = sprintf("%.2f", 50);
                $rates['logistic_base_rate_for_order'] = sprintf("%.2f", 50);
                $rates['percentage_rate_per_product'] = sprintf("%.2f", 10);
                $rates['logistic_percentage_rate_per_product'] = sprintf("%.2f", 10);
                $rates['fixed_rate_per_product'] = sprintf("%.2f", 5);
                $rates['logistic_fixed_rate_per_product'] = sprintf("%.2f", 5);
                $rates['fixed_rate_per_unit_weight'] = sprintf("%.2f", 1.5);
                $rates['logistic_fixed_rate_per_unit_weight'] = sprintf("%.2f", 1.5);
                $rates['estimate_shipping'] = '1';
                $rates['priority'] = '1';
                fputcsv($f, $rates, $delimiter);
        }

        fseek($f, 0);
        header("Content-type: text/x-csv");
        header("Content-Disposition: attachment; filename=".$filename."");
        fpassthru($f);
        /*update activity log start*/
        $action_type = "Export";
        $module_name = "Shipping Table Rates";   //Changes module name like : blog etc         
        $logdetails = "Admin has exported rates of shipping table rate "; //Change update message as requirement 
        $old_data = ""; //Optional old data in json format key and value as per requirement 
        $new_data = ""; //Optional new data json format key and value as per requirement 
        //Prepaire array for send data
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails,'old_data' =>$old_data,'new_data' =>$new_data );
        //Call method in module
        $this->updateLogActivity($logdata);
        /*update activity log end*/
    }

    /**
    * This function will export all the rates from db | End 
    */

    public function fielddata($datarray) {
        $replace_arr = ['0'=>false,'1'=>true];
        $table = \App\TableConfiguration::getTableConfig('shipping profile');
        $name = $datarray['name'];
        $status = $datarray['status'];
        $updated_at = $datarray['date'];
        $action = $datarray['action'];
        $sno = $datarray['sno'];
        //dd($sno->filter);

        $tableConfig = ['resizable'=>$replace_arr[$table->resizable],'row_rearrange'=>$replace_arr[$table->row_rearrange],'column_rearrange'=>$replace_arr[$table->column_rearrange],'filter'=>$replace_arr[$table->filter],'chk_action'=>$replace_arr[$table->chk_action],'col_setting'=>$replace_arr[$table->chk_action]];
       // dd($name->toArray());
        $marks = array('fieldSets' => array(
                    array("fieldName" => "shipping_name","showName" => "name", "sortable" => $replace_arr[$name->sort], "filterable" => $replace_arr[$name->filter],'width'=> $name->width,'align'=> $name->align,"fieldType" => "textbox", "textBoxType" => "single", "datatype" => "text"),
                    array("fieldName" => "date", "sortable" => $replace_arr[$updated_at->sort], "filterable" => $replace_arr[$updated_at->filter],'width'=> $updated_at->width,'align'=> $updated_at->align),
                    array("fieldName" => "action", "sortable" => $replace_arr[$action->sort], "filterable" => $replace_arr[$action->filter],'width'=> $action->width,'align'=> $action->align),
                    array("fieldName" => "s.no", "sortable" => $replace_arr[$sno->sort], "filterable" => $replace_arr[$sno->filter],'width'=> $sno->width,'align'=> $sno->align),
                   
                    array("fieldName" => "status", "sortable" => $replace_arr[$status->sort], "filterable" => $replace_arr[$status->filter],'width'=> $status->width,'align'=> $status->align, "fieldType" => "selectbox", "selectionType" => "single",
                        "optionValType" => "collection",
                        "defaultVal" => '',
                        "optionArr" => array(array('key' => '', 'value' => 'Please Select'), array('key' => '1', 'value' =>Lang::get('shipping.active')), array('key' => '0', 'value' => Lang::get('shipping.deactive')))
                    ),
                ),'tableConfig'=>[$tableConfig]
            );
        return json_encode($marks);
    }
    /**
    *
    */
    public function getShippingTypeContent(Request $request){
        //var_dump($request->all()); die;
        $shipping_type = $request->shipping_type;
        ///////////////////////////////////////////
        $productList = \App\Product::select('id','url')->orderBy('id','Desc')->get()->toArray();
        $country_lists = Country::select('id')->get();
        $country_data = Country::select('id')->with('countryName')->get()->toArray();
        $country_data2 =[];
        foreach ($country_data as $key => $value) {
           $country_data2[] = ['id'=>$value['id'],'name'=>$value['country_name']['country_name']];
        }
        
        $shop_address = [];

        ///////////////////////////////////////////

        switch ($shipping_type) {
            case 'shipping-rate-table':
                return View('admin.shippingProfile.shipping_rate_table');
            break;
        }
    }

    public function addNewTableRate(Request $request){
        $country_data = Country::select('id')->with('countryName')->get()->toArray();
        $shipping_profile_data = ShippingProfile::where('id', '1')->with('getShippingProfileDesc')->first();
        //dd($country_data);
        return view('admin.shippingProfile.addNewRates', ['shipping_profile' => $shipping_profile_data,'country_data'=>$country_data]);
    }

    public function editRate(Request $request){
         // Get All Country
        //dd($request->id);
        $country_data = Country::select('id')->with('countryName')->get()->toArray();
        $rateData = ShippingProfileRates::where('id',$request->id)->with('getRatesAllLangDesc')->first();
        $tableName = ( new \App\ShippingProfileRatesDesc )->getTable();
        //dd($subDistrictList,$rateData);
        $shipping_profile_data = ShippingProfile::where('id', $rateData->shipping_profile_id)->with('getShippingProfileDesc')->first();
        //dd($shipping_profile_data);
        
        return view('admin.shippingProfile.editRate', ['shipping_profile' => $shipping_profile_data,'country_data'=>$country_data,'rateData'=>$rateData,'rateDescTable'=>$tableName]);
    }

    ////

    public function importCsvRate(Request $request){
        $shipping_profile_data = ShippingProfile::with('getShippingProfileDesc')->first();
        
        return view('admin.shippingProfile.importCsvRates', ['shipping_profile' => $shipping_profile_data]);
    } 

    public function getRelatedData(Request $request){


        switch ($request->attribute) {
            case 'country':
                # code...
                $stateList = CountryProvinceState::where('country_id',$request->id)->with('provinceName')->get()->toArray();

                $response['optionList'] = $stateList;
                $response['success'] = "success";
            break;
            case 'state':
                $stateList = CountryCityDistrict::where('province_state_id',$request->id)->with('cityName')->get()->toArray();
                $response['optionList'] = $stateList;
                $response['success'] = "success";
            break;
            case 'district':
                $stateList = CountrySubDistrict::where('district_id',$request->id)->with('subDistrictName')->get()->toArray();
                $response['optionList'] = $stateList;
                $response['success'] = "success";
            break;
           
        }
        
        echo json_encode($response);
        exit;
    } 

    // This function will save new rate or update old rate data | Start

    public function saveRate(Request $request){
        //dd($request->all());
        $rules = [
            'province_state'=>'required',
            'district_city'=>'required',
            'sub_district'=>'required'
        ];

        $messsages = [
            'province_state.required'=>Lang::get('admin_shipping.province_state_required'),
            'district_city.required'=>Lang::get('admin_shipping.district_city_required'),
            'sub_district.required'=>Lang::get('admin_shipping.sub_district_required')
        ];

        if(is_array($request->province_state)){
            $input['province_state'] = $request->province_state[0];
        }else{
            $input['province_state'] = $request->province_state;
        }

        if(is_array($request->district_city)){
            $input['district_city'] = $request->district_city[0];
        }else{
            $input['district_city'] = $request->district_city;
        }

        if(is_array($request->sub_district)){
            $input['sub_district'] = $request->sub_district[0];
        }else{
            $input['sub_district'] = $request->sub_district;
        }

        $validate = Validator::make($input, $rules,$messsages);

        if ($validate->passes())
        {
            $update_arr = [];
            $lang_lists = Language::getLangugeDetails();
            $country_id = ($request->country_id!='All') ? $request->country_id : "*";
            $admin_id = Auth::guard('admin_user')->user()->id;
            if(isset($request->rate_id) && $request->rate_id!=''){
                $shippingProfileRate = ShippingProfileRates::find($request->rate_id);
                $sprate_old = (object)$shippingProfileRate->toArray();
                $action_type = 'edit';
                if($shippingProfileRate->country_id != $country_id) {
                    $update_arr['country_id'] = $shippingProfileRate->country_id.' => '.$country_id;
                }

                if($shippingProfileRate->zip_from != $request->zip_from) {
                    $update_arr['zip_from'] = $shippingProfileRate->zip_from.' => '.$request->zip_from;
                }
                if($shippingProfileRate->zip_to != $request->zip_to) {
                    $update_arr['zip_to'] = $shippingProfileRate->zip_to.' => '.$request->zip_to;
                }
                if($shippingProfileRate->weight_from != $request->weight_from) {
                    $update_arr['weight_from'] = $shippingProfileRate->weight_from.' => '.$request->weight_from;
                }
                if($shippingProfileRate->weight_to != $request->weight_to) {
                    $update_arr['weight_to'] = $shippingProfileRate->weight_to.' => '.$request->weight_to;
                }
                if($shippingProfileRate->qty_from != $request->qty_from) {
                    $update_arr['qty_from'] = $shippingProfileRate->qty_from.' => '.$request->qty_from;
                }
                if($shippingProfileRate->qty_to != $request->qty_to) {
                    $update_arr['qty_to'] = $shippingProfileRate->qty_to.' => '.$request->qty_to;
                }
                if($shippingProfileRate->price_from != $request->price_from) {
                    $update_arr['price_from'] = $shippingProfileRate->price_from.' => '.$request->price_from;
                }
                if($shippingProfileRate->price_to != $request->price_to) {
                    $update_arr['price_to'] = $shippingProfileRate->price_to.' => '.$request->price_to;
                }
                if($shippingProfileRate->product_type_id != $request->product_type_id) {
                    $update_arr['product_type_id'] = $shippingProfileRate->product_type_id.' => '.$request->product_type_id;
                }
                if($shippingProfileRate->estimate_shipping != $request->estimate_shipping) {
                    $update_arr['estimate_shipping'] = $shippingProfileRate->estimate_shipping.' => '.$request->estimate_shipping;
                }
                $shippingProfileRate->updated_at = date('Y-m-d H:i:s');
                $shippingProfileRate->updated_by = $admin_id;
            }else{
                $shippingProfileRate = new ShippingProfileRates;
                $action_type = 'add';
                $shippingProfileRate->created_at = date('Y-m-d H:i:s');
                $shippingProfileRate->created_by = $admin_id;
            }
            // The requested data vill be check before save to manage duplicacy. | Start 
            // The requested data vill be check before save to manage duplicacy. | End 

            

            // $district_city_id = ($request->district_city_id!='All') ? $request->district_city_id : "*";
            // $province_state_id = ($request->province_state_id!='All') ? $request->province_state_id : "*";
            // $sub_district_id = ($request->sub_district_id!='All') ? $request->sub_district_id : "*";
            
            $shippingProfileRate->shipping_profile_id = $request->shipping_profile_id;
            $shippingProfileRate->country_id = $country_id;
            // $shippingProfileRate->province_state_id = $province_state_id;
            // $shippingProfileRate->district_city_id = $district_city_id;
            // $shippingProfileRate->sub_district_id = $sub_district_id;
            $shippingProfileRate->zip_from = $request->zip_from;
            $shippingProfileRate->zip_to = $request->zip_to;
            $shippingProfileRate->weight_from = $request->weight_from;
            $shippingProfileRate->weight_to = $request->weight_to;
            $shippingProfileRate->qty_from = $request->qty_from;
            $shippingProfileRate->qty_to = $request->qty_to;
            $shippingProfileRate->price_from = $request->price_from;
            $shippingProfileRate->price_to = $request->price_to;
            $shippingProfileRate->product_type_id = $request->product_type_id;
            $shippingProfileRate->estimate_shipping = $request->estimate_shipping;

            if(isset($request->chkb_base_rate_for_order)){
                $shippingProfileRate->base_rate_for_order = $request->base_rate_for_order;
                $shippingProfileRate->logistic_base_rate_for_order = $request->logistic_base_rate_for_order;
            }else{
                $shippingProfileRate->base_rate_for_order = 0.00;
                $shippingProfileRate->logistic_base_rate_for_order = 0.00;
            }

            if(isset($request->chkb_percentage_rate_per_product)){
                $shippingProfileRate->percentage_rate_per_product = $request->percentage_rate_per_product;
                $shippingProfileRate->logistic_percentage_rate_per_product = $request->logistic_percentage_rate_per_product;
            }else{
                $shippingProfileRate->percentage_rate_per_product = 0.00;
                $shippingProfileRate->logistic_percentage_rate_per_product = 0.00;
            }

            if(isset($request->chkb_fixed_rate_per_product)){
                $shippingProfileRate->fixed_rate_per_product = $request->fixed_rate_per_product;
                $shippingProfileRate->logistic_fixed_rate_per_product = $request->logistic_fixed_rate_per_product;
            }else{
                $shippingProfileRate->fixed_rate_per_product = 0.00;
                $shippingProfileRate->logistic_fixed_rate_per_product = 0.00;
            }

            if(isset($request->chkb_fixed_rate_per_unit_weight)){
                $shippingProfileRate->fixed_rate_per_unit_weight = $request->fixed_rate_per_unit_weight;
                $shippingProfileRate->logistic_fixed_rate_per_unit_weight = $request->logistic_fixed_rate_per_unit_weight;
            }else{
                $shippingProfileRate->fixed_rate_per_unit_weight = 0.00;
                $shippingProfileRate->logistic_fixed_rate_per_unit_weight = 0.00;
            }
            
            $shippingProfileRate->priority = $request->priority;

            if($action_type == 'edit'){
                if($sprate_old->base_rate_for_order != $shippingProfileRate->base_rate_for_order) {
                    $update_arr['base_rate_for_order'] = $sprate_old->estimate_shipping.' => '.$shippingProfileRate->estimate_shipping;
                }

                if($sprate_old->logistic_base_rate_for_order != $shippingProfileRate->logistic_base_rate_for_order) {
                    $update_arr['base_rate_for_order'] = $sprate_old->logistic_base_rate_for_order.' => '.$shippingProfileRate->logistic_base_rate_for_order;
                }

                if($sprate_old->logistic_base_rate_for_order != $shippingProfileRate->logistic_base_rate_for_order) {
                    $update_arr['base_rate_for_order'] = $sprate_old->logistic_base_rate_for_order.' => '.$shippingProfileRate->logistic_base_rate_for_order;
                }

                if($sprate_old->percentage_rate_per_product != $shippingProfileRate->percentage_rate_per_product) {
                    $update_arr['percentage_rate_per_product'] = $sprate_old->percentage_rate_per_product.' => '.$shippingProfileRate->percentage_rate_per_product;
                }

                if($sprate_old->logistic_percentage_rate_per_product != $shippingProfileRate->logistic_percentage_rate_per_product) {
                    $update_arr['logistic_percentage_rate_per_product'] = $sprate_old->logistic_percentage_rate_per_product.' => '.$shippingProfileRate->logistic_percentage_rate_per_product;
                }

                if($sprate_old->fixed_rate_per_product != $shippingProfileRate->fixed_rate_per_product) {
                    $update_arr['fixed_rate_per_product'] = $sprate_old->fixed_rate_per_product.' => '.$shippingProfileRate->fixed_rate_per_product;
                }

                if($sprate_old->logistic_fixed_rate_per_product != $shippingProfileRate->logistic_fixed_rate_per_product) {
                    $update_arr['logistic_fixed_rate_per_product'] = $sprate_old->logistic_fixed_rate_per_product.' => '.$shippingProfileRate->logistic_fixed_rate_per_product;
                }

                if($sprate_old->fixed_rate_per_unit_weight != $shippingProfileRate->fixed_rate_per_unit_weight) {
                    $update_arr['fixed_rate_per_unit_weight'] = $sprate_old->fixed_rate_per_unit_weight.' => '.$shippingProfileRate->fixed_rate_per_unit_weight;
                }

                if($sprate_old->logistic_fixed_rate_per_unit_weight != $shippingProfileRate->logistic_fixed_rate_per_unit_weight) {
                    $update_arr['logistic_fixed_rate_per_unit_weight'] = $sprate_old->logistic_fixed_rate_per_unit_weight.' => '.$shippingProfileRate->logistic_fixed_rate_per_unit_weight;
                }
            }
            //dd($shippingProfileRate);
            // Code to check if same rate exist in rate table | Start

            if(isset($request->rate_id) && $request->rate_id!=''){
                if($shippingProfileRate->save()){
                    $shippingRateId = $shippingProfileRate->id;
                    // Add rate description | start
                    foreach($lang_lists as $languages){
                        $rateDescData = \App\ShippingProfileRatesDesc::where(['rate_id'=>$shippingRateId,'ship_profile_id'=>$request->shipping_profile_id,'lang_id'=>$languages->id])->first();

                        if(!empty($rateDescData)){
                            $rateDescObj = \App\ShippingProfileRatesDesc::where('id',$rateDescData->id)->first();
                            $action_desc = 'edit';
                            $rateDesc_old = (object)$rateDescObj->toArray();
                        }else{
                            $rateDescObj = new \App\ShippingProfileRatesDesc;
                            $action_desc = 'add';
                        }
                        
                        $rateDescObj->rate_id = $shippingRateId;
                        $rateDescObj->lang_id = $languages->id;
                        $rateDescObj->ship_profile_id = $request->shipping_profile_id;
                        

                        if(is_array($request->province_state)){
                            $rateDescObj->province_state = $request->province_state[$languages->id];
                        }else{
                            $rateDescObj->province_state = $request->province_state;
                        }

                        if(is_array($request->district_city)){
                            $rateDescObj->district_city = $request->district_city[$languages->id];
                        }else{
                            $rateDescObj->district_city = $request->district_city;
                        }

                        if(is_array($request->sub_district)){
                            $rateDescObj->sub_district = $request->sub_district[$languages->id];
                        }else{
                            $rateDescObj->sub_district = $request->sub_district;
                        }

                        if($action_desc == 'edit'){
                            if($rateDesc_old->province_state != $rateDescObj->province_state) {
                                $update_arr['province_state'] = $rateDesc_old->province_state.' => '.$rateDescObj->province_state;
                            }

                            if($rateDesc_old->district_city != $rateDescObj->district_city) {
                                $update_arr['district_city'] = $rateDesc_old->district_city.' => '.$rateDescObj->district_city;
                            }

                            if($rateDesc_old->sub_district != $rateDescObj->sub_district) {
                                $update_arr['sub_district'] = $rateDesc_old->sub_district.' => '.$rateDescObj->sub_district;
                            }
                        }
                        //dd($rateDescObj);
                        $rateDescObj->save();
                    }

                    if($action_type == 'edit'){
                        $change_log = ['shipping_profile_id'=>$shippingProfileRate->shipping_profile_id, 'shipping_profile_rate_id'=>$shippingProfileRate->id, 'update_detail'=>$update_arr];
                        ShippingProfileLog::updateShippingChangeLog($change_log);
                    }else{
                        $change_log = ['shipping_profile_id'=>$shippingProfileRate->shipping_profile_id, 'shipping_profile_rate_id'=>$shippingProfileRate->id, 'remark'=>'New rate created'];
                        ShippingProfileLog::updateShippingChangeLog($change_log);
                    }

                    // end
                    /*update activity log start*/
                    $action_type = "Add";
                    $module_name = "Shipping Table Rates";   //Changes module name like : blog etc         
                    $logdetails = "Admin has created new rate for shipping table rate "; //Change update message as requirement 
                    $old_data = ""; //Optional old data in json format key and value as per requirement 
                    $new_data = ""; //Optional new data json format key and value as per requirement 

                    //Prepaire array for send data
                    $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails,'old_data' =>$old_data,'new_data' =>$new_data );

                    //Call method in module
                    $this->updateLogActivity($logdata);
                    /*update activity log end*/

                    if($request->submit_type=='save_and_continue'){
                            $return_url = redirect()->action('Admin\ShippingProfile\ShippingRateTableController@editRate',['id'=>$shippingRateId])->with(['succMsg'=> 'Shipping rate has been saved.']);
                    }else{
                            $return_url = redirect()->action('Admin\ShippingProfile\ShippingRateTableController@deliveryAtAddress')->with(['succMsg'=> 'Shipping rate has been saved.','additional_rate_msg'=>'success']);
                    }
                }else{
                    $return_url = redirect()->action('Admin\ShippingProfile\ShippingRateTableController@deliveryAtAddress')->with('error', 'Oops ! Something went wrong, Try again.');
                }
            }else{
                $oldMatchingRates = ShippingProfileRates::where(['country_id'=>$country_id,'zip_from'=>$request->zip_from,'zip_to'=>$request->zip_to,'weight_from'=>$request->weight_from,'weight_to'=>$request->weight_to,'qty_from'=>$request->qty_from,'qty_to'=>$request->qty_to,'price_from'=>$request->price_from,'price_to'=>$request->price_to,'product_type_id'=>$request->product_type_id,'base_rate_for_order'=>$shippingProfileRate->base_rate_for_order,'logistic_base_rate_for_order'=>$shippingProfileRate->logistic_base_rate_for_order,'percentage_rate_per_product'=>$shippingProfileRate->percentage_rate_per_product,'logistic_percentage_rate_per_product'=>$shippingProfileRate->logistic_percentage_rate_per_product,'fixed_rate_per_product'=>$shippingProfileRate->fixed_rate_per_product,'logistic_fixed_rate_per_product'=>$shippingProfileRate->logistic_fixed_rate_per_product,'fixed_rate_per_unit_weight'=>$shippingProfileRate->fixed_rate_per_unit_weight,'logistic_fixed_rate_per_unit_weight'=>$shippingProfileRate->logistic_fixed_rate_per_unit_weight,'priority'=>$request->priority])->count();

                if($oldMatchingRates==0 ){
                    if($shippingProfileRate->save()){
                        $shippingRateId = $shippingProfileRate->id;
                        // Add rate description | start
                        foreach($lang_lists as $languages){
                            $rateDescData = \App\ShippingProfileRatesDesc::where(['rate_id'=>$shippingRateId,'ship_profile_id'=>$request->shipping_profile_id,'lang_id'=>$languages->id])->first();

                            if(!empty($rateDescData)){
                                $rateDescObj = \App\ShippingProfileRatesDesc::get($rateDescData->id);
                            }else{
                                $rateDescObj = new \App\ShippingProfileRatesDesc;
                            }
                            
                            $rateDescObj->rate_id = $shippingRateId;
                            $rateDescObj->lang_id = $languages->id;
                            $rateDescObj->ship_profile_id = $request->shipping_profile_id;
                            //dd($rateDescObj);

                            if(is_array($request->province_state)){
                                $rateDescObj->province_state = $request->province_state[$languages->id];
                            }else{
                                $rateDescObj->province_state = $request->province_state;
                            }

                            if(is_array($request->district_city)){
                                $rateDescObj->district_city = $request->district_city[$languages->id];
                            }else{
                                $rateDescObj->district_city = $request->district_city;
                            }

                            if(is_array($request->sub_district)){
                                $rateDescObj->sub_district = $request->sub_district[$languages->id];
                            }else{
                                $rateDescObj->sub_district = $request->sub_district;
                            }
                            $rateDescObj->save();
                        }

                        if($action_type == 'add'){
                            $change_log = ['shipping_profile_id'=>$shippingProfileRate->shipping_profile_id, 'shipping_profile_rate_id'=>$shippingProfileRate->id, 'remark'=>'New rate created'];
                            ShippingProfileLog::updateShippingChangeLog($change_log);
                        }
                        // end
                        /*update activity log start*/
                        $action_type = "Add";
                        $module_name = "Shipping Table Rates";   //Changes module name like : blog etc         
                        $logdetails = "Admin has created new rate for shipping table rate "; //Change update message as requirement 
                        $old_data = ""; //Optional old data in json format key and value as per requirement 
                        $new_data = ""; //Optional new data json format key and value as per requirement 

                        //Prepaire array for send data
                        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails,'old_data' =>$old_data,'new_data' =>$new_data );

                        //Call method in module
                        $this->updateLogActivity($logdata);
                        /*update activity log end*/

                        if($request->submit_type=='save_and_continue'){
                                $return_url = redirect()->action('Admin\ShippingProfile\ShippingRateTableController@editRate',['id'=>$shippingRateId])->with(['succMsg'=> 'Shipping rate has been saved.']);
                        }else{
                                $return_url = redirect()->action('Admin\ShippingProfile\ShippingRateTableController@deliveryAtAddress')->with(['succMsg'=> 'Shipping rate has been saved.','additional_rate_msg'=>'success']);
                        }
                    }else{
                        $return_url = redirect()->action('Admin\ShippingProfile\ShippingRateTableController@deliveryAtAddress')->with('error', 'Oops ! Something went wrong, Try again.');
                    }
                }else{
                    $return_url = redirect()->action('Admin\ShippingProfile\ShippingRateTableController@deliveryAtAddress')->with('error', 'Oops ! This rate is already exist.');
                }
            }
            // Code to check if same rate exist in rate table | End
        }else{
            $return_url = redirect()->action('Admin\ShippingProfile\ShippingRateTableController@addNewTableRate', $request->shipping_profile_id)->withErrors($validate)->withInput();
        }
        //dd($return_url);
        return $return_url;
        
    } 
    // This function will save new rate or update old rate data | End
    
    
    public function setTableGrid() {

        $data = \App\TableColumnConfiguration::whereIn('column_name',['sno','name','status','date','action'])->get()->toArray();
        $datarray = array();
        foreach($data as $resv){
          $datarray[$resv['column_name']] = $resv; 
        }
        $fieldSets = [];
        $replace = ['0'=>false,'1'=>true];
        
        foreach ($datarray as $key => $res) { 

                $showName = str_replace('_', ' ', $res['column_name']);
                $tempSets = ['fieldName'=>$key,'showName'=>$showName,'sortable'=>$replace[$res['sort']],'filterable'=>$replace[$res['filter']],'width'=> $res['width'],'align'=> $res['align'],"fieldType" => $res['field_type']];

                if($res['field_type'] == 'textbox'){
                    $tempSets['textBoxType'] = 'single';
                    $tempSets['datatype'] = 'text';
                }

                if($res['field_type'] == 'selectbox'){
                    $tempSets['selectionType'] = 'single';
                    $tempSets['optionValType'] = 'collection';
                    $tempSets['defaultVal']    = '';

                    if($res['column_name'] == 'status'){
                        $statusArr = generatedDD(['shipping.active','shipping.deactive']);
                        $tempSets['optionArr']    = $statusArr;
                    }
                    if($res['column_name'] == 'paid'){
                        $paidArr = generatedDD(['order.no','order.yes']);
                        $tempSets['optionArr']    = $paidArr;
                    }
                    if($res['column_name'] == 'shipped'){
                        $shippedArr = generatedDD(['order.no','order.yes']);
                        $tempSets['optionArr']    = $shippedArr;
                    }
                    if($res['column_name'] == 'rma'){
                        $rmaArr = generatedDD(['order.no','order.yes']);
                        $tempSets['optionArr']    = $rmaArr;
                    }
                }
                
                
            $fieldSets[] = $tempSets;
            
        }

        
        $table = \App\TableConfiguration::getTableConfig('shipping_rate_profile','slug');

        
        $tableConfig = ['resizable'=>$replace[$table->resizable],'row_rearrange'=>$replace[$table->row_rearrange],'column_rearrange'=>$replace[$table->column_rearrange],'filter'=>$replace[$table->filter],'chk_action'=>$replace[$table->chk_action],'col_setting'=>$replace[$table->chk_action]];

       // dd($name->toArray());
        $marks = array('fieldSets' =>$fieldSets,'tableConfig'=>[$tableConfig]);
        return json_encode($marks);
    }

    protected function setRatesTableGrid(){

        $data = \App\TableColumnConfiguration::whereIn('column_name',['country_id','province_state_id','district_city_id','sub_district_id','zip_from','zip_to','weight_from','weight_to','qty_from','qty_to','price_from','price_to'])->get()->toArray();

       
        $datarray = array();

        foreach($data as $resv){
          $datarray[$resv['column_name']] = $resv; 
        }

        $fieldSets = [];
        $replace = ['0'=>false,'1'=>true];
        
        foreach ($datarray as $key => $res) { 
                //echo "<pre>"; print_r($res); 
                $showName = str_replace('_', ' ', $res['display_name']);
                $tempSets = ['fieldName'=>$key,'showName'=>$showName,'sortable'=>$replace[$res['sort']],'filterable'=>$replace[$res['filter']],'width'=> $res['width'],'align'=> $res['align'],"fieldType" => $res['field_type']];

                if($res['field_type'] == 'textbox'){
                    $tempSets['textBoxType'] = 'single';
                    $tempSets['datatype'] = 'text';
                }

                if($res['field_type'] == 'selectbox'){
                    $tempSets['selectionType'] = 'single';
                    $tempSets['optionValType'] = 'collection';
                    $tempSets['defaultVal']    = '';

                    if($res['column_name'] == 'status'){
                        $statusArr = generatedDD(['shipping.deactive','shipping.active']);
                        $tempSets['optionArr']    = $statusArr;
                    }
                    if($res['column_name'] == 'paid'){
                        $paidArr = generatedDD(['order.no','order.yes']);
                        $tempSets['optionArr']    = $paidArr;
                    }
                    if($res['column_name'] == 'shipped'){
                        $shippedArr = generatedDD(['order.no','order.yes']);
                        $tempSets['optionArr']    = $shippedArr;
                    }
                    if($res['column_name'] == 'rma'){
                        $rmaArr = generatedDD(['order.no','order.yes']);
                        $tempSets['optionArr']    = $rmaArr;
                    }
                }
                
                
            $fieldSets[] = $tempSets;
            
        }

       
        $table = \App\TableConfiguration::getTableConfig('shipping_rate_profile','slug');
        //dd($table);
        
        $tableConfig = ['resizable'=>$replace[$table->resizable],'row_rearrange'=>$replace[$table->row_rearrange],'column_rearrange'=>$replace[$table->column_rearrange],'filter'=>$replace[$table->filter],'chk_action'=>$replace[$table->chk_action],'col_setting'=>$replace[$table->chk_action]];

        
        $marks = array('fieldSets' =>$fieldSets,'tableConfig'=>[$tableConfig]);
        return json_encode($marks);
    }


    public function listShippingRatesData( Request $request){

        //print_r($request->all()); die;
        $lang_id = session('default_lang');
        $searchData = [];

        $shipping_profile_id = $request->shipping_profile_id;
        $perpage = !empty($request->per_page) ? $request->per_page : 10;
        

        $shippingRateDataQuery = ShippingProfileRates::where('shipping_profile_id',$shipping_profile_id)->with(['getRatesDescription']);


        if(isset($request->country_id) && $request->country_id!=''){
            $country = CountryDesc::select('country_id')->where(['country_name'=>$request->country_id,'lang_id'=>$lang_id])->first();
            $searchData['country_id'] =  (isset($country->country_id)) ? $country->country_id : '';
            $shippingRateDataQuery->where('country_id', $searchData['country_id']);
        }

        // if(isset($request->province_state_id)){
        //     $province = CountryProvinceStateDesc::select('province_state_id')->where(['province_state_name'=>$request->province_state_id,'lang_id'=>$lang_id])->first();

        //     $searchData['province_state_id'] = (isset($province->province_state_id)) ? : '';
        //     $shippingRateDataQuery->where('province_state_id', $searchData['province_state_id']);
        // }
        // if(isset($request->district_city_id)){
        //     $cityDistrict = CountryCityDistrictDesc::select('city_district_id')->where(['city_district_name'=>$request->district_city_id,'lang_id'=>$lang_id])->first();
        //     $searchData['district_city_id'] = (isset($cityDistrict->city_district_id)) ? $cityDistrict->city_district_id: '';
        //     $shippingRateDataQuery->where('district_city_id', $searchData['district_city_id']);
        // }

        // if(isset($request->sub_district_id)){
        //     $subDistrict = CountrySubDistrictDesc::select('sub_district_id')->where(['sub_district_name'=>$request->sub_district_id,'lang_id'=>$lang_id])->first();
        //      $searchData['sub_district_id'] = isset($subDistrict->sub_district_id) ? $subDistrict->sub_district_id :'';
        //      $shippingRateDataQuery->where('sub_district_id', $searchData['sub_district_id']);
        // }

        if(isset($request->zip_from) && $request->zip_from!=''){
           
             $shippingRateDataQuery->where('zip_from', '>=',$request->zip_from);
        }

        if(isset($request->zip_to) && $request->zip_to!=''){
           
             $shippingRateDataQuery->where('zip_to', '<=',$request->zip_to);
        }

        if(isset($request->weight_from) && $request->weight_from!=''){
           
             $shippingRateDataQuery->where('weight_from', '>=',$request->weight_from);
        }

        if(isset($request->weight_to) && $request->weight_to!=''){
           
             $shippingRateDataQuery->where('weight_to', '<=',$request->weight_to);
        }

        if(isset($request->qty_from) && $request->qty_from!=''){
           
             $shippingRateDataQuery->where('qty_from', '>=',$request->qty_from);
        }

        if(isset($request->qty_to) && $request->qty_to!=''){
           
             $shippingRateDataQuery->where('qty_to', '<=',$request->qty_to);
        }

        if(isset($request->price_from) && $request->price_from!=''){
           
             $shippingRateDataQuery->where('price_from', '>=',$request->price_from);
        }

        if(isset($request->price_to) && $request->price_to!=''){
           
             $shippingRateDataQuery->where('price_to', '<=',$request->price_to);
        }

        $shippingRateDataQuery->orderBy('id', 'desc');

        $total = $shippingRateDataQuery->count(); 
        $shippingRateDataQuery->paginate($perpage);
        
        $shippingRateDataList = $shippingRateDataQuery->get();
        
        
        $shippingRateData = [];


        foreach ($shippingRateDataList as $key => $value) {
            
            $edit_url = action('Admin\ShippingProfile\ShippingRateTableController@editRate',['id'=>$value['id']]);
            $delete_url = action('Admin\ShippingProfile\ShippingRateTableController@deleteRate',['id'=>$value['id']]);
            
            if($value->country_id!='*'){
                $country = CountryDesc::select('country_name')->where(['country_id'=>$value->country_id,'lang_id'=>$lang_id])->first();
                $country_name = $country->country_name;
            }else{
                $country_name = $value->country_id;
            }
           
            if(isset($value->getRatesDescription) && $value->getRatesDescription!=null){
                $province_state_name = $value->getRatesDescription->province_state;
                $city_district_name = $value->getRatesDescription->district_city;
                $sub_district_name = $value->getRatesDescription->sub_district;
                
            }else{
                $province_state_name = '';
                $city_district_name = '';
                $sub_district_name = '';
            }
            
            $shippingRateData[$key]['country_id'] = (!empty($country_name)) ? $country_name :'';
            $shippingRateData[$key]['province_state_id'] = (!empty($province_state_name)) ? $province_state_name :'';
            $shippingRateData[$key]['district_city_id'] = (!empty($city_district_name)) ? $city_district_name :'';
            $shippingRateData[$key]['sub_district_id'] = (!empty($sub_district_name)) ? $sub_district_name :'';
            $shippingRateData[$key]['zip_from'] = $value->zip_from;
            $shippingRateData[$key]['zip_to'] = $value->zip_to;
            $shippingRateData[$key]['weight_from'] = $value->weight_from;
            $shippingRateData[$key]['weight_to'] = $value->weight_to;
            $shippingRateData[$key]['qty_from'] = $value->qty_from;
            $shippingRateData[$key]['qty_to'] = $value->qty_to;
            $shippingRateData[$key]['price_from'] = $value->price_from;
            $shippingRateData[$key]['price_to'] = $value->price_to;
            $shippingRateData[$key]['product_type_id'] = $value->product_type_id;
            $shippingRateData[$key]['base_rate_for_order'] = $value->base_rate_for_order;
            $shippingRateData[$key]['percentage_rate_per_product'] = $value->percentage_rate_per_product;
            $shippingRateData[$key]['fixed_rate_per_product'] = $value->fixed_rate_per_product;
            $shippingRateData[$key]['fixed_rate_per_unit_weight'] = $value->logistic_fixed_rate_per_unit_weight;
            $shippingRateData[$key]['logistic_base_rate_for_order'] = $value->logistic_base_rate_for_order;
            $shippingRateData[$key]['logistic_percentage_rate_per_product'] = $value->logistic_percentage_rate_per_product;
            $shippingRateData[$key]['logistic_fixed_rate_per_product'] = $value->logistic_fixed_rate_per_product;
            $shippingRateData[$key]['logistic_fixed_rate_per_unit_weight'] = $value->fixed_rate_per_unit_weight;
            $shippingRateData[$key]['priority'] = $value->priority;
            $shippingRateData[$key]['edit_url'] = $edit_url;
            $shippingRateData[$key]['delete_url'] = $delete_url;
         
        }

        return ['data'=>$shippingRateData,'total'=>$total];

    }

    public function deleteRate(Request $request){

        if(isset($request->id) && $request->id!=''){

            $shippingMethodData = ShippingProfileRates::where('id',$request->id)->first();
            
            if(isset($shippingMethodData->shipping_profile_id) && $shippingMethodData->shipping_profile_id!=''){

                if(ShippingProfileRates::where('id',$request->id)->delete()){
                    $return_url = redirect()->action('Admin\ShippingProfile\ShippingRateTableController@deliveryAtAddress')->with(['succMsg'=>'Rate has been deleted successfully','additional_rate_msg'=>'success']);

                    /*update activity log start*/
                    $action_type = "Delete";
                    $module_name = "Shipping Table Rates";   //Changes module name like : blog etc         
                    $logdetails = "Admin has deleted shipping table rate "; //Change update message as requirement 
                    $old_data = ""; //Optional old data in json format key and value as per requirement 
                    $new_data = ""; //Optional new data json format key and value as per requirement 

                    //Prepaire array for send data
                    $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails,'old_data' =>$old_data,'new_data' =>$new_data );

                    //Call method in module
                    $this->updateLogActivity($logdata);
                    /*update activity log end*/
                }else{
                     $return_url = redirect()->action('Admin\ShippingProfile\ShippingRateTableController@index')->with('errorMsg', 'Oops ! Something went wrong.');
                }


            }else{
                
                $return_url = redirect()->action('Admin\ShippingProfile\ShippingRateTableController@index')->with('errorMsg', 'Oops ! Something went wrong.');
            }
            

        }else{
            $return_url = redirect()->action('Admin\ShippingProfile\ShippingRateTableController@index')->with('errorMsg', 'Oops ! Something went wrong.');
        }

        return $return_url;
    }

    public function addWizardRate(Request $request){
        $country_data = Country::select('id')->where('id','1')->with('countryName')->get()->toArray();
        $shipping_profile_data = ShippingProfile::where('id', '1')->with('getShippingProfileDesc')->first();
       
        return view('admin.shippingProfile.addWizardRates', ['shipping_profile' => $shipping_profile_data,'country_data'=>$country_data]);
    }

    public function autosuggest(Request $request){
        
        $search_keyword = $request->input_val;
        $section = $request->section;
        $lang_id = substr($request->lang, -1);
        $ret_arr = [];
        if(strlen($search_keyword)){
            switch ($request->section) {
                case 'province_state_type':
                    $data = \App\CountryProvinceStateDesc::where('province_state_name','LIKE',"%".$search_keyword."%")->where('lang_id',$lang_id)->get();
                    if(count($data)){
                        foreach($data as $value){
                            array_push($ret_arr, $value->province_state_name);
                        }
                    }
                break;
                case 'district_city_type':
                    $data = \App\CountryCityDistrictDesc::where('city_district_name','LIKE',"%".$search_keyword."%")->where('lang_id',$lang_id)->get();
                    if(count($data)){
                        foreach($data as $value){
                            array_push($ret_arr, $value->city_district_name);
                        }
                    }
                break;
                case 'sub_district_type':
                    $data = \App\CountrySubDistrictDesc::where('sub_district_name','LIKE',"%".$search_keyword."%")->where('lang_id',$lang_id)->get();
                    if(count($data)){
                        foreach($data as $value){
                            array_push($ret_arr, $value->sub_district_name);
                        }
                    }
                break;
            }
        }
        
        return json_encode(['status'=>'success','data'=>$ret_arr,'section'=>$request->section]);
    }

    public function saveWizardRate(Request $request){
        
        $province_state = "*";
        $city_district = "*";
        $sub_district = "*";
        $lang_id = session('default_lang');
        $rate_array = [];
        $response = [];
        switch ($request->shipping_type) {
            case 'free_shipping':
                $rateObject = new \App\ShippingProfileRates;
                $rateObject->shipping_profile_id = $request->shipping_profile_id;
                $rateObject->country_id = $request->country_id;
                $rateObject->zip_from = "*";
                $rateObject->zip_to = "*";
                $rateObject->qty_from = 0;
                $rateObject->qty_to = 9999999;
                $rateObject->price_from = 0;
                $rateObject->price_to = 9999999;
                $rateObject->weight_from = 0;
                $rateObject->weight_to = 9999999;
                $rateObject->base_rate_for_order = 0;
                $rateObject->percentage_rate_per_product = 0;
                $rateObject->fixed_rate_per_product = 0;
                $rateObject->fixed_rate_per_unit_weight = 0;
                $rateObject->product_type_id = $request->product_type_id;
                $rateObject->estimate_shipping = $request->estimate_shipping;
                $rateObject->priority = 1;

                if($rateObject->save()){
                    $rate_id = $rateObject->id;
                    $response[0] = $this->saveRateDescription($rate_id,$request->shipping_profile_id,$province_state,$city_district,$sub_district,$lang_id);
                }

            break;
            case 'fixed_rate_per_order':
                $rateObject = new \App\ShippingProfileRates;

                $rateObject->shipping_profile_id = $request->shipping_profile_id;
                $rateObject->country_id = $request->country_id;
                $rateObject->zip_from = "*";
                $rateObject->zip_to = "*";
                $rateObject->qty_from = 0;
                $rateObject->qty_to = 9999999;
                $rateObject->price_from = 0;
                $rateObject->price_to = 9999999;
                $rateObject->weight_from = 0;
                $rateObject->weight_to = 9999999;
                $rateObject->base_rate_for_order = $request->fixed_rate_per_order['first']['ship_fee'];
                $rateObject->percentage_rate_per_product = 0;
                $rateObject->fixed_rate_per_product = 0;
                $rateObject->fixed_rate_per_unit_weight = 0;
                $rateObject->product_type_id = $request->product_type_id;
                $rateObject->estimate_shipping = $request->estimate_shipping;
                $rateObject->priority = 1;

                if($rateObject->save()){
                    $rate_id = $rateObject->id;
                    $response[0] = $this->saveRateDescription($rate_id,$request->shipping_profile_id,$province_state,$city_district,$sub_district,$lang_id);
                }
            break;
            case 'first_and_next_item':
                $rateObject = new \App\ShippingProfileRates;

                $rateObject->shipping_profile_id = $request->shipping_profile_id;
                $rateObject->country_id = $request->country_id;
                $rateObject->zip_from = "*";
                $rateObject->zip_to = "*";
                $rateObject->qty_from = 0;
                $rateObject->qty_to = 9999999;
                $rateObject->price_from = 0;
                $rateObject->price_to = 9999999;
                $rateObject->weight_from = 0;
                $rateObject->weight_to = 9999999;
                $rateObject->base_rate_for_order = ($request->first_and_next_item['first']['ship_fee']-$request->first_and_next_item['next']['ship_fee']);
                $rateObject->percentage_rate_per_product = 0;
                $rateObject->fixed_rate_per_product = $request->first_and_next_item['next']['ship_fee'];
                $rateObject->fixed_rate_per_unit_weight = 0;
                $rateObject->product_type_id = $request->product_type_id;
                $rateObject->estimate_shipping = $request->estimate_shipping;
                $rateObject->priority = 1;

                if($rateObject->save()){
                    $rate_id = $rateObject->id;
                    $response[0] = $this->saveRateDescription($rate_id,$request->shipping_profile_id,$province_state,$city_district,$sub_district,$lang_id);
                }
                
            break;
            case 'base_on_qty_range':
                $index = 0;
                $qty_from = 0;
                $qty_to = 0;
                foreach($request->base_on_qty_range as $key => $rate){
                    if($rate['ship_status']=='ship'){
                        $rateObject = new \App\ShippingProfileRates;
                        if($index==0){
                            if(isset($rate['first_input'])){
                                $qty_to = $rate['first_input'];
                            }else{
                                $qty_to = 9999999;
                            }
                        }else{
                            $qty_from = $qty_to;
                            if(isset($rate['first_input'])){
                                $qty_to = $rate['first_input'];
                            }else{
                                $qty_to = 9999999;
                            } 
                        }

                        $rateObject->shipping_profile_id = $request->shipping_profile_id;
                        $rateObject->country_id = $request->country_id;
                        $rateObject->zip_from = "*";
                        $rateObject->zip_to = "*";
                        $rateObject->qty_from = $qty_from;
                        $rateObject->qty_to = $qty_to;
                        $rateObject->price_from = 0;
                        $rateObject->price_to = 9999999;
                        $rateObject->weight_from = 0;
                        $rateObject->weight_to = 9999999;
                        $rateObject->base_rate_for_order = $rate['ship_fee'];
                        $rateObject->percentage_rate_per_product = 0;
                        $rateObject->fixed_rate_per_product =0;
                        $rateObject->fixed_rate_per_unit_weight = 0;
                        $rateObject->product_type_id = $request->product_type_id;
                        $rateObject->estimate_shipping = $request->estimate_shipping;
                        $rateObject->priority = (1+$index);

                        if($rateObject->save()){
                            $rate_id = $rateObject->id;
                            $response[$index] = $this->saveRateDescription($rate_id,$request->shipping_profile_id,$province_state,$city_district,$sub_district,$lang_id);
                        }

                        $index++;
                    }
                }
                
            break;
            case 'base_on_weight_range':
                $index = 1;
                $weight_from = 0;
                $weight_to = 0;
                foreach($request->base_on_weight_range as $key => $rate){
                    if($rate['ship_status']=='ship'){
                        $rateObject = new \App\ShippingProfileRates;
                        if($index==1){
                            if(isset($rate['first_input'])){
                                $weight_to = $rate['first_input'];
                            }else{
                                $weight_to = 9999999;
                            }
                        }else{
                            $weight_from = $weight_to;
                            if(isset($rate['first_input'])){
                                $weight_to = $rate['first_input'];
                            }else{
                                $weight_to = 9999999;
                            } 
                        }

                        $rateObject->shipping_profile_id = $request->shipping_profile_id;
                        $rateObject->country_id = $request->country_id;
                        $rateObject->zip_from = "*";
                        $rateObject->zip_to = "*";
                        $rateObject->qty_from = 0;
                        $rateObject->qty_to = 9999999;
                        $rateObject->price_from = 0;
                        $rateObject->price_to = 9999999;
                        $rateObject->weight_from = $weight_from;
                        $rateObject->weight_to = $weight_to;
                        $rateObject->base_rate_for_order = $rate['ship_fee'];
                        $rateObject->percentage_rate_per_product = 0;
                        $rateObject->fixed_rate_per_product =0;
                        $rateObject->fixed_rate_per_unit_weight = 0;
                        $rateObject->product_type_id = $request->product_type_id;
                        $rateObject->estimate_shipping = $request->estimate_shipping;
                        $rateObject->priority = (1+$index);

                        if($rateObject->save()){
                            $rate_id = $rateObject->id;
                            $response[$index] = $this->saveRateDescription($rate_id,$request->shipping_profile_id,$province_state,$city_district,$sub_district,$lang_id);
                        }
                        $index++;
                    }
                }
            break;
            case 'base_on_price_subtotal_range':
                $index = 1;
                $price_from = 0;
                $price_to = 0;
                
                foreach($request->base_on_price_subtotal_range as $key => $rate){
                    if($rate['ship_status']=='ship'){
                        $rateObject = new \App\ShippingProfileRates;
                        if($index==1){
                            if(isset($rate['first_input'])){
                                $price_to = $rate['first_input'];
                            }else{
                                $price_to = 9999999;
                            }
                        }else{
                            $price_from = $price_to;
                            if(isset($rate['first_input'])){
                                $price_to = $rate['first_input'];
                            }else{
                                $price_to = 9999999;
                            } 
                        }
                        $rateObject->shipping_profile_id = $request->shipping_profile_id;
                        $rateObject->country_id = $request->country_id;
                        $rateObject->zip_from = "*";
                        $rateObject->zip_to = "*";
                        $rateObject->qty_from = 0;
                        $rateObject->qty_to = 9999999;
                        $rateObject->price_from = $price_from;
                        $rateObject->price_to = $price_to;
                        $rateObject->weight_from = 0;
                        $rateObject->weight_to = 9999999;
                        $rateObject->base_rate_for_order = $rate['ship_fee'];
                        $rateObject->percentage_rate_per_product = 0;
                        $rateObject->fixed_rate_per_product =0;
                        $rateObject->fixed_rate_per_unit_weight = 0;
                        $rateObject->product_type_id = $request->product_type_id;
                        $rateObject->estimate_shipping = $request->estimate_shipping;
                        $rateObject->priority = (1+$index);

                        if($rateObject->save()){
                            $rate_id = $rateObject->id;
                            $response[$index] = $this->saveRateDescription($rate_id,$request->shipping_profile_id,$province_state,$city_district,$sub_district,$lang_id);
                        }

                        $index++;
                    }
                }
            break;
        }

        $falg = true;
        foreach ($response as $key => $value) {
            if($value===false)
                $falg = $value;

        }

        if($falg){
            $return_url = redirect()->action('Admin\ShippingProfile\ShippingRateTableController@deliveryAtAddress')->with(['succMsg'=>'Rate has been added successfully','additional_rate_msg'=>'success']);
        }else{
            $return_url = redirect()->action('Admin\ShippingProfile\ShippingRateTableController@deliveryAtAddress')->with('errorMsg', 'Oops ! Something went wrong.');
        }

        return $return_url;
    }

    protected function saveRateDescription($rate_id,$shipping_profile_id,$province_state,$district_city,$sub_district,$lang_id){
        $rateObject = new \App\ShippingProfileRatesDesc;
        $rateObject->ship_profile_id = $shipping_profile_id;
        $rateObject->rate_id = $rate_id;
        $rateObject->lang_id = $lang_id;
        $rateObject->province_state = $province_state;
        $rateObject->district_city = $district_city;
        $rateObject->sub_district = $sub_district;
        if($rateObject->save()){
            $return = true;
        }else{
            $return = false;
        }
        return $return;
    }

    public function getDeliveryAtAddress(Request $request) {
        $perpage = !empty($request->pq_rpp) ? $request->pq_rpp : getPagination('limit');
        $request->page = $current_page = !empty($request->pq_curpage) ? $request->pq_curpage : 0;

        $start_index = ($current_page - 1) * $perpage;
        
        $order_by = 'spr.priority';
        $order_by_val = 'asc';

        if(isset($request->pq_sort)){
            $sort_data = jsonDecodeArr($request->pq_sort);
            $order_by = $sort_data[0]['dataIndx'];
            $order_by_val = ($sort_data[0]['dir']=='up')?'asc':'desc';
        }


        try {

            $query = DB::table(with(new ShippingProfileRates)->getTable() . ' as spr')
                            ->join(with(new ShippingProfile)->getTable().' as sp', [['sp.id','=','spr.shipping_profile_id']])
                            ->join(with(new ShippingProfileRatesDesc)->getTable().' as sprd', [['sprd.rate_id','=','spr.id']])
                            ->where('sp.id', '1')
                            ->select('spr.*', 'sprd.province_state as state', 'sprd.district_city as district', 'sprd.sub_district as sub_district');

            if(isset($request->pq_filter)){
                $filter_req = json_decode($request->pq_filter,true);
                if(!empty($filter_req['data'])){
                    $filter_arr = $filter_req['data'];
                    foreach ($filter_arr as $fkey => $fvalue) {
                        $searchval = $fvalue['value'];
                        switch($fvalue['dataIndx']){
                            case 'country_name':
                                $query->where('sprd.country_name','like', '%'.$searchval.'%');
                            break; 
                            case 'state':
                                $query->where('sprd.province_state','like', '%'.$searchval.'%');
                            break;
                            case 'district':
                                $query->where('sprd.district_city','like', '%'.$searchval.'%');
                            break;
                            case 'sub_district':
                                $query->where('sprd.sub_district','like', '%'.$searchval.'%');
                            break;
                            case 'zip_from':
                                $query->where('spr.zip_from', $searchval);
                            break;
                            case 'zip_to':
                                $query->where('spr.zip_to', $searchval);
                            break;
                            case 'weight_from':
                                $query->where('spr.weight_from', $searchval);
                            break;
                            case 'weight_to':
                                $query->where('spr.weight_to', $searchval);
                            break;
                            case 'qty_from':
                                $query->where('spr.qty_from', $searchval);
                            break;
                            case 'qty_to':
                                $query->where('spr.qty_to', $searchval);
                            break;
                            case 'price_from':
                                $query->where('spr.price_from', $searchval);
                            break;
                            case 'price_to':
                                $query->where('spr.price_to', $searchval);
                            break;
                            case 'base_rate_for_order':
                                $query->where('spr.base_rate_for_order', $searchval);
                            break;
                            case 'percentage_rate_per_product':
                                $query->where('spr.percentage_rate_per_product', $searchval);
                            break;
                            case 'fixed_rate_per_product':
                                $query->where('spr.fixed_rate_per_product', $searchval);
                            break;
                            case 'fixed_rate_per_unit_weight':
                                $query->where('spr.fixed_rate_per_unit_weight', $searchval);
                            break;
                            case 'logistic_base_rate_for_order':
                                $query->where('spr.logistic_base_rate_for_order', $searchval);
                            break;
                            case 'logistic_percentage_rate_per_product':
                                $query->where('spr.logistic_percentage_rate_per_product', $searchval);
                            break;
                            case 'logistic_fixed_rate_per_product':
                                $query->where('spr.logistic_fixed_rate_per_product', $searchval);
                            break;
                            case 'logistic_fixed_rate_per_unit_weight':
                                $query->where('spr.logistic_fixed_rate_per_unit_weight', $searchval);
                            break;
                            
                            case 'created_at':
                                if(isset($fvalue['value']) && isset($fvalue['value2'])) {
                                    createDateFilter($query,'spr.created_at',$fvalue['value'],$fvalue['value2']);
                                }
                                break;
                            case 'updated_at':
                                if(isset($fvalue['value']) && isset($fvalue['value2'])) {
                                    createDateFilter($query,'spr.updated_at',$fvalue['value'],$fvalue['value2']);
                                }
                            break;
                            
                        }
                    }
                }
            }                



            $response = $query->orderBy($order_by,$order_by_val)->paginate($perpage,['*'],'page',$current_page);
            
                          //  ->orderBy('spr.priority');
            //$response = $query->paginate($perpage, ['*'], 'page', $current_page); 
            

            foreach ($response as $key => $row) {
                if($row->country_id!="*"){
                    $countryData = CountryDesc::select('country_name')->where(['country_id'=>$row->country_id,'lang_id'=>1])->first();
                    $response[$key]->country_name = (isset($countryData->country_name)) ? $countryData->country_name : '';
                }else{
                    $response[$key]->country_name = $row->country_id;
                }
                $response[$key]->edit_url = action('Admin\ShippingProfile\ShippingRateTableController@editRate',['id'=>$row->id]);
                $response[$key]->delete_url = action('Admin\ShippingProfile\ShippingRateTableController@deleteRate',['id'=>$row->id]);
                $response[$key]->log_url = action('Admin\ShippingProfile\ShippingRateTableController@changeLog',['id'=>$row->id]);
            }      
        } catch (QueryException $e) {
            $response = ['status' => 'fail', 'msg' => $e->getMessage()];
        }

        $this->setFilter('shipping-profile-updateMethod',$request);
        return $response;
    }

    public function changeLog(Request $request,$id){

        $table_rate = ShippingProfileRates::where('id',$id)->first();
        if(!$table_rate){
            abort(404);
        }

        $log_list = ShippingProfileLog::where(['shipping_profile_rate_id'=>$id])->latest()->get();

        return view('admin.shippingProfile.changeLog', ['rate_id'=>$id, 'log_list'=>$log_list,'table_rate'=>$table_rate]);
    }
}
