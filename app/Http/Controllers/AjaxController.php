<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;

use Session;
use Config;
use Image;
use Auth;
use Lang;
use DB;

class AjaxController extends MarketPlace
{
    
    function uploadImageAjax(Request $request){

        if(isset($request->uploadfile)) {
            
            $uploadDetails['path'] = $request->upload_path;
            $uploadDetails['file'] =  $request->uploadfile;
            $uploadDetails['height'] =  $request->height;
            $uploadDetails['width'] =  $request->width;

            if(isset($request->keep_original) && $request->keep_original == 'Y') {
                $uploadDetails['original_path'] = $request->upload_path.'/original';
            }                       

            $file_name = $this->uploadFileCustom($uploadDetails); 

            $arr['status'] = 'success';
            $arr['file_name'] = $file_name;
            //print_r($arr);die;

            return json_encode($arr);
        } 
        else {
        	$arr['status'] = 'fail';
        	return json_encode($arr);
        }       
    }

    function switchLanguage(Request $request) {
        //echo '<pre>';print_r($request->all());die;

        if($request->lang_id > 0 && !empty($request->lang_code)) {
            Session::put('lang_code', $request->lang_code);
            Session::put('default_lang', $request->lang_id);
            return 'success';
        }
    }

    function switchCurrency(Request $request){

        if(isset($request->currency_id)){
            $currencyDetail = \App\Currency::getCurrencyDetailsById($request->currency_id);
            if(!empty($currencyDetail)){
                Session::put('default_currency_code',$currencyDetail->currency_code);
                Session::put('default_currency_id',$currencyDetail->id);
                Session::put('default_currency_symbol',$currencyDetail->currency_symbol);
                return 'success';
            }
        }
    }  

    function getCountryDetail(Request $request) {
        if($request->country_id > 0) {
            $def_country_dtl = \App\Country::getCountryDetail($request->country_id);

            $country_dtl_arr['country_code'] = $def_country_dtl->short_code;
            $country_dtl_arr['isd_code'] = $def_country_dtl->country_isd;
            $country_dtl_arr['province_state'] = $def_country_dtl->countryName->province_state_header;
            $country_dtl_arr['city_district'] = $def_country_dtl->countryName->city_district_header;
            $country_dtl_arr['sub_district'] = $def_country_dtl->countryName->sub_district_header;
            $country_dtl_arr['status'] = 'success';

            return json_encode( $country_dtl_arr);
        }
    }

    function getStateCityDD(Request $request) {
        //dd($request->all());

        $opt_str = '<option value="">--'.Lang::get('common.select').'--</option>';
        $status = 'success';

        if(($request->address_type == 'country' || $request->address_type == 'billing_country') && $request->address_id > 0) {
            $result = \App\CountryProvinceState::getProvinceList($request->address_id);
            if(count($result) > 0) {
                foreach ($result as $value) {
                    $opt_str .= '<option value="'.$value->id.'">'.$value->provinceName->province_state_name.'</option>';
                }

                $def_country_dtl = \App\Country::getCountryDetail($request->address_id);
                $data_arr['country_code'] = $def_country_dtl->short_code;
                $data_arr['isd_code'] = $def_country_dtl->country_isd;
                $data_arr['province_state'] = $def_country_dtl->countryName->province_state_header;
                $data_arr['city_district'] = $def_country_dtl->countryName->city_district_header;
                $data_arr['sub_district'] = $def_country_dtl->countryName->sub_district_header;
            }           
        }        
        elseif(($request->address_type == 'province_state' || $request->address_type == 'billing_province_state') && $request->address_id > 0) {
            $result = \App\CountryCityDistrict::getCityList($request->address_id);
            if(count($result) > 0) {
                foreach ($result as $value) {
                    $opt_str .= '<option value="'.$value->id.'">'.$value->cityName->city_district_name.'</option>';
                }
            }                      
        }
        elseif(($request->address_type == 'city_district' || $request->address_type == 'billing_city_district') && $request->address_id > 0) {
            $result = \App\CountrySubDistrict::getSubDistList($request->address_id);
            if(count($result) > 0) {
                foreach ($result as $value) {
                    $opt_str .= '<option value="'.$value->id.'">'.$value->subDistrictName->sub_district_name.'</option>';
                }

                $zip_code = \App\CountryCityDistrict::getZipCode($request->address_id);
                $data_arr['zip_code'] = $zip_code;
            }                        
        }
        else {
            $status = 'failed';
        }        
        //echo count($opt_str);die;

        $data_arr['opt_str'] = $opt_str;
        $data_arr['status'] = $status;        

        echo json_encode($data_arr);
    }

    function getStateCityDropDown(Request $request) {
        //dd($request->all());

        $opt_str = '';
        $status = 'success';

        if(($request->address_type == 'country' || $request->address_type == 'billing_country') && !empty($request->address_id)) {

            $result = \App\CountryProvinceState::getProvinceList($request->address_id);
            if(count($result) > 0) {
                foreach ($result as $value) {
                    $opt_str .= '<option value="'.$value->id.'">'.$value->provinceName->province_state_name.'</option>';
                }
            }
            $def_country_dtl = \App\Country::getCountryDetail($request->address_id);
            //echo '====><pre>';print_r($def_country_dtl);die;
            $data_arr['country_code'] = $def_country_dtl->short_code;
            $data_arr['isd_code'] = $def_country_dtl->country_isd;
            $data_arr['province_state'] = $def_country_dtl->countryName->province_state_header;
            $data_arr['city_district'] = $def_country_dtl->countryName->city_district_header;
            $data_arr['sub_district'] = $def_country_dtl->countryName->sub_district_header;                      
        }        
        elseif(($request->address_type == 'province_state' || $request->address_type == 'billing_province_state') && !empty($request->address_id)) {

            $result = \App\CountryCityDistrict::getCityList($request->address_id);
            if(count($result) > 0) {
                foreach ($result as $value) {
                    $opt_str .= '<option value="'.$value->id.'">'.$value->cityName->city_district_name.'</option>';
                }
            }                      
        }
        elseif(($request->address_type == 'city_district' || $request->address_type == 'billing_city_district') && !empty($request->address_id)) {

            $result = \App\CountrySubDistrict::getSubDistList($request->address_id);
            if(count($result) > 0) {
                foreach ($result as $value) {
                    $opt_str .= '<option value="'.$value->id.'">'.$value->subDistrictName->sub_district_name.'</option>';
                }                
            }
            $zip_code = \App\CountryCityDistrict::getZipCode($request->address_id);
            $data_arr['zip_code'] = $zip_code;                                   
        }
        else {
            $status = 'failed';
        }        
        //echo count($opt_str);die;
        $data_arr['opt_str'] = $opt_str;
        $data_arr['status'] = $status;        

        echo json_encode($data_arr);
    }         
}



