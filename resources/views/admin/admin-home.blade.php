@extends('layouts/admin/default')
@section('title')
    @lang('admin.dashboard')
@stop
@section('header_styles')
<link rel="stylesheet" type="text/css" href="{{ Config('constants.admin_css_url') }}flatpickr.min.css"/>
@stop
@section('content')
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
        <div class="header-title">
            <h1 class="title">@lang('admin.dashboard')</h1>
            <div class="float-right mt-15">
            @if(getConfigValue('IS_DEMO_DATA')==1)
                <a onclick="confirmClean($(this));" href="javascript:void(0);">@lang('admin.go_live')</a>
            @endif
            </div>            
        </div>
        <div class="content-wrap clearfix">
            <div class="dashboard-page">
                <h3>@lang('common.hi') {{ Auth::guard('admin_user')->user()->first_name.' '.Auth::guard('admin_user')->user()->last_name }}</h3>
                
                <time class="text-warning">{{ date('l') }} : {{ getDateFormat(date('Y-m-d'),3) }}</time>
            </div>
            
        </div>
    </div>
@stop

@section('footer_scripts')
<script src="{{ Config('constants.admin_js_url') }}lang/{{ session('lang_code') }}.lang.js"></script>
<script type="text/javascript">

    function confirmClean(obj) {
        swal({
            text: langMsg.are_you_sure_to_clean_damo_data_and_go_to_live_mode,
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: langMsg.yes
        }).then(function () {    
            obj.hide();
            showHideLoaderAdmin('showLoader');
            window.location = "{{action('Admin\CleanDatabase\CleanDatabaseController@cleanDemoData')}}";
        });
        return false;
    }
</script> 
<script src="{{ Config('constants.admin_js_url') }}flatpickr.min.js"></script>
<script>
    var dismissurl = "{{ action('Admin\AdminHomeController@dismiss') }}";
    $(document).ready(function() {

        // Date time Pickers
        $(".date-select").flatpickr();

        jQuery('.btn_dismiss').click(function(e){
            if(confirm('Are u sure want to dismiss ?')){
                var _this = jQuery(this);
                var id = jQuery(this).data('val');
                var data = {id:id};
                callAjax(dismissurl,"post",data,function(result){
                    if(result.status=='success'){
                        _this.hide();
                    }else{
                        alert('error');
                    }
                })
            }
        })

        jQuery('#dismiss_all').click(function(e){
            if(confirm('Are u sure want to dismiss all ?')){
                var _this = jQuery(this);
                var data = {id:'all'};
                callAjax(dismissurl,"post",data,function(result){
                    if(result.status=='success'){
                        $('#you_have_to_create').hide();
                    }else{
                        alert('error');
                    }
                })
            }
        })
        
    });
</script>   
@stop
