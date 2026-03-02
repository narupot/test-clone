<?php
namespace App\Helpers;
use Illuminate\Support\Facades\DB;

use App\Country;
use App\CountryDesc;
use Carbon\Carbon;
use Auth;
use Lang;
use Request;
use Config;

class GeneralFunctions {

    public static function getCountryName($country_id) {
        
        $country_name = '';
        if(!empty($country_id)) { 
            $country = CountryDesc::select('country_name')->where([['country_id', '=', $country_id],['lang_id', '=', session('default_lang')]])->first();
            $country_name = $country->country_name;
        }
        return $country_name;
    } 
    
    public static function getProvinceName($province_id) {

        $province_name = '';
        if(!empty($province_id)) {         
            $province = \App\CountryProvinceStateDesc::select('province_state_name')->where([['province_state_id', '=', $province_id],['lang_id', '=', session('default_lang')]])->first();
            $province_name = $province->province_name;
        }
        return $province_name;
    }

    public static function getCityName($city_id) {

        $city_name = '';
        if(!empty($province_id)) {         
            $city = \App\CountryCityDistrictDesc::select('city_district_name')->where([['city_district_id', '=', $city_id],['lang_id', '=', session('default_lang')]])->first();
            $city_name = $province->province_name;
        }
        return $city_name;
    }     
    
    public static function getCountryArr() {
        
        $country_lists = Country::select('id')->get();
        
        $country_arr[''] = '--select country--';
        
        foreach($country_lists as $country)
        {                      
           $country_arr[$country->id] = $country->countryName->country_name;
        } 
        
        //echo '<pre>';print_r($country_arr);die;
        
        return $country_arr;
    }

    public static function getIsdCodeArr() {
        
        $country_lists = Country::select('id', 'country_isd')->get();
        
        $country_arr[''] = '--isd code--';
        
        foreach($country_lists as $country)
        {                      
           $country_arr[$country->country_isd] = $country->country_isd;
        }
        
        //echo '<pre>';print_r($country_arr);die;
        
        return $country_arr;
    }

    public static function getProductPriceById($productId, $quantity=1, $prdinfo=null){

        $date = date('Y-m-d');
        if(!empty($prdinfo)){
            $res = $prdinfo;
        }else{
            $res = \App\Product::select('unit_price','stock','is_tier_price')->where(['id' => $productId])->first();
        }
        
        if($res->is_tier_price == 1){
            /****for tier price***/
            $startQty = \App\ProductTierPrice::select('start_qty')->where('product_id',$productId)->orderBy('start_qty','asc')->first();
            $endQty = \App\ProductTierPrice::select('end_qty','unit_price')->where('product_id',$productId)->orderBy('end_qty','desc')->first();

            if(count($startQty) && count($endQty)){
                $start_quantity = $startQty->start_qty;
                $end_quantity = $endQty->end_qty;
            }else{
                return $res->unit_price;
            }
            
            if($quantity < $start_quantity){
                return $res->unit_price;
            }elseif($quantity > $end_quantity){
                return $endQty->unit_price;
            }else{ /**calculating tier price**/

                $tableName = with(new \App\ProductTierPrice)->getTable();
                $prefix = DB::getTablePrefix();
                $tire = DB::select( DB::raw("SELECT * FROM $prefix$tableName WHERE $quantity BETWEEN `start_qty` and `end_qty` and product_id = $productId ") );
                if($tire){
                    
                    return $tire[0]->unit_price;
                }else{
                    return $res->unit_price;
                }
                
            }
          
        }else{
          return $res->unit_price;
        }
    }


    /* it will return cartisian product of arrays */

    function getmultiply($qty,$max){
        $i = 0;
        $my = 0;
        if($qty >= $max){
        while($my <= $qty){
          $my = $max + $my;
          $i++;
        }
        return $i-1;
        }else return $i;
    }


    public static function getFormattedId() {
        return time().sprintf("%04d", mt_rand(1, 9999));
    } 

    public static function numberFormat($price, $shopId=Null)
    {
        return number_format($price,2);
    }

    public static function printData($data){
        return strip_tags($data);
    }


