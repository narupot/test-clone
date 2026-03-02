@extends('layouts/admin/default')

@section('title')
    @lang('cms.block_list')
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
        @if(Session::has('errorMsg'))
        <script type="text/javascript">               
            _toastrMessage('error', "{{ Session::get('errorMsg') }}");    
        </script>    
        @endif 
        <div class="header-title">
            <h1 class="title">@lang('cms.embeded_list')</h1>
            @if($permission_arr['add'] === true)
                <div class="float-right">
                    <a class="btn btn-primary" href="{{ action('Admin\Config\CssJsEmbededController@create') }}">@lang('common.create_new')</a> 
                </div>
            @endif
        </div> 
               
        <!-- Main content -->         
           
        <div class="content-wrap ">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('embeded','embeded','list')!!}
                </ul>
            </div>
            
            <div class="tab-content listing-tab">
                <div class="tab-pane fade active show" id="customBlock" role="tabpanel" aria-labelledby="profile-tab">
                <table class="table table-bordered " id="table">
                    <thead>
                        <tr class="filters">
                            <th>@lang('common.sno')</th>
                            <th>@lang('cms.title')</th>
                            <th>@lang('cms.custom_url')</th>
                            <th>@lang('cms.embeded_css')</th>
                            <th>@lang('common.created_at')</th>
                            <th>@lang('common.last_updated')</th>
                            <!-- <th>@lang('common.status')</th> -->
                            <th>@lang('common.actions')</th>
                        </tr>
                    </thead>
                    <tbody>
                    @if($results)
                    @foreach ($results as $keyes => $data)
                        <tr  @if($data['is_system']=='0') style="background-color: #ffffff;" @endif >
                            <td>{{ ++$keyes }}</td>
                            <td>{{ $data['title'] }}</td>
                            <td>{{ $data['custom_url'] }}</td>
                            <td>{{ $data['embeded_css'] }}</td>
                            @if($data['created_at'])
                                <td>{{ $data['created_at'] }}</td>
                                @else
                                    <td>-</td>
                            @endif
                            @if($data['updated_at'])
                                <td>{{ $data['updated_at'] }}</td>
                                @else
                                   <td>-</td>
                            @endif
                            <td>
                                @if($permission_arr['edit'] === true)
                                    <a class="link-primary" href="{{ action('Admin\Config\CssJsEmbededController@edit', $data['id']) }}">@lang('common.edit')</a>
                                @endif

                                @if($permission_arr['delete'] === true)
                                <span class="line"></span>                              
                                    
                                    <a class="link-primary" href="javascript:;" onclick="deleteRecord('{{ action('Admin\Config\CssJsEmbededController@destroy', $data['id']) }}')" data-toggle="modal">@lang('common.delete')
                                    </a>
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
        
        <div id="delete_record" class="modal fade" role="dialog">
            <form id="delete_record_frm" method="post" action=""> 
                <div class="modal-dialog">
                    {{ csrf_field() }}
                    {{ method_field('DELETE') }}   
                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">@lang('admin_common.confirm')</h4>
                        </div>
                        <div class="modal-body">
                            <p>@lang('admin_common.do_you_realy_want_to_delete_this_record')</p>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-danger">@lang('admin_common.yes')</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('admin_common.no')</button>
                        </div>
                    </div>
                </div>
            </form>
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
    
    function deleteRecord(delete_url) {   
        swal({
        title: "@lang('admin_common.confirm')",
        text: "@lang('admin_common.do_you_realy_want_to_delete_this_record')",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#DD6B55',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: "No, cancel it!"
     }).then(
           function () { $('#delete_record_frm').attr('action', delete_url);
                         $('#delete_record_frm').submit();
            },
           function () { return false; });
    }
    
    </script>
    <!-- end of page level js -->
    
@stop
