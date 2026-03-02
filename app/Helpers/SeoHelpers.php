<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

use Config;
use Lang;
use Auth;
use App\User;
use App\Language;
use App\SeoSuperAdmin;
use App\SeoSuperAdminDesc;

//use App\SeoProductWise;
//use App\SeoProductWiseDesc;

use App\SeoGlobal;
use App\SeoGlobalDesc;
use App\SeoPage;
use App\SeoPageDesc;
use App\Product;
use App\ProductDesc;
use App\Helpers\GeneralFunctions; 

class SeoHelpers{
	public static function FetchSeoTags($page=null, $data=null){
	    $replaceDefaultData = '';
	    $default_lang = session('default_lang');
	    switch ($page) {
	        case 'products'://SEO OF PRODUCT
	            if(!empty($data) &&   $data['template_type'] == '3'){
	              $replaceDefaultData = Self::fetchDefaultpageData((object)$data, $page);
	              return Self::headerSeoTags((object)$data, $replaceDefaultData);
	            }else if(!empty($data) &&   $data['template_type'] == '2'){
	              /*This is for the manual or Selected Template*/
	              $seoId =   $data['admin_template_id'];
	              if(!empty($seoId)){
	                $resultTemplate = Self::fetchglobalTemplate($seoId);
	                     //dd($resultTemplate);
	                return Self::replaceVariable($resultTemplate, (object)$data);
	              }
	            }else{ 
	              $replaceDefaultData = Self::fetchDefaultpageData((object)$data, $page);
	              return Self::headerSeoTags((object)$data, $replaceDefaultData);
	            }
	        break;
	        case 'blogDetails'://SEO OF PRODUCT
	            if(!empty($data) &&   $data['template_type'] == '3'){
	                $replaceDefaultData = Self::fetchDefaultpageData((object)$data, $page);
	                return Self::headerSeoTags((object)$data, $replaceDefaultData);

	            }else if(!empty($data) &&   $data['template_type'] == '2'){
	                /*This is for the manual or Selected Template*/
	                /*$seoId =   $data['admin_template_id'];
	                if(!empty($seoId)){
	                   $resultTemplate = Self::fetchglobalTemplate($seoId);
	                   //dd($resultTemplate);
	                    return Self::replaceVariable($resultTemplate, (object)$data);

	                }*/

	            }else{ 
	                $replaceDefaultData = Self::fetchDefaultpageData((object)$data, $page);
	                return Self::headerSeoTags((object)$data, $replaceDefaultData);
	            }
	        break;
	        case 'categoryBlogList'://SEO OF PRODUCT
	          //dd($data);
	          $replaceDefaultData = Self::fetchDefaultpageData((object)$data, $page);
	          return Self::headerSeoTags((object)$data, $replaceDefaultData);
	        break;
	        case 'category':
	          //$data = $data->getcategoryDetail;
	          
	          $replaceDefaultData = Self::fetchDefaultpageData($data, $page,'mongo');
	          //dd($replaceDefaultData,$data);
	          return Self::headerSeoTagsFromMongo((object)$data, $replaceDefaultData);
	        break;
	        case 'shop_cms_page':
	          $enableDisable = Self::GetSellerSeoShopSettingbyShopId($data->user_id, 'page');
	          $data = $data->cmsDesc;
	          $data->meta_description = $data->meta_desc;
	          if(!empty($enableDisable)){
	            $replaceDefaultData = Self::fetchDefaultpageData($data, $page);
	            return Self::headerSeoTags($data, $replaceDefaultData);
	          }else{
	            $data->name = $data->page_title;
	            $data->description = $data->page_desc;
	            /*$seoSellerId = GeneralFunctions::systemConfig('SEO_SELLER_CMS_PAGE');
	               $resultTemplate = Self::fetchglobalTemplate($seoSellerId);
	               return Self::replaceVariable($resultTemplate, $data);*/
	            $resultTemplate = Self::fetchDefaultpageData('', $page);
	            return Self::replaceVariable($resultTemplate, $data);
	          }
	          break;
	        case 'home':
	            $resultTemplate = Self::fetchDefaultpageData();
	            return Self::replaceVariable($resultTemplate);
	          break;
	        default:
	            $data = isset($data)?$data:'';
	            $resultTemplate = Self::fetchDefaultpageData($data, $page);
	            //dd($resultTemplate);
	            return Self::replaceVariable($resultTemplate);
	    }
	}



