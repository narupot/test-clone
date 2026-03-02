@extends('layouts/admin/default')

@section('title')
    @lang('admin_customer.user_details')
@stop

@section('header_styles')
    <link rel="stylesheet" type="text/css" href="{{Config('constants.css_url') }}cropper.min.css">
    <link rel="stylesheet" type="text/css" href="{{Config('constants.css_url') }}flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="{{Config('constants.css_url')}}jquery.fancybox.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <?php
$cropper_setting = [
    [
        'section' => 'user_thumb', 'dimension' => '{width : 115, height : 115}', 'file_field_selector' => '#userThumbImage', 'section_id' => 'user-thumb', 'image_type' => 'jpg,png'
    ],
    [
        'section' => 'banner_image_thumb',
        'dimension' => ['width' => 1110, 'height' => 145],
        'file_field_selector' => '#bannerImage',
        'section_id' => 'banner-image',
    ],
    [
        'section' => 'logo_image_thumb',
        'dimension' => ['width' => 153, 'height' => 153],
        'file_field_selector' => '#logoImage',
        'section_id' => 'logo-image',
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
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">×</button>
                <strong>@lang('admin_common.success'):</strong> {{ Session::get('succMsg') }}
            </div>
            @endif
            <h1 class="title">{{ucwords($user->first_name)}} {{ucwords($user->last_name)}} ({{ $shop_details->shopDesc->shop_name ?? '-' }})</h1>
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
                <h3 class="mb-3">ข้อมูลผู้ซื้อและผู้ขาย</h3>
                <div class="tablist">
                    <ul class="nav nav-tabs">
                        <li class="nav-item"><a class="nav-link show active" data-toggle="tab" data-target="#add-book">@lang('admin_customer.user_details')</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" data-target="#buyerinfo">@lang('admin_customer.buyer_info')</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" data-target="#fav-shop">@lang('admin_customer.fav_shop')</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" data-target="#fav-item">@lang('admin_customer.fav_item')</a></li>
                        @if($user->user_type=='seller')
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" data-target="#seller-info">@lang('admin_customer.seller_info')</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" data-target="#customer">@lang('admin_customer.customer')</a></li>
                            <!-- <li class="nav-item"><a class="nav-link" data-toggle="tab" data-target="#product-to-shop">@lang('admin_customer.assign_product_to_shop')</a></li> -->
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" data-target="#product-to-shop-new">@lang('admin_customer.assign_product_to_shop')</a></li>
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
                                            <label for="password">@lang('common.new_password')<span class="red">*</span></label>
                                            <input type="Password" name="password" placeholder="**********">
                                            <p class="error" id="error_password"></p>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="confirm_password">@lang('common.confirm_password')<span class="red">*</span></label>
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
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($favoriteShopList)>0)
                                    @foreach($favoriteShopList as $key => $shop)
                                        <tr>
                                            <td>
                                                <div class="product-wrap">
                                                    <div class="prod-img">
                                                        <img src="{{getImgUrl($shop['logo'],'logo')}}" width="50" height="50" alt="">
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
                                                <div class="prod-img"><a href="{{$f_prod['url']}}"><img src="{{$f_prod['thumbnail_image']}}" alt=""></a></div>
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
                                                        <img src="{{Config::get('constants.standard_badge_url')}}{{$f_prod['badge']['icon']}}" alt=""/>
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
                                                        <li class="w-100">
                                                            <span class="shop-label">อัปเดตล่าสุด</span>
                                                            <span class="res-num">{{getDateFormat($shop_details->updated_at,10)}}</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <aside class="col-md-2 left-sidebar">
                                            <div class="form-group">
                                                {{-- เพิ่ม code block shop โดย set ระงับการขายถึงวันที่ --}}
                                                <label for="block_until">ระงับการขายถึงวันที่</label>
                                                <input type="text" name="block_until" 
       value="{{ in_array($shop_details->block_until, ['0000-00-00', '0000-00-00 00:00:00', null]) ? '' : \Carbon\Carbon::parse($shop_details->block_until)->format('Y-m-d') }}" 
       class="date-select-new date-select" id="block_until" style="max-width: 70%">
                                                <p class="error" id="e_block_until"></p>
                                                {{-- เพิ่ม code block shop โดย set ระงับการขายถึงวันที่ --}}
                                                <label for="shop_status">@lang('shop.shop_status')</label>
                                                <label class="button-switch">
                                                   <input type="checkbox" value="1" name="shop_status" @if($shop_details->shop_status=='open') checked @endif class="switch switch-orange">
                                                     <span for="autoRelated" class="lbl-off">@lang('shop.closed')</span>
                                                     <span for="autoRelated" class="lbl-on">@lang('shop.open')</span>
                                               </label>
                                            </div>

                                            <div class="form-group">
                                                <label for="bargaining">@lang('shop.bargain')</label>
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
                                                            <label for="market">@lang('shop.market')</label>
                                                            <input type="text" name="market" value="{{$shop_details->market}}">
                                                            <p class="error"></p>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="panel_no">@lang('shop.panel')</label>
                                                            <input type="text" name="panel_no" value="{{$shop_details->panel_no}}">
                                                            <p class="error"></p>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="shop_url">@lang('shop.store_url')</label>
                                                            <input type="text" name="shop_url" value="{{ $shop_details->shop_url }}">
                                                            <p class="error" id="e_store_url"></p>
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="seller_unique_id">@lang('shop.vendor_code')</label>
                                                            <input type="text" name="seller_unique_id" value="{{$shop_details->seller_unique_id}}">
                                                            <p class="error"></p>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label for="seller_description">@lang('admin_customer.seller_description')</label>
                                                            <textarea name="seller_description" placeholder="">{{ $shop_details->seller_description }}</textarea>
                                                            <p id="" class="error"></p>
                                                        </div>

                                                        <div class="form-group">
                                                            {!! CustomHelpers::fieldstabWithLanuageEdit([['field'=>'text', 'name'=>'shop_name', 'label'=>Lang::get('shop.shop_name'), 'errorkey'=>'shop_name'],['field'=>'textarea', 'name'=>'description', 'label'=>Lang::get('shop.shop_description'), 'errorkey'=>'description','editor_required'=>'N']], '1','shop_id',$shop_details->id,$tblShopDesc, $errors) !!}
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="products_that_can_be_sold">สินค้าที่สามารถจำหน่ายได้</label>
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
                                                                                <button class="btn- btn custom-img-button btn-primary">@lang('common.attach')</button>
                                                                            </div>
                                                                        </div>

                                                                        <ul class="map-upload-img mt-0" id="shop-upload-img" style="display: inline-flex;">
                                                                            @if(count($shop_images))
                                                                                @foreach($shop_images as $val)
                                                                                <li>
                                                                                <a href="{{ getImgUrl($val,'map') }}" data-fancybox="images" class="shopfancy">
                                                                                    <img src="{{ getImgUrl($val,'map') }}" width="50" height="50" alt=""><br>
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
                                                                        <button class="btn- btn custom-img-button btn-primary">@lang('common.attach')</button>
                                                                    </div>
                                                                    
                                                                </div>

                                                            </div>

                                                            <ul class="map-upload-img" id="map-upload-img">
                                                                @if(count($map_images))
                                                                    @foreach($map_images as $val)
                                                                        <li>
                                                                            <a href="{{ getImgUrl($val,'map') }}" data-fancybox="images" class="mapfancy">
                                                                                <img src="{{ getImgUrl($val,'map') }}" width="50" height="50" alt=""><br>
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
                                                                <label for="open_time">@lang('shop.shop_opening_time')</label>
                                                                <input type="text" name="open_time" placeholder="11:00" value="{{$shop_details->open_time}}" class="time-clock" id="custom_time_from">
                                                                <p class="error" id="e_open_time"></p>
                                                            </div>

                                                            <div class="col-sm-6">
                                                                <label for="close_time">@lang('shop.shop_closing_time')</label>
                                                                <input type="text" name="close_time" placeholder="17:00" value="{{$shop_details->close_time}}" class="time-clock" id="custom_time_to">
                                                                <p class="error" id="e_close_time"></p>
                                                            </div>
                                                        </div>

                                                        <div class="form-group row" style="display: none;">
                                                            <div class="col-sm-6">
                                                                <label for="product_pickup_time">@lang('shop.the_time_to_pick_up_product') </label>
                                                                <select name="product_pickup_time">
                                                                    <option value="1">Within 1 hours</option>
                                                                    <option value="2">Within 2 hours</option>
                                                                </select>
                                                                <p class="error" id="e_product_pickup_time"></p>
                                                            </div>
                                                        </div>

                                                        <div class="form-group row" style="display: none;">
                                                            <div class="col-sm-6">
                                                                <label for="center_pickup_time">@lang('shop.the_time_to_pick_up_center') </label>
                                                                <select name="center_pickup_time">
                                                                    <option value="1">Within 1 hours</option>
                                                                    <option value="2">Within 2 hours</option>
                                                                </select>
                                                                <p class="error" id="e_center_pickup_time"></p>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <div class="col-sm-6">
                                                                <label for="ph_number">@lang('common.phone_number')</label>
                                                                <input type="text" name="ph_number" value="{{$shop_details->ph_number}}" onkeypress="return isNumericKey(event);" class="" id="ph_number">
                                                                <p class="error" id="e_ph_number"></p>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <div class="col-sm-6">
                                                                <label for="line_link">@lang('shop.line_link')</label>
                                                                <input type="text" name="line_link" value="{{$shop_details->line_link}}" class="" id="line_link">
                                                                <p class="error" id="e_line_link"></p>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="form-group row">
                                                            <div class="col-sm-6">
                                                                <label for="commission_rate">@lang('shop.commission_rate')</label>
                                                                <input type="text" name="commission_rate" value="{{$shop_details->commission_rate  ?? ''}}" class="" id="commission_rate">
                                                                <p class="error" id="e_commission_rate"></p>
                                                            </div>

                                                            <div class="col-sm-6">
                                                                <label for="comm_effective_date">@lang('shop.comm_effective_date')</label>
                                                                <input type="text" name="comm_effective_date" value="{{$shop_details->comm_effective_date ?? date('Y-m-d')}}" class="date-select-new date-select" id="comm_effective_date">
                                                                <p class="error" id="e_comm_effective_date"></p>
                                                            </div>
                                                        </div>

                                                        <div class="form-group row">
                                                            <div class="col-sm-6">
                                                                <label>@lang('shop.bank_name') <i class="red">*</i></label>                                                       
                                                                @php
                                                                    $bank_data = \App\BankInfo::dropdown() ?? [];
                                                                    $selectedBank = $shop_details->bank_code ?? '';
                                                                @endphp                                                         
                                                                <select name="bank_code" class="form-control" required>
                                                                    <option value="">-- เลือกธนาคาร --</option>
                                                                    @foreach($bank_data as $bank_code => $bank_name)
                                                                        <option value="{{ $bank_code }}" {{ $selectedBank == $bank_code ? 'selected' : '' }}>{{ $bank_name }}</option>
                                                                    @endforeach
                                                                </select>                                                                
                                                                <p class="error" id="e_bank_code"></p>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <label for="bank_branch_code">@lang('shop.bank_branch_code')</label>
                                                                <input type="text" name="bank_branch_code" value="{{$shop_details->bank_branch_code ?? ''}}" class="" id="bank_branch_code" inputmode="numeric" pattern="[0-9]*" placeholder="กรอกเฉพาะตัวเลขเท่านั้น" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                                                <p class="error" id="e_bank_branch_code"></p>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <div class="col-sm-6">
                                                                <label for="bank_account_code">@lang('shop.account_no') <i class="red">*</i></label>
                                                                <input type="text" name="bank_account_code" value="{{$shop_details->bank_account_code ?? ''}}" class="" id="bank_account_code" inputmode="numeric" pattern="[0-9]*" placeholder="กรอกเฉพาะตัวเลขเท่านั้น" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                                                                <p class="error" id="e_bank_account_code"></p>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <label for="bank_account_name">@lang('shop.account_name') <i class="red">*</i></label>
                                                                <input type="text" name="bank_account_name" value="{{$shop_details->bank_account_name ?? ''}}" class="" id="bank_account_name" required>
                                                                <p class="error" id="e_bank_account_name"></p>
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
                        <div id="product-to-shop-new" class="tab-pane fade">
                            <form method="post" id="assign_cat" action="{{ action('Admin\Customer\UserController@assignCategorySeller') }}">
                                {{ csrf_field() }}
                                <input type="hidden" name="shop_id" value="{{ $shop_details->id ?? '' }}">
                              
                                {{-- 🔹 Filter Section --}}
                                <div class="filter-section mb-4">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>กลุ่มสินค้า</label>
                                            <select id="group_id" class="form-control">
                                                <option value="">-- เลือกกลุ่มสินค้า --</option>
                                                @foreach($productGroups as $group)
                                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label>หมวดสินค้า</label>
                                            <select id="subgroup_id" class="form-control">
                                                <option value="">-- เลือกหมวดสินค้า --</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label>ประเภทสินค้า</label>
                                            <select id="catalog_id" class="form-control">
                                                <option value="">-- เลือกประเภทสินค้า --</option>
                                            </select>
                                        </div>
                                        

                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-4">
                                            <input type="text" id="search_text" class="form-control" placeholder="ค้นหาชนิดสินค้า...">
                                        </div>
                                        
                                    </div>
                                </div>

                                {{-- Tables แบ่งซ้ายขวา --}}
                                <div class="row">
                                    {{-- ซ้าย: ตารางผลลัพธ์การค้นหา --}}
                                    <div class="col-md-6">
                                        <h5>ผลลัพธ์การค้นหา</h5>
                                        <div class="table-responsive" style="max-height:400px; overflow:auto;">
                                            <table class="table table-bordered table-sm" id="search-result-table">
                                                <thead style="position: sticky; top: 0;">
                                                    <tr>
                                                        <th><input type="checkbox" id="check-all"></th>
                                                        <th>ID</th>
                                                        <th>ชนิดสินค้า</th>
                                                        <th>ประเภทสินค้า</th>
                                                        <th>หมวดสินค้า</th>
                                                        <th>กลุ่มสินค้า</th>                                                      
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {{-- Ajax load ผลลัพธ์สินค้า --}}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- ขวา: ตารางสินค้าที่เลือก --}}
                                    <div class="col-md-6">
                                        <h5>สินค้าที่เลือกแล้ว</h5>
                                        <div class="table-responsive" style="max-height:400px; overflow:auto;">
                                            <table class="table table-striped table-sm" id="selected-products-table">
                                                <thead style="position: sticky; top: 0;">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>ID</th>
                                                        <th>ชนิดสินค้า</th>
                                                        <th>ประเภทสินค้า</th>
                                                        <th>หมวดสินค้า</th>
                                                        <th>กลุ่มสินค้า</th>
                                                        <th>การจัดการ</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {{-- เติมจาก Javascript --}}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                {{-- ปุ่มบันทึก --}}
                                <div class="row mt-3">
                                    <div class="col-sm-12">
                                        <button type="submit" class="btn btn-success">บันทึก</button>
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
<script type="text/javascript">
    $(document).ready(function(){
        let searchTimer;
        
    // Add code by game Initialize datepicker for block_until
    flatpickr("#block_until", {
    dateFormat: "Y-m-d",
    minDate: "today",
    allowInput: false,
    defaultDate: document.querySelector("#block_until").value || null
    });
        // Add code by game Initialize datepicker for block_until
        
        $("form").on("keydown", function(e){
            if(e.key === "Enter"){
                e.preventDefault();
                return false;
            }
        });


        const showPreloader = (targetTableId) => {
            const preloaderHtml = `
                <tr class="preloader-row">
                    <td colspan="6" class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">กำลังโหลดข้อมูล...</p>
                    </td>
                </tr>
            `;
            $(`#${targetTableId} tbody`).html(preloaderHtml);
        };

    const loadSelectedProducts = () => {
        showPreloader('selected-products-table');

        let shopId = $("input[name='shop_id']").val(); // ดึงค่าจาก input

        $.get("{{ url('admin/get-assigned-products') }}", { shop_id: shopId }, function(res){
            let html = "";
            let count = 1;
            if(res.length === 0){
                html = `<tr><td colspan="7" class="text-center">ยังไม่มีข้อมูลที่เลือกไว้</td></tr>`;
            } else {
                res.forEach(function(p){

                    html += `
                        <tr id="selected-${p.id}">
                            <td>${count++}</td>
                            <td>${p.id}<input type="hidden" name="selected_products[]" value="${p.id}"></td>
                            <td>${p.category_name}</td>
                            <td>${p.product_catalog}</td>
                            <td>${p.product_subgroup}</td>
                            <td>${p.product_group}</td>
                            <td><button type="button" class="btn btn-danger btn-sm remove-product" data-id="${p.id}">ลบ</button></td>
                        </tr>
                    `;
                });
            }
            $("#selected-products-table tbody").html(html);
        }).fail(function(){
            console.error("Failed to load assigned products.");
            $("#selected-products-table tbody").html(`<tr><td colspan="6" class="text-center text-danger">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>`);
        });
    };

        const performSearch = () => {
    showPreloader('search-result-table');
    
    let params = {
        group_id: $("#group_id").val(),
        subgroup_id: $("#subgroup_id").val(),
        catalog_id: $("#catalog_id").val(),
        search_text: $("#search_text").val(),
        _token: "{{ csrf_token() }}"
    };
    
    $.post("{{ url('admin/search-products') }}", params, function(res){
        let html = "";
        if(res.length === 0){
            html = `<tr><td colspan="6" class="text-center">ไม่พบข้อมูลสินค้า</td></tr>`;
        } else {
            res.forEach(function(p){ 
                // เช็คว่ามีอยู่ใน selected แล้วหรือยัง
                const isAlreadySelected = $(`#selected-products-table #selected-${p.id}`).length > 0;

                if (isAlreadySelected) {
                    // ถ้ามีอยู่แล้ว ไม่ต้องแสดง checkbox
                    html += `<tr>
                                <td>—</td>
                                <td>${p.id}</td>
                                <td>${p.category_name}</td>
                                <td>${p.product_catalog}</td>
                                <td>${p.product_subgroup}</td>
                                <td>${p.product_group}</td>
                            </tr>`;
                } else {
                    html += `<tr>
                                <td>
                                    <input type="checkbox" class="select-product"
                                        data-id="${p.id}" 
                                        data-name="${p.category_name}" 
                                        data-product_catalog="${p.product_catalog}"
                                        data-product_subgroup="${p.product_subgroup}"
                                        data-product_group="${p.product_group}">
                                </td>
                                <td>${p.id}</td>
                                <td>${p.category_name}</td>
                                <td>${p.product_catalog}</td>
                                <td>${p.product_subgroup}</td>
                                <td>${p.product_group}</td>
                            </tr>`;
                }
            });
        }
        $("#search-result-table tbody").html(html);
    }).fail(function(xhr, status, error){
        console.error(error);
        $("#search-result-table tbody").html(`<tr><td colspan="6" class="text-center text-danger">เกิดข้อผิดพลาดในการค้นหาข้อมูล</td></tr>`);
    });
};
        
        const fetchSubgroups = (groupId) => {
            if (!groupId) {
                $("#subgroup_id").html('<option value="">-- เลือกทั้งหมด --</option>');
                $("#catalog_id").html('<option value="">-- เลือกทั้งหมด --</option>');
                performSearch();
                return;
            }

            $("#subgroup_id").html('<option value="">กำลังโหลด...</option>');
            $("#catalog_id").html('<option value="">-- เลือกทั้งหมด --</option>');
            performSearch(); 
            // console.log("groupId " + groupId);
            $.get(`/admin/category-management/get-subgroups/${groupId}`, function(subgroups){
                let options = '<option value="">-- เลือกทั้งหมด --</option>';
                subgroups.forEach(sg => {
                    options += `<option value="${sg.id}">${sg.subgroup_name}</option>`;
                });
                $("#subgroup_id").html(options);
            }).fail(function(){
                $("#subgroup_id").html('<option value="">-- เลือกทั้งหมด --</option>');
            });
        };

        const fetchCatalogs = (subgroupId) => {
            if (!subgroupId) {
                $("#catalog_id").html('<option value="">-- เลือกทั้งหมด --</option>');
                performSearch();
                return;
            }

            $("#catalog_id").html('<option value="">กำลังโหลด...</option>');
            performSearch();
            // console.log("subgroupId" + subgroupId);
            $.get(`/admin/category-management/get-catalogs/${subgroupId}`, function(catalogs){
                let options = '<option value="">-- เลือกทั้งหมด --</option>';
                catalogs.forEach(c => {
                    options += `<option value="${c.id}">${c.category_name}</option>`;
                });
                $("#catalog_id").html(options);
            }).fail(function(){
                $("#catalog_id").html('<option value="">-- เลือกทั้งหมด --</option>');
            });
        };

        $("#group_id").change(function(){
            fetchSubgroups($(this).val());
        });

        $("#subgroup_id").change(function(){
            fetchCatalogs($(this).val());
        });
        
        $("#catalog_id").change(function(){
            performSearch();
        });

        $("#btn-search").click(performSearch);
        $("#search_text").keyup(function(e){
            clearTimeout(searchTimer);
            if(e.key === "Enter"){
                e.preventDefault();
                return false;
            }
                    
            searchTimer = setTimeout(() => {
                performSearch();
            }, 500);

            if(e.which === 13){ 
                clearTimeout(searchTimer); 
                performSearch();
            }
        });

        $(document).on("change", ".select-product", function(){
            let id = $(this).data("id");
            let name = $(this).data("name");
            let p_catalog = $(this).data("product_catalog");
            let p_group = $(this).data("product_group");
            let p_subgroup = $(this).data("product_subgroup");
            
            if($(this).is(":checked")){
                if($("#selected-" + id).length === 0){ 
                    $("#selected-products-table tbody").append(`
                        <tr id="selected-${id}">
                            <td>${id}<input type="hidden" name="selected_products[]" value="${id}"></td>
                            <td>${name}</td>
                            <td>${p_catalog}</td>
                            <td>${p_subgroup}</td>
                            <td>${p_group}</td>
                            <td><button type="button" class="btn btn-danger btn-sm remove-product" data-id="${id}">ลบ</button></td>
                        </tr>
                    `);
                }
            } else {
                $("#selected-" + id).remove();
            }
        });

        $(document).on("click", ".remove-product", function(){
            let id = $(this).data("id");
            $("#selected-" + id).remove();
            $(".select-product[data-id='" + id + "']").prop("checked", false);
        });

        $("#check-all").change(function(){
            $(".select-product").prop("checked", $(this).is(":checked")).trigger("change");
        });

        // performSearch();
        loadSelectedProducts();
        
    });


