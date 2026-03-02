@extends('layouts/admin/default')

@section('title')
    @lang('common.create_embeded_cssjs')
@stop

@section('header_styles')
<!--page level css -->
<link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}select.css"/>
<style>         
    <style>
        .cursor-pointer{
            cursor: pointer;
        }
        .mt10 {
            margin-top: 10px;
        }
        .mb10 {
            margin-bottom: 10px;
        }
        .chosen-container {
            max-width: 100%;
        }
    </style>
</style>
<!-- end of page level css --> 
@stop

@section('content')
    <div class="content">
        @if(Session::has('succMsg'))
            <div class="alert alert-success alert-dismissable margin5">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <strong>@lang('common.success'):</strong> {{ Session::get('succMsg') }}
            </div>
        @endif 
        <form id="cmsForm" action="{{ action('Admin\Config\CssJsEmbededController@store') }}" method="post" class="form-horizontal form-bordered" novalidate="novalidate">
            {{ csrf_field() }}
            <div class="header-title">
                <h1 class="title">@lang('common.create_embeded_cssjs')</h1>
                <div class="float-right">
                    <a class="btn btn-link" href="{{ action('Admin\Config\CssJsEmbededController@index') }}"><span><</span>@lang('common.back')</a>
                    <!-- <button type="submit" name="submit_type" value="submit_continue" class="btn static-block-save btn-primary" data-action="submit_continue">@lang('common.save_and_continue')</button> -->
                                        
                    <button type="submit" name="submit_type" value="submit" class="btn btn-save static-block-save btn-primary" data-action="submit">@lang('common.save')</button>
                </div>
            </div>       
            <div class="content-wrap">
                <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('embeded','embeded')!!}
                </ul>
            </div>
                <div class="form-group row">
                    <div class="col-md-5">
                        <label>@lang('common.status') <i class="strick">*</i></label>
                        <select class="select" name="status">
                            <option value="1">@lang('common.active')</option>
                            <option value="0">@lang('common.inactive')</option>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-5">
                        <label for="form-text-input">@lang('cms.title')  <i class="strick">*</i></label>
                        <input type="text" name="title" value="{{ old('title')}}">
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-5" data-hint="@lang('admin.hint')" data-position="bottom" data-hintPosition="top-left">
                            <label class="control-label" for="form-text-input">@lang('admin_cms.url') </label> 
                        
                            <select class="chosen-select" name="page_url[]" multiple="multiple">
                                @if(isset($seo_pages) && !empty($seo_pages))
                                    @foreach($seo_pages as $skey => $svalue)
                                        <option value="{{$svalue['url']}}" >{{$svalue['name']}}</option>                 
                                    @endforeach
                                @endif
                            </select>

                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-5">
                            <label>@lang('admin_cms.custom_url')</label> 
                            <textarea name="custom_url"></textarea>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-8 col-lg-6 input_fields_wrap">
                            <label>@lang('admin_cms.embeded_css')</label>
                            <div class="row">
                                <div class="col form-group">
                                    <a href="javascript:;" class="btn btn-primary add_field_button" style="margin-bottom: 5px;"><i class="fa fa-plus align-baseline"></i></a>
                                </div>
                                
                            </div>
                            <ui class="css-board"></ui>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-8 col-lg-6 input_fields_wrap_js">
                            <label>@lang('admin_cms.embeded_js')</label>
                            <div class="row">
                            <div class="col form-group">
                                <a href="javascript:;" class="btn btn-primary add_field_button_js" style="margin-bottom: 5px;"><i class="fa fa-plus align-baseline"></i></a>
                            </div>
                                
                            </div>
                            <ui class="js-board"></ui>
                        </div>
                    </div>
                </div>                                                        
            </div>
        </form>
    </div>
      
@stop

@section('footer_scripts')
 
    <!-- begining of page level js --> 
    <script type="text/javascript">
       var csrftoken = window.Laravel.csrfToken;
       var lang_id = "{{Session::get('admin_default_lang')}}";
    </script>
    <script src="{{ Config('constants.admin_js_url') }}chosen.jquery.min.js"></script>
    <script type="text/javascript">
        $(".chosen-select").chosen({width: "100%"}); 
    </script>
    <script src="{{ Config('constants.js_url') }}jquery.validate.min.js" type="text/javascript"></script> 
    <script src="https://sep-demo-v1.sepplatform.com/assets/js/jquery.ui.touch-punch.min.js"></script>

    <script src="{{ Config('constants.admin_js_url') }}bootstrapValidator.min.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}flatpickr.min.js"></script>
    <script>
        $(".flatpickr-date").flatpickr();
    </script>
    <script type="text/javascript">
        $(document).ready(function() {
            var max_fields      = 10; //maximum input boxes allowed
            var wrapper         = $(".input_fields_wrap .css-board"); //Fields wrapper
            var add_button      = $(".add_field_button"); //Add button ID
            
            var x = 1; //initlal text box count
            $(add_button).click(function(e){ //on add input button click
                e.preventDefault();
                if(x < max_fields){ //max input box allowed
                    x++; //text box increment
                    $(wrapper).append('<div class="row align-items-center"><div class="col-sm-1 text-center form-group"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span></div><div class="col-sm-10 form-group"><input type="text" name="embeded_css[]" /></div><div class="col-sm-1 form-group"><span class="fas fa-backspace ui-icon-minusthick removeCss cursor-pointer"></span></div></div>'); //add input box
                }
            });
            
            $(wrapper).on("click",".removeCss", function(e){ //user click on remove text
                e.preventDefault(); 
                $(this).parent().parent().remove();
            })
        });   
    </script>
    <script type="text/javascript">
        $(document).ready(function() {
            var max_fields      = 10; //maximum input boxes allowed
            var wrapper         = $(".input_fields_wrap_js .js-board"); //Fields wrapper
            var add_button      = $(".add_field_button_js"); //Add button ID
            
            var x = 1; //initlal text box count
            $(add_button).click(function(e){ //on add input button click
                e.preventDefault();
                if(x < max_fields){ //max input box allowed
                    x++; //text box increment
                    $(wrapper).append('<div class="row align-items-center"><div class="col-sm-1 text-center form-group"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span></div><div class="col-sm-10 form-group"><input type="text" name="embeded_js[]" /></div><div class="col-sm-1 form-group"><span class="fas fa-backspace ui-icon-minusthick removeJs cursor-pointer"></span></div></div>'); //add input box
                }
            });
            
            $(wrapper).on("click",".remove_field_js", function(e){ //user click on remove text
                e.preventDefault(); $(this).parent('div').remove(); x--;
            })

            $(".js-board").sortable();
            $(".css-board").sortable();
            $(wrapper).on("click",".removeJs", function(e){ //user click on remove text
                e.preventDefault(); 
                $(this).parent().parent().remove();
            })
        });   
    </script>      
@stop
