@extends('layouts/admin/default')

@section('title')
    @lang('cms.banner_image_list') - {{getSiteName()}} 
@stop

@section('header_styles')

    <!--page level css -->
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css"/>
    <!-- end of page level css -->
    
@stop

@section('content')
  <div class="content">
    @if(Session::has('succMsg'))    
        <script type="text/javascript">               
            _toastrMessage('success', "{{ Session::get('succMsg') }}");    
        </script>                              
    @endif 
    <div class="header-title">
        <h1 class="title">@lang('cms.banner_image_list')</h1>
         @if($permission_arr['add'] === true)
        <a class="btn float-right btn-create" href="{{ action('Admin\Banner\BannerController@create') }}"> @lang('common.create_new')</a> 
        @endif
    </div>
        
    <!-- Main content -->         
    <div class="content-wrap">
      <div class="breadcrumb">
            <ul class="bredcrumb-menu">
                {!!getBreadcrumbAdmin('block','banner','list')!!}
            </ul>
        </div> 
      <table class="table table-bordered " id="table">
          <thead>
              <tr class="filters">
                  <th>@lang('common.sno')</th>
                  <th>@lang('cms.title')</th>
                  <th>@lang('cms.group_name')</th>
                  <th>@lang('cms.banner_image')</th>
                  <th>@lang('common.status')</th>
                  <th>@lang('common.created_at')</th>
                  <th>@lang('common.actions')</th>
              </tr>
          </thead>
          <tbody>

          @foreach ($results as $key => $result)
              <tr>
                <td>{{ ++$key }}</td>
                <td>{{ $result->admin_title ?? '' }}</td>
                <td>{{ isset($groups[$result->group_id]) ? $groups[$result->group_id] : '' }}</td>
                <td>
                  <a  href="#" class="imageViewer" data-toggle="modal" data-target="#imageViewer_{{$key}}"><img src="{{ Config::get('constants.banner_url').$result->banner_image }}" width="150"></a>

                  <div id="imageViewer_{{$key}}" class="modal fade" role="dialog">
                    <div class="modal-dialog free-size">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h2 class="modal-title"></h2>
                          <span class="fas fa-times" data-dismiss="modal"></span>
                        </div>
                        <div class="modal-body">
                          <img src="{{ Config::get('constants.banner_url').$result->banner_image }}">
                        </div>
                      </div>
                    </div>
                  </div>

                </td>
                @if($result->status == '2')
                  <td><span class=" inactive-btn">@lang('common.inactive')</span></td>
                @else
                  <td><span class="active-btn">@lang('common.active')</span></td>
                @endif
                <td>{{ getDateFormat($result->created_at, '1') }}</td>
                <td>
                @if($permission_arr['edit'] === true)
                  <a class="btn btn-dark" href="{{ action('Admin\Banner\BannerController@edit', $result->id) }}">
                      <i class="livicon" data-name="pen" data-loop="true" data-color="#000" data-hovercolor="black" data-size="14"></i>@lang('common.edit')
                  </a>
                @endif
                @if($permission_arr['delete'] === true) 
                  {!! Form::open(['style' => 'display: inline-block;', 'method' => 'DELETE', 'onsubmit' => 'return confirm("Are sure delete this data.");',  'url' => action('Admin\Banner\BannerController@update', $result->id)]) !!}
                    <button class=" black btn-grey btn-danger" type="submit">
                      <i class="livicon" data-name="trash" data-loop="true" data-color="#000" data-hovercolor="black" data-size="14"></i>@lang('common.delete')
                    </button>
                  {!! Form::close() !!}
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

    <script src="{{ Config('constants.admin_js_url') }}jquery.dataTables.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}dataTables.bootstrap.js"></script>
    <script>
    $(document).ready(function() {
        $('#table').dataTable();
    });
    </script>
    <!-- end of page level js -->
    
@stop
