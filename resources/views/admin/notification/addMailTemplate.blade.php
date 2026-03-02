@extends('layouts/admin/default')

@section('title')
    @lang('admin_common.add_mail_template')
@stop

@section('header_styles')

<!--page level css -->

<!-- end of page level css -->
    
@stop

@section('content')
    <div class="content">
        @if(Session::has('succMsg'))
        <div class="alert alert-success alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong>@lang('admin_common.success'):</strong> {{ Session::get('succMsg') }}
        </div>
        @endif 
        <form id="cmsForm" action="{{ action('Admin\Notification\MailTemplateController@store') }}" method="post" class="">
            <div class="header-title">
                <h1 class="title">@lang('admin_common.add_mail_template')</h1>
                <div class="float-right">
                    <a class="btn btn-back" href="{{ action('Admin\Notification\MailTemplateController@showdetails', [$tempId,$template_type]) }}">&lt;@lang('admin_common.back')</a>
                    <button type="submit" name="submit_type" value="submit" class="btn btn-primary">@lang('admin_common.save')</button>                    
                </div>
            </div>       
            <div class="content-wrap">
				<div class="breadcrumb">
					<ul class="bredcrumb-menu">
						{!!getBreadcrumbAdmin('config','mail','create')!!}
					</ul>
				</div>
                
                {{ csrf_field() }}
                <input type="hidden" name="noti_event_id" value="{{ $tempId }}">
                <input type="hidden" name="noti_type_id" value="{{ $template_type }}">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>@lang('admin_common.language') <i class="strick">*</i></label>                           
                            <select id="form-select" name="lang_id" class="form-control" size="1">
                                <option value="">--@lang('admin_common.select_language')--</option>
                                
                                @foreach($lang_lists as $language)
                                        <option value="{{ $language->id }}" {{ ($language->id==old('lang_id'))?'selected':'' }}>{{ $language->languageName }}</option>                                               
                                @endforeach
                                
                            </select>
                            @if($errors->has('lang_id'))
                              <p class="error error-msg">{{ $errors->first('lang_id') }}</p>
                            @endif                                     
                              
                        </div>
                        @if( $template_type != '6') 
                        <div class="form-group">
                            <label>@lang('admin_common.subject') <i class="strick">*</i></label>
                           
                            <input type="text" name="mail_subject" value="{{ old('mail_subject') }}" class="form-control" placeholder="mail subject">
                            @if($errors->has('mail_subject'))
                              <p class="error error-msg">{{ $errors->first('mail_subject') }}</p>
                            @endif                                         
                            
                        </div>
                        @endif
                        <div class="form-group" @if( $template_type != '1') style="display:none" @endif>
                            <label>@lang('admin_common.master_tempale') <i class="strick">*</i></label>
                            
                            <select name="master_template">
                                <option value="">--@lang('admin_common.select_master_template')--</option>
                                @foreach($master_templates as $templates)  
                                  <option value="{{ $templates->id }}">{{ $templates->name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('master_template'))
                              <p class="error error-msg">{{ $errors->first('master_template') }}</p>
                            @endif                            
                        </div> 
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label>@lang('admin_common.message') <i class="strick">*</i></label> 
                            @if($template_type=="1")
                                <textarea id="form-textarea-input" name="mail_containt" rows="16" class="form-control resize_vertical froala-editor-apply" placeholder="Description..">{{ old('mail_containt') }}</textarea>
                            @else
                                <textarea id="form-textarea-input" name="mail_containt" rows="16" class="form-control resize_vertical" placeholder="Description..">{{ old('mail_containt') }}</textarea>
                            @endif
                            
                            @if($errors->has('mail_containt'))
                              <p class="error error-msg">{{ $errors->first('mail_containt') }}</p>
                            @endif                                         
                        </div>
                        <span><b>@lang('admin_common.variables'): </b>[SITE_NAME], [SITE_URL], [USER_NAME], [USER_ID], [SHOP_NAME], [SHOP_ID], [ORDER_NO], [ORDER_ID], [SELLER_ORDER_NO], [CONTACT_NO], [TRANSACTION_ID]</span>
                           
                    </div>
                </div>                                                   
            </div>
        </form>
    </div>
      
@stop

@section('footer_scripts')
 
<!-- begining of page level js -->
<!--script src="{{ Config('constants.page_js_url') }}validatenotification.js" type="text/javascript"></script-->
<!-- end of page level js --> 

@include('includes.froalaeditor_dependencies')
<script type="text/javascript">
   var csrftoken = window.Laravel.csrfToken;
</script>
<script src="{{asset('js/normal_froala_editor_model.js')}}"></script>
<script src="{{asset('js/normal_froala_editor_setting.js')}}"></script>        
@stop