    public static function getUserDetail($user_id) {

        $user_detail = \App\User::userDetail($user_id);

        $user_detail->formated_id = $user_detail->id;
        $user_detail->user_name = ucfirst($user_detail->name).' '.ucfirst($user_detail->sur_name);
        $user_detail->display_name = ucfirst($user_detail->display_name);
        $user_detail->user_dob = getDateFormat($user_detail->dob);        

        if($user_detail->mobile > 0) {
            $user_detail->contact_no = $user_detail->mobile_isd_code.$user_detail->mobile;
        }

        if($user_detail->gender == 'M') {
            $user_detail->gender_new = 'Male';
        }        
        elseif($user_detail->gender == 'F') {
            $user_detail->gender_new = 'Female';
        }
        else {
            $user_detail->gender_new = 'Undefined';
        }

        if(!empty($user_detail->image)) {
            $user_detail->image_url = Config::get('constants.users_url').$user_detail->image;
        } 
        else if($user_detail->gender == 'M' || $user_detail->gender == 'U') {
            $user_detail->image_url = self::getPlaceholderImage('USER_IMAGE');
        }        
        elseif($user_detail->gender == 'F') {
             $user_detail->image_url = self::getPlaceholderImage('USER_IMAGE_FEMALE');
        }
        //echo '<pre>';print_r($user_detail->toArray());die;

        return $user_detail;        
    }

    

    public static function getCategoryDropDownData($cat_path) {

        $blog_mkt_cat_opt_arr = array();

        $blog_main_cat_arr = array_filter(explode('-', $cat_path));
        foreach ($blog_main_cat_arr as $value) {

            $blog_mkt_cat_opt_dtl = \App\Category::getMarketPlaceCatOpt($value);

            $blog_mkt_cat_opt_tmp = array();
            foreach ($blog_mkt_cat_opt_dtl->parentCategory as $cat_opt_value) {
                if(isset($cat_opt_value->categorydesc->name)){
                    $blog_mkt_cat_opt_tmp[] = ['cat_id'=>$cat_opt_value->id, 'cat_name'=>$cat_opt_value->categorydesc->name, 'selected_cat_id'=>$value];    
                }
                
            }
            $blog_mkt_cat_opt_arr[] = $blog_mkt_cat_opt_tmp;
        }

        return $blog_mkt_cat_opt_arr;
    } 

    public static function fetchValue($model, $field, $id) {
        return $model::select($field)->where('id', $id)->first()->$field;
    }

    public static function fetchValueDesc($modelDesc, $field, $match_field, $match_id) {
        return $modelDesc::select($field)->where([$match_field=>$match_id, 'lang_id'=>session('default_lang')])->first()->$field;
    }


    public static function getPlaceholderImage($systemname)
    {
        static $configCache = [];
        static $avatarCache = [];

        if (!isset($configCache[$systemname])) {
            $configCache[$systemname] = getConfigValue($systemname);
        }

        $system_val = $configCache[$systemname];

        if (empty($system_val)) {
            return '';
        }

        if (in_array($systemname, ['USER_IMAGE', 'USER_IMAGE_FEMALE'])) {
            if (!isset($avatarCache[$system_val])) {
                $avatarCache[$system_val] = \App\AdminAvatar::select('name')->find($system_val);
            }

            $img_name = $avatarCache[$system_val];
            return $img_name ? Config::get('constants.avtar_images_url') . $img_name->name : '';
        }

        return Config::get('constants.placeholder_url') . $system_val;
    }


    public static function payStatusCircle($value){
        //dd($value);
        return ($value == 1)?'c-tot':'';
    }

    public static function getSpecificStatus($status_id,$status_ids){
        $status = \App\OrderStatus::orderSpecificStatus($status_id,$status_ids);
        return @$status->orderStatusDesc->value;

    }

    public static function addDaysToDate($date,$numdays){
        return date('Y-m-d H:i:s', strtotime($date. ' + '.$numdays.' days'));
    }

    public static function dateDiffDetails($largeDate,$smallDate){
        $start_date = new \DateTime($smallDate);
        $since_start = $start_date->diff(new \DateTime($largeDate));
        $details = ['d'=>$since_start->d,'h'=>$since_start->h,'m'=>$since_start->i,'s'=>$since_start->s];
        return $details;
    }

    public static function getDateByTimezone($dt, $tz1, $df1, $tz2, $df2) {
      //echo '====>'.$dt.'==='.$tz1.'==='.$df1.'==='.$tz2.'==='.$df2;  
      // create DateTime object
      $d = \DateTime::createFromFormat($df1, $dt, new \DateTimeZone($tz1));
      // convert timezone
      $d->setTimeZone(new \DateTimeZone($tz2));
      // convert dateformat
      return $d->format($df2);
    }
  