  	public static function headerSeoTags($data, $replaceDefaultData=null){
	    if(!empty($replaceDefaultData)){
	      $meta_title = isset($data->meta_title) && !empty($data->meta_title) ? strip_tags($data->meta_title): strip_tags($replaceDefaultData->meta_title);
	      $meta_keyword = isset($data->meta_keyword) && !empty($data->meta_keyword) ?strip_tags($data->meta_keyword): strip_tags($replaceDefaultData->meta_keyword);
	      $meta_description = isset($data->meta_description) && !empty($data->meta_description) ? strip_tags($data->meta_description):strip_tags($replaceDefaultData->meta_description);
	    }else{
	      $meta_title = isset($data->meta_title)? strip_tags($data->meta_title):'';
	      $meta_keyword = isset($data->meta_keyword)?strip_tags($data->meta_keyword):'';
	      $meta_description = isset($data->meta_description)? strip_tags($data->meta_description):'';
	    }
	    $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	    $image = isset($data->prd_src) && !empty($data->prd_src) ? $data->prd_src:Config::get('constants.social_share_url').GeneralFunctions::systemConfig('OG_IMAGE');

	    $seoTags = '';
	    $seoTags .= '<title>'.$meta_title.'</title>
	        ';
	        $seoTags .= '<meta name="keywords" content="'.$meta_keyword.'" />
	        ';
	        $seoTags .= '<meta name="description" content="'.$meta_description.'" />
	        ';
	        $seoTags .= '<link rel="canonical" href="'.$actual_link.'" />
	        ';
	        $seoTags .= '<meta http-equiv="content-language" content="'.session('lang_code').'" />
	        ';
	        if(isset($data->meta_robots) && !empty($data->meta_robots)){
	          $seoTags .= '<meta name="robots" content="'.$data->meta_robots.'" />
	          ';
	        }
	        $seoTags .= '<meta property="og:url"  content="'.$actual_link.'"/>
	        ';
	        // $seoTags .= '<meta property="og:type"  content="article" />';
	        $seoTags .= '<meta property="og:title" content="'.$meta_title.'" />
	        ';
	        $seoTags .= '<meta property="og:description" content="'.$meta_description.'" />
	        ';
	        $seoTags .= '<meta property="og:image" content="'.$image.'" />
	        ';
	        $seoTags .= '<meta name="twitter:card" content="'.$meta_description.'" />
	        ';
	        //$seoTags .= '<meta name="twitter:site" content="@XXXX" />';
	        //$seoTags .= '<meta name="twitter:creator" content="@XXXX" />';
	        //$seoTags .= '<meta property="fb:admins" content="XXXXXX">';  
	        $seoTags .= '<meta name="twitter:url" content="'.$actual_link.'">
	        '; 
	        $seoTags .= '<meta name="twitter:title" content="'.$meta_title.'">
	        ';  
	        $seoTags .= '<meta name="twitter:description" content="'.$meta_description.'">
	        '; 
	        $seoTags .= '<meta name="twitter:image" content="'.$image.'">
	        '; 
	        $seoTags .= '<meta name="pinterest:description" content="'.$meta_description.'">
	        '; 
	        $seoTags .= '<meta name="pinterest:image" content="'.$image.'">'; 
	    //$seoTags .= '<meta name="google-site-verification" content="XXXXX" />'; 
	    return $seoTags;
  	}

