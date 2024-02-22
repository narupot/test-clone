<?php

function getSiteName() {
	return session('site_name');
}

if (! function_exists('loadFrontTheme')) {
	function loadFrontTheme($path) {
		return $path;
	}
}

function getDateFormat($date_time, $type=Null) {
    //echo '====>'.session('default_time_zone');die;
    if(strtotime($date_time)>0 && session('default_time_zone')) {
        $date_time = date('Y-m-d H:i:s', strtotime($date_time));

        /* Note :: Please dont uncomment bellow line, on local default time zone is UTC and live 'Asia/Bangkok' and date in db according to timezone so no need to set timezone again */

        //$date_time = GeneralFunctions::getDateByTimezone($date_time,'UTC','Y-m-d H:i:s',session('default_time_zone'),'Y-m-d H:i:s');
    }
    
    switch ($type) {
        case '1':
            $date_return = date('M d Y h:i a', strtotime($date_time));
            break;
        case '2':
            $date_return = date('M d / Y', strtotime($date_time));
            break;
        case '3':
            $date_return = date('d M Y', strtotime($date_time));
            break;                
        case '4':
            $date_return = date('M d, Y h:i A', strtotime($date_time));
            break;
        case '5':
            $date_return = date('d M, Y', strtotime($date_time));
            break;  
        case '6':
            $date_return = date('M d, Y', strtotime($date_time));
            break;  
        case '7':
            $date_return = date('d/m/Y H:i', strtotime($date_time));
            break;   
        case '8':
            $date_return = date('M d Y H:i', strtotime($date_time));
            break;  
		case '9':
            $date_return = date('Y-m-d H:i:s', strtotime($date_time));
            break;
        case 'Y':
            $date_return = date('Y', strtotime($date_time));
            break;
        case 'T':
            $date_return = date('H:i', strtotime($date_time));
            break;            
        default:
            $date_return = date('d/m/Y', strtotime($date_time));
            break;             
    }
    
    return $date_return;
}

function getcommentDateFormat($date_time, $cur_date=null) {

	$current_date = date('Y-m-d');
	$current_time = time();
	if(!empty($cur_date)) {
		$current_time = strtotime($cur_date);
		$current_date = date('Y-m-d', $current_time);
	}
    
    $db_date = date('Y-m-d', strtotime($date_time));
    if($current_date == $db_date) {
        $date_diff = $current_time-strtotime($date_time);
        $hour = floor($date_diff/3600);
        $minute = floor(($date_diff-($hour*3600))/60);
        $second = $date_diff;
        
        $date_return = $hour?:$minute.' '.Lang::get('common.minutes_ago');
        if($hour > 0 && $minute > 0) {
        	$date_return = $hour.' '.Lang::get('common.hours').' '.$minute.' '.Lang::get('common.minutes_ago');
        }
        elseif($hour > 0) {
        	$date_return = $hour.' '.Lang::get('common.hours_ago');
        }
        elseif($minute > 0){
        	$date_return = $minute.' '.Lang::get('common.minutes_ago');
        }
        elseif($minute > 0){
        	$date_return = $second.' '.Lang::get('common.seconds_ago');
        }        
        else {
        	$date_return = Lang::get('common.just_now');
        }
    }
    else {
        $date_return = date('d M, Y', strtotime($date_time));
    }
    return $date_return;
}

function getThaiMonth($monthval){

	switch ($monthval)
	{
		case 1  : $month="มกราคม"; break;
		case 2  : $month="กุมภาพันธ์"; break;
		case 3  : $month="มีนาคม"; break;
		case 4  : $month="เมษายน"; break;
		case 5  : $month="พฤษภาคม"; break;
		case 6  : $month="มิถุนายน"; break;
		case 7  : $month="กรกฎาคม"; break;
		case 8  : $month="สิงหาคม"; break;
		case 9  : $month="กันยายน"; break;
		case 10 : $month="ตุลาคม"; break;
		case 11 : $month="พฤศจิกายน"; break;
		case 12 : $month="ธันวาคม"; break;
	}
	return $month;
}


function getLanguageSwitcherData() {

    // $cur_url = \Request::path();
    // $cur_url_arr = explode('/', $cur_url);
    // unset($cur_url_arr['0']);
    // $cur_url = implode('/', $cur_url_arr);

	$cur_url = \Request::getRequestUri();
    $code_replace = ['/'.session('lang_code').'/', '/'.session('lang_code')];
    $cur_url = str_replace($code_replace, '', $cur_url);    

    $ajax_url = action('AjaxController@switchLanguage');

    $languages = \App\Language::getLangugeDetails();

    $cur_lang_img_url = '';
    foreach ($languages as $value) {
    	if($value->languageCode == session('lang_code')) {
    		$cur_lang_img_url = Config::get('constants.language_url').$value->languageFlag;
    	}
    }

    $data_arr['ajax_url'] = $ajax_url;
    $data_arr['languages'] = $languages;
    $data_arr['cur_url'] = $cur_url;
    $data_arr['cur_lang_img_url'] = $cur_lang_img_url;

    return $data_arr;
}



/*function getJsonFileContent($file_path){
    $file_contents = @file_get_contents($file_path);
    $json_cont = $file_contents ? json_decode($file_contents, true) : '';
    return $json_cont;
}*/

function getJsonFileContent($file_path){
	$file_contents = @file_get_contents($file_path);
    $json_cont = $file_contents ? json_decode($file_contents, true) : '';
    if (empty($json_cont)) {
    	$arrContextOptions=array(
		    "ssl"=>array(
		        "verify_peer"=> false,
		        "verify_peer_name"=> false,
		    ),
		);
	    $file_contents = @file_get_contents($file_path, false, stream_context_create($arrContextOptions));
	    $json_cont = $file_contents ? json_decode($file_contents, true) : '';
    }
    return $json_cont;
	
}
function getHeaderDropdownJson() {

    $login_header_list = [];
    $hint_file = config('constants.public_path').'/client_config/header/header.json';
    if(file_exists($hint_file)){
    	$predefined_header_page = getJsonFileContent($hint_file);
    	
    	if(is_array($predefined_header_page)){
    		foreach ($predefined_header_page['header'] as $key => $list) {
		        $login_header_list[$key] = $list;
		        $login_header_list[$key]['icon-class'] = $list['icon-class'];
		        $login_header_list[$key]['class'] = $list['class'];
		        //$login_page_logos[$key]['url'] = $this->server_url.$logo['url'];
		        $login_header_list[$key]['url'] = $list['url'];
		    }
    	}
    }

    return $login_header_list;
}