    public static function calculateTimeDifference($from, $to=null, $ype=1){

        if(empty($to)){
            $to = Carbon::now();

        }else{
            $to = new Carbon($to);

        }

        $returns_times = new Carbon($from);

       // echo $returns_times->diff($to)->h;

        if ($returns_times->diffInMinutes($to) <= 1 ) {
            $lastOnline = "a minute ago";
        } elseif ($returns_times->diffInHours($to) < 1) {
            $lastOnline = $returns_times->diffInMinutes($to) > 1 ? sprintf(" %d minutes ago", $returns_times->diffInMinutes($to)) : sprintf(" %d minute ago", $returns_times->diffInMinutes($to));
        } elseif ($returns_times->diffInDays($to) < 1) {
            $lastOnline = $returns_times->diffInHours($to) > 1 ? sprintf(" %d hours ago", $returns_times->diffInHours($to)) : sprintf(" %d hour ago", $returns_times->diffInHours($to));
        } elseif ($returns_times->diffInWeeks($to) < 1) {
            $lastOnline = $returns_times->diffInDays($to) > 1 ? sprintf(" %d days ago", $returns_times->diffInDays($to)) : sprintf(" %d day ago", $returns_times->diffInDays($to));
        } elseif ($returns_times->diffInMonths($to) < 1) {
            $lastOnline = $returns_times->diffInWeeks($to) > 1 ? sprintf(" %d weeks ago", $returns_times->diffInWeeks($to)) : sprintf(" %d week ago", $returns_times->diffInWeeks($to));
        } elseif ($returns_times->diffInYears($to) < 1) {
            $lastOnline = $returns_times->diffInMonths($to) > 1 ? sprintf(" %d months ago", $returns_times->diffInMonths($to)) : sprintf(" %d month ago", $returns_times->diffInMonths($to));
        } else {
            $lastOnline = $returns_times->diffInYears($to) > 1 ? sprintf(" %d years ago", $returns_times->diffInYears($to)) : sprintf(" %d year ago", $returns_times->diffInYears($to));
        }
        return $lastOnline;
    }


    public static function systemConfig($system_name=null) {
        $system_val = '';
        if(!empty($system_name)){ 
            $system_val = \App\SystemConfig::getSystemVal($system_name);
        }  
        return $system_val;         
    } 

    public static function getDefaultCountryDetail() {
        
        $def_country_code = self::getCountryByIp('country_code');
        //$def_country_code = 'IN';
        if(!empty($def_country_code)) {
            $def_country_dtl = Country::getCountryDetail($def_country_code, 'country_code');
        }
        else {
            $def_country_dtl = Country::getCountryDetail('', 'default');
        }

        return $def_country_dtl;
    }

    public static function getCountryByIp($return_type=null) {
        
        if(Config::get('constants.localmode') === true) {
            
            $data = '';
        }
        else {

            $data = '';

            // $user_ip = request()->ip();
            // $ip_detail = file_get_contents('https://ipapi.co/'.$user_ip.'/json/');
            // $ip_detail = json_decode($ip_detail);
            // //dd($ip_detail);


            // if($return_type === null) {
            //     $data = $ip_detail;
            // }
            // else if(isset($ip_detail->country) && $return_type == 'country_code') {
            //     $data = $ip_detail->country;
            // }
            // else{
            //     $data = '';
            // }            
        }
        
        return $data;        
    }

    public static function getTotalReply($review_id) {
        return \App\BlogReview::totalReply($review_id);
    }




    public static function  getGoogleShortUrl($url){

        $target = 'https://www.googleapis.com/urlshortener/v1/url?';
        $extended = false;
        $apiKey ='AIzaSyDjwue1pEpGtGKz-k_KbcTlozDv4ezjoMw';

        if ( $apiKey != null ) {
            $apiKey = $apiKey;
            $target .= 'key='.$apiKey.'&';
        }


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $target);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $data = array( 'longUrl' => $url );
        $data_string = '{ "longUrl": "'.$url.'" }';

        curl_setopt($ch, CURLOPT_POST, count($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array('Content-Type: application/json'));
       // $ret = json_decode(curl_exec($ch));
        //curl_close($ch);
        //return $ret;
       if ( $extended) {
            $ret = json_decode(curl_exec($ch));
            curl_close($ch);

        } else {
             $ret = json_decode(curl_exec($ch))->id;
            //$ret = json_decode(curl_exec($ch))->longUrl;
            curl_close($ch);
            return $ret;

        }
    }

    public static function getSiteLogo($system_name) {
        //$logo_name =  getConfigValue($system_name);
        $logo_name = '';
        $logo_url =  Config('constants.site_logo_url').$logo_name;
        return $logo_url;
    }

    public static function embededCssJs($page){
        $data = \App\CssJsEmbeded::getEmbededData();
        
        $page = \Request::path();

        $css_arr = $js_arr = [];
        foreach ($data as $key => $value) {
            if($value->custom_url || $value->page_url){
                if($value->custom_url && $value->page_url){
                    $value->page_url  = $value->page_url.','.$value->custom_url;
                }elseif($value->custom_url){
                    $value->page_url  = $value->custom_url;
                }
               
                $checkPage = \App\Block::checkPage($value->id,$page,$value);

                if($checkPage){
                    if($value->embeded_css){
                        $css_arr = array_merge($css_arr,explode(',', $value->embeded_css));
                    }

                    if($value->embeded_js){
                        $js_arr = array_merge($js_arr,explode(',', $value->embeded_js));
                    }
                    
                }
            }
        }
        return ['embeded_css'=>$css_arr,'embeded_js'=>$js_arr];
    }
    
