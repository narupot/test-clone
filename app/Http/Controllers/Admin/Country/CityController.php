<?php

namespace App\Http\Controllers\Admin\Country;

use App\Http\Controllers\MarketPlace;
use App\Helpers\CustomHelpers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;

use App\Country;
use App\CountryDesc;
use App\CountryProvinceState;
use App\CountryProvinceStateDesc;
use App\CountryCityDistrict;
use App\CountryCityDistrictDesc;
use App\CountrySubDistrict;
use App\CountrySubDistrictDesc;
use App\CountryCityDistrictZip;

use Lang;
use Auth;

class CityController extends MarketPlace
{
    private $tblCountryCityDistrictDesc;
    private $tblCountrySubDistrictDesc;


    public function __construct()
    {   
        $this->middleware('admin.user'); 
        $this->tblCountryCityDistrictDesc = with(new CountryCityDistrictDesc)->getTable();
        $this->tblCountrySubDistrictDesc = with(new CountrySubDistrictDesc)->getTable();
    }     

    public function index(Request $request)
    {
        $permission = $this->checkUrlPermission('manage_city_district');
        if($permission === true) {
            $permission_arr['add'] = $this->checkMenuPermission('add_city_district');
            $permission_arr['edit'] = $this->checkMenuPermission('edit_city_district');       
            $country_list = Country::getCountry();

            if(isset($request->country) && $request->country > 0) {
                $country_id = $request->country;
            }
            else {
                $default_country = Country::getCountryDetail('', 'default');
                $country_id = $default_country->id;
            }            
            $province_list = CountryProvinceState::getProvinceList($country_id);

            $city_list = '';
            $province_id = '';
            $city_id = '';
            $province_city_list = '';
            if(isset($request->province) && $request->province > 0) {
                $province_id = $request->province;
                if(isset($request->city) && $request->city > 0) {      // subdistrict
                    $city_id = $request->city;
                    $city_list = CountrySubDistrict::getSubDistList($city_id);
                }
                else{      // district/city
                    $city_list = CountryCityDistrict::getCityList($province_id);
                } 

                if($country_id == '1') {
                    $province_city_list = CountryCityDistrict::getCityList($province_id);
                }                
            }          
            //dd($province_list); 
            
            return view('admin.country.cityList', ['country_list'=>$country_list, 'permission_arr'=>$permission_arr, 'country_id'=>$country_id, 'province_id'=>$province_id, 'province_list'=>$province_list, 'city_list'=>$city_list, 'city_id'=>$city_id, 'province_city_list'=>$province_city_list]);
        }
    }

    public function create(Request $request)
    {       
        $permission = $this->checkUrlPermission('add_city_district');        
        if($permission === true) {         
            $country_list = Country::getCountry();

            $country_id = '';
            $province_list = '';
            if(isset($request->country) && $request->country > 0) {
                $country_id = $request->country;
                $province_list = CountryProvinceState::getProvinceList($country_id);
            }

            $province_id = '';
            $city_list = '';
            if(isset($request->province) && $request->province > 0) {
                $province_id = $request->province;
                $city_list = CountryCityDistrict::getCityList($province_id);
            }

            $city_id = '';
            if($country_id == '1' && isset($request->city) && $request->city > 0) {
                $city_id = $request->city;
            } 

            $district_type = '';
            if(isset($request->district_type) && $request->district_type > 0) {
                $district_type = $request->district_type;
            }

            return view('admin.country.cityAdd', ['country_list'=>$country_list, 'country_id'=>$country_id, 'province_list'=>$province_list, 'province_id'=>$province_id, 'city_list'=>$city_list, 'city_id'=>$city_id, 'district_type'=>$district_type]); 
        }       
    }

