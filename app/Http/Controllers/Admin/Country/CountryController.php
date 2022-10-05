<?php

namespace App\Http\Controllers\Admin\Country;

use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\MarketPlace;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;     // when use Rule function
use Illuminate\Http\Request;

use App\Country;
use App\CountryDesc;
use App\Language;
use Config;
use Lang;
use Auth;

class CountryController extends MarketPlace
{
    private $tblCountryDesc;
    private $tblCountry;


    public function __construct()
    {   
        $this->middleware('admin.user'); 
        $this->tblCountryDesc = with(new CountryDesc)->getTable();
        $this->tblCountry = with(new Country)->getTable();
    }     

    public function index()
    {
        $permission = $this->checkUrlPermission('manage_country');
        if($permission === true) {
            $permission_arr['add'] = $this->checkMenuPermission('add_country');
            $permission_arr['edit'] = $this->checkMenuPermission('edit_country');       
            $country_list = Country::getCountryAll(); 
            return view('admin.country.countryList', ['country_list'=>$country_list, 'permission_arr'=>$permission_arr]);
        }
    }

    public function create()
    {       
        $permission = $this->checkUrlPermission('add_country');        
        if($permission === true) {         
            return view('admin.country.countryAdd'); 
        }       
    }

    public function store(Request $request)
    {               
        //echo '<pre>';print_r($request->all());die;
        $def_lang_id = session('default_lang');

        $input = $request->all();
        $input['country_nm'] = $request->country_name[$def_lang_id];
        $input['ps_header'] = $request->province_state_header[$def_lang_id];
        $input['cd_header'] = $request->city_district_header[$def_lang_id];
        $input['sd_header'] = $request->sub_district_header[$def_lang_id];

        $validate = $this->validateCountry($input);
        if ($validate->passes()) {            

            if($request->is_default == '1') {

                Country::where('is_default', '1')->update(['is_default' => '0']);
            }            

            $country = new Country();            

            if(isset($request->country_flag)) {

                $uploadDetails['path'] = Config::get('constants.country_flag_path');
                $uploadDetails['file'] =  $request->country_flag;   

                $imageName = $this->uploadFileCustom($uploadDetails); 

                $country->country_flag = $imageName;
            }

            $country->country_code = $request->country_code;
            $country->short_code = $request->short_code;
            $country->country_isd = $request->country_isd;
            $country->is_default = $request->is_default;
            $country->status = $request->status;
            $country->created_by = Auth::guard('admin_user')->user()->id;
            $country->save();

            $def_lang_val_country = $request->country_name[$def_lang_id];
            $def_lang_val_ps = $request->province_state_header[$def_lang_id];
            $def_lang_val_cd = $request->city_district_header[$def_lang_id];
            $def_lang_val_sd = $request->sub_district_header[$def_lang_id];
            
            foreach($request->country_name as $lang=>$country_nm) {
                
                $ps_header = $request->province_state_header[$lang];
                $cd_header = $request->city_district_header[$lang];
                $sd_header = $request->sub_district_header[$lang];
                
                if(empty($country_nm)) {
                    $country_nm = $def_lang_val_country;
                }
                if(empty($ps_header)) {
                    $ps_header = $def_lang_val_ps;
                }
                if(empty($cd_header)) {
                    $cd_header = $def_lang_val_cd;
                }
                if(empty($sd_header)) {
                    $sd_header = $def_lang_val_sd;
                }                            
                
                $country_desc = new CountryDesc();
                $country_desc->country_id = $country->id;
                $country_desc->lang_id = $lang;
                $country_desc->country_name = $country_nm;
                $country_desc->province_state_header = $ps_header;
                $country_desc->city_district_header = $cd_header;
                $country_desc->sub_district_header = $sd_header;
                $country_desc->save();
            }

            /*update activity log start*/
            $action_type = "created"; 
            $module_name = "country";            
            $logdetails = "Admin has created ".$request->country_name[$def_lang_id]." ".$module_name;
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/

            return redirect()->action('Admin\Country\CountryController@index')->with('succMsg', Lang::get('country.country_added_successfully'));
        } 
        else {
            return redirect()->action('Admin\Country\CountryController@create')->withErrors($validate)->withInput();
        }            
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $permission = $this->checkUrlPermission('edit_country');        
        if($permission === true) {
            $country_detail = Country::with('countryName')->where('id', '=', $id)->first();
            //dd($country_detail);
            return view('admin.country.countryEdit', ['country_detail'=>$country_detail, 'tblCountryDesc'=>$this->tblCountryDesc]);
        }            
    }