function getConsumedSpace(){
	$space_consumes = json_decode(file_get_contents(public_path('/client_config/host/usage.json')),true);
	$project_size_in_mb = ($space_consumes['project_size_in_bytes']/1024)/1024;
	$db_size_in_mb = $space_consumes['db_size_in_MB'];
	$total_size = $project_size_in_mb + $db_size_in_mb;
	$total_size = round($total_size,2);
	return $total_size;
}

function currentDateTime($time='Y') {
	if($time=='N') {
		return date('Y-m-d');
	}
	else {
		return date('Y-m-d H:i:s');
	}
}


function addDaysTodate($date,$days,$time='Y'){

    $date = strtotime("+".$days." days", strtotime($date));
    if($time == 'N'){
    	return  date("Y-m-d", $date);
    }
    return  date("Y-m-d H:i:s", $date);

}

function dateEmptyQuery($date = null){
	$datecon = ($date)?$date:date('Y-m-d');
	return "expiry = 1 or (start_date <= '$datecon' and end_date >= '$datecon')";
}


if (! function_exists('getUserImage')) {
	function getUserImage($imgValue) {
		$img_path =  Config::get('constants.user_path').'/'.$imgValue;
		if(!empty($imgValue) && file_exists($img_path))
		{
			$imgSrc =  Config::get('constants.user_url').$imgValue;
		}
		else
		{				
			$imgSrc =  Config::get('constants.users_default_url').'user_default.jpg';
		}
		
		return $imgSrc;
	}
}


if(!function_exists('getUser')){
	function getUser($id){
		$data = \App\AdminUser::select('nick_name')->find($id); 
		if(is_null($data)){
			return '';
		}else{
			return $data->nick_name;	
		}
		
	}
}
if(!function_exists('getParentCategory')){
	function getParentCategory($id){
		$data = \App\Category::where('id',$id)->with('getCatDesc')->first(); ; 
		if(is_null($data)){
			return '';
		}else{
			if($data->getCatDesc){
				return $data->getCatDesc->name;
			}else{
				return " ";
			}
				
		}
		
	}
}
if(!function_exists('getParentCategoryIdsBySearchName')){
	function getParentCategoryIdsBySearchName($search_text){
		if(isset($search_text) && $search_text!='')
		{
			$data_arr = DB::table(with(new \App\CategoryDesc)->getTable() . ' as cd')
					->leftjoin(with(new \App\Category)->getTable() . ' as c', [['cd.cat_id', '=', 'c.id']])
					->where(['c.parent_id'=>'0'])
					->where('cd.category_name','like', '%'.$search_text.'%')
					->get()->pluck('id')->toArray();
			return $data_arr;		
		}
		else {
			return array();
		}		
	}
}
function getCurrencyVal($currency_id){
	return \App\Currency::where('id',$currency_id)->value('currency_value');
}




// here $value is price $currency1 is from currency and $currency2 is to currency
if (! function_exists('convertCurrency')) {
	function convertCurrency($value,$currency1,$currency2) { /**$currency2 = session currency id**/
		if($currency1 != $currency2){
			$currency_cache = @file_get_contents(Config('constants.data_cache_path').'/currency.dict');
			$cur_arr = json_decode($currency_cache,true);
			
			$currency1Value = isset($cur_arr[$currency1])?$cur_arr[$currency1]:getCurrencyVal($currency1);
			$currency2Value = isset($cur_arr[$currency2])?$cur_arr[$currency2]:getCurrencyVal($currency2);

			@$dollerValue = $value*(1/$currency1Value);
			$return = $dollerValue*$currency2Value;
			return round($return,4);
			}else{
				return round($value,4);
			}
	}
}

if (! function_exists('numberFormat')) {
	function numberFormat($price, $currencyId=Null)
	{

		$currencyVal = $price;
		if($currencyId)
			$currencyVal = ($currencyId) ? convertCurrency($price,$currencyId,session('default_currency_id')) : $price;
		$exp = $currencyVal ? explode($currencyVal, '.') : 0;
		
		if(isset($exp[1]) && $exp[1]>0){
			return number_format($currencyVal, 2);
		}else{
			return number_format($currencyVal);
		}
		
	}
}

if (! function_exists('convertString')) {
    function convertString($price){
        $price = number_format(floatval($price), 2);
        $price  = (String) $price;
        $price  = str_replace('.00', '', $price);
        return $price;

    }
}	

if (! function_exists('isMobile')) {
	function isMobile() {
		if(isset($_SERVER["HTTP_USER_AGENT"])){
			$checkMobile = preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|iPad|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);

			return $checkMobile;
		}
		else{
			return false;
		}

	}
}

if (! function_exists('getCartProduct')) {
	function getCartProduct() {
		return \App\Cart::getTotCartPrdNoti();
	}
}

if (! function_exists('getBargainForBuyerCount')) {
	function getBargainForBuyerCount() {
		if(Auth::check()){
			$userid = Auth::User()->id;
		    return \App\ProductBargain::totBargainCountForBuyerNoti($userid);
		}    
	}
}

if (! function_exists('getBargainForSellerCount')) {
	function getBargainForSellerCount() {
		if(Auth::check()){
			$shop_id = session('user_shop_id'); 
		    return \App\ProductBargain::totBargainCountForSellerNoti($shop_id);
		}    
	}
}

if (! function_exists('getProductsForSellerCount')) {
	function getProductsForSellerCount() {
		if(Auth::check()){
			$shop_id = session('user_shop_id'); 
		    return \App\Product::where('shop_id',$shop_id)->count();
		}    
	}
}

if (! function_exists('getDeliveryItemsForSellerCount')) {
	function getDeliveryItemsForSellerCount() {
		if(Auth::check()){
			$shop_id = session('user_shop_id'); 
			$check_date = date('Y-m-d');
		    $total_records_pending = \App\OrderShop::whereIn('order_status',[1,2])->where('shop_id',$shop_id)->where('end_shopping_date','!=',null)->whereNotIn('seller_status',['sent'])->count();

		    /*$total_records = \App\OrderShop::where('shop_id', $shop_id)->where('end_shopping_date','!=',null)
		    	->whereIn('order_status',[2])->where('payment_status',1)->where('seller_status','sent');*/
            //$total_records_ready = $total_records->count();

            return $total_record =  $total_records_pending;
		}    
	}
}


if (! function_exists('getCartPrice')) {
	function getCartPrice() {
		$cartDetail = '0.00';
		if(Auth::check()){
			$userid = Auth::User()->id;
			$cartDetail = \App\Cart::where(['user_id'=>$userid,'cart_status'=>'0'])->sum('total_price');
		}
		return $cartDetail;
	}
}