    public function store(Request $request)
    {               
        //echo '<pre>';dd($request->all());
        $def_lang_id = session('default_lang');

        $input = $request->all();
        $input['city_dn'] = $request->city_district_name[$def_lang_id];

        $validate = $this->validateCity($input);
        if ($validate->passes()) {  

            if($request->district_type == '2') { //subdistrict
                $sub_district = new CountrySubDistrict; 
                $sub_district->district_id = $request->city;
                $sub_district->status = $request->status;
                $sub_district->created_by = Auth::guard('admin_user')->user()->id;
                $sub_district->save();

                $def_sub_district = $request->city_district_name[$def_lang_id];
                foreach($request->city_district_name as $lang=>$sub_district_nm) {
                    
                    if(empty($sub_district_nm)) {
                        $sub_district_nm = $def_sub_district;
                    }                           
                    
                    $sub_district_desc = new CountrySubDistrictDesc;
                    $sub_district_desc->sub_district_id = $sub_district->id;
                    $sub_district_desc->lang_id = $lang;
                    $sub_district_desc->sub_district_name = $sub_district_nm;
                    $sub_district_desc->save();
                }                
            }
            else {  //district
                $city = new CountryCityDistrict; 
                $city->country_id = $request->country;
                $city->province_state_id = $request->province;
                $city->status = $request->status;
                $city->zip = $request->zip;
                $city->created_by = Auth::guard('admin_user')->user()->id;
                $city->save();

                $def_lang_val_city = $request->city_district_name[$def_lang_id];
                foreach($request->city_district_name as $lang=>$city_nm) {
                    
                    if(empty($city_nm)) {
                        $city_nm = $def_lang_val_city;
                    }                           
                    
                    $city_desc = new CountryCityDistrictDesc;
                    $city_desc->city_district_id = $city->id;
                    $city_desc->lang_id = $lang;
                    $city_desc->city_district_name = $city_nm;
                    $city_desc->save();
                }  
                if ($request->zip) {
                    foreach($request->zip as $key=>$zip_data) {
                        $zip_d = new CountryCityDistrictZip;
                        $zip_d->district_id = $city->id;
                        $zip_d->zip = $zip_data;
                        $zip_d->save();
                    }
                }              
            }

            /*update activity log start*/
            $action_type = "created"; 
            $module_name = "city";            
            $logdetails = "Admin has created ".$request->city_district_name[$def_lang_id]." ".$module_name;
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/                             

            if(isset($request->submit_type) && $request->submit_type == 'save_and_continue') {
                return redirect()->action('Admin\Country\CityController@create', 'country='.$request->country.'&province='.$request->province.'&city='.$request->city.'&district_type='.$request->district_type)->with('succMsg', Lang::get('country.city_district_added_successfully'));
            }
            else {
                return redirect()->action('Admin\Country\CityController@index', 'country='.$request->country.'&province='.$request->province.'&city='.$request->city)->with('succMsg', Lang::get('country.city_district_added_successfully'));
            }
        } 
        else {
            //dd($validate->errors());
            return redirect()->action('Admin\Country\CityController@create')->withErrors($validate)->withInput();
        }            
    }

    public function show($id)
    {
        //
    }

    public function edit(Request $request, $city_subcity_id)
    {
        $permission = $this->checkUrlPermission('edit_city_district');        
        if($permission === true && $city_subcity_id > 0) {

            $country_list = Country::getCountry();

            $country_id = '';
            $province_list = '';
            if(isset($request->country) && $request->country > 0) {
                $country_id = $request->country;
                $province_list = CountryProvinceState::getProvinceList($country_id);
            }
            $zip_all = '';
            $province_id = '';
            $city_list = '';
            if(isset($request->province) && $request->province > 0) {
                $province_id = $request->province;
                $city_list = CountryCityDistrict::getCityList($province_id);
            }

            $city_id = '';
            if($country_id == '1' && isset($request->city) && $request->city > 0) { // subdistrict
                $type = 'sub_district';
                $city_id = $request->city;
                $city_detail = CountrySubDistrict::getSubDistrictDetail($city_subcity_id);
                $tblCountryCityDistrictDesc = $this->tblCountrySubDistrictDesc;
            }
            else {   // district/city
                $type = 'city_district';
                $city_detail = CountryCityDistrict::getCityDetail($city_subcity_id);
                $tblCountryCityDistrictDesc = $this->tblCountryCityDistrictDesc;
                $zip_all = CountryCityDistrictZip::where('district_id',$city_subcity_id)->get();
            }
            
            return view('admin.country.cityEdit', ['country_list'=>$country_list, 'country_id'=>$country_id, 'province_list'=>$province_list, 'province_id'=>$province_id, 'city_list'=>$city_list, 'city_id'=>$city_id, 'city_detail'=>$city_detail, 'tblCountryCityDistrictDesc'=>$tblCountryCityDistrictDesc, 'type'=>$type]);
        }            
    }

