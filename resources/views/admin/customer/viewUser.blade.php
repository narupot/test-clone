@extends('layouts/admin/default')

@section('title')
    @lang('admin_customer.user_details')
@stop

@section('header_styles')
    <link rel="stylesheet" type="text/css" href="{{Config('constants.css_url') }}cropper.min.css">
    <link rel="stylesheet" type="text/css" href="{{Config('constants.css_url') }}flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="{{Config('constants.css_url')}}jquery.fancybox.min.css">
  <?php  
        $cropper_setting = [
            [
                'section' => 'user_thumb', 'dimension' => "{width : 115, height : 115}", 'file_field_selector' => '#userThumbImage', 'section_id'=>'user-thumb', 'image_type' => 'jpg,png'
            ],
            [
                'section' => 'banner_image_thumb', 'dimension' => ['width' => 1110, 'height' => 145], 'file_field_selector' => '#bannerImage', 'section_id'=>'banner-image',
            ],
            [    'section' => 'logo_image_thumb', 'dimension' => ['width' => 153, 'height' => 153], 'file_field_selector' => '#logoImage', 'section_id'=>'logo-image',
            ]
        ];
    ?>
    <script type="text/javascript">
        var CROPPER_SETTING = {!! json_encode($cropper_setting) !!}; 
        var user_id = {{ $user->id }};
        var shop_id ='';
        @if($user->user_type == 'seller' && $shop_details)
            var shop_id = {{ $shop_details->id }};
        @endif
        
    </script>
    
@stop

