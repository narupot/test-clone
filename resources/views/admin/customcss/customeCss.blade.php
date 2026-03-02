@extends('layouts/admin/default')

@section('title')
    CustomCSS 
@stop

@section('header_styles')
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}codemirror.css" />
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}foldgutter.css" />
    
<style type="text/css">
    #csscode {
        overflow: hidden;
    }
    .CodeMirror-scroll, .CodeMirror {
        height: auto;
        overflow-x: hidden;
        overflow-y: auto;
    }
</style>
@stop

@section('content')
    <div class="content">

        @if(Session::has('succMsg'))
        <div class="alert alert-success alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong>@lang('common.success'):</strong> {{ Session::get('succMsg') }}
        </div>
        @endif 

        <form id="customecss" action="javascript:void(0);" method="post" class="form-horizontal form-bordered">

            {{ csrf_field() }}

            <div class="header-title">
                <h1 class="title">Custom Css</h1>
                <div class="pull-right btn-groups">                    
                    <button type="submit" name="submit_type" value="submit" class="btn-effect-ripple btn btn-primary">@lang('common.save')</button>
                    @if($revision>1)
                        <a class="btn btn-info" id="revision" href="{{ action('Admin\CustomCss\CustomCssController@cssrevision') }}">Revision</a>
                    @endif
                </div>
            </div>  

            <div class="content-wrap">
                 <div class="breadcrumb">
                    <ul class="bredcrumb-menu">
                        <li>Custom Css</li>
                    </ul>
                </div>
                <div class="form-group">
                    <label for="form-text-input">CustomCSS <i class="strick">*</i> </label>
                    @if($last_update)
                    <div class="mb-2"><span style="color: red;">Last Updated :{{ getDateFormat($last_update->updated_at)}},By-{{getUser($last_update->updated_by)}}</span></div>
                    @endif
                    <textarea id="csscode" name="css" style="min-height: 400px;">{{ $css}}</textarea> 
                </div>
            </div>
        </form>
    </div>
      
@stop

@section('footer_scripts')
 
<!-- begining of page level js -->


<script src="{{ Config('constants.admin_js_url') }}codemirror.js" type="text/javascript"></script>
<script src="{{ Config('constants.admin_js_url') }}foldgutter.js" type="text/javascript"></script>
<script src="{{ Config('constants.admin_js_url') }}brace-fold.js" type="text/javascript"></script>
<script src="{{ Config('constants.admin_js_url') }}foldcode.js" type="text/javascript"></script>
<script src="{{ Config('constants.admin_js_url') }}matchbrackets.js" type="text/javascript"></script>
<script src="{{ Config('constants.admin_js_url') }}xml.js" type="text/javascript"></script>
<script src="{{ Config('constants.admin_js_url') }}codecss.js" type="text/javascript"></script>


<script src="{{ Config('constants.js_url') }}jquery.validate.min.js" type="text/javascript"></script>



<!-- end of page level js --> 

@include('includes.froalaeditor_dependencies')
<script type="text/javascript">
   var csrftoken = window.Laravel.csrfToken;
</script>
<script type="text/javascript">
    window.onload = function() {
        var cssMode = document.getElementById("csscode");
          window.editor = CodeMirror.fromTextArea(cssMode, {
            mode: "css",
            lineNumbers: true,
            lineWrapping: true,
            extraKeys: {"Ctrl-Q": function(cm){ cm.foldCode(cm.getCursor()); }},
            foldGutter: true,
            gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"]
          });
          editor.foldCode(CodeMirror.Pos(13, 0));
          editor.getWrapperElement().style["font-size"] = 15+"px";
          editor.refresh();
      }
</script>
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    (function($){
        var rules= {};            
            rules['full_name'] = 'required';          
        var messages = {};
            messages['full_name'] = "@lang('common.name_is_required')";
        var csrftoken = window.Laravel.csrfToken;       
        $("#customecss").validate({
            rules: rules,
            messages: messages,
            submitHandler : function(from, evt){          
                var formData = new FormData($('#customecss')[0]);
                formData.set('css', editor.getValue());
                $.ajax({
                    type: "POST",
                    url : "{{ action('Admin\AdminHomeController@customeCssSet') }}",
                    enctype: 'multipart/form-data',
                    processData: false,  // Important!
                    contentType: false,
                    cache: false,
                    data : formData,
                     beforeSend: function(){
                        showHideLoader('show');
                    },
                    success : function(response){
                        $('p[class="error"]').html('');
                        if(response.status=='fail'){
                            //$("#alreadyregister").css("display", "block");
                            showHideLoader('hide');
                            return false;
                        }                   
                        if(response && response.status === 'success')
                            {
                                showHideLoader('hide');
                                swal({
                                    title: "Wow!",
                                    text: response.message,
                                    type: "success"
                                }).then(function() {
                                    //location.reload();
                                });
                            }
                            
                    },
                });
            },
        });
    })(jQuery);
</script>
@stop
