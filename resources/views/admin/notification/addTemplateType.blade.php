@extends('layouts/admin/default')

@section('title')
    @lang('admin.add_mail_template')
@stop

@section('header_styles')
   
@stop

@section('content')

    <!-- Right side column. Contains the navbar and content of the page -->
    <div class="content">
        @if(Session::has('errorMsg'))
        <div class="alert alert-danger alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong>@lang('admin.error'):</strong> {{ Session::get('errorMsg') }}
        </div>
        @endif   
        <div class="header-title clearfix">
            <h1 class="title">@lang('admin.add_template_type')</h1>
        </div>     
        <div id="tab3" class="tab-pane content-wrap">
            <div class="containers">
                <div class="border">
                    <form id="MailTempForm" action="{{ action('MailTemplateController@store') }}" method="post" enctype="multipart/form-data" class="form-horizontal form-bordered">
                        {{ csrf_field() }}
                        <input type="hidden" name="mail_type_id" value="{{ $tempId }}">
                    <div class="row">
                       <label class="col-md-2 control-label" for="form-file-input">@lang('admin.type') *</label>
                     </div>


                        <div class="form-group striped-col">
                            
                           
                            @foreach($templateType as $key=>$value)
                               <div class="row">
                                 <label for="template_type">{{$value}}</label>
                                  <input type="checkbox" name="template_type[]" value="{{$key}}">
                                
                              </div>  

                            @endforeach
                              
                        </div>                                
                       
                    </form>
                </div>
            </div>
                
        </div>
    </div>
      
@stop

@section('footer_scripts')
 
    <!-- begining of page level js -->
    <script src="{{ asset('assets/js/pages/validateMailTemplate.js') }}" type="text/javascript"></script>
    <!-- end of page level js -->   
    
@stop