if (! function_exists('getUpdatedPrice')) {
	function getUpdatedPrice($old_value, $old_currency_val, $new_currency_val)
	{
        $doller_value = $old_value*(1/$old_currency_val);
        $new_value = $doller_value*$new_currency_val;
        $updated_price = round($new_value,4);
        return $updated_price;
	}
}

if (! function_exists('printData')) {
	function printData($data)
	{
			return strip_tags($data);
	}
}

if (! function_exists('validOrdAmt')) {
	function validOrdAmt($amount=0)
	{
		if($amount < 9999999999){
			return true;
		}else{
			return false;
		}
	}
}

if( ! function_exists('generateReadMoreLink')){

	function generateReadMoreLink($text, $limit, $url, $readMoreText = 'Read More') {

			$end = "<br><br><a href=\"$url\">$readMoreText</a>";

			return str_limit($text, $limit, $end);
	}

}


function createTree(&$list, $parent){
	$tree = array();
	foreach ($parent as $k=>$l){
			$l['checked'] = false;
			$l['name'] = $l['categorydesc']['name'];
			$l['children'] = [];
			if(isset($list[$l['id']])){
					$l['children'] = createTree($list, $list[$l['id']]);
			}
			$tree[] = $l;
	} 
	return $tree;
}



function getAllMarketplaceCat(){
	$cat_data_set = \App\Category::where('status','1')->with('categorydesc')->select(['id','url','custom_url','parent_id'])->get()->toArray();
					//dd($cat_data_set);
			if(count($cat_data_set)){
					foreach ($cat_data_set as $a){
							$new[$a['parent_id']][] = $a;
					}
					$tree = createTree($new, $new[0]); // changed         
					return $tree;    
			}
			else{
					return [];
			}
}


function cleanValue($value) {
	return trim(filter_var($value, FILTER_SANITIZE_STRING));
}
			
function getYoutubeId($url){
	parse_str( parse_url( $url, PHP_URL_QUERY ), $my_array_of_vars );
	return $my_array_of_vars['v'];    
}  

function getVimeoId($vimeo_url){
	if(preg_match("/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([0-9]{6,11})[?]?.*/", $vimeo_url, $output_array)) {
		return $output_array[5];
	}
}

function jsonEncode($data) {
	return json_encode($data,JSON_UNESCAPED_UNICODE);
}

function jsonDecode($data) {
	return json_decode($data);
}

function jsonDecodeArr($data) {
	return json_decode($data,true);
}

function userRequireRule($validation_type){
	$validation_rule = '';
	switch ($validation_type) {
		case 'name':
			$validation_rule = nameRule();
			break;
		case 'email':
			$validation_rule = emailRule();
			break;
		case 'date':
			$validation_rule = dateRule();
			break;
		case 'number':
			$validation_rule = numberRule();
			break;
		case 'image':
			$validation_rule = imageRule();
			break;
		default:
			$validation_rule = 'Required';
			break;
	}

	return $validation_rule;
}

//validation functions
function titleRule() {
	return 'Required|Min:3|Max:100';
}

function numberRule() {
	return 'Required';
}

function imageRule() {
	return 'Required|mimes:jpeg,jpg,png,gif';
}

function nameRule() {
	return 'Required|Min:3';
	//return 'Required|regex:/^[\pL\s]+$/u|Min:3|Max:100';
}

function AddressRule() {
	return 'Required|Min:1|Max:255';
}

function contactNoRule() {
	return 'Required|digits_between:9,10|numeric';
}

function numericRule($req=null) {
	if($req)
		return 'Required|numeric';
	else
		return 'numeric';
}

function emailRule($table_name='', $field='') {
	if($table_name != '' && $field != '') {
		return 'Required|email|unique:'.$table_name.','.$field;
	}
	else {
		return 'Required|email';
	}
}
function bankCodeRule($table_name='', $field='') {
	if($table_name != '' && $field != '') {
		return 'Required|unique:'.$table_name.','.$field;
	}
	else {
		return 'Required';
	}
}
function phoneRule($table_name='', $field='') {
	if($table_name != '' && $field != '') {
		return 'Required|digits:10|numeric|unique:'.$table_name.','.$field;
	}
	else {
		return 'Required|digits:10|numeric';
	}
}

function passwordRule() {
	return 'Required|Min:6|Max:10';
}

function confirmPasswordRule($password_field_name) {
	return 'Required|Min:6|Max:10|same:'.$password_field_name;
}

function arrayRule($min__val=1) {
	return 'Required|array|Min:'.$min__val;
}

function dateRule(){
	return 'Required|date';
}

function zipRule(){
	return 'Required|Min:3|numeric';
}

function reqRule(){
	return 'Required';
}

function uniqueIgnoreRule($tblname,$field,$fieldval,$ignoreCol='id'){
	return 'required|min:2|unique:'.$tblname.','.$field.','.$fieldval.','.$ignoreCol;
}

function uniqueRule($tblname,$field,$fieldval,$ignoreCol='id'){
	return 'required|min:2|unique:'.$tblname.','.$field.','.$fieldval;
}

function checkPageSection(){
	return Request::segment(1);
}

function descriptionRule() {
    return 'Required|Min:1';
}
//validation functions ends

function pageClass($left='',$right=''){
	$sideClass = '';
      if($left && $right){
        $class = 'main-content col-md-6';
        $sideClass = 'content-sidebar';
      }elseif($left || $right){
        $class = 'main-content col-md-9';
        $sideClass = 'content-sidebar';
      }else{
        $class = 'content';
      }

    return ['main'=>$class,'sideClass'=>$sideClass];
}

if(!function_exists('getBlogDetailUrl')){
	function getBlogDetailUrl($url){
		return action('BlogController@blogDetails',$url);

		//return session('lang_code').'/blog/'.$url;
	}
}

if(!function_exists('getBlogCategoryUrl')){
	function getBlogCategoryUrl($url){
		return action('BlogController@categoryBlogList',$url);
	}
}


function getSizeName($size){
	return 'PRODUCT_IMAGE';
}

function getBlogImageUrl($image_name, $dir_name) {

	$blog_image_path = Config::get('constants.blog_path').'/'.$dir_name.'/'.$image_name;
	if(!empty($image_name) && file_exists($blog_image_path)) {
	    $blog_image_url = Config::get('constants.blog_url').$dir_name.'/'.$image_name;
	}
	else {
	    $blog_image_url = GeneralFunctions::getPlaceholderImage('BLOG_IMAGE');
	}
	return $blog_image_url;	
}
/*Added By Satih Anand for Blog Module Start*/
function getBlogFeatureImageUrl($image_name, $placeholder='Y') {
    $news_image_path = Config::get('constants.blog_feature_img_path').'/'.$image_name;
    if(!empty($image_name) && file_exists($news_image_path)) {
        $news_image_url = Config::get('constants.blog_feature_url').$image_name;
    }
    elseif($placeholder=='Y') {
        $news_image_url = GeneralFunctions::getPlaceholderImage('BLOG_IMAGE');
    }
    else {
        $news_image_url = '';
    }
    return $news_image_url;     
}