    public static function sectionData($page){

        $page = \Request::path();
        
        $static_left = $static_header = $static_right = $slider = $static_footer = 0;
          $section = \App\Block::getBlockByIdArr();
        //   dd($section);
          $section_arr = [];
          foreach ($section as $key => $value) {
            $section_arr[$value->section_id][] = $value;
          }
        //   dd($section_arr);
          $header_section = isset($section_arr[1]) ? $section_arr[1] : [];
          $footer_section = isset($section_arr[2]) ? $section_arr[2] : [];
          $left_section = isset($section_arr[3]) ? $section_arr[3] : [];
          $right_section = isset($section_arr[4]) ? $section_arr[4] : [];
          $main_section = isset($section_arr[5]) ? $section_arr[5] : [];
        //   dd($main_section);
          //dd($header_section);
          
          $header_content = Self::checkSection($header_section,$page);
          $left_content = Self::checkSection($left_section,$page);
          $right_content = Self::checkSection($right_section,$page);
          $main_content = Self::checkSection($main_section,$page);
          $footer_content = Self::checkSection($footer_section,$page);
        //   dd($main_content);
          if(count($header_content)){
            $static_header = 1;
          }
          if(count($left_content)){
            $static_left = 1;
          }
          if(count($right_content)){
            $static_right = 1;
          }
          if(count($footer_content)){
            $static_footer = 1;
          }

          if(count($main_content)){
            foreach ($main_content as $key => $value) {
              if(isset($value->banner_type) && $value->banner_type == 'slider'){
                $slider = 1;
              }
            }
          }

          return ['header'=>$static_header,'left'=>$static_left,'right'=>$static_right,'header_content'=>$header_content,'left_content'=>$left_content,'right_content'=>$right_content,'main_content'=>$main_content,'footer_content'=>$footer_content,'slider'=>$slider];
    }


    public static function checkSection($contentSection,$page=''){
        //$sec_id = 4;
        $block_id_arr = [];
        $groupCon = $pageCon = 0;
        $user_group_id = (Auth::check()) ? session('user_group_id') : 1;
        //dd($contentSection);
        //$contentSection = \App\Block::where('section_id',$sec_id)->orderBy('order_by')->get();

        $ip_address = \Request::ip();
        //dd($ip_address);
        if(count($contentSection)){
            foreach ($contentSection as $key => $section) {

                /****checking ip address****
                ***if ip address exist then allow only that ips */
                if(trim($section->allow_ip)){
                    $exp_ip = explode(',', $section->allow_ip);
                    if(in_array($ip_address, $exp_ip)){
                        $ipcon = 1;
                    }else{
                        $ipcon = 0;
                    }
                }else{
                    $ipcon = 1;
                }

                $datecon = 0;
                $groupCon = 0;
                $pageCon = 0;
                if($ipcon){
                    /***checking start and end date ********/
                    if($section->start_date || $section->end_date){
                        $start_sec = strtotime($section->start_date);
                        $end_sec = strtotime($section->end_date);
                        $curdate = date('Y-m-d H:i:s');
                        $cur_sec = strtotime($curdate);
                        $datecon = 0;

                        if($start_sec>0 && $end_sec>0){
                            
                            if($cur_sec >= $start_sec && $cur_sec <= $end_sec){
                                $datecon = 1;
                            }
                        }elseif($start_sec>0 && $cur_sec >= $start_sec){
                            $datecon = 1;
                        }elseif($end_sec>0 && $cur_sec <= $end_sec){
                            $datecon = 1;
                        }
                    }else{
                        $datecon = 1;
                    }
                }

                if($ipcon && $datecon){
                    /***checking group*****/
                    if($section->customer_group == 1){
                        $groupCon = 1;
                    }elseif ($section->customer_group == 2) {
                       $checkGroup = \App\BlockCustomerGroup::checkGroup($section->id,$user_group_id);
                       $groupCon = ($checkGroup) ? 1 : 0;
                    }else{
                        $checkGroup = \App\BlockCustomerGroup::checkGroup($section->id,$user_group_id);
                        $groupCon = ($checkGroup) ? 0 : 1;
                    }
                }

                if($ipcon && $datecon && $groupCon){
                    /***checking pages****/
                    if($section->pages == 1){
                        $pageCon = 1;
                    }elseif ($section->pages == 2) {
                       $checkPage = \App\BlockPage::checkPage($section->id,$page);
                       $pageCon = ($checkPage) ? 1 : 0;
                    }else{
                        $checkPage = \App\BlockPage::checkPage($section->id,$page);
                        $pageCon = ($checkPage) ? 0 : 1;
                    }

                    if($pageCon){
                        $block_id_arr[$section->id] = $section;
                    }
                }

                if($ipcon && $datecon && $groupCon && $pageCon){
                    switch($section->type){

                        case 'static-block' :
                        $static_block_desc = \App\StaticBlockDesc::where('static_block_id',$section->type_id)->where('lang_id',session('default_lang'))->first();
                        $contentSection[$key]->static_title = isset($static_block_desc->page_title) ? $static_block_desc->page_title :'';
                        $contentSection[$key]->static_desc = isset($static_block_desc->page_desc) ?$static_block_desc->page_desc :'';
                        $contentSection[$key]->block_url_key = \App\StaticBlock::where('id',$section->type_id)->value('url');
                        break;

                        case 'banner' :
                        $banner_detail = \App\Banner::getBannerDetail($section->type_id);
                        $banner_type = (count($banner_detail) ==1) ? 'banner' : 'slider';
                        $contentSection[$key]['slider'] = $banner_detail;
                        $contentSection[$key]['banner_type'] = $banner_type;
                        $contentSection[$key]->block_url_key = \App\BannerGroup::where('id',$section->type_id)->value('group_name');
                        $contentSection[$key]->speed = \App\BannerGroup::where('id',$section->type_id)->value('slide_speed');
                        break;
                        case 'cms-slider' :
                        $slider_detail = \App\CmsSlider::getSliderDetail($section->type_id);
                        // dd($slider_detail);
                        if(!empty($slider_detail)){
                            
                            $contentSection[$key]->cms_slider = $slider_detail;
                            $contentSection[$key]->block_url_key = $slider_detail->name;
                        }
                        break;
                    }
                }else{
                    unset($contentSection[$key]);
                }
                
            }

            return count($block_id_arr) ? $contentSection : [];
        }
        else
            return [];
    }

