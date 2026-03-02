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
        <div class="header-title">
            <h1 class="title">@lang('common.revision')</h1>
        </div>
        @if(Session::has('succMsg'))
        <div class="alert alert-success alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong>@lang('common.success'):</strong> {{ Session::get('succMsg') }}
        </div>
        @elseif(Session::has('errorMsg'))
        <div class="alert alert-danger alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <strong>@lang('common.error'):</strong> {{ Session::get('errorMsg') }}
        </div>    
        @endif 
               
        <!-- Main content -->         
           
        <div class="content-wrap "> 
            <div class="tab-content listing-tab">
              <div class="tab-pane fade active show" id="customBlock" role="tabpanel" aria-labelledby="profile-tab">
                <table class="table table-bordered " id="table">
                    <thead>
                        <tr class="filters">
                            <th>@lang('common.sno')</th>
                            <th>@lang('cms.title')</th>
                            <th>@lang('cms.description')</th>
                            <th>@lang('common.last_updated')</th>
                            <th>@lang('common.actions')</th>
                        </tr>
                    </thead>
                    <tbody>
                    @if($revisions)
                    @foreach ($revisions as $keyes => $pagecus_dtl)
                        <tr  @if($pagecus_dtl['default_item']=='0') style="background-color: #ffffff;" @endif >
                            <td>{{ ++$keyes }}</td>
                            <td>{{ $pagecus_dtl['static_block_title'] }}</td>
                            <td>{{ $pagecus_dtl['static_block_desc'] }}</td>
                            @if($pagecus_dtl['updated_at'])
                                <td>{{ $pagecus_dtl['updated_at'] }}</td>
                                @else
                                   <td>-</td>
                            @endif
                            
                            <td>
                                
                                    <a class="btn-grey" href="{{ action('Admin\Block\StaticBlockController@restoreblockrevision',[$block_id,$pagecus_dtl['revision']]) }}">@lang('common.restore_this_revision')</a>
                                   
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
