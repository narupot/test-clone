@extends('layouts/admin/default')

@section('title')
    @lang('seo.list_seo_global')
@stop

@section('header_styles')

    <!--page level css -->
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css"/>
    <link href="{{ asset('assets/css/pages/tables.css') }}" rel="stylesheet" type="text/css" />
    <!-- end of page level css -->
    <script type="text/javascript">
        function _toastrMessage(status, message) {
            try {
                Command: toastr[status](message);
            }
            catch (err) {
                console.log;
            };
        };

    </script>
@stop

@section('content')
     <div class="content">
       @if(Session::has('succMsg'))    
            <script type="text/javascript">               
                _toastrMessage('success', "{{ Session::get('succMsg') }}");    
            </script>                              
            @endif
            @if(Session::has('errorMsg'))
            <script type="text/javascript">               
                _toastrMessage('error', "{{ Session::get('errorMsg') }}");    
            </script>    
        @endif

        <div class="header-title">
            <h1 class="title">@lang('seo.seo_template_listing')
            </h1>
             @if($permission_arr['add'] === true)
                    <div class="float-right">
                       <a class="float-right btn btn-primary" href="{{ action('Admin\SEO\SeoGlobalController@create') }}"> @lang('seo.add_seo_template')
                       </a>
                    </div>
             @endif
        </div>
        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config','seoglobal','list')!!}
                </ul>
            </div>
            <table class="table table-bordered " id="table">
                <thead>
                    <tr class="filters">
                        <th>@lang('seo.sno')</th>
                        <th>@lang('seo.name')</th>
                        <th>@lang('seo.use_for')</th>
                        <!--th>Code</th-->
                        <th>@lang('seo.status')</th>
                        <th>@lang('seo.created_at')</th>
                        <th>@lang('seo.action')</th>
                    </tr>
                </thead>
                <tbody>

                @foreach ($results as $key => $result)

                    <tr>
                        <td>{{ ++$key }}</td>
                       
                        <td>{{ $result->title }}</td>
                        <!--td>{{ $result->slug }}</td-->
                         @if($result->type == '2')
                          <td>@lang('seo.others')</td>
                       @else
                          <td>@lang('seo.products')</td>
                       @endif
                     @if($result->status == '2')
                          <td><span class=" inactive-btn">@lang('seo.inactive')</span></td>
                       @else
                          <td><span class="active-btn">@lang('seo.active')</span></td>
                       @endif

                        <td>{{ getDateFormat($result->created_at, '1') }}</td>
                        
                        
                        <td>
                        @if($permission_arr['edit'] === true)
                            <a class="btn-grey btn-primary" href="{{ action('Admin\SEO\SeoGlobalController@edit', $result->id) }}">
                               @lang('seo.edit')
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