    /*Added By Satish Anand for Blog Module Start*/
    public static function BlogSectionData($page){

        $page = \Request::path();
          $static_left =  $static_right = $slider = 0;
          $section = \App\Widget::getBlockByIdArr();
          $section_arr = [];
          foreach ($section as $key => $value) {
            $section_arr[$value->section_id][] = $value;
          }
                    
          $left_section = isset($section_arr[2]) ? $section_arr[2] : [];
          $right_section = isset($section_arr[3]) ? $section_arr[3] : [];
          $main_section = isset($section_arr[4]) ? $section_arr[4] : [];
          
          $left_content = Self::BlogCheckSection($left_section,$page);
          $right_content = Self::BlogCheckSection($right_section,$page);
          $main_content = Self::BlogCheckSection($main_section,$page);
                    
          if(count($left_content)){
            $static_left = 1;
          }
          if(count($right_content)){
            $static_right = 1;
          }

          if(count($main_content)){
            foreach ($main_content as $key => $value) {
              if(isset($value->banner_type) && $value->banner_type == 'slider'){
                $slider = 1;
              }
            }
          }

          return ['left'=>$static_left,'right'=>$static_right,'left_content'=>$left_content,'right_content'=>$right_content,'main_content'=>$main_content,'slider'=>$slider];
    }


    public static function blogCheckSection($contentSection,$page=''){
        
        $block_id_arr = [];
        $groupCon = $pageCon = 0;
        $user_group_id = (Auth::check()) ? session('user_group_id') : 1;
        
        
        if(count($contentSection)){
            foreach ($contentSection as $key => $section) {
                /***checking group*****/
                if($section->customer_group == 1){
                    $groupCon = 1;
                }elseif ($section->customer_group == 2) {
                   $checkGroup = \App\WidgetCustomerGroup::checkGroup($section->id,$user_group_id);
                   $groupCon = ($checkGroup) ? 1 : 0;
                }else{
                    $checkGroup = \App\WidgetCustomerGroup::checkGroup($section->id,$user_group_id);
                    $groupCon = ($checkGroup) ? 0 : 1;
                }

                if($groupCon){
                    /***checking pages****/
                    if($section->pages == 1){
                        $pageCon = 1;
                    }elseif ($section->pages == 2) {
                       $checkPage = \App\WidgetPage::checkPage($section->id,$page);
                       $pageCon = ($checkPage) ? 1 : 0;
                    }else{
                        $checkPage = \App\WidgetPage::checkPage($section->id,$page);
                        $pageCon = ($checkPage) ? 0 : 1;
                    }

                    if($pageCon){
                        $block_id_arr[$section->id] = $section;
                    }
                }

                if($groupCon && $pageCon){
                    switch($section->type){

                        case 'widget' :
                        $static_block_desc = \App\StaticBlockDesc::where('static_block_id',$section->type_id)->where('lang_id',session('default_lang'))->first();
                        $contentSection[$key]->static_title = isset($static_block_desc->page_title) ? $static_block_desc->page_title :'';
                        $contentSection[$key]->static_desc = isset($static_block_desc->page_desc) ?$static_block_desc->page_desc :'';
                        $contentSection[$key]->block_url_key = \App\StaticBlock::where('id',$section->type_id)->value('url');
                        break;

                        case 'banner' :
                        $banner_detail = \App\Banner::getBannerDetail($section->type_id);
                        $banner_type = (count($banner_detail) ==1) ? 'banner' : 'slider';
                        $contentSection[$key]['slider'] = $banner_detail;
                        $contentSection[$key]['banner_type'] = $banner_type;
                        $contentSection[$key]->block_url_key = \App\BannerGroup::where('id',$section->type_id)->value('group_name');
                    }
                }else{
                    unset($contentSection[$key]);
                }
                
            }


            return count($block_id_arr) ? $contentSection : [];
        }
        else
            return [];
    }

