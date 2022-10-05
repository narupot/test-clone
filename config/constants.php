<?php

// define path
$public_path = public_path();           // /var/www/html/marketadmin/public
$files_path = $public_path.'/files';
$base_path = base_path();               // /var/www/html/marketadmin

$public_url = env('APP_URL');    // http://marketadmin.localhost/
$files_url = $public_url.'files/';
$assets_url = $public_url.'assets/';
$product_detail_url = $public_url.'product/';
$cdn_base_url = $public_url; 

$mobile_app_url = env('MOBILE_APP_URL'); 


$localmode = false;
if(env('APP_ENV') == 'local') {
    $localmode = true;
}

return [

    'enable_cache'=>1,
    'localmode'=>$localmode,
    'default_theme' =>'',
    'base_path'=>$base_path,
    'public_path'=>$public_path,
    'files_path'=>$files_path,
    'image_path'=>$public_path.'/images',
    'language_path'=>$files_path.'/language',
    'multi_language_path'=>$base_path.'/resources/lang',
    'country_flag_path'=>$files_path.'/country_flag',
    'user_path'=>$files_path.'/users',
    'customer_path'=>$files_path.'/customer',
    'placeholder_path'=>$files_path.'/placeholder',
    'site_logo_path' => $files_path.'/site_logo',
    'site_loader_path'=> $files_path.'/site_loader',
    'color_path'=>$files_path.'/color',
    'banner_path'=>$files_path.'/banner',
    'social_share_path' => $files_path.'/social_share',
    'social_icon_path' => $files_path.'/social_icon', 
    'froala_img_path'=>'/files/media_manager/',
    'avtar_images_path' =>$files_path.'/avtar_images',
    'blog_path'=>$files_path.'/blog',
    'blog_path_570'=>$files_path.'/blog/blog-570x402',
    'blog_original_image_path'=>$files_path.'/blog/original_blog_image/',
    'blog_feature_img_path'=>$files_path.'/blog/feature_image',
    'blog_socialshare_img_path'=>$files_path.'/blog/socialshare_image/',
    'blog_slider_img_path'=>$files_path.'/blog/slider_image/',
    'blog_image_path'=>$files_path.'/blog/image/',
    'static_page_path'=>$files_path.'/static_page',
    'data_cache_path'=>$files_path.'/data_cache',
    'page_socialshare_img_path'=>$files_path.'/page/socialshare_image/',
    'seller_img_path'=>$files_path.'/seller',
    'payment_option_path'=>$files_path.'/payment_option', 
    'payment_bank_path'=>$files_path.'/payment_bank',
    'standard_badge_path'=>$files_path.'/standard_badge',
    'shop_img_path'=>$files_path.'/shop',
    'shop_original_path'=>$files_path.'/shop/original',
    'category_img_path'=>$files_path.'/category',
    'product_path'=>$files_path.'/product',
    'product_original_image_path'=>$files_path.'/product/original',
    'review_complain_file_path'=>$files_path.'/review_complain',
    'firebase_file_path' => env('FIREBASE_CREDENTIALS'),

    'public_url'=>$public_url,
    'cdn_base_url'=>$public_url,
    'assets_url'=>$assets_url,
    'admin_js_url'=>$assets_url.'js/',
    'admin_css_url'=>$assets_url.'css/',
    'js_url'=>$public_url.'js/',
    'angular_url'=>$public_url.'js/angular/',
    'angular_front_url'=>$public_url.'js/angular/smmApp/',
    'angular_libs_url'=>$public_url.'js/angular/libs/',
    'angular_admin_url'=>$assets_url.'js/angular/',
    'angular_admin_lib_url'=>$assets_url.'js/angular/libs/',
    'angular_app_url'=>$assets_url.'js/angular/master-client/',
    'css_url'=>$public_url.'css/',
    'theme_url'=>$public_url.'theme/',
    'files_url'=>$files_url,
    'image_url'=>$public_url.'images/',
    'language_url'=>$files_url.'language/',    
    'country_flag_url'=>$files_url.'country_flag/',
    'user_url'=>$files_url.'users/',
    'users_default_url'=> $files_url.'placeholder/',
    'customer_url'=>$files_url.'customer/',
    'loader_url'=>$public_url.'loader/',
    'placeholder_url'=>$files_url.'placeholder/',   
    'site_logo_url' => $files_url.'site_logo/', 
    'site_loader_url'=>$files_url.'site_loader/',
    'banner_url'=>$files_url.'banner/',
    'social_share_url' => $files_url.'social_share/',
    'social_icon_url' => $files_url.'social_icon/', 
    'remind_url' => $files_url.'reminder_icon/',
    'avtar_images_url' =>$files_url.'avtar_images/',
    'blog_url'=>$files_url.'blog/',
    'blog_feature_url'=>$files_url.'blog/feature_image/',
    'blog_socialshare_url'=>$files_url.'blog/socialshare_image/',
    'blog_slider_url'=>$files_url.'blog/slider_image/',
    'blog_image_url'=>$files_url.'blog/image/',
    'badge_category_image_url'=>$files_url.'badge/category/',
    'badge_product_image_url'=>$files_url.'badge/product/',
    'comment_product_url' =>$files_url.'comment_files/',
    'uploaded_image_server_path' => env('UPLOADED_IMAGE_SERVER_PATH'),
    'static_page_url'=>$files_url.'static_page/',
    'footer_img_url'=>$files_url.'footer/',
    'data_cache_url'=>$files_url.'data_cache',
    'page_socialshare_url'=>$files_url.'page/socialshare_image/',
    'seller_url'=>$files_url.'seller/',
    'payment_option_url'=>$files_url.'payment_option/',
    'payment_bank_url'=>$files_url.'payment_bank/',
    // smm project constants
    'seller_shop_img_url'=>$public_url.'images/shop/',
    'standard_badge_url'=>$files_url.'standard_badge/',
    'shop_img_url'=>$files_url.'shop/',
    'shop_original_url'=>$files_url.'shop/original/',
    'category_img_url'=>$files_url.'category/',
    'product_img_url'=>$files_url.'product/',
    'review_complain_file_url'=>$files_url.'review_complain/',
    'mobile_notification_url' =>$mobile_app_url.'api/buyer/v1/sendMobileNotification',
    'mobile_app_url' => $mobile_app_url,
    'mobile_app_chat_url'=> env('MOBILE_APP_CHAT_URL'),



];

?>