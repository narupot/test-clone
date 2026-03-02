@extends('layouts/admin/default')

@section('title')
    @lang('translation.language_module_list')
@stop

@section('header_styles')
    <!--page level css -->
    <link rel="stylesheet" type="text/css" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css" />
    <!-- end of page level css -->
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
        <!-- Main content -->
        <div class="header-title">
            <h1 class="title">@lang('translation.language_module_list')</h1>
            <span class="float-right">
                <a class="btn btn-primary" href="{{ action('Admin\Translation\TranslationModuleController@create') }}">@lang('translation.add_module')</a>
            </span> 
        </div>
        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config','translation-module','list')!!}
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
            <table class="table table-bordered" id="table">
                <thead>
                    <tr class="filters">
                        <th>@lang('common.slno')</th>
                        <th>@lang('translation.module_name')</th>
                        <th>@lang('translation.lang_file_name')</th>
                        <th>@lang('common.status')</th>
                        <th>@lang('common.created_at')</th>
                        <th>@lang('common.actions')</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($results as $key => $result)
                
                    <tr>
                        <td>{{ ++$key }}</td>
                        <td>{{ $result->module_name }}</td>
                        <td>{{ $result->lang_file_name }}</td>

                        @if($result->status == '1')
                            <td>@lang('common.active')</td>
                        @else
                            <td>@lang('common.inactive')</td>
                        @endif

                        <td>{{ getDateFormat($result->created_at, '1') }}</td>

                        <td>
                            <a class="btn btn-dark" href="{{ action('Admin\Translation\TranslationModuleController@edit', $result->id) }}">
                               @lang('common.edit')
                            </a>
                            <a class="btn btn-delete btn-danger" href="{{ action('Admin\Translation\TranslationModuleController@deleteModule', $result->id) }}" onclick="return confirm('{{ Lang::get('common.are_you_sure_to_delete_this_record') }}')">
                               @lang('common.delete')
                            </a>  
                        </td>
                    </tr>                            
                @endforeach  
                </tbody>
            </table>
        </div>
    </div>
        
@stop

@section('footer_scripts')

    <!-- begining of page level js -->
    <script src="{{ Config('constants.admin_js_url') }}dataTables.bootstrap.js"></script>

    <script>
    $(document).ready(function() {
        $('#table').dataTable();
    });

    $('body').on('change','#module_type',function(){
        var type = $(this).val();
        var action = "{{ action('Admin\Translation\TranslationModuleController@index') }}";
        window.location = action+'?type='+type;
    });
    </script>
    <!-- end of page level js -->
    
@stop
