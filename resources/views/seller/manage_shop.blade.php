@extends('layouts.app') 

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/myaccount','css/bootstrap-select'],'css') !!}
    <link rel="stylesheet" type="text/css" href="{{Config('constants.css_url')}}cropper.min.css">
    <link rel="stylesheet" type="text/css" href="{{Config('constants.css_url')}}jquery.fancybox.min.css">
    <style type="text/css">
        .shop-banner-content {
            position: absolute;
            left: 0;top: 16px;
        }
        .shop-banner {
            margin-bottom: 2rem;
        }
        .shop-banner-content .shop-cam-vacation .shop-cam .fa-camera {
            right: 0;
        }
        .shop-banner-content .shop-cam-vacation{
            bottom: 0;
        }
        .banner-awatar {
            position: relative;
        }
        .banner-awatar .shop-cam-vacation {
            position: absolute;
            bottom: 15px;right: 20px;
            color: #fff;
        }
    </style>
@endsection
<?php 

    $cropper_setting = [
        
        [
            'section' => 'thumb_image', 'dimension' => ['width' => 153, 'height' => 153], 'file_field_selector' => '#thumb_image_logo', 'section_id' => 'thumb-image-logo'
        ],
        [
            'section' => 'social_image', 'dimension' => ['width' => 1110, 'height' => 145], 'file_field_selector' => '#social_image_logo', 'section_id' => 'social-image-logo'
        ]
    ];

?>
@section('header_script')
var url_checkStoreName = "{{ action('Auth\SellerRegisterController@checkStoreName') }}";
var url_checkStoreUrl = "{{ action('Auth\SellerRegisterController@checkStoreUrl') }}";
var url_deleteshopimage = "{{ action('Seller\ShopController@deleteShopImg') }}";
var txt_delete_confirm = "@lang('common.are_you_sure_to_delete_this_record')";
var yes_delete_it = "@lang('common.yes_delete_it')";
var are_you_sure = "@lang('common.are_you_sure')";
var text_reject_message = "@lang('common.reject_message')";
var text_yes_reject_it = "@lang('common.yes_reject_it')";
var txt_no = "@lang('common.no')";
var text_ok_btn = "@lang('common.ok_btn')";
var text_success = "@lang('common.text_success')";
var text_error = "@lang('common.text_error')";
var text_yes_remove_it = "@lang('common.yes_remove_it')";
var updateStatusUrl = "{{ action('Seller\ShopController@updateShopStatus') }}";
var CROPPER_SETTING = {!! json_encode($cropper_setting) !!}; 
@endsection

@section('content')

