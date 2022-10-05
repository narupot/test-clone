@extends('layouts/admin/default')

@section('title')
   @lang('blog.edit_widget') 
@stop

@section('header_styles')
    <style type="text/css">
        .hide-div{
            display: none;
        }
    </style>
@stop

@section('content')
<div class="content"> 
    @if(Session::has('succMsg'))
        <script type="text/javascript">               
            _toastrMessage('success', "{{ Session::get('succMsg') }}");    
        </script> 
    @endif        

    <div class="header-title">                   
        <h1 class="title">@lang('blog.edit_widget') :  @if(isset($detail->type_name)){{$detail->type_name}} @else {{'N/A'}} @endif</h1>
     </div>
    <div class="content-wrap">
        <div class="breadcrumb">
            <ul class="bredcrumb-menu">
                {!!getBreadcrumbAdmin('blog','widget')!!}
            </ul>
        </div>
        <form id="cmsForm" action="{{ action('Admin\Widget\WidgetController@update',$detail->id) }}" method="post" class="form-horizontal form-bordered">
           {{ csrf_field() }}
            {{ method_field('PUT') }}
 
            {{--<div class="form-group row">
                <label>@lang('common.status') <i class="strick">*</i></label> 
                <div class="col-md-5">
                    <select class="select" name="status">
                        <option value="1" @if($detail->status == '1') selected="selected" @endif>@lang('common.enable')</option>
                        <option value="0" @if($detail->status == '0') selected="selected" @endif>@lang('common.disable')</option>
                    </select>
                </div>
            </div> --}}
            <div class="form-group">
                <label>@lang('cms.section') </label> 
                <div class="row">
                    <div class="col-md-5">
                        <select class="select" name="section">
                            <option value="">@lang('common.select')</option>
                            @if(!empty($section))
                            @foreach($section as $key => $value)
                                <option value="{{ $value->id }}"  @if($detail->section_id == $value->id) selected="selected" @endif>{{ ucfirst(str_replace('-',' ', $value->section_name)) }}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">                
                <div class="row"> 
                    <div class="col-md-5">
                        <label class="check-wrap">
                            <input type="checkbox" @if($detail->is_fix == '1') checked="checked" @endif name="fixed" value="1"> 
                            <span class="chk-label">@lang('cms.is_fixed')</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="nopad-top">@lang('cms.type') </label> 
                <div class="row">
                    <div class="col-md-5"> {{ str_replace('-',' ',ucfirst($detail->type)) }}
                        {{-- <select class="select" name="type">
                        <option value="">@lang('common.select')</option>
                            <option value="widget" @if($detail->type == 'widget') selected="selected" @endif>@lang('blog.widget')</option>
                            <option value="static-block" @if($detail->type == 'static-block') selected="selected" @endif>@lang('common.static-block')</option>
                            <option value="banner" @if($detail->type == 'banner') selected="selected" @endif>@lang('common.banner')</option>
                            <option value="product-slider" @if($detail->type == 'product-slider') selected="selected" @endif>@lang('common.product_slider')</option>
                            <option value="part-finder" @if($detail->type == 'part-finder') selected="selected" @endif>@lang('common.part_finder')</option>>
                        </select> --}}
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>@lang('cms.pages') </label>
                <div class="row">
                    <div class="col-md-10">
                        <label class="radio-wrap mt-10">
                        <input type="radio" name="pages"  @if($detail->pages == '1') checked="checked" @endif checked="checked" value="1"> 
                        <span class="radio-label">@lang('cms.show_all_pages')</span>
                        </label>
                        <label class="radio-wrap mt-10">
                        <input type="radio" name="pages"  @if($detail->pages == '2') checked="checked" @endif value="2"> 
                        <span class="radio-label">@lang('cms.show_checked_pages')</span>
                        </label>
                        <label class="radio-wrap mt-10">
                        <input type="radio" name="pages"  @if($detail->pages == '3') checked="checked" @endif value="3"> 
                        <span class="radio-label">@lang('cms.hide_checked_pages')</span>
                        </label>
                    </div>
                </div> 
            </div>
            <div class="form-group row hide-div" id="page_value">
                <label class="col-md-2 control-label" for="form-text-input">@lang('cms.url') </label> 
                <div class="col-md-5">                    
                    @if(isset($seo_pages) && !empty($seo_pages))
                        @foreach($seo_pages as $skey => $svalue)
                            <label class="check-wrap"><input type="checkbox" name="page_url[]" value="{{$svalue['url']}}" @if(in_array($svalue['url'],$page_arr)) checked="checked" @endif><span class="chk-label">{{$svalue['name']}}</span>
                            </label>
                        @endforeach
                    @endif
                    <!-- <input type="text" name="page_url" value="{{ !empty($detail->blockPage) ? $detail->blockPage->page_url : '' }}"> 
                    <div class="bg-success col-sm-12">
                        <p class="mt-2">Specify pages by using their paths (Page Name).
                        <br/> Enter one path per line. 
                        The '*' character is a wildcard. <br/>
                        Example paths are blog for the blog page and blog/* for every blog. </p>
                    </div>  -->
                </div>
            </div>
            <div class="form-group">
                <label>@lang('cms.customer_group') </label>
                <div class="row">
                    <div class="col-md-5">
                        <label class="radio-wrap mt-10">
                        <input type="radio" name="group"  @if($detail->customer_group == '1') checked="checked" @endif checked="checked" value="1"> <span class="radio-label">@lang('cms.show_all_group')</span>
                        </label>
                        <label class="radio-wrap mt-10">
                        <input type="radio" name="group"  @if($detail->customer_group == '2') checked="checked" @endif value="2"> 
                        <span class="radio-label">@lang('cms.show_checked_group')</span>
                        </label>
                        <label class="radio-wrap mt-10">
                        <input type="radio" name="group"  @if($detail->customer_group == '3') checked="checked" @endif value="3"> 
                        <span class="radio-label">@lang('cms.hide_checked_group')</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-group row hide-div" id="group_value">
                <label class="col-md-2 control-label" for="form-text-input">&nbsp;</label> 
                <div class="col-md-5">
                    <select name="group_id[]" multiple="multiple" class="multiple-selectw">
                        <option value="">@lang('common.select')</option>
                        @if(!empty($customer_group))
                        @foreach($customer_group as $key => $group)
                            <option value="{{ $group->id}}" @if(in_array($group->id, $group_id_arr)) selected="selected" @endif>{{ $group->customerGroupDesc->group_name  }}</option>
                        @endforeach
                        @endif
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-9 col-md-offset-3 btns-group mt-10">  
                    <a href="{{ action('Admin\Widget\WidgetController@index') }}" class="btn btn-back">@lang('common.back')</a>                  
                    <button type="submit" name="submit_type" value="submit_continue" class="btn btn-save btn-secondary">@lang('common.update')</button>                    
                </div>
            </div>
        </form>
    </div>
</div>
      
@stop

@section('footer_scripts')
 
<!-- begining of page level js -->
<script src="{{ Config('constants.page_js_url') }}validateCMS.js" type="text/javascript"></script>
<script type="text/javascript">
    jQuery(document).ready(function(){
        if(jQuery('input[name=pages]:checked').val() !='1'){
            $('#page_value').show();
        }else{
             $('#page_value').hide();
        }

        if(jQuery('input[name=group]:checked').val() !='1'){
            $('#group_value').show();
        }else{
             $('#group_value').hide();
        }
    })
    jQuery('input[name=pages]').click(function(){
        if($(this).val() != '1'){
            $('#page_value').show();
        }else{
            $('#page_value').hide();
        }
    })

    jQuery('input[name=group]').click(function(){
        if($(this).val() != '1'){
            $('#group_value').show();
        }else{
            $('#group_value').hide();
        }
    })
</script>
<!-- end of page level js --> 

@include('includes.froalaeditor_dependencies')
<script type="text/javascript">
   var csrftoken = window.Laravel.csrfToken;
</script>
<script src="{{asset('js/normal_froala_editor_setting.js')}}"></script>        
@stop
