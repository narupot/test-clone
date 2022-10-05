@extends('layouts/admin/default')

@section('title')
    @lang('cms.page_list')
@stop

@section('header_styles')

    <!--page level css -->

    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css"/>
    <!-- end of page level css -->
    
@stop

@section('content')
    <div class="content">
        <div class="header-title">
            <h1 class="title">@lang('cms.page_list')</h1>
             @if($permission_arr['add'] === true)
            <div class="float-right">
                <a class="btn btn-primary" href="{{ action('Admin\Page\StaticPageController@create') }}">@lang('common.create_new_label')</a> 
            </div>          
            @endif
        </div>
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
        <div class="content-wrap ">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('cms', 'cms', 'list')!!}
                </ul>
            </div>
            <ul class="nav nav-tabs listing-nav-tabs" id="myTab" role="tablist">
                <li class="nav-item ">
                    <a class="nav-link active" id="profile-tab" data-toggle="tab" href="#customPage" role="tab">@lang('cms.custom_block')</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " id="home-tab" data-toggle="tab" href="#staticPage" role="tab">@lang('cms.static_block')</a>
                </li>
                
            </ul>
            <div class="tab-content listing-tab">
                <div class="tab-pane fade" id="staticPage">
                    <table class="table table-bordered " id="table">
                        <thead>
                            <tr class="filters">
                                <th>@lang('common.sno')</th>
                                <th>@lang('cms.url_key')</th>
                                <th>@lang('common.created_at')</th>
                                <th>@lang('common.last_updated')</th>
                                <th>@lang('common.status')</th>
                                <th>@lang('common.actions')</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($page_dtls as $key => $page_dtl)
                        
                            <tr @if($page_dtl['is_system']=='1') style="background-color: #EFF0F1;" @endif  >
                                <td>{{ ++$key }}</td>
                                <td>{{ $page_dtl['url'] }}</td>

                                @if($page_dtl['created_at'])
                                <td>{{ $page_dtl['created_at'] }}</td>
                                @else
                                   <td>-</td>
                                @endif
                                
                                @if($page_dtl['updated_at'])
                                <td>{{ $page_dtl['updated_at'] }}</td>
                                @else
                                   <td>-</td>
                                @endif

                                <td>
                                {{ $page_dtl['status'] }}
                                </td>
                                <td>
                                    <a class="btn btn-outline-primary btn-back" target="_blank" href="/page/{{$page_dtl['url']}}">@lang('common.view')</a>
                                    @if($permission_arr['edit'] === true)
                                        <a class="btn btn-dark" href="{{ action('Admin\Page\StaticPageController@edit', $page_dtl['id']) }}">@lang('common.edit')</a>
                                    @endif

                                    @if($permission_arr['delete'] === true) 
                                    <form method="post" action="{{ action('Admin\Page\StaticPageController@destroy', $page_dtl['id']) }}" onsubmit="return confirm('Are you sure to delete this record ?');" class="inblock"> 
                                        {{ csrf_field() }}
                                        {{ method_field('DELETE') }}   
                                        @if($page_dtl['is_system']=='0')<a class="btn btn-danger" onclick="$(this).closest('form').submit();" data-toggle="modal">
                                           @lang('common.delete')
                                        </a>
                                        @endif
                                    </form>
                                    @endif
                                </td>
                            </tr>
                         @endforeach  
                         </tbody>
                    </table>
                </div>
                <div class="tab-pane fade active show" id="customPage" role="tabpanel">
                    <table class="table table-bordered " id="table1">
                        <thead>
                            <tr class="filters">
                                <th>@lang('common.sno')</th>
                                <th>@lang('cms.url_key')</th>
                                <th>@lang('cms.title')</th>
                                <th>@lang('common.created_at')</th>
                                <th>@lang('common.last_updated')</th>
                                <th>@lang('common.status')</th>
                                <th>@lang('common.actions')</th>
                            </tr>
                        </thead>
                        <tbody>
                        @if($page_cus_dtl)    
                            @foreach ($page_cus_dtl as $keyes => $pagecus_dtl)
                            <tr @if($pagecus_dtl['is_system']=='0') style="background-color: #ffffff;" @endif  >
                                <td>{{ ++$keyes }}</td>
                                <td>{{ $pagecus_dtl['url'] }}</td>
                                <td>{{ $pagecus_dtl['title'] }}</td>

                                @if($pagecus_dtl['created_at'])
                                <td>{{ $pagecus_dtl['created_at'] }}</td>
                                @else
                                   <td>-</td>
                                @endif

                                @if($pagecus_dtl['updated_at'])
                                <td>{{ $pagecus_dtl['updated_at'] }}</td>
                                @else
                                   <td>-</td>
                                @endif
                                <!--<td>{{ $pagecus_dtl['updated_at'] }}</td>-->
                                <td>
                                <a id="status_{{ $pagecus_dtl['id'] }}" href="javascript:void(0);" onclick="callForAjax('{{ action('Admin\Page\StaticPageController@changeStatus', $pagecus_dtl['id']) }}', 'status_{{ $pagecus_dtl['id'] }}')" class="{{($pagecus_dtl['status'] =='Active')?'status active':'status inactive'}}">
                                {{ $pagecus_dtl['status'] }}
                                </a>
                                </td>
                                <td>
                                    <a class="btn btn-outline-primary" target="_blank" href="/page/{{$pagecus_dtl['url']}}">
                                        @lang('common.view')
                                    </a>
                                    @if($permission_arr['edit'] === true)
                                        <a class="btn btn-dark" href="{{ action('Admin\Page\StaticPageController@edit', $pagecus_dtl['id']) }}">@lang('common.edit')</a>
                                    @endif

                                    @if($permission_arr['delete'] === true) 
                                    <form method="post" action="{{ action('Admin\Page\StaticPageController@destroy', $pagecus_dtl['id']) }}" onsubmit="return confirm('Are you sure to delete this record ?');" class="inblock"> 
                                        {{ csrf_field() }}
                                        {{ method_field('DELETE') }}   
                                        @if($pagecus_dtl['is_system']=='0')
                                        <a class="btn btn-delete btn-danger" onclick="$(this).closest('form').submit();" data-toggle="modal">
                                           @lang('common.delete')
                                        </a>
                                        @endif
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        @endif  
                         </tbody>
                    </table>
                </div>
            </div>

            
        </div>
    </div>
@stop

@section('footer_scripts')

    <!-- begining of page level js -->

    <script src="{{ Config('constants.admin_js_url') }}jquery.dataTables.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}dataTables.bootstrap.js"></script>
    <script>
    $(document).ready(function() {
        $('#table').dataTable();
        $('#table1').dataTable();
    });
    </script>
    <!-- end of page level js -->
    
@stop