function getBlogSocialshareImageUrl($image_name, $placeholder='Y') {
    $news_image_path = Config::get('constants.blog_socialshare_img_path').'/'.$image_name;
    if(!empty($image_name) && file_exists($news_image_path)) {
        $news_image_url = Config::get('constants.blog_socialshare_url').$image_name;
    }
    elseif($placeholder=='Y') {
        $news_image_url = GeneralFunctions::getPlaceholderImage('BLOG_IMAGE');
    }
    else {
        $news_image_url = '';
    }
    return $news_image_url;     
}

function getBlogSliderImageUrl($image_name, $placeholder='Y') {
    $news_image_path = Config::get('constants.blog_slider_img_path').'/'.$image_name;
    if(!empty($image_name) && file_exists($news_image_path)) {
        $news_image_url = Config::get('constants.blog_slider_url').$image_name;
    }
    elseif($placeholder=='Y') {
        $news_image_url = GeneralFunctions::getPlaceholderImage('BLOG_IMAGE');
    }
    else {
        $news_image_url = '';
    }
    return $news_image_url;     
}

function getBadgeCategoryImageUrl($image_name, $placeholder='Y') {
    $news_image_path = Config::get('constants.badge_category_img_path').'/'.$image_name;
    if(!empty($image_name) && file_exists($news_image_path)) {
        $news_image_url = Config::get('constants.badge_category_image_url').$image_name;
    }
    elseif($placeholder=='Y') {
        $news_image_url = GeneralFunctions::getPlaceholderImage('BLOG_IMAGE');
    }
    else {
        $news_image_url = '';
    }
    return $news_image_url;     
}

function getBadgeProductImageUrl($image_name, $placeholder='Y') {
    $news_image_path = Config::get('constants.badge_product_img_path').'/'.$image_name;
    if(!empty($image_name) && file_exists($news_image_path)) {
        $news_image_url = Config::get('constants.badge_product_image_url').$image_name;
    }
    elseif($placeholder=='Y') {
        $news_image_url = GeneralFunctions::getPlaceholderImage('BLOG_IMAGE');
    }
    else {
        $news_image_url = '';
    }
    return $news_image_url;     
}

function getPageSocialshareImageUrl($image_name, $placeholder='Y') {
    $news_image_path = Config::get('constants.page_socialshare_img_path').'/'.$image_name;
    if(!empty($image_name) && file_exists($news_image_path)) {
        $news_image_url = Config::get('constants.page_socialshare_url').$image_name;
    }
    elseif($placeholder=='Y') {
        $news_image_url = GeneralFunctions::getPlaceholderImage('BLOG_IMAGE');
    }
    else {
        $news_image_url = '';
    }
    return $news_image_url;     
}

function getPayImgUrl($image_name) {

	$bank_image_path = Config::get('constants.payment_option_path').'/'.$image_name;
	if(!empty($image_name) && file_exists($bank_image_path)) {
	    $blog_image_url = Config::get('constants.payment_option_url').$image_name;
	}
	else {
	    $blog_image_url = Config::get('constants.placeholder_url').'pay_opt_image.jpg';
	}
	return $blog_image_url;	
}

function getBankImageUrl($image_name){
	$bank_image_path = Config::get('constants.payment_bank_path').'/'.$image_name;
	if(!empty($image_name) && file_exists($bank_image_path)) {
	    $blog_image_url = Config::get('constants.payment_bank_url').$image_name;
	}
	else {
	    $blog_image_url = Config::get('constants.placeholder_url').'pay_opt_image.jpg';
	}
	return $blog_image_url;
}

function getBadgeImageUrl($image_name) {

	$bank_image_path = Config::get('constants.standard_badge_path').'/'.$image_name;
	//echo '====>'.$bank_image_path;die;
	if(!empty($image_name) && file_exists($bank_image_path)) {
	    $blog_image_url = Config::get('constants.standard_badge_url').$image_name;
	}
	else {
	    $blog_image_url = Config::get('constants.placeholder_url').'badge_image.jpg';
	}
	return $blog_image_url;	
}

if(!function_exists('createUrl')) {

	function createUrl($url_str){
	    $string = $url_str;
	    $string = preg_replace("`\[.*\]`U","",$string);
        $string = preg_replace('`&(amp;)?#?[a-z0-9]+;`i','-',$string);
        $string = str_replace('%', '-percent', $string);
        $string = htmlentities($string, ENT_COMPAT, 'utf-8');
        $string = preg_replace( "`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i","\\1", $string );
        $string = preg_replace( array("`[^a-z0-9ก-๙เ-า]`i","`[-]+`") , "-", $string);
        return strtolower(trim($string, '-'));
	}
}

function BlogPageClass($left='',$right=''){
	$sideClass = '';
  	if($left && $right){
        $class = 'content col-sm-6';
        $sideClass = 'content-sidebar';
  	}elseif($left || $right){
        $class = 'content col-sm-9';
        $sideClass = 'content-sidebar';
  	}else{
        $class = 'content';
  	}

    return ['main'=>$class,'sideClass'=>$sideClass];
}	
/*Added By Satih Anand for Blog Module End*/

function getUserImageUrl($image_name, $gender='') {
	$users_image_path = Config::get('constants.user_path').'/'.$image_name;
	if(!empty($image_name) && file_exists($users_image_path))
		$user_image_url = Config::get('constants.user_url').$image_name;
	elseif($gender == 'F')
	  	$user_image_url = GeneralFunctions::getPlaceholderImage('USER_IMAGE_FEMALE');
	else
		$user_image_url = GeneralFunctions::getPlaceholderImage('USER_IMAGE');

	return $user_image_url;
}

function generatedDD($value){
	$ddArr[] = ['key'=>'', 'value'=>'Please select'];
	foreach ($value as $key => $result) {
		$ddArr[] = ['key'=>$key+1, 'value'=>Lang::get($result)];
	}
	return $ddArr;
}

function generatedGroupDD($value){
	$ddArr[] = ['key'=>'', 'value'=>'Please select'];
	foreach ($value as $result) {
		$ddArr[] = ['key'=>$result->id, 'value'=>ucfirst($result->customerGroupDesc->group_name)];
	}
	return $ddArr;
}


if(!function_exists('getCategoryUrl')){
	function getCategoryUrl($url){
		return action('ProductsController@category',$url);
	}
}