  	public static function headerSeoTagsFromMongo($data, $replaceDefaultData=null){
  		//dd($data,$replaceDefaultData,$replaceDefaultData->meta_title);
	    if(!empty($replaceDefaultData)){
	    	//dd("ok1",$replaceDefaultData);
	      $meta_title = isset($data->meta_title['de']) && !empty($data->meta_title['de']) ? $data->meta_title['de']: $replaceDefaultData->meta_title;
	      $meta_keyword = isset($data->meta_keyword['de']) && !empty($data->meta_keyword['de']) ?$data->meta_keyword['de']: $replaceDefaultData->meta_keyword;
	      $meta_description = isset($data->meta_description['de']) && !empty($data->meta_description['de']) ? $data->meta_description['de']:$replaceDefaultData->meta_description;
	    }else{
	    	//dd("ok2",$data);
	      $meta_title = isset($data->meta_title)? strip_tags($data->meta_title['de']):'';
	      $meta_keyword = isset($data->meta_keyword)?strip_tags($data->meta_keyword['de']):'';
	      $meta_description = isset($data->meta_description)? strip_tags($data->meta_description['de']):'';
	    }
	    $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	    $image = isset($data->prd_src) && !empty($data->prd_src) ? $data->prd_src:Config::get('constants.social_share_url').GeneralFunctions::systemConfig('OG_IMAGE');

	    $seoTags = '';
	    $seoTags .= '<title>'.$meta_title.'</title>
	        ';
	        $seoTags .= '<meta name="keywords" content="'.$meta_keyword.'" />
	        ';
	        $seoTags .= '<meta name="description" content="'.$meta_description.'" />
	        ';
	        $seoTags .= '<link rel="canonical" href="'.$actual_link.'" />
	        ';
	        $seoTags .= '<meta http-equiv="content-language" content="'.session('lang_code').'" />
	        ';
	        if(isset($data->meta_robots) && !empty($data->meta_robots)){
	          $seoTags .= '<meta name="robots" content="'.$data->meta_robots.'" />
	          ';
	        }
	        $seoTags .= '<meta property="og:url"  content="'.$actual_link.'"/>
	        ';
	        // $seoTags .= '<meta property="og:type"  content="article" />';
	        $seoTags .= '<meta property="og:title" content="'.$meta_title.'" />
	        ';
	        $seoTags .= '<meta property="og:description" content="'.$meta_description.'" />
	        ';
	        $seoTags .= '<meta property="og:image" content="'.$image.'" />
	        ';
	        $seoTags .= '<meta name="twitter:card" content="'.$meta_description.'" />
	        ';
	        //$seoTags .= '<meta name="twitter:site" content="@XXXX" />';
	        //$seoTags .= '<meta name="twitter:creator" content="@XXXX" />';
	        //$seoTags .= '<meta property="fb:admins" content="XXXXXX">';  
	        $seoTags .= '<meta name="twitter:url" content="'.$actual_link.'">
	        '; 
	        $seoTags .= '<meta name="twitter:title" content="'.$meta_title.'">
	        ';  
	        $seoTags .= '<meta name="twitter:description" content="'.$meta_description.'">
	        '; 
	        $seoTags .= '<meta name="twitter:image" content="'.$image.'">
	        '; 
	        $seoTags .= '<meta name="pinterest:description" content="'.$meta_description.'">
	        '; 
	        $seoTags .= '<meta name="pinterest:image" content="'.$image.'">'; 
	    //$seoTags .= '<meta name="google-site-verification" content="XXXXX" />'; 
	    return $seoTags;
  	}

	public static function replaceVariable($resultTemplate, $data=null){
	    //dd($resultTemplate);
	    // dd($data);
	    $replaceVariable =  Self::Variables($data);
	    //dd($replaceVariable);
	    $replaceVariable = array_filter($replaceVariable);
	    if(empty($resultTemplate)) return false;
	    foreach($replaceVariable as $key => $value){
	      $replaceKey = '['.$key.']';
	      $resultTemplate->meta_title = str_replace($replaceKey ,$value, $resultTemplate->meta_title);
	      $resultTemplate->meta_keyword = str_replace( $replaceKey ,$value, $resultTemplate->meta_keyword);
	      $resultTemplate->meta_description = str_replace( $replaceKey ,$value, $resultTemplate->meta_description);
	    } 
	    //dd($resultTemplate);
	    return Self::headerSeoTags($resultTemplate);    
	}


  	public static function replaceVariableData($resultTemplate, $data=null,$type=null){
      	//dd($resultTemplate,$data);
	    if(!empty($data)){
	      	$replaceVariable = Self::Variables($data);
	      	//dd($replaceVariable,$resultTemplate);
	      	if(empty($resultTemplate)) return false;
	      	if (empty($type)) {
	      		foreach($replaceVariable as $key => $value){

		        	$replaceKey = '['.$key.']';
		        	$resultTemplate->meta_title = str_replace($replaceKey ,$value, $resultTemplate->meta_title);

		        	$resultTemplate->meta_keyword = str_replace( $replaceKey ,$value, $resultTemplate->meta_keyword);
		        	$resultTemplate->meta_description = str_replace( $replaceKey ,$value, $resultTemplate->meta_description);
		      	} 
	      	}else{
	      		$resultTemplate->meta_title = $resultTemplate->meta_title;
		        $resultTemplate->meta_keyword = $resultTemplate->meta_keyword;
		        $resultTemplate->meta_description = $resultTemplate->meta_description;
	      	}
	      	
	      	//dd($resultTemplate);
	      return $resultTemplate;
	    }
  	}
  public static function Variables($data){
      //dd($data);
    return[ 
      'SITE_NAME' => config('app.name'),
      'SITE_URL'  => url('/'),
      'USER_NAME'  => Auth::check()?Auth::user()->display_name:'',
      'PRODUCT_NAME' => isset($data->prd_name)?$data->prd_name:'',
      'PRODUCT_SKU' => isset($data->sku)?$data->sku:'',
      'PRODUCT_DESCRIPTION' => isset($data->prd_desc)?$data->prd_desc:'',
      'PRODUCT_PRICE' => isset($data->productPrice)?$data->productPrice.' '.session('default_currency_code'):'',
      'CATEGORY_NAME' => isset($data->category_name)?$data->category_name:'',
      'CATEGORY_DESCRIPTION' => isset($data->cat_description)?$data->cat_description:'',
      'CMS_TITLE' => isset($data->name)?$data->name:'',
      'CMS_DESCRIPTION' => isset($data->description)?$data->description:'',
      'BLOG_NAME' => isset($data->title)?$data->title:'',
      'TAG_NAME' => isset($data->title)?$data->title:'',
    ];
  }
  
