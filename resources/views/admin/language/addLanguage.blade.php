@extends('layouts/admin/default')

@section('title')
    @lang('admin.all_language')
@stop

@section('header_styles')

    <!--page level css -->
    <link href="{{ asset('assets/vendors/jasny-bootstrap/css/jasny-bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendors/iCheck/css/all.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/pages/form_layouts.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/vendors/bootstrapvalidator/css/bootstrapValidator.min.css') }}" type="text/css" rel="stylesheet">
    <!-- end of page level css -->
    
@stop

@section('content')

    <!-- Right side column. Contains the navbar and content of the page -->
    <aside class="right-side">
        @if(Session::has('succMsg'))    
            <script type="text/javascript">               
                _toastrMessage('success', "{{ Session::get('succMsg') }}");    
            </script>                            
        @endif        
        <div id="tab3" class="tab-pane">
        
                    <div class="containers">
                        <div class="heading-wrapper clearfix">
                            <h2 class="title">@lang('admin.add_language')</h2>
                        </div>
                        <div class="border">
                            <div class="breadcrumb">
                                <ul class="bredcrumb-menu">
                                    {!!getBreadcrumbAdmin('config','language')!!}
                                </ul>
                            </div>
                            <form id="addLanguageForm" action="{{ action('LanguageController@store') }}" method="post" enctype="multipart/form-data" class="col-sm-6">
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <label for="form-text-input">@lang('admin.language_name')</label>
                                    <input type="text" name="language_name" value="" class="form-control" placeholder="Language Name">
                                    @if($errors->has('language_name'))
                                      <p class="error error-msg">{{ $errors->first('language_name') }}</p>
                                    @endif                                    
                                </div>
                                <div class="form-group">
                                    <label for="form-text-input">@lang('admin.language_code')</label> 
                                    <input type="text" name="language_code" value="" class="form-control" placeholder="eg: Thai language code is th">
                                    @if($errors->has('language_code'))
                                      <p class="error error-msg">{{ $errors->first('language_code') }}</p>
                                    @endif                                    
                                </div>
                                <div class="form-group">
                                    <label>Is Default</label>
                                        <label class="radio-inline " for="form-inline-radio1">
                                        <input type="checkbox" id="form-inline-radio1" class="radio-blue" name="is_default" value="1"> @lang('admin.yes')</label>                                       
                                </div>                                
                                <div class="form-group ">
                                    <label for="form-file-input">@lang('admin.language_flag')</label>
                                    <div class="pad-top10 ">               
                                        <input id="form-file-input" name="language_flag" type="file">
                                        @if($errors->has('language_flag'))
                                          <p class="error error-msg">{{ $errors->first('language_flag') }}</p>
                                        @endif                                        
                                    </div>
                                </div>                                                
                                <div class="form-group form-actions">
                                    <div class="">
                                        <button type="submit" class="btn-lg btn-skyblue">@lang('admin.submit')</button>
                                        <a href="{{ action('LanguageController@index') }}"><button type="button" class="btn-lg btn-skyblue">@lang('admin.back')</button></a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                
        </div>
    </aside>
      
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