function getPagination($type='') {
	if($type == 'limit') {
		$limit = 20;
		return $limit;
	}
	else {
	   	$limit_opt = array(10,20,50,100,200);
	   	foreach($limit_opt as $value){
	       $data[] = array('key' => $value, 'value' => $value);
	   	}
	   	return json_encode($data);		
	}
}


function getShortArray(){
	return json_encode(['0'=>['name'=>'name','order'=>'ASC','label'=>'Name (A-Z)'],'1'=>['name'=>'name','order'=>'DESC','label'=>'Name (Z-A)'],'2'=>['name'=>'price','order'=>'DESC','label'=>'Heighest Price'],'3'=>['name'=>'price','order'=>'ASC','label'=>'Lowest Price'],'4'=>['name'=>'nastatus','order'=>'DESC','label'=>'New Arrivals']]);
}

function tableGeneralAction(){
	$general_actions['edit'] = Lang::get('common.edit');
    $general_actions['view'] = Lang::get('common.view');
    $general_actions['delete'] = Lang::get('common.delete');
    return json_encode($general_actions);
}

function getBulkActionOption(){

	return json_encode([
			['id'=>"",'name'=>'-- Please select --', 'slug' => 'not_selected'],
			['id'=>1,'name'=>'Active', 'status_value' => 1, 'slug' => 'active'],
			['id'=>2,'name'=>'Inactive', 'status_value' => 0, 'slug' => 'inactive'],
			['id'=>3,'name'=>'Delete', 'status_value' => 'Delete', 'slug' => 'delete']
		]);
}

function getSiteLogo($system_name) {
    $logo_name =  GeneralFunctions::systemConfig($system_name);
    $logo_url =  Config::get('constants.site_logo_url').$logo_name;
    return $logo_url;
}

function getSiteLoader($system_name) {
    $logo_name =  GeneralFunctions::systemConfig($system_name);
    $logo_url =  Config::get('constants.site_loader_url').$logo_name;
    return $logo_url;
} 


function getDepartmentName($role_id) {
	return \App\RoleDepartment::getDepartmentName($role_id);
}

function getStatickPage($page_url){

    $return_str = '';
    $statickPage = \App\StaticPage::where(['url'=>$page_url])->with('staticPageDesc')->first();
    if(!empty($statickPage)){
        $return_str = isset($statickPage->staticPageDesc->page_desc)?$statickPage->staticPageDesc->page_desc:'';
    }
    return $return_str;
}

function getStaticPageUrl($url){
	return action('StaticPageController@pagedata',$url);
}

function getStaticBlock($page_url){

    $return_str = '';
    $statickBlock = \App\StaticBlock::where(['url'=>$page_url])->with('blockDesc')->first();
    if(!empty($statickBlock) && !empty($statickBlock->blockDesc)){
        $return_str = $statickBlock->blockDesc->page_desc;
    }
    return $return_str;
}

function getStaticBlockUrl($url){
	return action('StaticPageController@pagedata',$url);
}

function getConfigValue($system_name=null) {
    $system_val = '';
    if(!empty($system_name)){ 
        $system_val = \App\SystemConfig::getSystemVal($system_name);
    }  
    return $system_val;         
}
function getImageDimension($type){

	if($type=='banner'){
		return json_encode(['width'=>1150,'height'=>513]);
	}

	if($type=="blog_thumb"){
		return json_encode(['width'=>570,'height'=>402]);
	}

	if($type=="menu_design"){
		return [['section' => 'menu_image_thumb', 'dimension' => ['width' => 1170, 'height' => 450], 'file_field_selector' => '#MenuThumbImage', 'section_id'=>'menu-image']];
	}
	if($type=='mobile_banner'){
		return json_encode(['width'=>350,'height'=>120]);
	}
}

function getDomainNameByBaseUrl($url){
	$url_array = explode('://', $url);
	return str_replace('/','',end($url_array));
}

function getProductImageUrl($image_name, $size='', $parent_id=0) {
	
	if($image_name && $size){
		
		$prd_url = Config::get('constants.product_img_url').$size.'/'.$image_name;
		$prd_path = Config::get('constants.product_path').'/'.$size.'/'.$image_name;
	}else {
		$prd_url = Config::get('constants.product_url').'thumb_104x145/'.$image_name;
		$prd_path = Config::get('constants.product_path').'/thumb_104x145/'.$image_name;
	}

	if(file_exists($prd_path) && $image_name){
			return $prd_url;
	}
	else{
		$size_name = getSizeName($size);
		return GeneralFunctions::getPlaceholderImage($size_name);
	}
}

function getProductImageUrlRunTime($image_name, $folder='') {
	$image_name = $image_name? $image_name:'product_image.jpg';

	$folder = $folder=='thumb'?'thumb_135x100':$folder;
	
	return action('JsonController@imageResize',[$folder,$image_name]);
}

function getCategoryImageUrl($image_name) {
	$category_image_path = Config::get('constants.category_img_path').'/'.$image_name;
	if(!empty($image_name) && file_exists($category_image_path)) {
	    $category_image_url = Config::get('constants.category_img_url').$image_name;
	}
	else {
	    $category_image_url = GeneralFunctions::getPlaceholderImage('CATEGORY_IMAGE');
	}
	return $category_image_url;	
}

function checkPath($image_name,$path,$url,$placeholder){
	$ret_img_path = $path.'/'.$image_name;
	if($image_name && file_exists($ret_img_path)){
		$ret_img_url = $url.$image_name;
	}else{
		$ret_img_url = Config('constants.placeholder_url').$placeholder;
	}
	
	return $ret_img_url;
}

if(! function_exists('getImgUrl')){
	function getImgUrl($image_name, $type){
		switch ($type) {
			case 'banner':
				$ret_img_url = checkPath($image_name,Config('constants.shop_img_path'),Config('constants.shop_img_url'),'shop_banner.jpg');
				//dd($ret_img_url);
			break;
			case 'logo':
				$ret_img_url = checkPath($image_name,Config('constants.shop_img_path'),Config('constants.shop_img_url'),'shop_logo.jpg');
			break;
			case 'map':
			case 'shop':
				$ret_img_url = checkPath($image_name,Config('constants.shop_original_path'),Config('constants.shop_original_url'),'shop_banner.jpg');
			break;
			case 'customer':
				$ret_img_url = checkPath($image_name,Config('constants.customer_path'),Config('constants.customer_url'),'user_default.jpg');
			break;
			case 'category':
				$ret_img_url = checkPath($image_name,Config('constants.category_img_path'),Config('constants.category_img_url'),'category.jpg');
			break;
			case 'product':
				$ret_img_url = checkPath($image_name,Config('constants.product_path'),Config('constants.product_img_url'),'product_image.jpg');
			break;
		}
		return $ret_img_url;
	}
}
function getCatImgUrl($image,$size){
	return action('JsonController@convertImage',['category',$image,$size]);
}

