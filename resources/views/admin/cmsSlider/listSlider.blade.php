@extends('layouts/admin/default')

@section('title')
    @lang('admin_slider.slider_list')
@stop

@section('header_styles')
    <!--page level css -->
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}global.css" />
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}style.css" />
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css"/>
    <!-- end of page level css -->
@stop

@section('header_left_menu_content')
   <div class="header-col no-border">
      <a href="javascript:void(0);"><span class="text-nwrap page-setting">@lang('admin_slider.slider_list')</span></a>
   </div>   
@stop

@section('content')
    <div class="content">
        @if(Session::has('succMsg'))
        <div class="alert alert-success alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong>@lang('admin_common.success'):</strong> {{ Session::get('succMsg') }}
        </div>
        @elseif(Session::has('errorMsg'))
        <div class="alert alert-danger alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <strong>@lang('admin_common.error'):</strong> {{ Session::get('errorMsg') }}
        </div>    
        @endif 
        <div class="header-title">
            <h1 class="title">@lang('admin_slider.slider_list')</h1>
             @if($permission_arr['add'] === true)
            <div class="float-right">
                <a class="btn-primary" href="{{ action('Admin\CmsSlider\CmsSliderController@create') }}">@lang('common.create_new')</a> 
            </div>          
            @endif
        </div>       
        <!-- Main content -->
        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('block','slider_listing','list')!!}
                </ul>
            </div>
            <div class="tab-pane fade active show" id="customPage" role="tabpanel">
                <table class="table table-bordered " id="table">
                    <thead>
                        <tr class="filters">
                            <th>@lang('admin_common.sno')</th>
                            <th>@lang('admin_common.type')</th>
                            <th>@lang('admin_common.name')</th>
                            <th>@lang('admin_common.title')</th>
                            <th>@lang('admin_common.created_at')</th>
                            <th>@lang('admin_common.last_updated')</th>
                            <th>@lang('admin_common.status')</th>
                            <th>@lang('admin_common.actions')</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($slider_dtl as $key => $dtl)
                        <tr>
                            <td>{{ ++$key }}</td>
                            <td>{{ ucfirst($dtl->type) }}</td>
                            <td>{{ $dtl->name }}</td>
                            <td>{!! $dtl->sliderdesc->title ?? '' !!}</td>
                            <td>{{ $dtl->created_at }}</td>
                            <td>{{ $dtl->updated_at }}</td>
                            <td>
                                <a id="status_{{ $dtl->id }}" href="javascript:void(0);" onclick="callForAjax('{{ action('Admin\CmsSlider\CmsSliderController@changeStatus', $dtl->id) }}', 'status_{{ $dtl->id }}')" class="{{($dtl->status == 1)?'status active':'status inactive'}}">
                                {{ ($dtl->status)?'Active':'Inactive' }}
                                </a>
                            </td>
                            <td class="text-nowrap">                            
                                @if($permission_arr['edit'] === true)
                                    <a class="link-primary" href="{{ action('Admin\CmsSlider\CmsSliderController@edit', $dtl->id) }}">@lang('admin_common.edit')</a>
                                @endif
                                <span class="line"></span>
                                @if($permission_arr['delete'] === true) 
                                <form method="post" action="{{ action('Admin\CmsSlider\CmsSliderController@destroy', $dtl->id) }}" onsubmit="return confirm('Are you sure to delete this record ?');" class="inblock"> 
                                    {{ csrf_field() }}
                                    {{ method_field('DELETE') }}   
                                        <a href="javascript:;" class="link-primary" onclick="$(this).closest('form').submit();" data-toggle="modal">
                                       @lang('admin_common.delete')
                                    </a>
                                    
                                </form>
                                 
                                @endif
                            </td>
                        </tr>
                     @endforeach  
                     </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('footer_scripts')

    <!-- begining of page level js -->

    <script src="{{ Config('constants.admin_js_url') }}jquery.dataTables.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}dataTables.bootstrap.js"></script>
    <script src="{{ Config('constants.js_url') }}clipboard.min.js"></script>
    <script src="{{ Config('constants.js_url') }}tooltips.js"></script>
    <script>
    $(document).ready(function() {
        $('#table').dataTable();
    });
    </script>
    <!-- end of page level js -->
@stop