    public function update(Request $request, $id)
    {
        if($id > 0) {
            //echo '<pre>';print_r($request->all());die;
            $def_lang_id = session('default_lang');

            $input = $request->all();
            $input['country_nm'] = $request->country_name[$def_lang_id];
            $input['ps_header'] = $request->province_state_header[$def_lang_id];
            $input['cd_header'] = $request->city_district_header[$def_lang_id];
            $input['sd_header'] = $request->sub_district_header[$def_lang_id];            
            $validate = $this->validateCountry($input, $id);

            if ($validate->passes()) {     

                if($request->is_default == '1') {
                    Country::where('is_default', '1')->update(['is_default' => '0']);
                }            
                
                $country = Country::find($id);

                if(isset($request->country_flag)) {

                    $uploadDetails['path'] = Config::get('constants.country_flag_path');
                    $uploadDetails['file'] =  $request->country_flag;   

                    $imageName = $this->uploadFileCustom($uploadDetails); 

                    $country->country_flag = $imageName;
                }

                $country->country_code = $request->country_code;
                $country->short_code = $request->short_code;
                $country->country_isd = $request->country_isd;
                $country->is_default = $request->is_default;
                $country->status = $request->status;
                $country->updated_by = Auth::guard('admin_user')->user()->id;
                $country->save();
                
                CountryDesc::where('country_id', '=', $id)->delete();

                $def_lang_val_country = $request->country_name[$def_lang_id];
                $def_lang_val_ps = $request->province_state_header[$def_lang_id];
                $def_lang_val_cd = $request->city_district_header[$def_lang_id];
                $def_lang_val_sd = $request->sub_district_header[$def_lang_id];
                
                foreach($request->country_name as $lang=>$country_nm) {
                    $ps_header = $request->province_state_header[$lang];
                    $cd_header = $request->city_district_header[$lang];
                    $sd_header = $request->sub_district_header[$lang];
                    
                    if(empty($country_nm)) {
                        $country_nm = $def_lang_val_country;
                    }
                    if(empty($ps_header)) {
                        $ps_header = $def_lang_val_ps;
                    }
                    if(empty($cd_header)) {
                        $cd_header = $def_lang_val_cd;
                    }
                    if(empty($sd_header)) {
                        $sd_header = $def_lang_val_sd;
                    }                            
                    
                    $country_desc = new CountryDesc();
                    $country_desc->country_id = $country->id;
                    $country_desc->lang_id = $lang;
                    $country_desc->country_name = $country_nm;
                    $country_desc->province_state_header = $ps_header;
                    $country_desc->city_district_header = $cd_header;
                    $country_desc->sub_district_header = $sd_header;
                    $country_desc->save();
                }

                /*update activity log start*/
                $action_type = "updated"; 
                $module_name = "country";            
                $logdetails = "Admin has updated ".$request->country_name[$def_lang_id]." ".$module_name;
                $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

                $this->updateLogActivity($logdata);
                /*update activity log End*/
                return redirect()->action('Admin\Country\CountryController@index')->with('succMsg', Lang::get('country.country_updated_successfully'));
            } 
            else {
                return redirect()->action('Admin\Country\CountryController@edit', $id)->withErrors($validate)->withInput();
            }                
        }
    }  

    function validateCountry($input, $country_id=null) {   

        if(empty($country_id)) {
            $rules['country_flag'] = 'Required';
        } 
        $rules['country_nm'] = nameRule();
        $rules['ps_header'] = nameRule();
        $rules['cd_header'] = nameRule();
        $rules['sd_header'] = nameRule();
        
        $rules['country_code'] = 'Required|unique:'.$this->tblCountry.',country_code';
        if(!empty($country_id) && !empty($input['country_code'])) {
            $rules['country_code'] = Rule::unique($this->tblCountry)->ignore($country_id);
        }

        $rules['short_code'] = 'Required';
        $rules['country_isd'] = 'Required';    

        $error_msg['country_flag.required'] = Lang::get('country.please_select_image');
        $error_msg['country_nm.required'] = Lang::get('country.please_enter_country_name');
        $error_msg['ps_header.required'] = Lang::get('country.please_enter_province_state_header');
        $error_msg['cd_header.required'] = Lang::get('country.please_enter_city_district_header');
        $error_msg['sd_header.required'] = Lang::get('country.please_enter_sub_district_header');
        $error_msg['country_code.required'] = Lang::get('country.please_enter_country_code');
        $error_msg['short_code.required'] = Lang::get('country.please_enter_country_short_code');
        $error_msg['country_isd.required'] = Lang::get('country.please_select_isd_code');      

        $validate = Validator::make($input, $rules, $error_msg);
        //echo '<pre>';print_r($validate->errors());die;
        return $validate; 
    }     
}