function getShopImageUrl($image,$size){
	return action('JsonController@convertImage',['shop',$image,$size,'original']);
}

function getShopLogoImageUrl($image,$size){
    if ($image) {
        return action('JsonController@convertImage', ['shop', $image, $size]);
    } else {
        return Config('constants.placeholder_url').'shop_logo.jpg';
    }
}

function getFolderSize($folder_path){
	$file_size = 0;
	//$all_files = File::allFiles($folder_path);
	//dd($all_files);

	foreach( File::allFiles($folder_path) as $file)
	{
	    $file_size += $file->getSize();
	}
	return $file_size;

}

function websiteMaintenanceMode(){
	return \App\WebsiteConfiguration::getWebsiteValue('SITE_MAINTENANCE');
}

function mobileMaintenanceMode(){
	return \App\WebsiteConfiguration::getWebsiteValue('MOBILE_MAINTENANCE');
}

function getPrdThumbDim(){
    return ['w'=>340,'h'=>340];
}

function updateSyncData($slug=null,$type=null,$dataArr){

	$limitobj = new \App\SyncLog;
	$limitobj->section = $slug;
	$limitobj->type = $type;
	$limitobj->main_value = $dataArr['key_val'];
	$limitobj->data = json_encode($dataArr);
	$limitobj->created_at = date('Y-m-d H:i:s');
	$limitobj->save();
}


function getFooter(){
	$val = \App\Footer::getFrontFooter();
	if(!empty($val)){
		return $val;
	}else{
		return '';
	}
}

function heightArr(){
	return json_encode(['one'=>'540','two'=>'1040']);
}

function widthArr(){
	$arr = ['2'=>'160','3'=>'255','4'=>'350','5'=>'540','6'=>'350','7'=>'255','8'=>'350','9'=>'540','10'=>'160','11'=>'255','12'=>'350','13'=>'540','14'=>'350','15'=>'255','16'=>'350','17'=>'540'];
	return json_encode($arr);
}

function designVal(){
	$arr = ['1'=>'one_left_12_12','3'=>'one_left_3_9','18'=>'one_left_3_9'];
	$arr['4'] = 'one_left_4_8';$arr['19'] = 'one_left_4_8';
	$arr['5'] = 'one_left_6_6';$arr['20'] = 'one_left_6_6';
	$arr['6'] = 'one_right_4_8';$arr['21'] = 'one_right_4_8';
	$arr['7'] = 'one_right_3_9';$arr['22'] = 'one_left_4_8';
	$arr['8'] = 'one_right_4_8';$arr['23'] = 'one_right_4_8';
	$arr['9'] = 'one_right_6_6';$arr['24'] = 'one_right_6_6';
	$arr['10'] = 'one_right_2_10';
	$arr['11'] = 'two_left_3_9';$arr['25'] = 'two_left_3_9';
	$arr['12'] = 'two_left_4_8';$arr['26'] = 'two_left_4_8';
	$arr['13'] = 'two_left_6_6';$arr['27'] = 'two_left_6_6';
	$arr['14'] = 'two_left_4_8';$arr['28'] = 'two_left_4_8';
	$arr['15'] = 'two_right_3_9';$arr['29'] = 'two_right_3_9';
	$arr['16'] = 'two_right_4_8';$arr['30'] = 'two_right_4_8';
	$arr['17'] = 'two_right_6_6';$arr['31'] = 'two_right_6_6';
	return $arr;
}

function prdSliderDesign(){
	$arr['1'] = \Lang::get('admin_slider.only_product');
	//$arr['2'] = \Lang::get('admin_slider.one_row_banner_and_product').' (2/10)';
	$arr['3'] = \Lang::get('admin_slider.one_row_banner_and_product').' (3/9)';
	$arr['4'] = \Lang::get('admin_slider.one_row_banner_and_product').' (4/8)';
	$arr['5'] = \Lang::get('admin_slider.one_row_banner_and_product').' (6/6)';
	$arr['6'] = \Lang::get('admin_slider.one_row_product_and_banner').' (8/4)';
	$arr['7'] = \Lang::get('admin_slider.one_row_product_and_banner').' (9/3)';
	$arr['8'] = \Lang::get('admin_slider.one_row_product_and_banner').' (8/4)';
	$arr['9'] = \Lang::get('admin_slider.one_row_product_and_banner').' (6/6)';
	//$arr['10'] = \Lang::get('admin_slider.two_row_banner_and_product').' (2/10)';
	$arr['11'] = \Lang::get('admin_slider.two_row_banner_and_product').' (3/9)';
	$arr['12'] = \Lang::get('admin_slider.two_row_banner_and_product').' (4/8)';
	$arr['13'] = \Lang::get('admin_slider.two_row_banner_and_product').' (6/6)';
	$arr['14'] = \Lang::get('admin_slider.two_row_banner_and_product').' (8/4)';
	$arr['15'] = \Lang::get('admin_slider.two_row_product_and_banner').' (9/3)';
	$arr['16'] = \Lang::get('admin_slider.two_row_product_and_banner').' (8/4)';
	$arr['17'] = \Lang::get('admin_slider.two_row_product_and_banner').' (6/6)';
	return $arr;
}

function blogSliderDesign(){
	$arr['1'] = \Lang::get('admin_slider.only_blog');
	//$arr['2'] = \Lang::get('admin_slider.one_row_banner_and_blog').' (2/10)';
	$arr['3'] = \Lang::get('admin_slider.one_row_banner_and_blog').' (3/9)';
	$arr['4'] = \Lang::get('admin_slider.one_row_banner_and_blog').' (4/8)';
	$arr['5'] = \Lang::get('admin_slider.one_row_banner_and_blog').' (6/6)';
	$arr['6'] = \Lang::get('admin_slider.one_row_blog_and_banner').' (8/4)';
	$arr['7'] = \Lang::get('admin_slider.one_row_blog_and_banner').' (9/3)';
	$arr['8'] = \Lang::get('admin_slider.one_row_blog_and_banner').' (8/4)';
	$arr['9'] = \Lang::get('admin_slider.one_row_blog_and_banner').' (6/6)';
	//$arr['10'] = \Lang::get('admin_slider.two_row_banner_and_blog').' (2/10)';
	$arr['11'] = \Lang::get('admin_slider.two_row_banner_and_blog').' (3/9)';
	$arr['12'] = \Lang::get('admin_slider.two_row_banner_and_blog').' (4/8)';
	$arr['13'] = \Lang::get('admin_slider.two_row_banner_and_blog').' (6/6)';
	$arr['14'] = \Lang::get('admin_slider.two_row_blog_and_banner').' (8/4)';
	$arr['15'] = \Lang::get('admin_slider.two_row_blog_and_banner').' (9/3)';
	$arr['16'] = \Lang::get('admin_slider.two_row_blog_and_banner').' (8/4)';
	$arr['17'] = \Lang::get('admin_slider.two_row_blog_and_banner').' (6/6)';
	return $arr;
}

