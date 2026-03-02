@extends('layouts/admin/default')

@section('title')
    @lang('admin.image_placeholder')
@stop

@section('header_styles')
    <!--page level css -->
    <link rel="stylesheet" type="text/css" href="{{ Config('constants.admin_css_url') }}jasny-bootstrap.css" />
    <!-- end of page level css -->      
@stop

@section('content')

    <!-- Right side column. Contains the navbar and content of the page -->
    <div class="content">
        @if(Session::has('succMsg'))    
            <script type="text/javascript">               
                _toastrMessage('success', "{{ Session::get('succMsg') }}");    
            </script>                            
        @elseif(Session::has('errorMsg'))
            <script type="text/javascript"> 
                _toastrMessage('error', "{{ Session::get('errorMsg') }}");
            </script>  
        @endif         
        <!-- Main content -->
        <form action="{{ action('Admin\Config\SystemConfigController@placeholderImageUpload') }}" method="post" enctype="multipart/form-data" class="form-horizontal form-bordered">
            {{ csrf_field() }}
            <div class="header-title">
                <h1 class="title">@lang('admin.image_placeholder')</h1>
                <span class="float-right">
                    <button type="submit" class="btn btn-primary">@lang('common.update')</button>
                </span>
            </div>
            <div class="content-wrap">
                <div class="breadcrumb">
                    <ul class="bredcrumb-menu">
                        {!!getBreadcrumbAdmin('config')!!}
                    </ul>
                </div>
                <!--code for file upload -->
                @if(count($user_images_arr['M']) > 0)
                    <div class="form-group">
                        <h3 class="mb-3">@lang('admin.user_male'):</h3>
                        @foreach($user_images_arr['M'] as $male)
                            <div class="fileinput fileinput-exists" data-provides="fileinput">
                                <div class="fileinput-preview fileinput-exists thumbnail" style="width:136px; height:136px;">
                                    <img src="{{ Config::get('constants.avtar_images_url').$male['name'] }}">
                                </div>
                                <div class="set-default text-center">
                                    <label>
                                        <input type="radio" name="user_male" value="{{ $male['id'] }}" @if($config_arr['USER_IMAGE'] == $male['id']) checked="checked" @endif>
                                    </label>
                                    @if($config_arr['USER_IMAGE'] == $male['id'])
                                        @lang('common.default')
                                    @else
                                        @lang('common.set_default')
                                    @endif 
                                </div>
                            </div>
                        @endforeach
                    </div>                                    
                @endif
                @if(count($user_images_arr['F']) > 0)
                    <div class="form-group">
                        <h3 class="mb-3"> @lang('admin.user_female'): </h3>
                        @foreach($user_images_arr['F'] as $female)
                            <div class="fileinput fileinput-exists" data-provides="fileinput">
                                <div class="fileinput-preview fileinput-exists thumbnail" style="width:136px; height:136px;">
                                    <img src="{{ Config::get('constants.avtar_images_url').$female['name'] }}">
                                </div>
                                <div class="set-default text-center">
                                    <label>
                                        <input type="radio" name="user_female" value="{{ $female['id'] }}" @if($config_arr['USER_IMAGE_FEMALE'] == $female['id']) checked="checked" @endif>
                                    </label>
                                    @if($config_arr['USER_IMAGE_FEMALE'] == $female['id'])
                                        @lang('common.default')
                                    @else
                                        @lang('common.set_default')
                                    @endif 
                                </div>
                            </div>
                        @endforeach
                    </div>                                
                @endif

                <div class="form-group">
                    <label class="mb-3" for="form-text-input">Product: </label>
                    <div class="fileinput fileinput-exists" data-provides="fileinput">
                        <div class="fileinput-new thumbnail" style="width:210px; height:210px;">
                            </div>
                        <div class="fileinput-preview fileinput-exists thumbnail" style="width:210px; height:210px;">
                            <img src="{{ Config::get('constants.placeholder_url').$config_arr['PRODUCT_IMAGE'] }}">
                        </div> 
                        <span class="btn btn-primary btn-file">
                            <span class="fileinput-exists">Change</span>
                            <input type="file" name="images[PRODUCT_IMAGE]">
                        </span>                                       
                    </div>
                </div>

                <div class="form-group">
                    <h3 class="mb-3">@lang('admin.category')</h3>
                    <div class="fileinput fileinput-exists" data-provides="fileinput">
                        <div class="fileinput-new thumbnail" style="width:210px; height:210px;">
                            </div>
                        <div class="fileinput-preview fileinput-exists thumbnail" style="width:210px; height:210px;">
                            <img src="{{ Config::get('constants.placeholder_url').$config_arr['CATEGORY_IMAGE'].'?product-'.rand(10, 1000) }}">
                        </div> 
                        <span class="btn btn-file">
                            <span class="fileinput-exists">@lang('common.change')</span>
                            <input type="file" accept="image/*" name="images[CATEGORY_IMAGE]">
                        </span>                                       
                    </div>
                </div>                                

                 <!--<div class="form-group">
                    <h3 class="col-md-2 control-label"> @lang('admin.product') 185x185: </h3>
                    <div class="fileinput fileinput-exists col-sm-5" data-provides="fileinput">
                        <div class="fileinput-new thumbnail" style="width:185px; height:185px;">
                            </div>
                        <div class="fileinput-preview fileinput-exists thumbnail" style="width:185px; height:185px;">
                            <img src="{{ Config::get('constants.placeholder_url').$config_arr['PRODUCT_IMAGE_185x185'].'?products-'.rand(10, 1000) }}">
                        </div> 
                        <span class="btn btn-file">
                            <span class="fileinput-exists">@lang('common.change')</span>
                            <input type="file" accept="image/*" name="images[PRODUCT_IMAGE_185x185]">
                        </span>                                       
                    </div>
                </div>                                

                <div class="form-group">
                    <h3 class="col-md-2 control-label"> @lang('admin.product') 265x360: </h3>
                    <div class="fileinput fileinput-exists col-sm-5" data-provides="fileinput">
                        <div class="fileinput-new thumbnail" style="width:265px;height: 360px">
                            </div>
                        <div class="fileinput-preview fileinput-exists thumbnail" style="width:265px;height: 360px">
                            <img src="{{ Config::get('constants.placeholder_url').$config_arr['PRODUCT_IMAGE_265x360'].'?productm-'.rand(10, 1000) }}">
                        </div> 
                        <span class="btn btn-file">
                            <span class="fileinput-exists">@lang('common.change')</span>
                            <input type="file"  accept="image/*" name="images[PRODUCT_IMAGE_265x360]">
                        </span>                                       
                    </div>
                </div>                                

                <div class="form-group">
                    <h3 class="col-md-2 control-label"> @lang('admin.product') 405x405: </h3>
                    <div class="fileinput fileinput-exists col-sm-5" data-provides="fileinput">
                        <div class="fileinput-new thumbnail" style="width:405px;height:405px;">
                            </div>
                        <div class="fileinput-preview fileinput-exists thumbnail" style="width:405px;height:405px;">
                            <img src="{{ Config::get('constants.placeholder_url').$config_arr['PRODUCT_IMAGE_405'].'?productl-'.rand(10, 1000) }}">
                        </div> 
                        <span class="btn btn-file">
                            <span class="fileinput-exists">@lang('common.change')</span>
                            <input type="file" accept="image/*" name="images[PRODUCT_IMAGE_405]">
                        </span>                                       
                    </div>
                </div>--> 

                <div class="form-group">
                    <h3 class="mb-3">@lang('blog.blog') 570x402: </h3>
                    <div class="fileinput fileinput-exists" data-provides="fileinput">
                        <div class="fileinput-new thumbnail" style="width:570px;height:402px;">
                            </div>
                        <div class="fileinput-preview fileinput-exists thumbnail" style="width:570px;height:402px;">
                            <img src="{{ Config::get('constants.placeholder_url').$config_arr['BLOG_IMAGE'].'?blog-'.rand(10, 1000) }}">
                        </div> 
                        <span class="btn btn-file">
                            <span class="fileinput-exists">@lang('common.change')</span>
                            <input type="file" accept="image/*" name="images[BLOG_IMAGE]">
                        </span>                                       
                    </div>
                </div>                

                <!--<div class="form-group">
                    <h3 class="col-md-2 control-label"> @lang('admin.banner'): </h3>
                    <div class="fileinput fileinput-exists col-sm-5" data-provides="fileinput">
                        <div class="fileinput-new thumbnail" style="width:830px; height:240px;">
                            </div>
                        <div class="fileinput-preview fileinput-exists thumbnail" style="width:830px; height:240px;">
                            <img src="{{ Config::get('constants.placeholder_url').$config_arr['BANNER_IMAGE'] }}">
                        </div> 
                        <span class="btn btn-file">
                            <span class="fileinput-exists">@lang('common.change')</span>
                            <input type="file" name="images[BANNER_IMAGE]">
                        </span>                                       
                    </div>
                </div>-->
                <!--code for file upload ended-->
            </div>
        </form>    
    </div>
      
@stop

@section('footer_scripts')
    <script type="text/javascript" src="{{ Config('constants.admin_js_url') }}jasny-bootstrap.js"></script>
@stop
