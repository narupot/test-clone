@extends('layouts/admin/default')

@section('title')
    @lang('admin.language_list')
@stop

@section('header_styles')

    <!--page level css -->
    <link rel="stylesheet" type="text/css" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css" />
    <!-- end of page level css -->
    
@stop

@section('content')

    <!-- Right side column. Contains the navbar and content of the page -->
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
            <h1 class="title">@lang('admin.language_list')</h1>
            <div class="float-right">
                <a class="btn btn-primary" href="{{ action('Admin\Config\SystemConfigController@show', 'setting') }}"> @lang('common.back')</a>
            </div>            
            @if($permission_arr['add'] === true)
            <!--<span class="col-sm-3 float-right">
                <a class="float-right btn btn-skyblue" href="{{ action('LanguageController@create') }}">
                   Add Language
                </a>
            </span>-->
            @endif
        </div>
        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config','language','list')!!}
                </ul>
            </div>                                             
            <table class="table table-bordered " id="table">
                <thead>
                    <tr class="filters">
                        <th>@lang('common.slno')</th>
                        <th>@lang('admin.language_name')</th>
                        <th>@lang('common.code')</th>
                        <th>@lang('common.flag')</th>
                        <th>@lang('common.default')</th>
                        <th>@lang('common.created_at')</th>
                        <th>@lang('common.updated_at')</th>
                        <th>@lang('common.actions')</th>
                    </tr>
                </thead>
                <tbody>

                @foreach ($language_list as $key => $languageDtl)
                
                    <tr>
                        <td>{{ ++$key }}</td>
                        <td>{{ $languageDtl->languageName }}</td>
                        <td>{{ $languageDtl->languageCode }}</td>
                        <td><img title="{{ $languageDtl->languageName }}" src="{{ Config::get('constants.language_url').$languageDtl->languageFlag }}"></td>
                       
                        @if($languageDtl->isDefault == '1')
                            <td><span style="color:green">@lang('common.yes')</span></td>
                        @else
                            <td>@lang('common.no')</td>
                        @endif
                        
                        <td>{{ getDateFormat($languageDtl->created_at, '1') }}</td>
                        <td>{{ getDateFormat($languageDtl->updated_at, '1') }}</td>
                        <td>
                            @if($permission_arr['edit'] === true)
                            <a class="btn btn-dark" href="{{ action('Admin\Config\LanguageController@edit', $languageDtl->id) }}">
                               @lang('common.edit')
                            </a> 
                            @endif
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
        var table =  $('table.table').DataTable();
    });
    </script>
    <!-- end of page level js -->
@stop