function generateOTP() { 
      
    $n = 4;
    $generator = "1357902468"; 
    $result = ""; 
  
    for ($i = 1; $i <= $n; $i++) { 
        $result .= substr($generator, (rand()%(strlen($generator))), 1); 
    } 
    return $result; 
} 

function generateUniqueNo($digit=15){
	return substr(number_format(time() * rand(),0,'',''),0,$digit);
}

function userIpAddress(){
	return \Request::ip();
}

function getActiveLanguage(){
	$lang = \App\Language::where(['status'=>'1'])->select('id','languageCode')->get();
	$lang_arr = [];
	if(count($lang)){
		foreach ($lang as $key => $value) {
			$lang_arr[$value->id] = $value->languageCode;
		}
	}
	return $lang_arr;
}

function createDateFilter($obj,$col_name,$from_date,$to_date){
    if($from_date || $to_date){
        $from_date=date('Y-m-d',strtotime($from_date));
        $to_date = date('Y-m-d',strtotime($to_date));
        if(strtotime($from_date) && strtotime($to_date)){
            $obj->whereDate($col_name,'>=',$from_date);
            $obj->whereDate($col_name,'<=',$to_date);
        }else if(strtotime($from_date)){
            $obj->whereDate($col_name,'>=',$from_date);
        }else{
            $obj->whereDate($col_name,'<=',$to_date);
        }
    }
    return $obj;
}

function getStandardBadge(){
	return \App\MongoBadge::getAllBadge();
}

function getPackageData(){
	return \App\MongoPackage::getAllPackage();
}
function getPackageName($package_id){
	$package_data = getPackageData();
	return isset($package_data[$package_id])?$package_data[$package_id]->package_name:'';
}

function getUnitData(){
	return \App\MongoUnit::getAllUnit();
}
function getUnitName($unit_id){
	return isset(getUnitData()[$unit_id])?getUnitData()[$unit_id]->unit_name:'';
}
function getBadgeImage($badge_id){
	//dd(getStandardBadge()[$badge_id]);
	$badge_icon = isset(getStandardBadge()[$badge_id])?getStandardBadge()[$badge_id]->icon:'';
	return getBadgeImageUrl($badge_icon);
}
/**cache function****/
function cache_hasKey($cache_key){
	return \Cache::has($cache_key);
}

function cache_getData($cache_key){
	return \Cache::get($cache_key);
}

function cache_getDate($cache_key){
	return \Cache::get($cache_key);
}

function cache_putData($cache_key,$data=null,$minutes=null){
	$minutes = ($minutes)?$minutes:300;
	$expiresAt = now()->addMinutes($minutes);
	\Cache::put($cache_key, $data, $expiresAt);
}

function cache_deleteKey($cache_key){
	\Cache::forget($cache_key);
}
function getSystemValFontFamily(){
  return GeneralFunctions::systemConfig('FONT_FAMILY');
}
function getSystemValBgColour(){
  return GeneralFunctions::systemConfig('BG_CLOUR');
}
function getSystemValFontColour(){
  return GeneralFunctions::systemConfig('FONT_COLOUR');
}

function getShowRangePerPage(){
	return [10,20,50];
}

function getChannel($key) {
	$channel_arr = ['1'=>Lang::get('product.channel_create_new'), '2'=>Lang::get('product.channel_checkout'), '3'=>Lang::get('product.channel_mannual')];
	return $channel_arr[$key];
}

function getSortingItems(){
	return [
		[
			"name"=>"created_at",
			"by"=>"desc",
			"value"=>Lang::get('product.new')
		],
		[	
			"name"=>"created_at",
			"by"=>"asc",
			"value"=>Lang::get('product.old'),
		],
		// [
		// 	"name"=>"prod_name",

		// 	"value"=>Lang::get('product.name_asc')
		// ],
		// [

		// 	"name"=>"prod_name_desc",
		// 	"value"=>Lang::get('product.name_desc')
		// ],
		[
			"name"=>"unit_price",
			"by"=>"ASC",
			"value"=>Lang::get('product.price_low_to_high'),
		],
		[

			"name"=>"unit_price",
			"by"=>"DESC",
			"value"=>Lang::get('product.price_high_to_low')
		],
		[
			"name"=>"avg_star",
			"by"=>"ASC",
			"value"=>Lang::get('product.rating_low_to_high')
		],
		[
			"name"=>"avg_star",
			"by"=>"DESC",
			"value"=>Lang::get('product.rating_high_to_low')
		]
	];
}
 function shippingMethodName($val){
 	switch ($val) {
 		case '1':
 			$name = Lang::get('checkout.pick_up_at_center');
 			break;
 		case '2':
 			$name = Lang::get('checkout.pick_up_at_the_store');
 			break;
 		case '3':
 			$name = Lang::get('checkout.shipping_address');
 			break;
 		default:
 			$name = '';
 			break;
 	}
 	return $name;
 }
function getRatingStarItems(){
	return [
			[
				'rating'=>5,
				'value'=>'',				
			],
			[
				'rating'=>4,
				'value'=>Lang::get('product.and_up'),
			],
			[
				'rating'=>3,
				'value'=>Lang::get('product.and_up'),
			],
			[
				'rating'=>2,
				'value'=>Lang::get('product.and_up'),
			],
			[
				'rating'=>1,
				'value'=>Lang::get('product.and_up'),
			],
			[
				'rating'=>0,				
				'value'=>Lang::get('product.and_up'),
			], 
	];
}

function logisticTimeArr(){
	return [10,14,16,18,20,22];
}

