<?php

namespace App\Http\Controllers;

use App\PackageDesc;
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
        // dd($request->all());

        $opt_str = '<option value="">--'.Lang::get('common.select').'--</option>';
        $status = 'success';
        $zip_data ='';
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
            if($result) {
                foreach ($result as $value) {
                    $opt_str .= '<option value="'.$value->id.'">'.$value->subDistrictName->sub_district_name.'</option>';
                }

                $zip_code = \App\CountryCityDistrict::getZipCode($request->address_id);
                $data_arr['zip_code'] = $zip_code;
                $zip_all = \App\CountryCityDistrictZip::where('district_id',$request->address_id)->get();
                if($zip_all) {
                    foreach ($zip_all as $zipdata) {
                        $zip_data .= '<option value="'.$zipdata->zip.'">'.$zipdata->zip.'</option>';
                    }                
                } 
            }                        
        }
        else {
            $status = 'failed';
        }        
        //echo count($opt_str);die;

        $data_arr['opt_str'] = $opt_str;
        $data_arr['status'] = $status;        
        $data_arr['zip_data'] = $zip_data;
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

    /**
     * แปลงพิกัด lat/long เป็น province_id, district_id, sub_district_id ผ่าน Google Geocoding API
     * ใช้สำหรับ auto-fill dropdown หลังเลือกพิกัดบนแผนที่
     */
    function geocodeToSmm(Request $request) {
        $lat = $request->lat ?? null;
        $long = $request->long ?? null;
        if (!$lat || !$long) {
            return response()->json(['status' => 'failed', 'message' => 'ต้องการ lat และ long']);
        }
        $lat = (float) $lat;
        $long = (float) $long;
        $apiKey = env('GOOGLE_MAPS_API_KEY', 'AIzaSyCBYiKxZ3HYjLO7vZVa2n0f3_QuZj2lC9g');
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . urlencode($lat . ',' . $long) . '&key=' . $apiKey . '&language=th';
        $response = @file_get_contents($url);
        if ($response === false) {
            return response()->json(['status' => 'failed', 'message' => 'ไม่สามารถเชื่อมต่อ Geocoding API ได้']);
        }
        $data = json_decode($response, true);
        if (empty($data['results'])) {
            return response()->json([
                'status' => 'failed',
                'message' => 'ไม่พบข้อมูลที่อยู่จากพิกัดนี้',
                'geocode_log' => ['raw_response' => $data],
            ]);
        }
        // ใช้เฉพาะ result แรกที่มี country=TH (ผลที่ตรงกับพิกัดที่ปักหมุดมากที่สุด)
        $components = null;
        $formattedAddressForExtract = '';
        foreach ($data['results'] as $result) {
            $comps = $result['address_components'] ?? [];
            foreach ($comps as $c) {
                if (in_array('country', $c['types'] ?? [])) {
                    if (($c['short_name'] ?? '') === 'TH') {
                        $components = $comps;
                        $formattedAddressForExtract = $result['formatted_address'] ?? '';
                        break 2;
                    }
                    break;
                }
            }
        }
        if (!$components) {
            $firstResult = $data['results'][0] ?? null;
            return response()->json([
                'status' => 'failed',
                'message' => 'พิกัดไม่อยู่ในประเทศไทย',
                'geocode_log' => [
                    'formatted_address' => $firstResult['formatted_address'] ?? null,
                    'first_result_components' => $firstResult['address_components'] ?? [],
                ],
            ]);
        }
        $extracted = $this->extractAddressFromComponents($components, $formattedAddressForExtract);
        $province = $this->findProvince($extracted['provinceName']);
        if (!$province) {
            $formattedAddr = $data['results'][0]['formatted_address'] ?? '';
            return response()->json([
                'status' => 'partial',
                'message' => 'ไม่พบจังหวัดที่ตรงกับ: ' . $extracted['provinceName'],
                'geocode_log' => [
                    'formatted_address' => $formattedAddr,
                    'extracted' => $extracted,
                    'matched' => null,
                ],
            ]);
        }
        $isBangkok = (mb_stripos($province->name_th ?? '', 'กรุงเทพ') !== false);
        $district = $this->findDistrict($province->id, $extracted['districtName'], $isBangkok);
        if (!$district) {
            $formattedAddr = '';
            foreach ($data['results'] as $r) {
                $comps = $r['address_components'] ?? [];
                foreach ($comps as $c) {
                    if (in_array('country', $c['types'] ?? []) && ($c['short_name'] ?? '') === 'TH') {
                        $formattedAddr = $r['formatted_address'] ?? '';
                        break 2;
                    }
                }
            }
            return response()->json([
                'status' => 'partial',
                'province_id' => $province->id,
                'districts_opt_str' => $this->buildDistrictOptions($province->id),
                'message' => 'ไม่พบเขตที่ตรงกับ: ' . $extracted['districtName'],
                'geocode_log' => [
                    'formatted_address' => $formattedAddr,
                    'extracted' => $extracted,
                    'matched' => ['province' => $province->name_th ?? '', 'district' => null, 'sub_district' => null, 'zip_code' => null],
                ],
            ]);
        }
        $subDistrict = $this->findSubDistrict($district->id, $extracted['subDistrictName'], $extracted['zipCode']);
        if (!$subDistrict && !empty($extracted['zipCode'])) {
            $subByZip = $this->findSubDistrictByZipInProvince($province->id, $extracted['zipCode']);
            if ($subByZip && $subByZip->district_id == $district->id) {
                $subDistrict = $subByZip;
            }
        }
        $resZip = $subDistrict ? (string) ($subDistrict->zip_code ?? '') : $extracted['zipCode'];
        $formattedAddress = '';
        foreach ($data['results'] as $r) {
            $comps = $r['address_components'] ?? [];
            foreach ($comps as $c) {
                if (in_array('country', $c['types'] ?? []) && ($c['short_name'] ?? '') === 'TH') {
                    $formattedAddress = $r['formatted_address'] ?? '';
                    break 2;
                }
            }
        }
        return response()->json([
            'status' => 'success',
            'province_id' => $province->id,
            'district_id' => $district->id,
            'sub_district_id' => $subDistrict ? $subDistrict->id : null,
            'zip_code' => $resZip,
            'districts_opt_str' => $this->buildDistrictOptions($province->id),
            'sub_districts_opt_str' => $this->buildSubDistrictOptions($district->id),
            'geocode_log' => [
                'formatted_address' => $formattedAddress,
                'extracted' => $extracted,
                'matched' => [
                    'province' => $province->name_th ?? '',
                    'district' => $district->name_th ?? '',
                    'sub_district' => $subDistrict ? ($subDistrict->name_th ?? '') : null,
                    'zip_code' => $resZip,
                ],
            ],
        ]);
    }

    /**
     * ตรวจสอบว่าพื้นที่อยู่นอกเขตจัดส่งหรือไม่ (จาก smm_delivery_region_detail)
     * รองรับ 2 โหมด: 1) ส่ง lat,long (จากแผนที่) 2) ส่ง sub_district_id, zip_code (จาก dropdown)
     */
    public function checkDeliveryZone(Request $request)
    {
        $subDistrictId = $request->sub_district_id ?? null;
        $zipCode = $request->zip_code ?? $request->postcode ?? null;
        $lat = $request->lat ?? null;
        $long = $request->long ?? null;

        // โหมด: ส่ง sub_district_id + zip_code โดยตรง (จาก dropdown ที่อยู่)
        if (!empty($subDistrictId) && !empty($zipCode)) {
            $isInZone = \App\SmmDeliveryRegionDetail::isInDeliveryZone($subDistrictId, $zipCode);
            if (!$isInZone && Auth::check()) {
                $this->logOutsideZoneSearch(Auth::id(), $subDistrictId, $zipCode, null, null, []);
            }
            return response()->json([
                'in_delivery_zone' => $isInZone,
                'message' => !$isInZone ? \Lang::get('customer.outside_delivery_zone') : null,
            ]);
        }

        // โหมด: ส่ง lat,long (จากแผนที่) - ต้อง geocode ก่อน
        if (!$lat || !$long) {
            return response()->json([
                'in_delivery_zone' => true,
                'message' => 'ไม่สามารถตรวจสอบได้',
            ]);
        }
        $geocodeRequest = new Request(['lat' => $lat, 'long' => $long]);
        $geocodeResponse = $this->geocodeToSmm($geocodeRequest);
        $data = json_decode($geocodeResponse->getContent(), true);
        $subDistrictId = $data['sub_district_id'] ?? null;
        $zipCode = $data['zip_code'] ?? null;
        $formattedAddress = $data['geocode_log']['formatted_address'] ?? null;
        $matched = $data['geocode_log']['matched'] ?? [];
        $addressParts = [
            'sub_district' => $matched['sub_district'] ?? '',
            'district' => $matched['district'] ?? '',
            'province' => $matched['province'] ?? '',
            'zip_code' => $matched['zip_code'] ?? $zipCode ?? '',
        ];
        // กรณีจังหวัดไม่พบ (status=0 นอกเขตพื้นที่การขาย) - ถือว่าอยู่นอกเขต
        if (isset($data['status']) && $data['status'] === 'partial' && !empty($data['message']) && strpos($data['message'], 'ไม่พบจังหวัด') !== false) {
            return response()->json([
                'in_delivery_zone' => false,
                'message' => \Lang::get('customer.outside_delivery_zone'),
                'formatted_address' => $formattedAddress,
                'address_parts' => $addressParts,
            ]);
        }
        if (empty($subDistrictId) || empty($zipCode)) {
            return response()->json([
                'in_delivery_zone' => true,
                'message' => $data['message'] ?? 'ไม่พบข้อมูลที่อยู่',
                'formatted_address' => $formattedAddress,
                'address_parts' => $addressParts,
            ]);
        }
        $isInZone = \App\SmmDeliveryRegionDetail::isInDeliveryZone($subDistrictId, $zipCode);
        if (!$isInZone && Auth::check()) {
            $this->logOutsideZoneSearch(Auth::id(), $subDistrictId, $zipCode, $lat, $long, $addressParts);
        }
        return response()->json([
            'in_delivery_zone' => $isInZone,
            'message' => !$isInZone ? \Lang::get('customer.outside_delivery_zone') : null,
            'formatted_address' => $formattedAddress,
            'address_parts' => $addressParts,
        ]);
    }

    /**
     * บันทึก log เมื่อลูกค้าเลือกพื้นที่นอกเขตจัดส่ง (สำหรับวิเคราะห์ขยายเขตจัดส่ง)
     */
    private function logOutsideZoneSearch($userId, $subDistrictId, $zipCode, $lat = null, $long = null, $addressParts = [])
    {
        try {
            $provinceId = null;
            $districtId = null;
            if (!empty($addressParts['province_id'] ?? null)) {
                $provinceId = $addressParts['province_id'];
            }
            if (!empty($addressParts['district_id'] ?? null)) {
                $districtId = $addressParts['district_id'];
            }
            \App\ShippingAddressLog::logOutsideZoneSearch($userId, $subDistrictId, $zipCode, $lat, $long, $addressParts, $provinceId, $districtId);
        } catch (\Exception $e) {
            \Log::warning('logOutsideZoneSearch failed: ' . $e->getMessage());
        }
    }

    /**
     * ดึงชื่อจังหวัด/เขต/ตำบล/รหัสไปรษณีย์จาก address_components
     * รองรับหลายรูปแบบ (level 1-3, locality, sublocality)
     * กรณีกรุงเทพ: เขตอาจไม่อยู่ใน components ให้ดึงจาก formatted_address เป็น fallback
     */
    private function extractAddressFromComponents(array $components, $formattedAddress = '') {
        $provinceName = $districtName = $subDistrictName = $zipCode = '';
        foreach ($components as $c) {
            $types = $c['types'] ?? [];
            $long = $c['long_name'] ?? '';
            if (in_array('administrative_area_level_1', $types)) {
                if (empty($provinceName)) $provinceName = $long;
            } elseif (in_array('administrative_area_level_2', $types)) {
                if (empty($districtName)) $districtName = $long;
            } elseif (in_array('administrative_area_level_3', $types)) {
                if (empty($subDistrictName)) $subDistrictName = $long;
            } elseif (in_array('sublocality', $types) || in_array('sublocality_level_1', $types) || in_array('sublocality_level_2', $types)) {
                if (empty($subDistrictName)) $subDistrictName = $long;
            } elseif (in_array('postal_code', $types)) {
                if (empty($zipCode)) $zipCode = $long;
            }
        }
        if (empty($districtName)) {
            foreach ($components as $c) {
                if (in_array('locality', $c['types'] ?? [])) {
                    $districtName = $c['long_name'] ?? '';
                    break;
                }
            }
        }
        // fallback: ดึงเขตจาก formatted_address (กรุงเทพฯ address_components มักไม่มีเขตใน level_2/locality)
        if (empty($districtName) && $formattedAddress !== '') {
            if (preg_match('/เขต\s*([ก-๙\p{L}\s]+?)(?=\s+แขวง|\s+กรุง|\s+จังหวัด|\s+\d{5}|\s*$)/u', $formattedAddress, $m)) {
                $districtName = trim($m[1]);
            } elseif (preg_match('/อำเภอ\s*([ก-๙\p{L}\s]+?)(?=\s+ตำบล|\s+จังหวัด|\s+\d{5}|\s*$)/u', $formattedAddress, $m)) {
                $districtName = trim($m[1]);
            }
        }
        if (empty($provinceName)) {
            foreach ($components as $c) {
                if (in_array('locality', $c['types'] ?? [])) {
                    $provinceName = $c['long_name'] ?? '';
                    break;
                }
            }
        }
        return [
            'provinceName' => $this->normalizeAddressName($provinceName, ['จังหวัด']),
            'districtName' => $this->normalizeAddressName($districtName, ['อำเภอ', 'เขต']),
            'subDistrictName' => $this->normalizeAddressName($subDistrictName, ['ตำบล', 'แขวง']),
            'zipCode' => trim((string) $zipCode),
        ];
    }

    /**
     * หาตำบล/แขวงจาก zip_code ในจังหวัด (fallback เมื่อ district match ไม่ตรง)
     */
    private function findSubDistrictByZipInProvince($provinceId, $zipCode) {
        $zipInt = (int) preg_replace('/\D/', '', (string) $zipCode);
        if ($zipInt <= 0) return null;
        $districtIds = \App\SmmMasterDistrict::where('province_id', $provinceId)->pluck('id');
        return \App\SmmMasterSubDistrict::whereIn('district_id', $districtIds)
            ->where('zip_code', $zipInt)->first();
    }

    /**
     * ตัดคำนำหน้าชื่อที่อยู่ (จังหวัด, อำเภอ, ตำบล ฯลฯ)
     */
    private function normalizeAddressName($name, array $prefixes) {
        $name = trim(preg_replace('/\s+/', ' ', $name));
        foreach ($prefixes as $p) {
            if (mb_stripos($name, $p) === 0) {
                $name = trim(mb_substr($name, mb_strlen($p)));
                break;
            }
        }
        return $name;
    }

    /**
     * หาจังหวัด: exact match ก่อน (name_th, name_en), ไม่เจอค่อย LIKE
     * ใช้ LIKE เฉพาะเมื่อชื่อยาวพอ (>=3 ตัวอักษร) เพื่อลดการ match ผิด
     */
    private function findProvince($name) {
        if (empty($name)) return null;
        $name = trim($name);
        $base = \App\SmmMasterProvince::where('status', 1);
        // 1. Exact match name_th
        $p = (clone $base)->where('name_th', $name)->first();
        if ($p) return $p;
        // 2. Exact match name_en (กรณี Google คืนภาษาอังกฤษ เช่น Bangkok)
        $p = (clone $base)->where('name_en', $name)->first();
        if ($p) return $p;
        // 3. LIKE เฉพาะเมื่อชื่อยาวพอ (ลดการ match ผิด เช่น "น" match หลายจังหวัด)
        if (mb_strlen($name) >= 3) {
            $p = (clone $base)->where('name_th', 'LIKE', '%' . $name . '%')
                ->orderByRaw('LENGTH(name_th) ASC')->first();
            if ($p) return $p;
            $p = (clone $base)->where('name_en', 'LIKE', '%' . $name . '%')
                ->orderByRaw('LENGTH(name_en) ASC')->first();
            if ($p) return $p;
        }
        return null;
    }

    /**
     * หาเขต: exact (name_th, name_en), starts with, contains (รองรับกรุงเทพที่ใช้ "เขต" นำหน้า)
     */
    private function findDistrict($provinceId, $name, $isBangkok = false) {
        if (empty($name)) return null;
        $name = trim($name);
        $base = \App\SmmMasterDistrict::where('province_id', $provinceId);
        // 1. Exact match name_th (DB ใช้ "พระนคร" ไม่มี "เขต" นำหน้า)
        $d = (clone $base)->where('name_th', $name)->first();
        if ($d) return $d;
        $d = (clone $base)->where('name_en', $name)->first();
        if ($d) return $d;
        if ($isBangkok && mb_strpos($name, 'เขต') !== 0) {
            $d = (clone $base)->where('name_th', 'เขต' . $name)->first();
            if ($d) return $d;
        }
        // 2. Starts with (เลือกตัวที่สั้นที่สุด)
        $d = (clone $base)->where('name_th', 'LIKE', $name . '%')
            ->orderByRaw('LENGTH(name_th) ASC')->first();
        if ($d) return $d;
        $d = (clone $base)->where('name_en', 'LIKE', $name . '%')
            ->orderByRaw('LENGTH(name_en) ASC')->first();
        if ($d) return $d;
        if ($isBangkok) {
            $d = (clone $base)->where('name_th', 'LIKE', 'เขต' . $name . '%')
                ->orderByRaw('LENGTH(name_th) ASC')->first();
            if ($d) return $d;
        }
        // 3. Contains (เลือกตัวที่สั้นที่สุด)
        $d = (clone $base)->where('name_th', 'LIKE', '%' . $name . '%')
            ->orderByRaw('LENGTH(name_th) ASC')->first();
        if ($d) return $d;
        return (clone $base)->where('name_en', 'LIKE', '%' . $name . '%')
            ->orderByRaw('LENGTH(name_en) ASC')->first();
    }

    /**
     * หาตำบล/แขวง: zip_code ก่อน (แม่นยำที่สุด), exact (name_th, name_en), contains
     * zip_code ใน DB เป็น int - ต้อง cast ให้ตรง
     */
    private function findSubDistrict($districtId, $name, $zipCode = null) {
        $base = \App\SmmMasterSubDistrict::where('district_id', $districtId);
        // 1. ใช้ zip_code ก่อน (ถ้ามีจาก Google) - DB ใช้ int
        if (!empty($zipCode)) {
            $zipInt = (int) preg_replace('/\D/', '', (string) $zipCode);
            if ($zipInt > 0) {
                $s = (clone $base)->where('zip_code', $zipInt)->first();
                if ($s) return $s;
            }
        }
        if (empty($name)) return null;
        $name = trim($name);
        // 2. Exact match name_th, name_en
        $s = (clone $base)->where('name_th', $name)->first();
        if ($s) return $s;
        $s = (clone $base)->where('name_en', $name)->first();
        if ($s) return $s;
        // 3. Contains (เลือกตัวที่สั้นที่สุด)
        $s = (clone $base)->where('name_th', 'LIKE', '%' . $name . '%')
            ->orderByRaw('LENGTH(name_th) ASC')->first();
        if ($s) return $s;
        return (clone $base)->where('name_en', 'LIKE', '%' . $name . '%')
            ->orderByRaw('LENGTH(name_en) ASC')->first();
    }

    private function buildDistrictOptions($provinceId) {
        $opt = '<option value="">--' . Lang::get('common.select') . '--</option>';
        $list = \App\SmmMasterDistrict::getByProvinceId($provinceId);
        foreach ($list as $d) {
            $opt .= '<option value="' . $d->id . '">' . htmlspecialchars($d->name_th) . '</option>';
        }
        return $opt;
    }

    private function buildSubDistrictOptions($districtId) {
        $opt = '<option value="">--' . Lang::get('common.select') . '--</option>';
        $list = \App\SmmMasterSubDistrict::getByDistrictId($districtId);
        foreach ($list as $s) {
            $zip = isset($s->zip_code) ? htmlspecialchars($s->zip_code) : '';
            $opt .= '<option value="' . $s->id . '" data-zip="' . $zip . '">' . htmlspecialchars($s->name_th) . '</option>';
        }
        return $opt;
    }

    /**
     * Dropdown ที่อยู่จาก smm_master_* (จังหวัด→เขต→ตำบล/แขวง→รหัสไปรษณีย์ auto-fill)
     * กรองเฉพาะพื้นที่ที่อยู่ในเขตจัดส่ง - พื้นที่นอกเขตจะไม่แสดงใน dropdown
     */
    function getSmmAddressDD(Request $request) {
        try {
            $opt_str = '<option value="">--'.Lang::get('common.select').'--</option>';
            $status = 'success';
            $zip_code = '';
            $address_id = $request->address_id;
            $address_type = $request->address_type ?? '';

            if ($address_type == 'province_state' && $address_id !== '' && $address_id !== null) {
                // กรองเฉพาะเขตที่อยู่ในเขตจัดส่ง
                $district_ids_in_zone = \App\SmmDeliveryRegionDetail::getDistrictIdsInZone($address_id);
                $result = \App\SmmMasterDistrict::getByProvinceId($address_id);
                if ($district_ids_in_zone->isNotEmpty()) {
                    $result = $result->whereIn('id', $district_ids_in_zone);
                }
                if ($result->isNotEmpty()) {
                    foreach ($result as $value) {
                        $opt_str .= '<option value="'.$value->id.'">'.htmlspecialchars($value->name_th).'</option>';
                    }
                }
            }
            elseif ($address_type == 'city_district' && $address_id !== '' && $address_id !== null) {
                // กรองเฉพาะตำบล/แขวงที่อยู่ในเขตจัดส่ง
                $result = \App\SmmDeliveryRegionDetail::getSubDistrictsInZone($address_id);
                if ($result->isNotEmpty()) {
                    foreach ($result as $value) {
                        $zip_val = isset($value->zip_code) ? (string) $value->zip_code : '';
                        $opt_str .= '<option value="'.$value->id.'" data-zip="'.htmlspecialchars($zip_val).'">'.htmlspecialchars($value->name_th).'</option>';
                    }
                }
            }
            elseif ($address_type == 'sub_district' && $address_id !== '' && $address_id !== null) {
                $zip_val = \App\SmmMasterSubDistrict::getZipBySubDistrictId($address_id);
                $zip_code = $zip_val !== null ? (string) $zip_val : '';
            }
            else {
                $status = 'failed';
            }

            return response()->json([
                'opt_str' => $opt_str,
                'status' => $status,
                'zip_code' => $zip_code,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'opt_str' => '<option value="">--'.Lang::get('common.select').'--</option>',
                'status' => 'failed',
                'zip_code' => '',
                'error' => $e->getMessage(),
            ], 500);
        }
    }         

    public function WeightPerPackage(Request $request){
        $packageid = $request->packageid;
        $packagedesc = PackageDesc::where("package_id",$packageid)->select("package_name")->first();
        return response()->json(['status' => 'success', 'message' => 'กิโลกรัม/'.$packagedesc->package_name]);
    }
}