    public function update(Request $request, $id)
    {
        if($id > 0) {
            //echo '<pre>';dd($request->all());
            $def_lang_id = session('default_lang');

            $input = $request->all();
            if($request->district_type == '2') { //subdistrict
                $input['city_dn'] = $request->sub_district_name[$def_lang_id];
            }
            else {  //district
                $input['city_dn'] = $request->city_district_name[$def_lang_id];
            }

            $validate = $this->validateCity($input);
            if ($validate->passes()) { 

                if($request->district_type == '2') { //subdistrict
                    $sub_district = CountrySubDistrict::find($id); 
                    $sub_district->district_id = $request->city;
                    $sub_district->status = $request->status;
                    $sub_district->updated_by = Auth::guard('admin_user')->user()->id;
                    $sub_district->save();

                    CountrySubDistrictDesc::where('sub_district_id', '=', $id)->delete();

                    $def_sub_district = $request->sub_district_name[$def_lang_id];
                    foreach($request->sub_district_name as $lang=>$sub_district_nm) {
                        
                        if(empty($sub_district_nm)) {
                            $sub_district_nm = $def_sub_district;
                        }                           
                        
                        $sub_district_desc = new CountrySubDistrictDesc;
                        $sub_district_desc->sub_district_id = $sub_district->id;
                        $sub_district_desc->lang_id = $lang;
                        $sub_district_desc->sub_district_name = $sub_district_nm;
                        $sub_district_desc->save();
                    }                
                }
                else {  //district
                    $city = CountryCityDistrict::find($id);
                    $city->country_id = $request->country;
                    $city->province_state_id = $request->province;
                    $city->status = $request->status;
                    $city->zip = $request->zip;
                    $city->updated_by = Auth::guard('admin_user')->user()->id;
                    $city->save();

                    CountryCityDistrictDesc::where('city_district_id', '=', $id)->delete();

                    $def_lang_val_city = $request->city_district_name[$def_lang_id];
                    foreach($request->city_district_name as $lang=>$city_nm) {
                        
                        if(empty($city_nm)) {
                            $city_nm = $def_lang_val_city;
                        }                           
                        
                        $city_desc = new CountryCityDistrictDesc;
                        $city_desc->city_district_id = $city->id;
                        $city_desc->lang_id = $lang;
                        $city_desc->city_district_name = $city_nm;
                        $city_desc->save();
                    }
                    if ($request->zip) {
                        CountryCityDistrictZip::where('district_id', '=', $id)->delete();    
                        foreach($request->zip as $key=>$zip_data) {
                            $zip_d = new CountryCityDistrictZip;
                            $zip_d->district_id = $id;
                            $zip_d->zip = $zip_data;
                            $zip_d->save();
                        }
                    }                 
                }

                /*update activity log start*/
                $action_type = "updated"; 
                $module_name = "city";            
                $logdetails = "Admin has updated ".$input['city_dn']." ".$module_name;
                $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

                $this->updateLogActivity($logdata);
                /*update activity log End*/ 

                return redirect()->action('Admin\Country\CityController@index', 'country='.$request->country.'&province='.$request->province.'&city='.$request->city)->with('succMsg', Lang::get('country.city_district_updated_successfully'));
            } 
            else {
                return redirect()->action('Admin\Country\CityController@edit', [$id,'country='.$request->country.'&province='.$request->province.'&city='.$request->city])->withErrors($validate)->withInput();
            }                
        }
    }  

    function validateCity($input) {   

        $rules['country'] = 'Required';
        $rules['province'] = 'Required';
        if($input['district_type'] == '1' && $input['country'] == '1') {
            //$rules['zip'] = zipRule();
        }
        elseif($input['district_type'] == '2') {
            $rules['city'] = 'Required';
        }
        $rules['city_dn'] = nameRule();   

        $error_msg['country.required'] = Lang::get('country.please_select_country');
        $error_msg['province.required'] = Lang::get('country.please_select_province_state');

        $error_msg['city.required'] = Lang::get('country.please_select_district'); 
        $error_msg['zip.required'] = Lang::get('country.please_enter_zip_code');

        $error_msg['city_dn.required'] = Lang::get('country.please_enter_city_district_name');      

        $validate = Validator::make($input, $rules, $error_msg);
        //echo '<pre>';print_r($validate->errors());die;
        return $validate; 
    }

    function getProvinceList(Request $request) {
        //dd($request->All());
        $province_list = '';
        $status = 'failed';
        if($request->country_id > 0) {
            $province_list = '<option value="">--'.Lang::get('common.select').'--</option>';
            $province_opt = CustomHelpers::getProvinceStateDD($request->country_id);
            if(!empty($province_opt)) {
                 $province_list .= $province_opt;
                $status = 'success';
            }
        }
        $ret_arr['status'] = $status;
        $ret_arr['province_list'] = $province_list;
        return json_encode($ret_arr);
    }

    function getCityList(Request $request) {
        //dd($request->All());
        $city_list = '';
        $status = 'failed';
        if($request->province_id > 0) {
            $city_list = '<option value="">--'.Lang::get('common.select').'--</option>';
            $city_opt = CustomHelpers::getCityDistrictDD($request->province_id);
            if(!empty($city_opt)) {
                $city_list .= $city_opt;
                $status = 'success';
            }
        }
        $ret_arr['status'] = $status;
        $ret_arr['city_list'] = $city_list;
        return json_encode($ret_arr);
    }         
}