if(!function_exists('createSlug')) {
	function createSlug($str, $options = array()) {
		// Make sure string is in UTF-8 and strip invalid UTF-8 characters
		$str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());
		
		$defaults = array(
			'delimiter' => '-',
			'limit' => null,
			'lowercase' => true,
			'replacements' => array(),
			'transliterate' => false,
		);
		
		// Merge options
		$options = array_merge($defaults, $options);
		
		$char_map = array(
			// Latin
			'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C', 
			'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 
			'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O', 
			'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH', 
			'ß' => 'ss', 
			'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c', 
			'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 
			'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o', 
			'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th', 
			'ÿ' => 'y',
			// Latin symbols
			'©' => '(c)',
			// Greek
			'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
			'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
			'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
			'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
			'Ϋ' => 'Y',
			'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
			'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
			'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
			'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
			'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',
			// Turkish
			'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
			'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g', 
			// Russian
			'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
			'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
			'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
			'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
			'Я' => 'Ya',
			'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
			'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
			'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
			'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
			'я' => 'ya',
			// Ukrainian
			'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
			'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',
			// Czech
			'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U', 
			'Ž' => 'Z', 
			'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
			'ž' => 'z', 
			// Polish
			'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z', 
			'Ż' => 'Z', 
			'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
			'ż' => 'z',
			// Latvian
			'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N', 
			'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',
			'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
			'š' => 's', 'ū' => 'u', 'ž' => 'z'
		);
		
		// Make custom replacements
		$str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);
		
		// Transliterate characters to ASCII
		if ($options['transliterate']) {
			$str = str_replace(array_keys($char_map), $char_map, $str);
		}
		
		// Replace non-alphanumeric characters with our delimiter
		$str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);
		
		// Remove duplicate delimiters
		//$str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);
		
		// Truncate slug to max. characters
		$str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');
		
		// Remove delimiter from ends
		$str = trim($str, $options['delimiter']);
		
		return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
	}
}
function getConfigurationValue($system_name) {
    $conf_lists = \App\SystemConfig::getSystemVal($system_name); 
    return $conf_lists;
}
function sendOtp($phone_no){
    $server = \App\SmsTransmissionMethod::where('is_default','1')->where('type', 'otp')->first();
    if(!empty($server)){

        /*$key = getConfigValue('SMS_KEY');
        $secret = getConfigValue('SMS_SECRET_KEY');
        $url = getConfigValue('SMS_URL').'request';*/

        $key = $server->username;
        $secret = $server->password;
        $url = $server->api_url.'request';
        $post_arr = ['key'=>$key,'secret'=>$secret,'msisdn'=>$phone_no];
        $otp_response = handleCurlRequestOtp($url,$post_arr);
        if(!empty($otp_response['data']) && isset($otp_response['data']['token'])){
            $token = $otp_response['data']['token'];
            $response = ['status'=>'success','token'=>$token];
        }else{
            $response = ['status'=>'fail','msg'=>$otp_response];
        }
        return $response;

    }
    

    
}
function handleCurlRequestOtp($server_url,$post_data) {
    try{
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $server_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 1,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $post_data,
            CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "content-type: multipart/form-data"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        $return  = [];
        if ($err) {
            $returnResponse = ['status'=>'failed','message'=>$err];
        } else {
            if($response)
                $returnResponse = json_decode($response,true);
            else
                $returnResponse = ['status'=>'failed','message'=>$err];
        }
    }
    catch(\Exception $e) {
        $returnResponse = ['status'=>'failed','message'=>$e->getMessage()];
    }
    return $returnResponse;
}
if(!function_exists('getProductUrl')){
	function getProductUrl($url){
		return action('ProductDetailController@display',$url);
	}
}
if (! function_exists('formatVal')) {
	function formatVal($price, $currencyId=Null,$convert_cur='Y')
	{
		//dd(session('default_currency_id'),'ok');
		$cur_text = $cur_det = '';
		$currencyVal = $price;
		if($convert_cur=='Y'){
			$currencyVal = ($currencyId) ? convertCurrency($price,$currencyId,session('default_currency_id')) : $price;
		} else {
			$cur_det = getCurrency($currencyId);
			//dd($cur_det);
		}
		//dd($cur_det);
		
		$price_decimal = getConfigValue('PRICE_DECIMAL');
		$thousand_seprate = getConfigValue('THOUSAND_SEPRATE');
		$decimal_seprate = getConfigValue('DECIMAL_SEPRATE');

		
		if(getConfigValue('CURRENCY_MODE')=='symbol'){
			$cur_text = ($cur_det)?$cur_det['symbol']:session('default_currency_symbol');
		}else{
			$cur_text = ($cur_det)?$cur_det['code']:session('default_currency_code');
		}
		//dd($cur_text);
		
		$no_val = $currencyVal;

		if($price_decimal == 'show'){
			$no_val= number_format($currencyVal, 2,$decimal_seprate,$thousand_seprate);
		}else{
			if(intval($currencyVal) == $currencyVal){
				/****for integer*******/
				$no_val= number_format($currencyVal,0,$decimal_seprate,$thousand_seprate);
			}else{
				$no_val= number_format($currencyVal, 2,$decimal_seprate,$thousand_seprate);
			}
		}
		if(getConfigValue('SHOW_CURRENCY')=='after_price'){
			return $no_val.' '.$cur_text;
		}else{
			return $cur_text.' '.$no_val;
		}
		
	}
}
if(!function_exists('getCurrencyCode')){
	function getCurrencyCode($currency_id){
		$curr = \App\Currency::where('id',$currency_id)->first();
		//dd($curr,$curr->currency_code);
		if(isset($curr->code)){
			return $curr->code;	
		}else{
			
		}
			
	}
}

function getCurrency($cur_id){
	$cur_data = \App\Currency::select('id')->where('is_default','1')->first();
	if ($cur_data) {
		$cur_id = $cur_data->id;
	}
	$cur = \App\Currency::select('id','code','symbol','name')->get();
    $cur_arr = [];
    
	foreach ($cur as $key => $value) {
		$cur_arr[$value->id] = ['code'=>$value->code,'name'=>$value->name,'symbol'=>$value->symbol];
	}
	if ($cur_id) {
		return $cur_arr[$cur_id];
	}else{
		return $cur_arr;
	}
    
}
function getZip($id=null)
{
	$return_obj = \App\CountryCityDistrictZip::Select('zip')->where(['district_id'=>$id])->get();
	$str='';
	if ($return_obj) {
		
		foreach ($return_obj as $key => $value) {
			if ($key=="0") {
				$str =$value->zip;
			}else{
				$str =$str.','.$value->zip;
			}
			
		}
		
	}
	return $str;
	//dd($str);
}

function convert_string($price){
	$price = number_format(floatval($price), 2);
	$price  = (String) $price;
	$price  = str_replace('.00', '', $price);
	return $price;
}

function stripTags($text=''){
   return trim(strip_tags($text));
}
function checkPermission($slug){
	$menus = \App\Menu::where([['status', '=', '1'],['slug', '=', $slug]])->first();
	if(!empty($menus)){

		//dd($menus, session('menu_permision_arr'));

		if(!empty(session('menu_permision_arr')) && in_array($menus->id, session('menu_permision_arr'))) {
				return true;
		}
		else {
			return false;
		}            
	}
	else {
		return false;
	}
}