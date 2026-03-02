@extends('layouts/admin/default')

@section('title')
    @lang('admin_slider.create_slider')
@stop
<?php 

    $cropper_setting = [
        [
            'section' => 'banner_image', 'dimension' => ['width' => 540, 'height' => 513], 'file_field_selector' => '#bannerImage', 'section_id'=>'banner-image',
        ],
        [    'section' => 'banner_image_mob', 'dimension' => ['width' => 153, 'height' => 153], 'file_field_selector' => '#bannerImageMob', 'section_id'=>'banner-image-mob',
        ]
    ];

?>
@section('header_styles')
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}global.css" />
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}style.css" />
    <script src="{{ Config('constants.angular_url') }}libs/flatpickr.min.js"></script>
    <link rel="stylesheet" type="text/css" href="{{Config('constants.admin_css_url') }}cropper.min.css">
    <link rel="stylesheet" type="text/css" href="{{Config('constants.public_url')}}/angular-froala/bower_components/font-awesome/css/font-awesome.min.css">

    <script type="text/javascript">
        //for banner image cropper setting
        var height = {!! getPrdThumbDim()['h'] !!} ;
        var cropper_setting = {!! getImageDimension('banner')!!};
        var CROPPER_SETTING = {!! json_encode($cropper_setting) !!};
        var width_arr = {!! widthArr() !!};
        var height_arr = {!! heightArr() !!};
        var height_one =  height + 40 ;
        var height_two = height_one * 2;
        var height_blog_one = height_arr['one'];
        var height_blog_two = height_arr['two'];
    </script>
@stop

@section('header_left_menu_content')
   <div class="header-col no-border">
      <a href="javascript:void(0);"><span class="page-setting">@lang('admin_slider.edit_slider')</span></a>
   </div>   
@stop