    /*Added By Satish Anand for Blog Module End*/


    public static function checkFixSection($sec_id,$page='',$block_key,$type){

        return \App\Block::checkBlockExist($sec_id,$page,$block_key,$type);
    }

    public static function userAttributeData($show_on='2'){

        $default_group_id = \App\CustomerGroup::select('id')->where(['is_default'=>'1','status'=>'1'])->first();

        return \App\CustomerAttribute::attributeByGroup($default_group_id->id, $show_on);
    }

    public static function getEmailToken()
    {
         $length = 10;
         $token = "";
         $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
         $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
         $codeAlphabet.= "0123456789";
         $max = strlen($codeAlphabet); // edited

        for ($i=0; $i < $length; $i++) {
            $token .= $codeAlphabet[random_int(0, $max-1)];
        }
        
        return $token;
    }
    
    public static function getShippingMethod($shipping_method){
        switch ($shipping_method) {
            case '1':
                $val = Lang::get('checkout.pick_up_at_center');
                break;
            case '2':
                $val = Lang::get('checkout.pick_up_at_the_store');
                break;
            case '3':
                $val = Lang::get('checkout.delivery_at_the_address');
                break;
            
            default:
                $val = '';
                break;
        }
        return $val;
    }

    public static function getOrderText($order_slug,$item_name='',$pay_type=''){
        switch ($order_slug) {
            case 'order_created':
                $val = 'Order created';
                break;

            case 'shop_order_created':
                $val = 'Shop order created';
                break;

            case 'item_payment_done':
                $val = 'Order item '.$item_name.' payment done from '.$pay_type;
                break;

            case 'order_end_shopping':
                $val = 'Order end shopping with payment';
                break;

            case 'item_cancel':
                $val = 'Order item '.$item_name.' cancelled';
                break;

            case 'item_center_receive':
                $val = 'Order item '.$item_name.' center received';
                break;

            case 'order_completed':
                $val = 'Order completed';
                break;

            case 'order_cancelled':
                $val = 'Order cancelled';
                break;

            case 'item_receive_buyer':
                $val = 'Order item '.$item_name.' received';
                break;
            case 'all_item_receive_buyer':
                $val = 'Shop Order '.$item_name.' items  received';
                break;

            case 'order_receive_buyer':
                $val = 'Order '.$item_name.' items  received';
                break;
            case 'edit_payment_status':
                $val = 'Order '.$item_name.' payment status updated';
                break;
            default:
                # code...
                break;
        }
        return $val;
    }
    public static function getPaymentInfo($orderInfo, $return_type="N") {
        //dd($orderInfo);

        $orderId = $orderInfo->id;
        $paymentType = $orderInfo->payment_type;

        $orderInfoJson = json_decode($orderInfo->order_json,true);

        $lang_code = !empty(session('lang_code'))?session('lang_code'):$orderInfo->lang_checkout;

        $pay_opt_name = $orderInfo->payment_type_id ? $orderInfoJson['payment_method'][$lang_code]:'';
        //dd($paymentType);
        if($paymentType == '1') { /**online**/
            $str = '<span class="text-default not-log  d-block text-primary">'.Lang::get('checkout.online').' ('.$pay_opt_name.')</span>';
            if($orderInfo->payment_status == '0') {
                $str .= '<span class="text-default d-block">'.Lang::get('common.status').' :<span class="text-failed"> '.Lang::get('checkout.not_paid').'</span></span>';
            }
            else {
                $str .= '<span class="text-default d-block">'.Lang::get('common.status').' :<span class="text-success"> '.Lang::get('checkout.paid').'</span></span>';
                $str .= '<span class="text-default d-block">'.Lang::get('checkout.transaction_id').' : '.$orderInfo->txn_id.'</span>';
            }
        }   
        else if($paymentType == '3') {   //COD
            $str = '<h5 class="not-log d-block text-primary text-left pl-0" style="font-size: 12px;margin: 0 0 5px 0;">'.Lang::get('checkout.offline').' ('.$pay_opt_name.')</h5>
                <h5 class="text-default d-block w-100" style="font-size: 12px;margin: 0 0 5px 0;">'.Lang::get('common.status').' : ';
            if($orderInfo->payment_status == '1') {
                $str.='<span class="text-success">'.Lang::get('checkout.paid').'</span>';
            }
            else {
                $str.='<span class="text-failed">'.Lang::get('checkout.not_paid').'</span>';
            }
            $str .= '</h5>';
        }            
        else if($paymentType == '2'){  //bank payment

            $str = '<h5 class="not-log d-block text-primary text-left pl-0" style="font-size: 12px;margin: 0 0 5px 0;">'.Lang::get('checkout.offline').' ('.$pay_opt_name.')</h5>';

            $offPayInfoRes = \App\OrderOfflinePayment::where('order_id',$orderId)->with('bank')->with('toBank')->orderBy('id','Desc')->get();
            //dd($offPayInfoRes);
            if(count($offPayInfoRes)){
                foreach ($offPayInfoRes as $key => $offPayInfo) {
                    $bank_name = !empty($offPayInfo->toBank)?$offPayInfo->toBank->bank_name:'';
                    if($offPayInfo->bank->account_type == '1') {
                        $ac_type = Lang::get('checkout.saving_account');
                    } else {
                        $ac_type = Lang::get('checkout.current_account');
                    }
                    $str .= ++$key.'.<br/>';
                    $str.= '<h5 class="text-default" style="font-size: 12px;margin: 0 0 5px 0;"><span>'.Lang::get('checkout.name').' :</span> <span class="emphasis-default">'.$offPayInfo->name.'</span></h5>';
                    $str.='<h5 class="text-default" style="font-size: 12px;margin: 0 0 5px 0;"><span>'.Lang::get('checkout.amount').' :</span> <span class="emphasis-default">'.numberFormat($offPayInfo->amount,$orderInfo->currency_id).' '.$orderInfo->getCurrency->currency_code.'</span></h5>';

                    $str.= '<h5 class="text-default" style="font-size: 12px;margin: 0 0 5px 0;"><span>'.Lang::get('checkout.to_account_no').' :</span> <span class="emphasis-default">'.$offPayInfo->to_account_no.'/'.$offPayInfo->bank->account_name.'</span></h5>';
                    $str.= '<h5 class="text-default" style="font-size: 12px;margin: 0 0 5px 0;"><span>'.Lang::get('checkout.account_detail').' :</span> <span class="emphasis-default"><img src="'.Config::get('constants.payment_bank_url').$offPayInfo->bank->bank_image.'" class="payment-logo" style="display: inline-block;max-width: 42px;vertical-align: middle;"> '.$bank_name.', '.$offPayInfo->bank->branch.', '.$ac_type.'</span></h5>';
                    $str .='<h5 class="text-default" style="font-size: 12px;margin: 0 0 5px 0;"><span>'.Lang::get('checkout.transfer_date').' :</span> <span class="emphasis-default">'.getDateFormat($offPayInfo->transfer_date, '5').'</span></h5>';
                    if($offPayInfo->file)
                        $str.='<h5 class="text-default" style="font-size: 12px;margin: 0 0 5px 0;"><span><a class="" href="'.Config::get('constants.buyer_payment_url').$offPayInfo->file.'" target="_blank"><img src="'.Config::get('constants.buyer_payment_url').$offPayInfo->file.'" class="payment-logo" style="display: inline-block;max-width: 42px;vertical-align: middle;"></a></span></h5>';
                    
                    if($offPayInfo->status == '1'){
                        $str.='<h5 class="text-default d-block w-100" style="font-size: 12px;margin: 0 0 5px 0;">'.Lang::get('common.status').' : <span class="text-success" style="color: #28a745;">'.Lang::get('checkout.paid').'</span></h5>';
                    }elseif($offPayInfo->status == '0'){
                        $str.='<h5 class="text-default d-block w-100" style="font-size: 12px;margin: 0 0 5px 0;">'.Lang::get('common.status').' : <span class="text-pending" style="color: #ffc107;">'.Lang::get('checkout.waiting_for_approve').'</span> </h5>';
                    }else{
                        $str .='<h5 class="text-default d-block w-100" style="font-size: 12px;margin: 0 0 5px 0;">'.Lang::get('common.status').' : <span class="text-failed" style="color: #ff0000;">'.Lang::get('checkout.payment_rejected').'</span></h5>';
                    }

                    if($offPayInfo->comment) {
                        $str .='<h5 class="text-default d-block" style="font-size: 12px;margin: 0 0 5px 0;">'.Lang::get('checkout.comment').' : '.$offPayInfo->comment.'</h5>';
                    }
                    $str .='';                                        
                }
            }else{
                $str .='<h5 class="text-default d-block w-100" style="font-size: 12px;margin: 0 0 5px 0;">'.Lang::get('common.status').' : <span class="text-failed" style="color: #ff0000;">'.Lang::get('checkout.not_paid').'</span></h5>';
            }
        }else{ // admin order created without payment
            $str = '';
        }

        if($return_type == 'Y'){
            return $str;
        }else{
            echo $str;
        }
    }
    