@if(Session::has('verify_msg'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    {{Session::get('verify_msg')}}
</div>
@endif

@if(Session::has('not_verify_msg')) 
    <div class="alert alert-danger alert-dismissable margin5"> 
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button> 
        {{Session::get('not_verify_msg')}} 
    </div> 
@endif
<!--- including seller top panel -->
    <h1 class="page-title title-border d-flex pb-2">@lang('shop.manage_shop') </h1>
    <form method="POST" action="{{action('Seller\ShopController@updateStore')}}" enctype="multipart/form-data" name="update_shop_form" id="update_shop_form">
        {{ csrf_field() }}
        <!-- <input type="hidden" name="banner_image" id="bannerImage"> -->
        <div class="shop-banner-header">
            <!-- <div class="shop-ban-img">
                <img class="" src="{{getImgUrl($shop_details->banner,'banner')}}" alt="" id="banner_image_thumb">
            </div> -->
            <div class="shop-banner">
                <input type="hidden" name="banner_image" id="social_image_logo">
                <div id="social-image-logo" class="logos">   
                    <div class="avatar-view banner-awatar">
                       <img class="w-100" src="{{getImgUrl($shop_details->banner,'banner')}}" alt="" id="social_image"> 
                       <div class="shop-cam-vacation">
                            <span class="shop-cam"><i class="fas fa-camera"></i></span>                     
                        </div>   
                    </div>
                     @include('includes.common_cropper_upload',['cropper_setting'=>[$cropper_setting[1]]]) 
                </div>
            </div>
            
             
            <div class="shop-banner-content">
                <div class="shop-img" style="position: relative;">
                    <input type="hidden" name="logo_image" id="thumb_image_logo">
                    <div id="thumb-image-logo" class="logos">   
                        <div class="avatar-view">
                            <img src="{{getImgUrl($shop_details->logo,'logo')}}" id="thumb_image" >
                            <div class="shop-cam-vacation">
                                <span class="shop-cam"><i class="fas fa-camera"></i></span>                     
                            </div>
                        </div>
                        @include('includes.common_cropper_upload',['cropper_setting'=>[$cropper_setting[0]]]) 
                    </div>
                </div>                       
            </div>
        </div>
        <div class="shop-content">
            <div class="row">
                <aside class="col-md-2 left-sidebar">
                    <div class="form-group">
                        <label>@lang('shop.shop_status')</label>
                        <label class="button-switch">
                           <input type="checkbox" value="1" name="shop_status" @if($shop_details->shop_status=='open') checked @endif class="switch switch-orange">                        
                             <span for="autoRelated" class="lbl-off">@lang('shop.closed')</span>
                             <span for="autoRelated" class="lbl-on">@lang('shop.open')</span>
                       </label>
                    </div>

                    <div class="form-group">
                        <label>@lang('shop.bargain')</label>
                         <label class="button-switch">
                           <input type="checkbox" value="1" name="bargaining" @if($shop_details->bargaining=='yes') checked @endif class="switch switch-orange">                        
                             <span for="autoRelated" class="lbl-off">@lang('common.no')</span>
                             <span for="autoRelated" class="lbl-on">@lang('common.yes')</span>
                       </label>
                    </div>
                    
                    <div class="side-content ">                         
                        <u>@lang('shop.shops_location')</u>
                        
                        <!-- <div class="shop-location-row mt-2">
                            @lang('shop.market') : Fruit Market
                        </div> -->
                        
                        <div class="shop-location-row">
                            @lang('shop.panel') : {{ $shop_details->panel_no }}
                        </div>

                        <div class="shop-location-row mt-4">
                            @lang('shop.map')
                            <div class="mt-2 mb-2"> 
                                <div class="file-wrapper">
                                    <div class="custom-attach-file">
                                        <span id="map_img"><input type="file" class="location_image" name="location_image[]" accept="image/*" multiple="multiple"></span>                        
                                        <button class="btn-blue custom-img-button">@lang('common.attach')</button>
                                    </div>
                                    
                                </div>

                            </div>

                            <ul class="map-upload-img" id="map-upload-img">  
                                @if(count($map_images))    
                                    @foreach($map_images as $val)
                                        <li>
                                            <a href="{{ getImgUrl($val,'map') }}" data-fancybox="images" class="mapfancy">
                                                <!-- <img src="{{ getImgUrl($val,'map') }}" width="50" height="50"><br> -->
                                                <img src="{{getShopImageUrl($val,'50x50')}}" alt=""><br/>
                                            </a>
                                            <a href="javascript:;" data-val="{{ $val }}" data-type="map" class="deleteShopImg" >
                                                <i class="fas fa-times"></i>
                                            </a>
                                        </li>
                                    @endforeach
                                @endif                            
                            </ul>
                        </div>
                    </div>

                </aside>
                <div class="col-md-10">
                    <div class="shop-content-header manage-store-header">
                        <div class="respons-shop-list">
                            <ul class="respon-update">
                                <!-- <li>
                                    <span class="shop-label">@lang('shop.chat_response')</span>
                                    <span class="res-num">-</span>
                                </li> -->
                                <li class="w-100">
                                    <span class="shop-label">@lang('shop.last_update')</span>
                                    <span class="res-num">{{getDateFormat($shop_details->updated_at,5)}}</span>
                                </li>
                                <!-- <li>
                                    <span class="shop-label">@lang('shop.response_speed')</span>
                                    <span class="res-num">-</span>
                                </li> -->
                                <!-- <li>
                                    <span class="shop-label">@lang('shop.order_cancellation')</span>
                                    <span class="res-num">-</span>
                                </li> -->
                            </ul>
                        </div>                                                      
                    </div>

                    <div class="shop-content box-grey">
                        <div class="row">
                            <div class="col-sm-8">
                                <div class="form-group">
                                    <label>@lang('shop.store_url')</label>
                                    <div class="d-flex align-items-center">
                                        <div>{{ $shop_details->shop_url }}</div>
                                        <input type="hidden" name="store_url" value="{{ $shop_details->shop_url }}">
                                        <!-- <input type="text" name="store_url" value="{{ $shop_details->shop_url }}"> -->
                                        <button type="button" class="btn ml-3" id="copy_url">@lang('common.copy')</button>
                                    </div>
                                    <p class="error" id="e_store_url"></p>
                                </div>
                                <input type="hidden" id="copy_url_val" value="{{ action('ShopController@index',$shop_details->shop_url) }}">
                                
                                <div class="form-group">
                                    <label>@lang('shop.shop_name')</label>
                                    <input type="text" name="store_name" value="{{$shop_details->shopDesc?$shop_details->shopDesc->shop_name:''}}">
                                    <p class="error" id="e_store_name"></p>
                                </div>

                                <div class="form-group">
                                    <label>@lang('shop.products_that_can_be_sold')</label> 
                                    <ul class="sold-product">
                                    @if(count($seller_prod_cat) > 0)
                                        @foreach($seller_prod_cat as $prod_cat)
                                            @if($prod_cat->img)
                                            <li>
                                                <div class="prod-img">                                                  
                                                    <img src="{{getCatImgUrl($prod_cat->img,'50x50')}}" alt="">
                                                </div>
                                                <div class="prod-name">{{ $prod_cat->category_name }}</div>
                                            </li>
                                            @endif
                                        @endforeach
                                    @endif
                                    </ul>
                                </div>

                                <div class="form-group">

                                    <div class="shop-location-row">
                                            @lang('shop.shops_image')
                                            <div class="mt-2 mb-2"> 
                                                <div class="file-wrapper mb-2" style="display: inline-flex; vertical-align: top;">
                                                    <div class="custom-attach-file">
                                                        <span id="shop_img_span"><input type="file" class="shop_image" name="shop_image[]" accept="image/*" multiple="multiple"></span>                        
                                                        <button class="btn-blue custom-img-button">@lang('common.attach')</button>
                                                    </div>
                                                </div>

                                                <ul class="map-upload-img mt-0" id="shop-upload-img" style="display: inline-flex;">
                                                    @if(count($shop_images))    
                                                        @foreach($shop_images as $val)
                                                        <li>
                                                        <a href="{{ getImgUrl($val,'map') }}" data-fancybox="images" class="shopfancy">
                                                            <!-- <img src="{{ getImgUrl($val,'map') }}" width="50" height="50"><br> -->
                                                            <img src="{{getShopImageUrl($val,'50x50')}}" alt=""><br/>
                                                        </a>                                                        

                                                        <a href="javascript:;" data-val="{{ $val }}" data-type="shop" class="deleteShopImg">
                                                            <i class="fas fa-times"></i>
                                                        </a>
                                                        </li>
                                                        @endforeach
                                                    @endif 
                                                </ul>
                                            </div>
                                        </div>                                  
                                </div>

                                <div class="form-group">
                                    <label>@lang('shop.shop_description')</label>
                                    <textarea name="description">{{$shop_details->shopDesc?$shop_details->shopDesc->description:''}}</textarea>
                                    <p class="error" id="e_description"></p>
                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <label>@lang('shop.shop_opening_time')</label>
                                        <input type="text" name="open_time" placeholder="11:00" value="{{$shop_details->open_time}}" class="time-clock" id="custom_time_from">
                                        <p class="error" id="e_open_time"></p>                                     
                                    </div>

                                    <div class="col-sm-4">
                                        <label>@lang('shop.shop_closing_time')</label>
                                        <input type="text" name="close_time" placeholder="17:00" value="{{$shop_details->close_time}}" class="time-clock" id="custom_time_to">
                                        <p class="error" id="e_close_time"></p>                                     
                                    </div>
                                </div>

                                <div class="form-group row" style="display: none;">
                                    <div class="col-sm-6">
                                        <label>@lang('shop.the_time_to_pick_up_product') </label>
                                        <select name="product_pickup_time">
                                            <option value="1" @if($shop_details->product_pickup_time == '1') selected="selected" @endif>@lang('shop.within') 1 @lang('shop.hours')</option>
                                            <option value="2" @if($shop_details->product_pickup_time == '2') selected="selected" @endif>@lang('shop.within') 2 @lang('shop.hours')</option>
                                        </select>
                                        <p class="error" id="e_product_pickup_time"></p>
                                    </div>
                                </div>

                                <div class="form-group row" style="display: none;">
                                    <div class="col-sm-6">
                                        <label>@lang('shop.the_time_to_pick_up_center') </label>
                                        <select name="center_pickup_time">
                                            <option value="1" @if($shop_details->center_pickup_time == '1') selected="selected" @endif>@lang('shop.within') 1 @lang('shop.hours')</option>
                                            <option value="2" @if($shop_details->center_pickup_time == '2') selected="selected" @endif>@lang('shop.within') 2 @lang('shop.hours')</option>
                                        </select>
                                        <p class="error" id="e_center_pickup_time"></p>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <label>@lang('shop.manage_phone_number')</label>
                                        <input type="text" name="ph_number" value="{{$shop_details->ph_number}}" onkeypress="return NumberField(event);" class="" id="" maxlength="25">
                                        <p class="error" id="e_ph_number"></p>                                     
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <label>@lang('shop.line_link')</label>
                                        <input type="text" name="line_link" value="{{$shop_details->line_link}}" class="" id="">
                                        <p class="error" id="e_line_link"></p>                                     
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row manage-store-btn">
                            <div class="col-sm-12">
                                <div class="button-group mt-3">                                         
                                        <button class="btn" type="button" id="btn_shop_info">@lang('common.save')</button>
                                </div>
                            </div>
                        </div>
                        
                        
                    </div>
                
                    
                </div>
            </div>
        </div>
    </form>
    





@endsection 

@section('footer_scripts') 
<script src="{{ Config('constants.js_url') }}jquery-cropper.min.js" type="text/javascript"></script>
<script src="{{ Config('constants.js_url') }}common_cropper_upload_setting.js" type="text/javascript"></script>
<script src="{{ Config('constants.js_url') }}jquery.fancybox.min.js" type="text/javascript"></script>
<!-- <script src="{{ Config('constants.js_url') }}jquery.flickity.pkgd.min.js" type="text/javascript"></script> -->

<script type="text/javascript">       
    $().fancybox({
        selector : '.mapfancy'
    }); 
    $().fancybox({
        selector : '.shopfancy'
    });    

    function NumberField(evt)
    {
        var charCode = (evt.which) ? evt.which : event.keyCode;
        
        if (charCode >= 48 && charCode <= 57 || charCode == 8 || charCode == 44)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
</script>

<script type="text/javascript">
jQuery(document).ready(function(){  
     
        if (jQuery(window).width() < 1023) {
            jQuery('.seller-carousel').flickity({             
              resize: true,
              wrapAround: false,
              cellAlign: 'left',      
              pageDots: false,
              contain: true
            });
        }
    
});
$('#copy_url').click(function(e){
    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val($('#copy_url_val').val()).select();
    document.execCommand("copy");
    $temp.remove();
    /*var shopurl = $(this).data('clipboard-text');
    shopurl.select();
    console.log(shopurl);
    document.execCommand("copy");*/
})
</script>
{!! CustomHelpers::combineCssJs(['js/seller/manage_shop'],'js') !!}

@stop