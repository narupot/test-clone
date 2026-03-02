@extends('layouts/admin/default')

@section('title')
    @lang('admin.edit_language')
@stop

@section('header_styles')
    
@stop

@section('content')

    <!-- Right side column. Contains the navbar and content of the page -->
    <div class="content">
        @if(Session::has('succMsg'))    
            <script type="text/javascript">               
                _toastrMessage('success', "{{ Session::get('succMsg') }}");    
            </script>                             
        @endif 

        <form id="editLanguageForm" action="{{ action('Admin\Config\LanguageController@update', $language_detail->id) }}" method="post" enctype="multipart/form-data">
            {{ csrf_field() }}
            {{ method_field('PUT') }}
            <div class="header-title">
                <h1 class="title">@lang('admin.edit_language')</h1>
                <div class="float-right">
                    <a class="btn btn-back" href="{{ action('Admin\Config\LanguageController@index') }}">@lang('common.back')</a>
                    <button type="submit" class="btn btn-secondary">@lang('common.update')</button>
                </div>
            </div>

            <div class="content-wrap clearfix">
                <div class="breadcrumb">
                    <ul class="bredcrumb-menu">
                        {!!getBreadcrumbAdmin('config','language')!!}
                    </ul>
                </div>
                <div class="row">
                    <div class="col-sm-5">
                           
                        <div class="form-group">
                            <label>@lang('admin.language_name')</label>                            
                            <input type="text" name="language_name" value="{{ $language_detail->languageName }}" class="form-control" placeholder="Language Name">
                            @if($errors->has('language_name'))
                              <p class="error error-msg">{{ $errors->first('language_name') }}</p>
                            @endif                                       
                           
                        </div>
                        <div class="form-group">
                            <label>@lang('admin.language_code')</label>
                            <input type="text" name="language_code" value="{{ $language_detail->languageCode }}" class="form-control" placeholder="eg: Thai language code is th">
                            @if($errors->has('language_code'))
                              <p class="error error-msg">{{ $errors->first('language_code') }}</p>
                            @endif                                        
                           
                        </div>
                        <div class="form-group">
                            <label>@lang('common.is_default')</label>                           
                            <label class="check-wrap" @if($language_detail->isDefault == 1) style="pointer-events: none;" @endif>
                                <input type="checkbox" name="is_default" value="1" @if($language_detail->isDefault == 1) checked @endif > 
                                <span class="chk-label">@lang('common.yes')</span>
                            </label>                                      
                            
                        </div>                                
                        <div class="form-group">
                            <label>@lang('admin.language_flag')</label>
                            <div class="mt-10">
                                <img src="{{ Config::get('constants.language_url').$language_detail->languageFlag }}">
                                <input id="form-file-input" name="language_flag" type="file">
                                @if($errors->has('language_flag'))
                                  <p class="error error-msg">{{ $errors->first('language_flag') }}</p>
                                @endif                                        
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
      
@stop

@section('footer_scripts')
 
    <!-- begining of page level js -->
    <script src="{{ asset('assets/vendors/jasny-bootstrap/js/jasny-bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendors/iCheck/js/icheck.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form_layouts.js') }}"></script>
    <script src="{{ asset('assets/vendors/bootstrapvalidator/js/bootstrapValidator.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/pages/addEditLanguage.js') }}" type="text/javascript"></script>
    <!-- end of page level js -->
    <script>
    $(document).ready(function() {.js

        $("#dob").datetimepicker({
            format: 'YYYY-MM-DD',
            widgetPositioning:{
                vertical:'bottom'
            },
            keepOpen:false,
            useCurrent: false,
            maxDate: new Date()
        });
    });
    </script>    
    
@stop