    public static function getPaymentInfoText($orderInfo, $return_type="N") {

        $orderId = $orderInfo->id;
        $paymentType = $orderInfo->payment_type;

        $orderInfoJson = json_decode($orderInfo->order_json,true);

        $lang_code = !empty(session('lang_code'))?session('lang_code'):$orderInfo->lang_checkout;

        $pay_opt_name = $orderInfo->payment_type_id ? $orderInfoJson['payment_method'][$lang_code]:'';
        if($paymentType == '1') { /**online**/
            $str = Lang::get('checkout.online').' ('.$pay_opt_name.')'."\n";
            if($orderInfo->payment_status == '0') {
                $str .= Lang::get('common.status').':'.Lang::get('checkout.not_paid')."\n";
            }
            else {
                $str .= Lang::get('common.status').':'.Lang::get('checkout.paid')."\n";
                $str .= Lang::get('checkout.transaction_id').':'.$orderInfo->txn_id."\n";
            }
        }   
        else if($paymentType == '3') {   //COD
            $str = Lang::get('checkout.offline').' ('.$pay_opt_name.'\n'.Lang::get('common.status').':';
            if($orderInfo->payment_status == '1') {
                $str.=Lang::get('checkout.paid')."\n";
            }
            else {
                $str.=Lang::get('checkout.not_paid')."\n";
            }
            $str .= "\n";
        }            
        else if($paymentType == '2'){  //bank payment

            $str = Lang::get('checkout.offline').' ('.$pay_opt_name.')'."\n";

            $offPayInfoRes = \App\OrderOfflinePayment::where('order_id',$orderId)->with('bank')->with('toBank')->orderBy('id','Desc')->get();
            //dd($offPayInfoRes);
            if(count($offPayInfoRes)){
                foreach ($offPayInfoRes as $key => $offPayInfo) {
                    $bank_name = !empty($offPayInfo->toBank)?$offPayInfo->toBank->bank_name:'';
                    if($offPayInfo->bank->account_type == '1') {
                        $ac_type = Lang::get('checkout.saving_account');
                    } else {
                        $ac_type = Lang::get('checkout.current_account');
                    }
                    $str .= ++$key.".\n";
                    $str.= Lang::get('checkout.name').':'.$offPayInfo->name."\n";
                    $str.= Lang::get('checkout.amount').':'.numberFormat($offPayInfo->amount,$orderInfo->currency_id).' '.$orderInfo->getCurrency->currency_code."\n";
                    $str.= Lang::get('checkout.to_account_no').':'.$offPayInfo->to_account_no.'/'.$offPayInfo->bank->account_name."\n";
                    $str.= Lang::get('checkout.account_detail').':<img src="'.Config::get('constants.payment_bank_url').$offPayInfo->bank->bank_image.'" width="30"> '.$bank_name.', '.$offPayInfo->bank->branch.', '.$ac_type."\n";
                    $str.= Lang::get('checkout.transfer_date').':'.getDateFormat($offPayInfo->transfer_date, '5')."\n";
                    if($offPayInfo->file)
                        $str.='<a href="'.Config::get('constants.buyer_payment_url').$offPayInfo->file.'" target="_blank"><img src="'.Config::get('constants.buyer_payment_url').$offPayInfo->file.'" width="60" hight="60"></a>'."\n";
                    
                    if($offPayInfo->status == '1'){
                        $str.=Lang::get('common.status').':'.Lang::get('checkout.paid')."\n";
                    }elseif($offPayInfo->status == '0'){
                        $str.=Lang::get('common.status').':'.Lang::get('checkout.waiting_for_approve')."\n";
                    }else{
                        $str .=''.Lang::get('common.status').':'.Lang::get('checkout.payment_rejected')."\n";
                    }

                    if($offPayInfo->comment) {
                        $str .=Lang::get('checkout.comment').':'.$offPayInfo->comment."\n";
                    }
                    $str .='';                                        
                }
            }else{
                $str .=Lang::get('common.status').':'.Lang::get('checkout.not_paid')."\n";
            }
        }else{ // admin order created without payment
            $str = '';
        }

        if($return_type == 'Y'){
            return $str;
        }else{
            echo $str;
        }
    }

}