@section('content')
<div class="content cms-content container">
    <form id="cmsForm" action="{{ action('Admin\CmsSlider\CmsSliderController@update',$slider_dtls->id) }}" method="post" class="form-horizontal form-bordered">
        <div class="header-title">
            <h1 class="title">@lang('admin_product.edit_slider')</h1>
            <div class="float-right">
                <a class="btn-back" href="{{ action('Admin\CmsSlider\CmsSliderController@index') }}">&lt;@lang('admin_common.back')</a>                   
                <button type="submit" name="submit_type" value="submit" class="btn btn-primary">@lang('admin_common.save')</button>    
            </div>
        </div>
        @if(Session::has('succMsg'))
        <div class="alert alert-success alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong>@lang('admin_common.success'):</strong> {{ Session::get('succMsg') }}
        </div>
        @endif 
        {{ csrf_field() }}
        {{ method_field('PUT') }}
        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('block','slider_listing')!!}
                </ul>
            </div>
            <div class="row">
                <div class="col-sm-8">
                <h2 class="title-prod">General</h2>
                <div class="form-group row">
                    <div class="col-md-7">
                    <label for="form-text-input">@lang('admin_common.type') <i class="strick">*</i></label>
                        <div class="radio-group">
                            <label class="radio-wrap">
                                <input type="radio" name="slider_type" value="product" @if($slider_dtls->type == 'product') checked="checked" @endif>
                                <span class="radio-label">@lang('admin_slider.product')</span>
                            </label>                                                    
                        </div>
                        @if ($errors->has('type'))
                            <p class="error error-msg">{{ $errors->first('type') }}</p>
                        @endif
                    </div>
                </div> 

                <div class="form-group row">
                    <div class="col-md-6">
                    <label for="form-text-input">@lang('admin_common.name') <i class="strick">*</i></label>
                        <input type="text" name="name" value="{{ $slider_dtls->name }}">
                        @if ($errors->has('name'))
                            <p class="error error-msg">{{ $errors->first('name') }}</p>
                        @endif
                    </div>
                </div> 
                <div class="form-group row">
                    <div class="col-md-8">

                        {!! CustomHelpers::fieldstabWithLanuageEdit([['field'=>'textarea', 'name'=>'title', 'label'=>Lang::get('cms.title').' <!-- <i class="strick">*</i> -->', 'errorkey'=>'slider_ttl']], '3', 'cms_slider_id', $slider_dtls->id, $tblCmsSliderDesc, $errors) !!}
                    </div>
                </div> 
                <div class="form-group row">
                    <div class="col-md-6">
                        <label for="form-text-input">@lang('admin_common.status') <i class="strick">*</i></label>
                        <select class="select" name="status">
                            <option value="1" @if($slider_dtls->status == '1') selected="selected" @endif>@lang('admin_common.active')</option>
                            <option value="0" @if($slider_dtls->status == '0') selected="selected" @endif>@lang('admin_common.inactive')</option>
                        </select>
                    </div>
                </div>
                @if($slider_dtls->banner)
                <div class="form-group">
                    <label for="form-text-input"></label>
                    <div>
                        <img src="{{ Config::get('constants.cms_slider_url').$slider_dtls->banner }}" class="img-responsive">
                    </div>    
                </div>
                @endif

                
                <div class="form-group row">
                    <div class="col-md-7">
                        <label for="form-text-input">@lang('admin_slider.design') <i class="strick">*</i> <span class="popovertxt" data-toggle="modal" data-target="#SliderModalDesign">?</span></label>
                        <select name="prd_design">
                            @foreach(prdSliderDesign() as $key => $val )
                                <option value="{{ $key }}" @if($slider_dtls->design==$key) selected="selected" @endif>{{ $val }}</option>
                            @endforeach
                        </select>
                        <select name="blog_design">
                            @foreach(blogSliderDesign() as $key => $val )
                                <option value="{{ $key }}" @if($slider_dtls->design==$key) selected="selected" @endif>{{ $val }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="box_bg" id="feature_div">
                    <div class="row">
                        <div class="col-md-6 form-group mb-0">
                            <label>@lang('admin_slider.sku_for_feature_product')</label>
                            <textarea name="feature_sku">{{ $slider_dtls->feature_sku }}</textarea>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="banner_image" id="bannerImage">
                <input type="hidden" name="banner_image_mob" id="bannerImageMob">
                <div class="form-group box_bg" id="banner_div">
                    <div class="form-group cropper-main" id="banner-image">
                        <div class="avatar-view single-file-upload" title="Change the avatar" data-section="banner_image">
                            <img src="{{asset('assets/images/please_upload_image.jpg')}}" alt="" id="banner_image">
                        </div>
                         @include('admin.includes.common_cropper_upload',['cropper_setting'=>[$cropper_setting[0]]])
                        @if ($errors->has('banner_image'))
                            <p id="banner_image-error" class="error error-msg">{{ $errors->first('banner_image') }}</p>
                        @endif
                    </div>
                    <div class="form-group">
                        <label class="check-wrap">
                            <input type="checkbox" name="banner_mobile" class="banner-mobile-radio" value="1" @if(!empty($slider_dtls->banner_mob_image)) checked="checked" @endif>
                            <span class="chk-label mt-2">Choose banner Seperate for Mobile</span>
                        </label>
                        <div class="form-group cropper-main mobile-upload-banner" id="banner-image-mob"  @if(empty($slider_dtls->banner_mob_image)) style="display: none;" @endif>
                            <div class="avatar-view single-file-upload" title="Change the avatar" data-section="banner_image_mob">
                                
                                    <img src="{{asset('assets/images/please_upload_image.jpg')}}" alt="" id="banner_image_mob">
                                    
                                        <!-- <span><img src="{{ Config::get('constants.cms_slider_url').$slider_dtls->banner_mob_image }}" class="img-responsive"></span> -->
                                    
                               
                            </div>
                             @include('admin.includes.common_cropper_upload',['cropper_setting'=>[$cropper_setting[1]]])
                        </div> 
                        
                    </div>  

                    <div class="row">
                        <div class="col-md-6 form-group mb-0">
                            <label>@lang('admin_cms.banner_url')</label>
                            <input type="text" value="{{ $slider_dtls->banner_url }}" name="banner_url" placeholder="Enter link">
                        </div>
                    </div>                    
                </div>
                {{-- <div class="form-group" id="banner_div">
                    <input type="hidden" name="banner_image" value="" id="banner_image_input">
                    @include('admin.includes.banner_image_upload')
                    <div>
                       
                        @if ($errors->has('banner_image'))
                            <p id="banner_image-error" class="error error-msg">{{ $errors->first('banner_image') }}</p>
                        @endif
                    </div>
                    
                </div> --}}
                <hr>
                <div class="row" id="show_hide_slider">
                    <div class="col-md-6">
                        <div class="radio-group">
                            <label class="radio-wrap">
                                <input type="radio" name="show_slider" value="yes" @if($slider_dtls->show_slider == 'yes') checked="checked" @endif>
                                <span class="radio-label"> @lang('admin_slider.show_slider')</span>
                            </label>
                            <label class="radio-wrap">
                                <input type="radio" name="show_slider" value="no" @if($slider_dtls->show_slider == 'no') checked="checked" @endif>
                                <span class="radio-label"> @lang('admin_slider.hide_slider')</span>
                            </label>
                        </div>                        
                    </div>
                </div>
                <div class="product-slider-option" id="slider_option">
                    <h3 class="banner-title">@lang('admin_slider.container_width')<i class="strick">*</i></h3>
                    <div class="row">
                        <div class="col-sm-5">
                            <div class="form-group mb-2">
                                <div class="radio-group">
                                    <label class="radio-wrap">
                                        <input type="radio" name="cont_width" {{$slider_dtls->container_width=='12'?'checked="checked"':'' }} value="12">
                                        <span class="radio-label"> 12</span>
                                    </label>
                                    <label class="radio-wrap">
                                        <input type="radio" name="cont_width" value="10" {{ $slider_dtls->container_width=='10'?'checked="checked"':'' }}>
                                        <span class="radio-label"> 10</span>
                                    </label>
                                    <label class="radio-wrap">
                                        <input type="radio" name="cont_width" value="8" {{ $slider_dtls->container_width=='8'?'checked="checked"':'' }}>
                                        <span class="radio-label"> 8</span>
                                    </label>
                                    <label class="radio-wrap">
                                        <input type="radio" name="cont_width" value="6" {{ $slider_dtls->container_width=='6'?'checked="checked"':'' }}>
                                        <span class="radio-label"> 6</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="product-slider-option slider-settings mb-3 mt-3">
                    <div class="form-row align-items-end">
                        <div class="col-sm-3"><img src="images/containersetting.png" alt=""></div>
                        <div class="col-sm-9 form-group">
                            <h3 class="banner-title font-weight-bold pt-0">@lang('admin_slider.container_setting')</h3>
                            <label>@lang('admin_slider.number_of_product_per_slider') <i class="strick">*</i></label>
                            <div class="col-sm-12 pl-0 pr-0">
                                <textarea name="setting_slider">@if(isset($slider_opt['setting_slider'])) {{$slider_opt['setting_slider']}} @endif</textarea>
                                <!-- <input type="text" name="item_per_slider"  class="col-md-3" value="{{ $slider_opt['item_per_slider']??'' }}"> -->
                            </div>
                        </div>
                    </div>
                    <div class="form-row align-items-end">
                        <div class="col-sm-3"><img src="images/slideramount.png" alt=""></div>
                        <div class="col-sm-9">
                            <div class="form-group mb-0">
                                <label>@lang('admin_slider.limit_of_slider_amount') <i class="strick">*</i></label>
                                <div class="col-sm-3 pl-0 pr-0">
                                    <select name="tot_item">
                                        @for($i=1; $i<=20;$i++)
                                            <option value="{{ $i }}" @if($slider_dtls->tot_item == $i) selected="selected" @endif>{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="product-slider-option slider-settings mb-3 mt-3">
                    <div class="form-row">
                        <div class="col-sm-3"><img src="images/containerspace.png" alt=""></div>
                        <div class="col-sm-9">
                            <h3 class="banner-title font-weight-bold pt-0">@lang('admin_slider.container_space')</h3>
                            <div class="row">
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label>@lang('admin_slider.top') <i class="strick">*</i></label>
                                        <div class="btn-group mb-0 align-items-center">
                                            <input class="text-center" type="text" value="{{ $slider_opt['cont_space_top']??'' }}" name="cont_space_top">
                                            <span class="ml-2">@lang('admin_slider.px')</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-3 float-right">
                                    <div class="form-group mb-0">
                                        <label>@lang('admin_slider.bottom') <i class="strick">*</i></label>
                                        <div class="btn-group align-items-center">
                                            <input class="text-center" type="text" value="{{ $slider_opt['cont_space_bottom']??'' }}" name="cont_space_bottom">
                                            <span class="ml-2">@lang('admin_slider.px')</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="product-slider-option slider-settings mb-3 mt-3">
                    <div class="form-row">
                        <div class="col-sm-3"><img src="images/thumbnailspace.png" alt=""></div>
                        <div class="col-sm-9">
                            <h3 class="banner-title font-weight-bold pt-0">@lang('admin_slider.thumbnail_space')</h3>
                            <div class="form-group mb-0">
                                <label>@lang('admin_slider.left_and_right') <i class="red">*</i></label> 
                                <div class="row">
                                    <div class="btn-group align-items-center col-sm-3">
                                        <input class="text-center" type="text" value="{{ $slider_opt['thumb_space']??'' }}" name="thumb_space">
                                        <span class="ml-2">@lang('admin_slider.px')</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="form-text-input">@lang('admin_slider.condition') <i class="strick">*</i></label>
                    <span id="">
                        <div class="row m-0">
                            <div class="col-sm-6'">
                                <label class="radio-wrap">
                                    <input type="radio" name="slider_con" value="master_level_1" class="prdRd" @if($slider_dtls->slider_condition == 'master_level_1') checked="checked" @endif>
                                    <span class="radio-label">@lang('admin_slider.master_product_level_1')</span>
                                </label>
                                <label class="radio-wrap">
                                    <input type="radio" name="slider_con" value="specific_level_2" class="prdRd" @if($slider_dtls->slider_condition == 'specific_level_2') checked="checked" @endif>
                                    <span class="radio-label">@lang('admin_slider.specific_product_level_2')</span>
                                </label>
                                
                            </div>
                            
                        </div>
                    </span>

                </div>
                <div class="form-group row" >
                    <div class="col-md-5" id="custom_sku" style="display: none;">
                        <textarea  name="custom_cat_id">{{ $slider_dtls->custom_id }}</textarea>
                    </div>
                    <div class="col-md-5" id="custom_blog_id" style="display: none;">
                        <textarea  name="custom_id">{{ $slider_dtls->custom_id }}</textarea>
                    </div>
                    <div class="col-sm-6" style="display: none;" id="prd_cat_con">
                        @if(count($prd_categories) > 0) 
                            <ul class="tree tree-menu">
                            @foreach($prd_categories as $key=>$mainCategory)
                                <li>
                                    <a href="javascript:void(0);">
                                    @if(count($mainCategory->category) > 0)
                                        <i class="menuIcon glyphicon glyphicon-plus"></i>
                                    @endif
                                    <input type="checkbox" class="cat-checkbox" name="prd_cat_id[]" value="{{$mainCategory->id}}" @if(in_array($mainCategory->id, $categoryId)) checked="checked" @endif>
                                    <i><img src="assets/images/folder.svg" alt=""></i> {{$mainCategory->categorydesc->category_name}}</a>
                                    
                                </li>
                            @endforeach
                            </ul>
                            @if ($errors->has('blog_cat_id'))
                                <p class="error">{{ $errors->first('blog_cat_id') }}</p>
                            @endif
                        @endif                                         
                    </div>
                    <div class="col-sm-6" style="display: none;" id="blog_cat_con">
                        @if(count($blog_categories) > 0) 
                            <ul class="tree tree-menu">
                            @foreach($blog_categories as $key=>$mainCategory)
                                <li>
                                    <a href="javascript:void(0);">
                                    @if(count($mainCategory->category) > 0)
                                        <i class="menuIcon glyphicon glyphicon-plus"></i>
                                    @endif
                                    <input type="checkbox" class="cat-checkbox" name="blog_cat_id[]" value="{{$mainCategory->id}}" @if(in_array($mainCategory->id, $blogCategoryId)) checked="checked" @endif>
                                    <i><img src="assets/images/folder.svg" alt=""></i> {{$mainCategory->getCatDesc->name}}</a>
                                    @if(count($mainCategory->category) > 0) 
                                        <ul>
                                        @foreach($mainCategory->category as $subcategory)
                                            <li>
                                                <a href="javascript:void(0);">
                                                @if(count($subcategory->category) > 0)
                                                    <i class="menuIcon glyphicon glyphicon-plus"></i>
                                                @endif
                                                <input type="checkbox" class="cat-checkbox" name="blog_cat_id[]" value="{{$subcategory->id}}" @if(in_array($subcategory->id, $blogCategoryId)) checked="checked" @endif>
                                                <i><img src="assets/images/folder.svg" alt=""></i> {{$subcategory->getCatDesc->name}}</a> 
                                                @if(count($subcategory->category) > 0)
                                                    <ul>
                                                    @foreach($subcategory->category as $subsubcategory)
                                                        <li>
                                                            <a href="javascript:void(0);">
                                                            @if(count($subsubcategory->category) > 0)
                                                                <i class="menuIcon glyphicon glyphicon-plus"></i>
                                                            @endif
                                                            <input type="checkbox" class="cat-checkbox" name="blog_cat_id[]" value="{{$subsubcategory->id}}" @if(in_array($subsubcategory->id, $blogCategoryId)) checked="checked" @endif>
                                                            <i><img src="assets/images/subfolder.svg" alt=""></i> {{$subsubcategory->getCatDesc->name}}</a>
                                                            @if(count($subsubcategory->category) > 0) 
                                                                <ul>
                                                                @foreach($subsubcategory->category as $finalcategory)
                                                                    <li>
                                                                        <a href="javascript:void(0);">
                                                                        <input type="checkbox" class="cat-checkbox"  name="blog_cat_id[]" value="{{$finalcategory->id}}" @if(in_array($finalcategory->id, $blogCategoryId)) checked="checked" @endif>
                                                                        <i><img src="assets/images/subfolder.svg" alt=""></i> {{$finalcategory->getCatDesc->name}}</a> 
                                                                    </li>
                                                                @endforeach
                                                                </ul>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                    </ul>
                                                @endif
                                            </li>
                                        @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                            </ul>
                            @if ($errors->has('blog_cat_id'))
                                <p class="error">{{ $errors->first('blog_cat_id') }}</p>
                            @endif
                        @endif                                         
                    </div>
                    
                </div>
                <div class="form-group">
                    <label for="form-text-input">@lang('admin_slider.select_standard')<i class="strick">*</i></label>
                    <span id="">
                        <div class="row m-0">
                            <div class="col-sm-6">
                                @if(count($badge_dtl))
                                @foreach($badge_dtl as $key => $val)
                                    <label class="radio-wrap">
                                        <input type="checkbox" name="badge[]" value="{{$val->id}}" class="" @if(in_array($val->id,$badge_id_arr)) checked="checked" @endif>
                                        <span class="">{{$val->badgedesc->badge_name ?? ''}} ({{$val->size.' '.$val->grade}})</span>
                                    </label>
                                @endforeach
                                @endif
                            </div>
                            
                        </div>
                    </span>

                </div>
                <div class="form-group">
                    <label for="form-text-input">@lang('admin_slider.select_package')<i class="strick">*</i></label>
                    <span id="">
                        <div class="row m-0">
                            <div class="col-sm-6">
                                @if(count($package_dtl))
                                @foreach($package_dtl as $key => $val)
                                    <label class="radio-wrap">
                                        <input type="checkbox" name="package[]" value="{{$val->id}}" class="" @if(in_array($val->id,$package_id_arr)) checked="checked" @endif>
                                        <span class="">{{$val->title}}</span>
                                    </label>
                                @endforeach
                                @endif
                            </div>
                            
                        </div>
                    </span>

                </div>
                <div class="form-group row">
                    <div class="col-md-12">
                        <label for="form-text-input">@lang('admin_slider.sort_by') <i class="strick">*</i></label>
                        <span id="radio_prd_sort_by" class="radio-group">
                            <label class="radio-wrap">
                                <input type="radio" name="sort_by" value="name" @if(isset($slider_opt['sort_by']) && $slider_opt['sort_by']=='name') checked="checked" @endif class="">
                                <span class="radio-label">@lang('admin_slider.name')</span>
                            </label>
                            <label class="radio-wrap">
                                <input type="radio" name="sort_by" value="updated_at" class="" @if(isset($slider_opt['sort_by']) && $slider_opt['sort_by']=='updated_at') checked="checked" @endif>
                                <span class="radio-label">@lang('admin_slider.last_updated')</span>
                            </label>
                            <label class="radio-wrap">
                                <input type="radio" name="sort_by" value="created_at" @if(isset($slider_opt['sort_by']) && $slider_opt['sort_by']=='created_at') checked="checked" @endif class="">
                                <span class="radio-label">@lang('admin_slider.created_date')</span>
                            </label>
                            <label class="radio-wrap" id="sort_by_price">
                                <input type="radio" name="sort_by" value="price" @if(isset($slider_opt['sort_by']) && $slider_opt['sort_by']=='price') checked="checked" @endif class="">
                                <span class="radio-label">@lang('admin_product.price')</span>
                            </label>
                            
                        </span>
                        
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-12">
                        <label for="form-text-input">@lang('admin_slider.sort_by_value') <i class="strick">*</i></label>
                        <span id="radio_prd_sort_by_val" class="radio-group">
                            <label class="radio-wrap">
                                <input type="radio" name="sort_by_val" value="asc" @if(isset($slider_opt['sort_by_val']) && $slider_opt['sort_by_val']=='asc') checked="checked" @endif class="">
                                <span class="radio-label">@lang('admin_slider.asc')</span>
                            </label>
                            <label class="radio-wrap">
                                <input type="radio" name="sort_by_val" value="desc" class="" @if(isset($slider_opt['sort_by_val']) && $slider_opt['sort_by_val']=='desc') checked="checked" @endif>
                                <span class="radio-label">@lang('admin_slider.desc')</span>
                            </label>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </form>
</div>

<!-- Modal -->
<div id="SliderModalDesign" class="modal fade" role="dialog">
    <div class="modal-dialog">
    <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header line-default">
                <h3 class="modal-title">Choose Design</h3>
                <span class="close fa fa-times" data-dismiss="modal"></span>
            </div>
            <div class="modal-body">
                <!-- CMS Slider Update Desigen -->
                <div class="slider_design">
                    <h2 class="slider-title">One Roll</h2>
                    <div class="select-section">Please select</div>
                        <h3 class="banner-title"><i class="fas fa-tshirt banner_icons"></i> Only Product</h3>
                        <label class="custom_radio_slider onepslider">
                                <input type="radio" name="banner_product" value="">
                                <span class="radio-label"></span>
                             </label>
                        <h3 class="banner-title"><i class="fa fa-picture-o banner_icons"></i> Banner & <i class="fas fa-tshirt banner_icons"></i> Product</h3>
                        <div class="custom-radio-group">
                            <label class="custom_radio_slider sbp3by9">
                                <input type="radio" name="banner_product" value="">
                                <span class="radio-label"></span>
                             </label>
                              <label class="custom_radio_slider sbp4by8">
                                <input type="radio" name="banner_product" value="">
                                <span class="radio-label"></span>
                             </label>
                              <label class="custom_radio_slider sbp6by6">
                                <input type="radio" name="banner_product" value="">
                                <span class="radio-label"></span>
                             </label>
                              <label class="custom_radio_slider sbp8by4">
                                <input type="radio" name="banner_product" value="">
                                <span class="radio-label"></span>
                             </label>
                        </div>
                        <h3 class="banner-title"><i class="fas fa-tshirt banner_icons"></i> Product &  <i class="fa fa-picture-o banner_icons"></i> Banner</h3>
                        <div class="custom-radio-group">
                            <label class="custom_radio_slider spb3by9">
                                <input type="radio" name="banner_product" value="">
                                <span class="radio-label"></span>
                             </label>
                              <label class="custom_radio_slider spb4by8">
                                <input type="radio" name="banner_product" value="">
                                <span class="radio-label"></span>
                             </label>
                              <label class="custom_radio_slider spb6by6">
                                <input type="radio" name="banner_product" value="">
                                <span class="radio-label"></span>
                             </label>
                              <label class="custom_radio_slider spb8by4">
                                <input type="radio" name="banner_product" value="">
                                <span class="radio-label"></span>
                             </label>
                        </div>
                        <h3 class="banner-title"><i class="fa fa-star banner_icons"></i> Featured Product &  <i class="fas fa-tshirt banner_icons"></i> Product</h3>
                        <div class="custom-radio-group">
                            <label class="custom_radio_slider fp3by9">
                                <input type="radio" name="banner_product" value="">
                                <span class="radio-label"></span>
                             </label>
                              <label class="custom_radio_slider fp4by8">
                                <input type="radio" name="banner_product" value="">
                                <span class="radio-label"></span>
                             </label>
                              <label class="custom_radio_slider fp6by6">
                                <input type="radio" name="banner_product" value="">
                                <span class="radio-label"></span>
                             </label>
                        </div>
                         <h3 class="banner-title"><i class="fas fa-tshirt banner_icons"></i> Product & <i class="fa fa-star banner_icons"></i> Featured Product</h3>
                         <div class="custom-radio-group">
                            <label class="custom_radio_slider pf9by3">
                                <input type="radio" name="banner_product" value="">
                                <span class="radio-label"></span>
                             </label>
                              <label class="custom_radio_slider pf6by6">
                                <input type="radio" name="banner_product" value="">
                                <span class="radio-label"></span>
                             </label>
                        </div>
                        <h2 class="slider-title mt-3">Two Roll</h2>
                        <div class="select-section">Please select</div>
                        <h3 class="banner-title"><i class="fa fa-picture-o banner_icons"></i> Banner & <i class="fas fa-tshirt banner_icons"></i> Product</h3>
                        <div class="custom-radio-group">
                            <label class="custom_radio_dslider bpd3by9">
                                <input type="radio" name="banner_product" value="">
                                <span class="radio-label"></span>
                            </label>
                            <label class="custom_radio_dslider bpd4by8">
                                <input type="radio" name="banner_product" value="">
                                <span class="radio-label"></span>
                            </label>
                            <label class="custom_radio_dslider bpd6by6">
                                <input type="radio" name="banner_product" value="">
                                <span class="radio-label"></span>
                            </label>
                            <label class="custom_radio_dslider bpd8by4">
                                <input type="radio" name="banner_product" value="">
                                <span class="radio-label"></span>
                            </label>
                        </div>
                        <h3 class="banner-title"><i class="fas fa-tshirt banner_icons"></i> Product & <i class="fa fa-picture-o banner_icons"></i> Banner</h3>
                        <div class="custom-radio-group">
                            <label class="custom_radio_dslider pbd9by3">
                                <input type="radio" name="banner_product" value="">
                                <span class="radio-label"></span>
                            </label>
                            <label class="custom_radio_dslider pbd8by4">
                                <input type="radio" name="banner_product" value="">
                                <span class="radio-label"></span>
                            </label>
                            <label class="custom_radio_dslider pbd6by6">
                                <input type="radio" name="banner_product" value="">
                                <span class="radio-label"></span>
                            </label>
                        </div>
                        <h3 class="banner-title"><i class="fa fa-star banner_icons"></i> Featured Product &  <i class="fas fa-tshirt banner_icons"></i> Product</h3>
                        <div class="custom-radio-group">
                            <label class="custom_radio_dslider fdb3by9">
                                <input type="radio" name="banner_product" value="">
                                <span class="radio-label"></span>
                            </label>
                            <label class="custom_radio_dslider fdb4by8">
                                <input type="radio" name="banner_product" value="">
                                <span class="radio-label"></span>
                            </label>
                            <label class="custom_radio_dslider fdb6by6">
                                <input type="radio" name="banner_product" value="">
                                <span class="radio-label"></span>
                            </label>
                            <label class="custom_radio_dslider fdb8by4">
                                <input type="radio" name="banner_product" value="">
                                <span class="radio-label"></span>
                            </label>
                        </div>
                        <h3 class="banner-title"><i class="fas fa-tshirt banner_icons"></i> Product & <i class="fa fa-star banner_icons"></i> Featured Product</h3>
                        <div class="custom-radio-group">
                            <label class="custom_radio_dslider pfd4by4">
                                <input type="radio" name="banner_product" value="">
                                <span class="radio-label"></span>
                            </label>
                             <label class="custom_radio_dslider pfd9by3">
                                <input type="radio" name="banner_product" value="">
                                <span class="radio-label"></span>
                            </label>
                             <label class="custom_radio_dslider pfd6by6">
                                <input type="radio" name="banner_product" value="">
                                <span class="radio-label"></span>
                            </label>                                
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>



@stop

@section('footer_scripts')
<script src="{{ Config('constants.js_url') }}jquery-cropper.min.js" type="text/javascript"></script>
<script src="{{ Config('constants.js_url') }}common_cropper_upload_setting.js" type="text/javascript"></script>
<script src="{{ Config('constants.admin_js_url') }}cms_slider.js" type="text/javascript"></script>
@include('includes.froalaeditor_dependencies')
<script src="{{asset('js/normal_froala_editor_setting.js')}}"></script>
<!-- end of page level js -->     
@stop