$(document).on("submit", "#assign_cat", function(e){
    e.preventDefault(); 

    let form = $(this);
    let shopId = form.find("input[name='shop_id']").val();
    let selectedProducts = [];

    form.find("input[name='selected_products[]']").each(function(){
        selectedProducts.push($(this).val());
    });

    console.log("shop_id:", shopId);
    console.log("selected_products count:", selectedProducts.length);
console.log("selected_products :", selectedProducts);
    if(selectedProducts.length === 0){
        Swal.fire({
            icon: "warning",
            title: "กรุณาเลือกสินค้าอย่างน้อย 1 รายการ",
        });
        return;
    }

    Swal.fire({
        title: "ยืนยันการบันทึก?",
        text: "คุณต้องการบันทึกสินค้าที่เลือกหรือไม่",
        showCancelButton: true,
        confirmButtonText: "บันทึก",
        cancelButtonText: "ยกเลิก"
    }).then((result) => {
        if(result.isConfirmed){
            $.ajax({
                url: form.attr("action"),
                type: "POST",
                data: {
                    _token: form.find("input[name='_token']").val(),
                    shop_id: shopId,
                    selected_products: selectedProducts
                },
                beforeSend: function(){
                    Swal.fire({
                        title: "กำลังบันทึก...",
                        text: "กรุณารอสักครู่",
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(res){
                    Swal.fire({
                        icon: "success",
                        title: "บันทึกสำเร็จ",
                        text: "ข้อมูลถูกบันทึกเรียบร้อยแล้ว",
                    }).then(() => location.reload());
                },
                error: function(xhr){
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: "error",
                        title: "เกิดข้อผิดพลาด",
                        text: "ไม่สามารถบันทึกข้อมูลได้",
                    });
                }
            });
        }
    });
});



</script>

<!-- end of page level js -->
@stop