  public static function fetchglobalTemplate($seoTemplateId, $currentResult=null){
    $default_lang = session('default_lang');
    $templateResult = DB::table(with(new SeoGlobal)->getTable().' as sg')
     ->leftjoin(with(new SeoGlobalDesc)->getTable().' as sgd', 
      [['sg.id', '=','sgd.seo_global_id'],['sgd.lang_id', '=' , DB::raw($default_lang)]] 
      )->select('sgd.meta_title', 'sgd.meta_keyword', 'sgd.meta_description', 'sg.meta_robots')->where(['sg.id'=> $seoTemplateId])->first();
    if(!empty($currentResult)){
      $templateResult->meta_robots = $currentResult->meta_robots;
    }
    return $templateResult;
  }

public static function getDefaultUrl(){
  $url = $_SERVER['REQUEST_URI'];
  $lang_code = session('lang_code');
  $lang_code1  = '/'.$lang_code.'/';
  if(strpos($url, $lang_code1) !== false){
    return trim(str_replace($lang_code1, '', $url));
  }else{
    $lang_code1  = '/'.$lang_code; 
    return trim(str_replace($lang_code1, '/', $url));
  }
}


public static function convertUrlwithRegulerExpression(){
  /*this function use for get current URL*/ 
  $dburl = Self::getDefaultUrl();
  $dburlexplodes = explode('/', $dburl); 
  //dd($dburlexplodes);
  if (is_array($dburlexplodes) && is_array($dburlexplodes)>0){ $urlcount=is_array($dburlexplodes); } else { $urlcount=0; }
  $default_lang = session('default_lang');
  $prefix = DB::getTablePrefix();
  //dd($urlcount);
  //found out fixed position in array
  $start = 0;
  if($urlcount){
    $start = $urlcount - 1;
  }
  if($start>=2){
    $start = 2;
  }else{
    $start = 1;
  }
  //$start = !empty($start)?$start:1;
  //dd($start);
  if (is_array($dburlexplodes) && is_array($dburlexplodes)>1){
    $newUrl = '';
    for($i=0; $i < $start; $i++){
       $newUrl .= empty($newUrl)?$dburlexplodes[$i]:'/'.$dburlexplodes[$i];
    }
    
    //dd($newUrl);

    $preg_match_strings = array('[a-z]','[a-zA-Z]','[0-9]','[a-zA-Z0-9]');
    foreach($preg_match_strings as $preg_match_string){
      $currentURL = '';
      //dd($dburlexplodes[$start]);
      $currentURL = str_replace('-', '', $dburlexplodes[$start]);
      if(preg_match('/^'.$preg_match_string.'+$/', $currentURL)){
        $currentURL = $newUrl.'/'.$preg_match_string;
        $result = DB::table(with(new SeoPage)->getTable().' as sp')->leftjoin(with(new SeoPageDesc)->getTable().' as spd',[['sp.id', '=','spd.seo_page_id'],['spd.lang_id', '=' , DB::raw($default_lang)]])->select('spd.meta_title', 'spd.meta_keyword', 'spd.meta_description', 'sp.meta_robots', 'sp.template_type', 'sp.admin_template_id')->whereRaw('FIND_IN_SET("'.$currentURL.'", '.$prefix.'sp.url)')->first();
        if(!empty($result)){
           return $result;
        }
      }    
    }


    if(!empty($newUrl)){
      $result = DB::table(with(new SeoPage)->getTable().' as sp')->leftjoin(with(new SeoPageDesc)->getTable().' as spd',[['sp.id', '=','spd.seo_page_id'],['spd.lang_id', '=' , DB::raw($default_lang)]])->select('spd.meta_title', 'spd.meta_keyword', 'spd.meta_description', 'sp.meta_robots', 'sp.template_type', 'sp.admin_template_id')->whereRaw('FIND_IN_SET("'.$newUrl.'", '.$prefix.'sp.url)')->first();
        if(!empty($result)){
           return $result;
        }
    }

    /*for($i=$start; $i < $urlcount; $i++){
      if(preg_match('/^[a-zA-Z0-9]+$/', $dburlexplodes[$i])){
        $newUrl = $newUrl.'/'.'[a-zA-Z0-9]';
        //$newUrl = $newUrl.'/'.$dburlexplodes[$i];
      }else if(preg_match('/^[a-zA-Z]+$/', $dburlexplodes[$i])){
        //$newUrl = $newUrl.'/'.'[a-z]';
        $newUrl = $newUrl.'/'.'[a-zA-Z]';
      }else if(preg_match('/^[0-9]+$/', $dburlexplodes[$i])){
        $newUrl = $newUrl.'/'.'[0-9]';
      }else{
        $newUrl = $newUrl.'/'.'[a-zA-Z0-9]';
      }
      
    }*/
  
    
  }
  

}



public static function fetchDefaultpageData($data=null, $slug = null,$type=null){

  $default_lang = session('default_lang');
  $prefix = DB::getTablePrefix();
  if(empty($slug)) $currentURL = Self::getDefaultUrl();
  else $currentURL = $slug;

  $result = DB::table(with(new SeoPage)->getTable().' as sp')
    ->leftjoin(with(new SeoPageDesc)->getTable().' as spd', 
       [   ['sp.id', '=','spd.seo_page_id'],
           ['spd.lang_id', '=' , DB::raw($default_lang)]
       ] 
    )
  ->select('spd.meta_title', 'spd.meta_keyword', 'spd.meta_description', 'sp.meta_robots', 'sp.template_type', 'sp.admin_template_id')
  ->where(['sp.url'=> $currentURL])->first();
  //dd($currentURL,$result);
  if(empty($result)){
    $result = Self::convertUrlwithRegulerExpression();
    //dd($currentURL); //where(['sp.url'=> $currentURL])
    /*$result = DB::table(with(new SeoPage)->getTable().' as sp')
      ->leftjoin(with(new SeoPageDesc)->getTable().' as spd', 
      [['sp.id', '=','spd.seo_page_id'],['spd.lang_id', '=' , DB::raw($default_lang)]])
      ->select('spd.meta_title', 'spd.meta_keyword', 'spd.meta_description', 'sp.meta_robots', 'sp.template_type', 'sp.admin_template_id')
      ->whereRaw('FIND_IN_SET("'.$currentURL.'", '.$prefix.'sp.url)')->first();*/
  }
  if(isset($result->template_type) && $result->template_type == '2'){
    $seoadmin_template_id = '';  
    $seoadmin_template_id = $result->admin_template_id;
    if(!empty($seoadmin_template_id)){
      $resultTemplate = Self::fetchglobalTemplate($seoadmin_template_id, $result); //dd($resultTemplate);
    }  
  }else{
    $resultTemplate = $result;
  }
  //dd($resultTemplate,$data);
  if(!empty($data)){
  	if (!empty($type)) {
  		return $replacedData = Self::replaceVariableData($resultTemplate, $data,$type);
  	}else{
  		return $replacedData = Self::replaceVariableData($resultTemplate, $data);
  	}
    
     //dd($replacedData);
  }else{
  	//dd("ok");
    return $resultTemplate; 
  }
}


public static function googleTagManagerHead(){
  return GeneralFunctions::systemConfig('GOOGLE_ANALYTICS_HEAD');
}



public static function googleTagManagerBody(){
  return GeneralFunctions::systemConfig('GOOGLE_ANALYTICS_BODY');
}


public static function GoogleAnalytic($data){
  $code = '';
  /*if(isset($data->google_analytic) && !empty($data->google_analytic)){
    $code = "<script>
           (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

      ga('create', '".$data->google_analytic."', 'auto');
      ga('send', 'pageview');
      </script>";
  }*/

// $data = json_decode($data); 
 //dd($data->productInfo->google_analytic); 
  return $code;


   //return isset($data->google_analytic);
} 






}