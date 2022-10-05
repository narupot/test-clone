@extends('layouts/admin/default')

@section('title')
   @lang('cms.edit_block')
@stop

@section('header_styles')

<!--page level css -->
<link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}flatpickr.min.css"/>
<link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}select.css"/>
<!-- end of page level css -->
    <style type="text/css">
        .hide-div{
            display: none;
        }
    </style>
@stop

@section('content')
<div class="content">       

    <div class="header-title clearfix">
        @if(Session::has('succMsg'))
        <div class="alert alert-success alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong>@lang('common.success'):</strong> {{ Session::get('succMsg') }}
        </div>
        @endif                     
        <h1 class="title">@lang('common.edit_block') :  @if(isset($detail->staticBlockDesc->page_title)){{$detail->staticBlockDesc->page_title}} @else {{'N/A'}} @endif</h1>
     </div>
    <div class="content-wrap">
        <div class="breadcrumb">
            <ul class="bredcrumb-menu">
                {!!getBreadcrumbAdmin('block','layout')!!}
            </ul>
        </div>
        <form id="cmsForm" action="{{ action('Admin\Block\BlockController@update',$detail->id) }}" method="post" class="form-horizontal form-bordered">
           {{ csrf_field() }}
            {{ method_field('PUT') }}
 
{{--             <div class="form-group row">
                    <div class="col-md-5">
                        <label class="control-label" for="form-text-input">@lang('common.status') <i class="strick">*</i></label> 
                
                    <select class="select" name="status">
                        <option value="1" @if($detail->status == '1') selected="selected" @endif>@lang('common.enable')</option>
                        <option value="0" @if($detail->status == '0') selected="selected" @endif>@lang('common.disable')</option>
                    </select>
                </div>
            </div> --}}
            <div class="form-group row">
                <div class="col-md-5">
                    <label class="control-label" for="form-text-input">@lang('cms.section') </label> 
                
                    <select class="select" name="section">
                    <option value="">@lang('common.select')</option>
                    @if(count($section))
                    @foreach($section as $key => $value)
                        <option value="{{ $value->id }}"  @if($detail->section_id == $value->id) selected="selected" @endif>{{ ucfirst(str_replace('-',' ', $value->section_name)) }}</option>
                    @endforeach
                    @endif
                        
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-5 ">                    
                    <label class="check-wrap nopadding">
                    <input type="checkbox" @if($detail->is_fix == '1') checked="checked" @endif name="fixed" value="1"> 
                    <span class="chk-label">@lang('cms.is_fixed')</span>
                    </label>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-md-5">
                <label class="control-label nopad-top" for="form-text-input">@lang('cms.type') </label> 
                 {{ str_replace('-',' ',ucfirst($detail->type)) }}
                    {{-- <select class="select" name="type">
                    <option value="">@lang('common.select')</option>
                        <option value="static-block" @if($detail->type == 'static-block') selected="selected" @endif>@lang('common.static-block')</option>
                        <option value="banner" @if($detail->type == 'banner') selected="selected" @endif>@lang('common.banner')</option>
                        <option value="product-slider" @if($detail->type == 'product-slider') selected="selected" @endif>@lang('common.product_slider')</option>
                        <option value="part-finder" @if($detail->type == 'part-finder') selected="selected" @endif>@lang('common.part_finder')</option>>
                    </select> --}}
                </div>
            </div>

           

            <div class="form-group row">
                <div class="col-md-5">
                    <label class="control-label nopad-top" for="form-text-input">@lang('cms.pages') </label> 
                
                    <label class="radio-wrap mt-5">
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

            <div class="form-group hide-div" id="page_value">
                <div class="row">
                    <div class="col-md-5">
                    <label class="control-label" for="form-text-input">@lang('cms.url') </label> 
                    
                        <!--brijesh---->
                     <!--    @if(isset($seo_pages) && !empty($seo_pages))
                            @foreach($seo_pages as $skey => $svalue)
                            <label class="check-wrap">
                                <input type="checkbox" name="page_url[]" value="{{$svalue['url']}}" @if(in_array($svalue['url'],$page_arr)) checked="checked" @endif> <span class="chk-label">{{$svalue['name']}} </span>
                            </label>
                            @endforeach
                        @endif -->

                        <select class="chosen-select" name="page_url[]" multiple="multiple">
                            @if(isset($seo_pages) && !empty($seo_pages))
                                @foreach($seo_pages as $skey => $svalue)
                                    <option value="{{$svalue['url']}}" @if(in_array($svalue['url'],$page_arr)) selected="selected"  @endif>{{$svalue['name']}}</option>                           
                                @endforeach
                            @endif
                        </select>
                        <!-- <input type="text" name="page_url" value="{{ !empty($detail->blockPage) ? $detail->blockPage->page_url : '' }}"> 
                        <div class="bg-success col-sm-12">
                            <p class="mt-2">Specify pages by using their paths (Page Name).
                            <br/> Enter one path per line. 
                            The '*' character is a wildcard. <br/>
                            Example paths are blog for the blog page and blog/* for every blog. </p>
                        </div> -->
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-md-5">
                <label class="control-label nopad-top" for="form-text-input">@lang('cms.customer_group') </label> 
                
                    <label class="radio-wrap mt-5">
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

            <div class="form-group row hide-div" id="group_value">
                <div class="col-md-5">
                <label class="control-label" for="form-text-input">&nbsp; </label> 
                
                    <select name="group_id[]" multiple="multiple" class="multiple-selectw">
                        <option value="">@lang('common.select')</option>
                        @if(count($customer_group))
                        @foreach($customer_group as $key => $group)
                            <option value="{{ $group->id}}" @if(in_array($group->id, $group_id_arr)) selected="selected" @endif>{{ $group->customerGroupDesc->group_name  }}</option>
                        @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <div class="form-group row" id="">
                <div class="col-md-5">
                    <label class="control-label" for="form-text-input">@lang('cms.allow_ip') (@lang('cms.comma_separate'))</label>
                    <input type="text" name="allow_ip" value="{{ $detail->allow_ip }}"> 
                    
                </div>
            </div>

            <div class="form-group row" id="">
                <div class="col-md-5">
                    <label class="control-label" for="form-text-input">@lang('common.start_date')</label>
                    <input type="text" id="datepickers" class="date-select-new flatpickr-input" name="start_date" value="{{ !empty($detail->start_date)?$detail->start_date:'' }}" readonly="readonly">
                </div>
            </div>

            <div class="form-group row" id="">
                <div class="col-md-5">
                    <label class="control-label" for="form-text-input">@lang('common.end_date')</label> 
                    <input type="text" id="datepickers" class="date-select-new flatpickr-input" name="end_date" value="{{ !empty($detail->end_date)?$detail->end_date:'' }}" readonly="readonly">
                    
                </div>
            </div>
                                             
            <div class="form-group row">
                <div class="col-md-9 btns-group mt-10"> 
                    <a class="btn btn-back" href="{{ action('Admin\Block\BlockController@index') }}">@lang('common.back')</a>                   
                    <button type="submit" name="submit_type" value="submit_continue" class="btn btn-secondary">@lang('common.update')</button>
                </div>
            </div>
        </form>
    </div>

</div>
      
@stop

@section('footer_scripts')
 
<!-- begining of page level js -->
<script src="{{ Config('constants.admin_js_url') }}flatpickr.min.js"></script>
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

    $(document).ready(function() {
        // Date time Pickers
        $(".date-select-new").flatpickr({
            altFormat: 'F j, Y H:i:S',
            dateFormat: 'Y-m-d H:i:S',
            enableTime: true,
            enableSeconds: true,       
            showOtherMonths: true
        });
    });
</script>
<script src="{{ Config('constants.admin_js_url') }}chosen.jquery.min.js"></script>
<script type="text/javascript">
     $(".chosen-select").chosen({width: "100%"}); 
</script>
<!-- end of page level js --> 

@include('includes.froalaeditor_dependencies')
<script type="text/javascript">
   var csrftoken = window.Laravel.csrfToken;
</script>
<script src="{{asset('js/normal_froala_editor_setting.js')}}"></script>        
@stop