@section('content')
<div class="content">
    <div class="form-horizontal form-bordered">
        <div class="header-title">
            @if(Session::has('succMsg'))
            <div class="alert alert-success alert-dismissable margin5">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <strong>@lang('admin_common.success'):</strong> {{ Session::get('succMsg') }}
            </div>
            @endif 
            <h1 class="title">{{ucwords($user->first_name)}} {{ucwords($user->last_name)}}</h1> 
            <div class="float-right">
                @if($user->user_type == 'seller')
                <a href="{{ action('Admin\Customer\SellerController@index') }}" class="btn btn-back"><span><</span>@lang('admin_common.back')</a>
                @else
                <a href="{{ action('Admin\Customer\UserController@index') }}" class="btn btn-back"><span><</span>@lang('admin_common.back')</a>
                @endif

            </div>               
        </div>
        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('seller')!!}
                </ul>
            </div>
            <div class="content-left">
                <h3 class="mb-3">Buyer and Seller info</h3>
                <div class="tablist">                    
                    <ul class="nav nav-tabs">
                        <li class="nav-item"><a class="nav-link show active" data-toggle="tab" data-target="#add-book">@lang('admin_customer.user_details')</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" data-target="#buyerinfo">@lang('admin_customer.buyer_info')</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" data-target="#fav-shop">@lang('admin_customer.fav_shop')</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" data-target="#fav-item">@lang('admin_customer.fav_item')</a></li>
                        @if($user->user_type=='seller')
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" data-target="#seller-info">@lang('admin_customer.seller_info')</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" data-target="#customer">@lang('admin_customer.customer')</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" data-target="#product-to-shop">@lang('admin_customer.assign_product_to_shop')</a></li>
                        @endif
                    </ul>
                </div>
            </div>
            <div class="content-right">
                <div class="tab-content">
                    <div id="add-book" class="tab-pane fade show active">
                        {{ csrf_field() }}
                        {{ method_field('PUT') }}
                        <div class="">
                        <div class="form-group row">
                            <div class="col-md-5">
                            <label class="clable" for="form-text-input"><strong>@lang('admin_common.name') </strong></label> 
                                {{ $user->salutation.' '.$user->first_name.' '.$user->last_name }}
                            </div>
                        </div> 

                        <div class="form-group row">
                            <div class="col-md-5">
                            <label class="clable" for="form-text-input"><strong>@lang('admin_common.email') </strong></label> 
                                {{ $user->email }}
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-5">
                            <label class="clable" for="form-text-input"><strong>@lang('admin_common.date_of_birth') </strong></label> 
                                {{ $user->dob }}
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-5">
                            <label class="clable" for="form-text-input"><strong>@lang('admin_common.phone_number') </strong></label> 
                                {{ $user->ph_number  }}
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-5">
                            <label class="clable" for="form-text-input"><strong>@lang('admin_common.gender') </strong></label> 
                               
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-5">
                            <label class="clable" for="form-text-input"><strong>@lang('admin_common.country') </strong></label> 
                                {{ isset($user->countryName) ? $user->countryName->country_name : '' }}
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-5">
                                <lable class="clable" for="status">@lang('admin_common.status')</label>
                                    
                                @if($user->status=='1') {{'Active'}} @else {{"Inactive"}} @endif
                                
                            </div>

                        </div>
                        </div>
                    </div>
                    <div id="buyerinfo" class="tab-pane fade">
                        <form id="customerForm" action="{{action('Admin\Customer\UserController@saveBuyer')}}" method="post" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            <div class="">
                                
                                <div class="form-group">
                                    <div class="select-profile text-left">
                                        <input type="hidden" name="image" class="file-upload" id="userThumbImage">
                                        <div class="cropper-main" id="user-thumb">   
                                            <div class="avatar-view single-file-upload profile-img" title="Change the avatar" data-section="user_thumb">
                                                <img src="{{getUserImageUrl($user->image,'customer')}}" alt="" id="user_thumb">
                                                <span class="btn-grey btn-primary mt-2">+ @lang('admin.choose_profile_image')</span>
                                            </div>                                
                                            @include('includes.common_cropper_upload',['cropper_setting'=>[$cropper_setting[0]]]) 
                                            
                                        </div>                          
                                    </div> 
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-5">
                                        <label class="clable" for="form-text-input">@lang('admin_common.name')<i class="red">*</i></label> 
                                        <input type="text" name="first_name" value="{{ $user->first_name }}">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-5">
                                    <label class="clable" for="form-text-input">@lang('admin_common.last_name')<i class="red">*</i></label> 
                                        <input type="text" name="last_name" value="{{ $user->last_name }}">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-5">
                                    <label class="clable" for="form-text-input">@lang('admin_common.email')<i class="red">*</i></label> 
                                        <input type="text" name="email" value="{{ $user->email  }}" required="return">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-5">
                                    <label class="clable" for="form-text-input">@lang('admin_common.phone_number')<i class="red">*</i></label> 
                                        <input type="text" name="ph_number" value="{{ $user->ph_number  }}" required="return">
                                        @if($errors->has('ph_number'))
                                            <p class="red">{{ $errors->first('ph_number') }}</p>
                                        @endif 
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-5">
                                    <label class="clable" for="form-text-input">@lang('admin_common.date_of_birth') </label>
                                    <input type="text" name="dob" class="date-select-new date-select" value="{{ $user->dob }}">
                                    </div>
                                </div>

                                <div class="form-group">                                
                                    <label class="check-wrap" style="display: inline-block;">
                                        <input type="checkbox" id="change_password" name="change_password" value="1">
                                        <span class="chk-label">@lang('customer.change_password')</span>
                                    </label>                                
                                </div>

                                <div class="row form-change-password" id="password_div" style="display: none;">
                                    <div class="col-sm-5">                                                 
                                        <div class="form-group">
                                            <label>@lang('common.new_password')<span class="red">*</span></label>
                                            <input type="Password" name="password" placeholder="**********">
                                            <p class="error" id="error_password"></p>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label>@lang('common.confirm_password')<span class="red">*</span></label>
                                            <input type="Password" name="confirm_password" placeholder="**********">
                                            <p class="error" id="error_confirm_password"></p>
                                        </div>
                                    </div>
                                </div>
                       
                            </div>
                            
                            <input type="hidden"  name ="id" value="{{$user->id }}">
                            <button type="submit"  class="btn btn-save btn-primary">@lang('admin_customer.save')</button>
                        </form>
                    </div>
                    <div id="fav-shop" class="tab-pane fade">
                        <table class="table table-bordered " id="table">
                            <thead>
                                <tr class="filters">
                                    <th class="text-center">@lang('shop.favorit_shop_store')</th>
                                    <th class="text-center">@lang('shop.favorit_shop_market')</th>
                                    <th class="text-center">@lang('shop.favorit_shop_product_type')</th>
                                    <th class="text-center">@lang('shop.favorit_shop_rating')</th>
                                    <th class="text-center">@lang('shop.favorit_shop_update_price')</th>
                                    <!-- <th class="text-center">&nbsp;</th> -->
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($favoriteShopList)>0)
                                    @foreach($favoriteShopList as $key => $shop)
                                        <tr>
                                            <td>
                                                <div class="product-wrap">
                                                    <div class="prod-img">
                                                        <img src="{{getImgUrl($shop['logo'],'logo')}}" width="50" height="50">                         
                                                    </div>
                                                    <div class="product-info">
                                                        <div class="shop-name">
                                                            <a href="{{$shop['shop_url']}}">{{$shop['shop_name']}}</a>
                                                        </div>                                                     
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="marketname text-center">{{ $shop['market']}}</td>
                                            <td class="product-name text-center">{{ $shop['shop_category']}}</td>
                                            <td class="chat-wrap">
                                                <div class="review-star">
                                                    <div class="grey-stars"></div>
                                                    <div class="filled-stars" style="width: {{$shop['avg_rating']*20}}%"></div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="last-updatebox">                             
                                                   <?php echo $shop['last_updated_price']; ?>
                                                </div>
                                            </td>
                                            <!-- <td>
                                                <a href="javascript://" class="delete-favorite-shop" data-del_url="{{$shop['del_f_shop_url']}}"><i class="fas fa-times">&nbsp;</i></a>
                                            </td> -->
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div id="fav-item" class="tab-pane fade">
                        <table class="table table-bordered " id="table">
                            <thead>
                                <tr class="filters">
                                    <th class="text-center">@lang('product.product')</th>
                                    <th class="text-center">@lang('product.product_standard')</th>
                                    <th class="text-center">@lang('product.price')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($fav_products)>0)
                                    @foreach($fav_products as $key => $f_prod)
                                        <tr>
                                            <td>
                                                <div class="prod-img"><a href="{{$f_prod['url']}}"><img src="{{$f_prod['thumbnail_image']}}"></a></div>
                                                <h3 class="product-name">{{$f_prod['category']['category_name']}}</h3>
                                                <div class="review-star">
                                                  <div class="grey-stars"></div>
                                                  <div class="filled-stars" style="width: {{$f_prod['avg_star']*20}}%"></div>
                                               </div>
                                               <div class="shop-name">
                                                    <a href="{{$f_prod['shop']['shop_url']}}">{{$f_prod['shop']['shop_name']}}</a>
                                               </div>
                                            </td>
                                            <td>
                                                <div class="prod-standard">
                                                    
                                                     <span class="la">
                                                        <img src="{{Config::get('constants.standard_badge_url')}}{{$f_prod['badge']['icon']}}" />
                                                    </span>
                                                    
                                               </div>
                                            </td>
                                            <td>{{$f_prod['unit_price']}} @lang('common.baht') / {{$f_prod['package_name']}}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>  
                    @if($user->user_type=='seller')
                        <div id="seller-info" class="tab-pane fade">
                            @if($shop_details)
                            <form method="POST" action="{{action('Admin\Customer\UserController@updateShopInfo')}}" enctype="multipart/form-data" name="update_shop_form" id="update_shop_form">
                                {{ csrf_field() }}
                                <input type="hidden" name="shop_id" value="{{ $shop_details->id }}">
                                <input type="hidden" name="banner_image" id="bannerImage">
                                <input type="hidden" name="logo_image" id="logoImage">
                                <div class="shop-banner-header">
                                    <div class="shop-ban-img">
                                        <img class="" src="{{getImgUrl($shop_details->banner,'banner')}}" alt="" id="banner_image_thumb">
                                    </div>
                                   
                                    <div class="shop-banner-content">
                                        <div class="shop-img" style="position: relative;">
                                            <img src="{{getImgUrl($shop_details->logo,'logo')}}" alt="" id="logo_image_thumb">
                                            <div class="cropper-main" id="logo-image">
                                                <div class="avatar-view single-file-upload" title="Change the avatar" data-section="logo_image_thumb">
                                                    <div class="logoupld">
                                                        <span class="shop-cam"><i class="fas fa-camera"></i></span>                     
                                                    </div>
                                                </div>
                                                @include('includes.common_cropper_upload',['cropper_setting'=>[$cropper_setting[2]]])  -
                                            </div>
                                        </div>
                                        <div class="cropper-main" id="banner-image">   
                                            <div class="avatar-view single-file-upload" title="Change the avatar" data-section="banner_image_thumb">
                                                <div class="shop-cam-vacation">
                                                    <span class="shop-cam"><i class="fas fa-camera"></i></span>                     
                                                </div>
                                            </div>                                
                                             @include('includes.common_cropper_upload',['cropper_setting'=>[$cropper_setting[1]]])
                                        </div>                
                                    </div>

                                </div>
                                <div class="shop-content">
                                    <div class="row">
                                        <div class="col-sm-12">
                                             <div class="shop-content-header manage-store-header">
                                                <div class="respons-shop-list">
                                                    <ul class="respon-update">
                                                        <!-- <li>
                                                            <span class="shop-label">Chat Respon</span>
                                                            <span class="res-num">80%</span>
                                                        </li> -->
                                                        <li class="w-100">
                                                            <span class="shop-label">Last Update</span>
                                                            <span class="res-num">{{getDateFormat($shop_details->updated_at,5)}}</span>
                                                        </li>
                                                        <!-- <li>
                                                            <span class="shop-label">Response speed</span>
                                                            <span class="res-num">With in 15 Min</span>
                                                        </li>
                                                        <li>
                                                            <span class="shop-label">Order cancellation</span>
                                                            <span class="res-num">10%</span>
                                                        </li> -->
                                                    </ul>
                                                </div>                                                      
                                            </div>
                                        </div>
                                    </div>
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
                                        </aside>

                                        <div class="col-md-10">                  

                                            <div class="shop-content box-grey">
                                                <div class="row">
                                                    <div class="col-sm-8">
                                                        
                                                        <div class="form-group">
                                                            <label>@lang('shop.market')</label>
                                                            <input type="text" name="seller_description" value="{{$shop_details->seller_description}}">
                                                            <p class="error"></p>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>@lang('shop.panel')</label>
                                                            <input type="text" name="panel_no" value="{{$shop_details->panel_no}}">
                                                            <p class="error"></p>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>@lang('shop.store_url')</label>
                                                            <input type="text" name="shop_url" value="{{ $shop_details->shop_url }}">
                                                            <p class="error" id="e_store_url"></p>
                                                        </div>

                                                        <div class="form-group">
                                                            <label>@lang('shop.vendor_code')</label>
                                                            <input type="text" name="seller_unique_id" value="{{$shop_details->seller_unique_id}}">
                                                            <p class="error"></p>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label>@lang('admin_customer.seller_description')</label>
                                                            <textarea name="seller_description" placeholder="">{{ $shop_details->seller_description }}</textarea>
                                                            <p id="" class="error"></p>
                                                        </div>

                                                        <div class="form-group">
                                                            {!! CustomHelpers::fieldstabWithLanuageEdit([['field'=>'text', 'name'=>'shop_name', 'label'=>Lang::get('shop.shop_name'), 'errorkey'=>'shop_name'],['field'=>'textarea', 'name'=>'description', 'label'=>Lang::get('shop.shop_description'), 'errorkey'=>'description','editor_required'=>'N']], '1','shop_id',$shop_details->id,$tblShopDesc, $errors) !!}
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Products that can be sold</label> 
                                                            @if(count($category_data)>0)
                                                                <ul class="sold-product">
                                                                    @foreach($category_data as $key => $val)
                                                                    <li>
                                                                        <div class="prod-img">
                                                                            <img src="{{ getCategoryImageUrl($val->img) }}" width="50" height="50" alt="">
                                                                        </div>
                                                                        <div class="prod-name">{{ $val->category_name }}</div>
                                                                    </li>
                                                                    @endforeach
                                                                </ul>
                                                            @endif
                                                        </div>

                                                        <div class="form-group">

                                                            <div class="shop-location-row">
                                                                    @lang('shop.shops_image')
                                                                    <div class="mt-2 mb-2"> 
                                                                        <div class="file-wrapper mb-2" style="display: inline-flex; vertical-align: top;">
                                                                            <div class="custom-attach-file">
                                                                                <span id="shop_img_span"><input type="file" class="shop_image" name="shop_image[]" accept="image/*" multiple="multiple"></span>                        
                                                                                <button class="btn-blue custom-img-button btn-primary">@lang('common.attach')</button>
                                                                            </div>
                                                                        </div>

                                                                        <ul class="map-upload-img mt-0" id="shop-upload-img" style="display: inline-flex;">
                                                                            @if(count($shop_images))    
                                                                                @foreach($shop_images as $val)
                                                                                <li>
                                                                                <a href="{{ getImgUrl($val,'map') }}" data-fancybox="images" class="shopfancy">
                                                                                    <img src="{{ getImgUrl($val,'map') }}" width="50" height="50"><br>
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


                                                        <div class="shop-location-row">
                                                            @lang('shop.map')
                                                            <div class="mt-2 mb-2"> 
                                                                <div class="file-wrapper" style="overflow: visible;">
                                                                    <div class="custom-attach-file">
                                                                        <span id="map_img"><input type="file" class="location_image" name="location_image[]" accept="image/*" multiple="multiple"></span>                        
                                                                        <button class="btn-blue custom-img-button btn-primary">@lang('common.attach')</button>
                                                                    </div>
                                                                    
                                                                </div>

                                                            </div>

                                                            <ul class="map-upload-img" id="map-upload-img">  
                                                                @if(count($map_images))    
                                                                    @foreach($map_images as $val)
                                                                        <li>
                                                                            <a href="{{ getImgUrl($val,'map') }}" data-fancybox="images" class="mapfancy">
                                                                                <img src="{{ getImgUrl($val,'map') }}" width="50" height="50"><br>
                                                                            </a>
                                                                                <a href="javascript:;" data-val="{{ $val }}" data-type="map" class="deleteShopImg" >
                                                                                    <i class="fas fa-times"></i>
                                                                                </a>
                                                                        </li>
                                                                    @endforeach
                                                                @endif                            
                                                            </ul>
                                                        </div>
                                                        <div class="form-group row">
                                                            <div class="col-sm-6">
                                                                <label>@lang('shop.shop_opening_time')</label>
                                                                <input type="text" name="open_time" placeholder="11:00" value="{{$shop_details->open_time}}" class="time-clock" id="custom_time_from">
                                                                <p class="error" id="e_open_time"></p>                                     
                                                            </div>

                                                            <div class="col-sm-6">
                                                                <label>@lang('shop.shop_closing_time')</label>
                                                                <input type="text" name="close_time" placeholder="17:00" value="{{$shop_details->close_time}}" class="time-clock" id="custom_time_to">
                                                                <p class="error" id="e_close_time"></p>                                     
                                                            </div>
                                                        </div>

                                                        <div class="form-group row" style="display: none;">
                                                            <div class="col-sm-6">
                                                                <label>@lang('shop.the_time_to_pick_up_product') </label>
                                                                <select name="product_pickup_time">
                                                                    <option value="1">Within 1 hours</option>
                                                                    <option value="2">Within 2 hours</option>
                                                                </select>
                                                                <p class="error" id="e_product_pickup_time"></p>
                                                            </div>
                                                        </div>

                                                        <div class="form-group row" style="display: none;">
                                                            <div class="col-sm-6">
                                                                <label>@lang('shop.the_time_to_pick_up_center') </label>
                                                                <select name="center_pickup_time">
                                                                    <option value="1">Within 1 hours</option>
                                                                    <option value="2">Within 2 hours</option>
                                                                </select>
                                                                <p class="error" id="e_center_pickup_time"></p>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <div class="col-sm-6">
                                                                <label>@lang('common.phone_number')</label>
                                                                <input type="text" name="ph_number" value="{{$shop_details->ph_number}}" onkeypress="return isNumericKey(event);" class="" id="">
                                                                <p class="error" id="e_ph_number"></p>                                     
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <div class="col-sm-6">
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
                                                                <button class="btn btn-primary" type="button" id="btn_shop_info">@lang('admin_common.save')</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                
                                            </div>
                                        
                                            
                                        </div>
                                    </div>
                                </div>
                            </form>
                            @endif

                        </div>               

                        <div id="customer" class="tab-pane fade">
                           
                        </div>
                        <div id="product-to-shop" class="tab-pane fade">
                            <form method="post" name="assign_cat" id="assign_cat" action="{{ action('Admin\Customer\UserController@assignCategorySeller') }}">
                                {{ csrf_field() }}
                                @if($shop_details)
                                    <input type="hidden" name="shop_id" value="{{ $shop_details->id }}">
                                @else
                                    <input type="hidden" name="shop_id" value="">
                                @endif
                                <div class="col-sm-6" id="prd_cat_con">
                                    @if(count($prd_categories) > 0) 
                                        <ul class="tree tree-menu">
                                        @foreach($prd_categories as $key=>$mainCategory)
                                            <li>
                                                <a href="javascript:void(0);">
                                                @if(count($mainCategory->category) > 0)
                                                    <i class="menuIcon glyphicon glyphicon-plus"></i>
                                                @endif
                                                {{-- <input type="checkbox" class="cat-checkbox" name="prd_cat_id[]" value="{{$mainCategory->id}}" @if(in_array($mainCategory->id, $categoryId)) checked="checked" @endif> --}}
                                                <i><img src="assets/images/folder.svg" alt=""></i> {{$mainCategory->categorydesc->category_name}} m</a>
                                                @if(count($mainCategory->category) > 0) 
                                                    <ul>
                                                    @foreach($mainCategory->category as $subcategory)
                                                        <li>
                                                            <a href="javascript:void(0);">
                                                            @if(count($subcategory->category) > 0)
                                                                <i class="menuIcon glyphicon glyphicon-plus"></i>
                                                            @endif
                                                            <input type="checkbox" class="cat-checkbox" name="prd_cat_id[]" value="{{$subcategory->id}}" @if(in_array($subcategory->id, $categoryId)) checked="checked" @endif>
                                                            <i><img src="assets/images/folder.svg" alt=""></i> {{$subcategory->categorydesc->category_name ?? ''}}</a> 
                                                            
                                                        </li>
                                                    @endforeach
                                                    </ul>
                                                @endif
                                            </li>
                                        @endforeach
                                        </ul>
                                        
                                        <p class="error" id="e_prd_cat_id"></p>
                                        
                                    @endif                                         
                                </div>
                                <div class="row manage-store-btn">
                                    <div class="col-sm-12">
                                        <div class="button-group mt-3">                                         
                                                <button class="btn btn-primary" type="button" id="assign_cat_seller">@lang('admin_common.save')</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif
                    <!--Customer Order Tab ends Here-->
                </div>
            </div>
        </div>
    </div>      
</div>

@stop
@section('footer_scripts')
<!-- begining of page level js -->

<script src="{{ Config('constants.js_url') }}jquery-cropper.min.js" type="text/javascript"></script>
<script type="text/javascript">
    var loader_url = "{{ Config::get('constants.loader_url') }}loader_small.gif";
    var url_deleteshopimage = "{{ action('Admin\Customer\UserController@deleteShopImg') }}";
    var txt_delete_confirm = "@lang('common.are_you_sure_to_delete_this_record')";
    var yes_delete_it = "@lang('common.yes_delete_it')";
    var txt_no = "@lang('common.no')";
</script>    
<!-- for file upload ended -->    
<script src="{{ Config('constants.admin_js_url') }}flatpickr.min.js"></script>
<script src="{{ Config('constants.js_url') }}common_cropper_upload_setting.js"></script>
<script src="{{ Config('constants.js_url') }}jquery.fancybox.min.js" type="text/javascript"></script>
<script src="{{ Config('constants.admin_js_url') }}user/user_edit.js"></script>
<script type="text/javascript">        
    

    let siteurll = "{{Config::get('constants.public_url')}}";

    var csrftoken = window.Laravel.csrfToken;
    
</script>

<!-- end of page level js --> 
@stop
