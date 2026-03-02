<?php

namespace App\Http\Controllers\Admin\Country;

use App\Http\Controllers\MarketPlace;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;

use App\Country;
use App\CountryDesc;
use App\CountryProvinceState;
use App\CountryProvinceStateDesc;
use Lang;
use Auth;

class ProvinceController extends MarketPlace
{
    private $tblCountryProvinceStateDesc;

    public function __construct()
    {   
        $this->middleware('admin.user'); 
        $this->tblCountryProvinceStateDesc = with(new CountryProvinceStateDesc)->getTable();
    }     

    public function index(Request $request)
    {
        $permission = $this->checkUrlPermission('manage_province_state');
        if($permission === true) {
            $permission_arr['add'] = $this->checkMenuPermission('add_province_state');
            $permission_arr['edit'] = $this->checkMenuPermission('edit_province_state');       
            $country_list = Country::getCountry();

            if(isset($request->country) && $request->country > 0) {
                $country_id = $request->country;
            }
            else {
                $default_country = Country::getCountryDetail('', 'default');
                $country_id = $default_country->id;
            }            
            $province_list = CountryProvinceState::getProvinceList($country_id);
            //dd($province_list); 
            
            return view('admin.country.provinceList', ['country_list'=>$country_list, 'permission_arr'=>$permission_arr, 'country_id'=>$country_id, 'province_list'=>$province_list]);
        }
    }

    public function create(Request $request)
    {       
        $permission = $this->checkUrlPermission('add_country');        
        if($permission === true) {         
            $country_list = Country::getCountry();
            $country_id = '';
            if(isset($request->country) && $request->country > 0) {
                $country_id = $request->country;
            }
            return view('admin.country.provinceAdd', ['country_list'=>$country_list, 'country_id'=>$country_id]); 
        }       
    }

    public function store(Request $request)
    {               
        //echo '<pre>';dd($request->all());
        $def_lang_id = session('default_lang');

        $input = $request->all();
        $input['province_sn'] = $request->province_state_name[$def_lang_id];

        $validate = $this->validateProvince($input);
        if ($validate->passes()) {                       

            $province = new CountryProvinceState(); 
            $province->country_id = $request->country;
            $province->status = $request->status;
            $province->created_by = Auth::guard('admin_user')->user()->id;
            $province->save();

            $def_lang_val_province = $request->province_state_name[$def_lang_id];
            foreach($request->province_state_name as $lang=>$province_nm) {
                
                if(empty($province_nm)) {
                    $province_nm = $def_lang_val_province;
                }                           
                
                $province_desc = new CountryProvinceStateDesc();
                $province_desc->province_state_id = $province->id;
                $province_desc->lang_id = $lang;
                $province_desc->province_state_name = $province_nm;
                $province_desc->save();
            }

            /*update activity log start*/
            $action_type = "created"; 
            $module_name = "province";            
            $logdetails = "Admin has created ".$def_lang_val_province." ".$module_name;
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/

            if(isset($request->submit_type) && $request->submit_type == 'save_and_continue') {
                return redirect()->action('Admin\Country\ProvinceController@create', 'country='.$request->country)->with('succMsg', Lang::get('country.province_added_successfully'));
            }
            else {
                return redirect()->action('Admin\Country\ProvinceController@index', 'country='.$request->country)->with('succMsg', Lang::get('country.province_added_successfully'));
            }
        } 
        else {
            return redirect()->action('Admin\Country\ProvinceController@create')->withErrors($validate)->withInput();
        }            
    }

    public function show($id)
    {
        //
    }

    public function edit(Request $request, $province_id)
    {
        $permission = $this->checkUrlPermission('edit_province_state');        
        if($permission === true) {

            $country_list = Country::getCountry();
            $country_id = '';
            if(isset($request->country) && $request->country > 0) {
                $country_id = $request->country;
            }
            $province_detail = CountryProvinceState::getProvinceDetail($province_id);
            //dd($province_detail);
            return view('admin.country.provinceEdit', ['country_list'=>$country_list, 'country_id'=>$country_id, 'province_detail'=>$province_detail, 'tblCountryProvinceStateDesc'=>$this->tblCountryProvinceStateDesc]);
        }            
    }

    public function update(Request $request, $id)
    {
        if($id > 0) {
            //echo '<pre>';dd($request->all());
            $def_lang_id = session('default_lang');

            $input = $request->all();
            $input['province_sn'] = $request->province_state_name[$def_lang_id];

            $validate = $this->validateProvince($input);
            if ($validate->passes()) {                
                
                $province = CountryProvinceState::find($id);
                $province->country_id = $request->country;
                $province->status = $request->status;
                $province->updated_by = Auth::guard('admin_user')->user()->id;
                $province->save();
                
                CountryProvinceStateDesc::where('province_state_id', '=', $id)->delete();

                $def_lang_val_province = $request->province_state_name[$def_lang_id];
                foreach($request->province_state_name as $lang=>$province_nm) {
                    
                    if(empty($province_nm)) {
                        $province_nm = $def_lang_val_province;
                    }                           
                    
                    $province_desc = new CountryProvinceStateDesc();
                    $province_desc->province_state_id = $province->id;
                    $province_desc->lang_id = $lang;
                    $province_desc->province_state_name = $province_nm;
                    $province_desc->save();
                }

                /*update activity log start*/
                $action_type = "updated"; 
                $module_name = "province";            
                $logdetails = "Admin has updated ".$def_lang_val_province." ".$module_name;
                $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

                $this->updateLogActivity($logdata);
                /*update activity log End*/

                return redirect()->action('Admin\Country\ProvinceController@index', 'country='.$request->country)->with('succMsg', Lang::get('country.province_updated_successfully'));
            } 
            else {
                return redirect()->action('Admin\Country\ProvinceController@edit', [$id,'country='.$request->country])->withErrors($validate)->withInput();
            }                
        }
    }  

    function validateProvince($input) {   

        $rules['country'] = 'Required';
        $rules['province_sn'] = nameRule();    

        $error_msg['country.required'] = Lang::get('country.please_select_country');
        $error_msg['province_sn.required'] = Lang::get('country.please_enter_province_name');      

        $validate = Validator::make($input, $rules, $error_msg);
        //echo '<pre>';print_r($validate->errors());die;
        return $validate; 
    }     
}
