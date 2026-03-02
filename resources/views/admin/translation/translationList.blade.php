@extends('layouts/admin/default')

@section('title')
    @lang('translation.general_translation')
@stop

@section('header_styles')
    <!--page level css -->
    <link rel="stylesheet" type="text/css" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css" />
    <link href="{{ Config('constants.css_url') }}sweetalert2.min.css" rel="stylesheet">
    <!-- end of page level css -->
@stop

@section('content')

    <div class="content">
                 
        <!-- Main content -->
        <div class="header-title clearfix">
            <h1 class="title">@lang('translation.general_translation')</h1> 
        </div>
        <div class="content-wrap">
          <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config','translation','list')!!}
                </ul>
            </div>
            <div class="form-group text-center">
                Module Type : 
                <select id="module_type">
                    <option value="0">@lang('admin_common.all')</option>
                    <option value="1" @if($module_type == '1') selected="selected" @endif>@lang('admin_translation.front')</option>
                    <option value="2" @if($module_type == '2') selected="selected" @endif>@lang('admin_translation.admin')</option>
                </select>
            </div>                                    
            <table class="table table-bordered " id="table">
                <thead>
                    <tr class="filters">
                        <th>@lang('translation.module')</th>
                        <th>@lang('translation.source')</th>
                        <th>@lang('translation.translation')</th>
                        <th>@lang('common.remark')</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($results as $key => $result)
                    <tr>
                        <td>{{$result->module_name}}</td>
                        <td><a href="{{ action('Admin\Translation\TranslationController@addsource')}}/{{$result->id}}">@lang('common.view')</a></td>
                        <td>
                        @foreach($languages as $language)
                            <a href="{{ action('Admin\Translation\TranslationController@addsourcevalue', [$result->id, $language->id]) }}">
                                {{$language->languageName}} 
                            </a>&nbsp; 
                            ({{ isset($percentcal[$result->id][$language->id])?$percentcal[$result->id][$language->id]:'0'}})% 
                            - @lang('common.last_pdated'): 
                            {{ isset($lastUpdated[$result->id][$language->id]) ? $lastUpdated[$result->id][$language->id] : Lang::get('translation.not_yet') }} 
                              &nbsp; 
                              <a href="{{ action('Admin\Translation\TranslationController@exportSource',[$result->id, $language->id])}}">
                                <button type="button" class="btn btn-outline-primary" onclick="expotClick('{{ $result->id.'_'.$language->id }}')">Export</button>
                              </a> 
                              &nbsp;
                              <form id="form_{{ $result->id.'_'.$language->id }}" method="post" action="{{ action('Admin\Translation\TranslationController@importSource')}}" enctype="multipart/form-data" class="inblock">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="module_id" value="{{ $result->id }}">
                                    <input type="hidden" name="lang_id" value="{{ $language->id }}">
                                    <span class="btn btn-outline-primary btn-file">
                                        <span class="fileinput-exists">Import</span>
                                        <input type="file" id="file_{{ $result->id.'_'.$language->id }}" name="import_file" onclick="return exportFile(this)" data-attr="export_false"  onchange="importFile('{{$result->id.'_'.$language->id}}');">
                                    </span>
                                    <span id="import_span_{{$result->id.'_'.$language->id}}"></span>
                              </form>
                              <br/><br/>
                        @endforeach
                        </td>
                        <td>{{$result->remark}}</td>
                    </tr> 
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
        
@stop

@section('footer_scripts')

    <!-- begining of page level js -->
    <script type="text/javascript" src="{{ Config('constants.admin_js_url') }}jquery.dataTables.js"></script>
    <script type="text/javascript" src="{{ Config('constants.admin_js_url') }}dataTables.bootstrap.js"></script>
    <script type="text/javascript" src="{{ Config('constants.js_url') }}SweetAlert.min.js"></script>    
    <script>
    $(document).ready(function() {
        $('#table').dataTable();
    });

    function exportFile(self) {
       var dataAttr = $(self).attr('data-attr');
       if(dataAttr==='export_true'){
        return true;
      }else {
        swal({
          title: 'Warning!',
          text: "@lang('translation.please_export_the_file_for_backup_before_importing_the_file')",
          type: 'warning',
          showCancelButton: false,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: "@lang('common.ok')"         
        }).then(function () {
            return false;
        });
        return false;
       }
    };
    function importFile(loader_id){

      var loader_url = $('#small_loader_path').val();
      //alert(loader_id+'===='+loader_url);return;

      $('#import_span_'+loader_id).html('<img src="'+loader_url+'">');
      $('#form_'+loader_id).submit();
    };
    function expotClick(file_id){
      $('#file_'+file_id).attr('data-attr','export_true');
    }

    $('body').on('change','#module_type',function(){
        var type = $(this).val();
        var action = "{{ action('Admin\Translation\TranslationController@index') }}";
        window.location = action+'?type='+type;
    });    
    </script>
    <!-- end of page level js -->
    
@stop
