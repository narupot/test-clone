@extends('layouts/admin/default')

@section('title')
    @lang('admin.gender_list')
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
        @endif      
        <!-- Main content -->
        <div class="header-title">
            <h1 class="title">@lang('admin.gender_list')</h1>
            @if($permission_arr['add'] === true)
            <span class="float-right">
                <a class="btn btn-secondary" href="{{ action('Admin\Gender\GenderController@create') }}">
                    @lang('admin.add_gender')
                </a>
            </span>
            @endif
        </div>
        <div class="content-wrap">                                                
            <table class="table table-bordered">
                <thead>
                    <tr class="filters">
                        <th>@lang('common.slno')</th>
                        <th>@lang('common.gender')</th>                           
                        <th>@lang('common.status')</th>
                        <th>@lang('common.actions')</th>
                    </tr>
                </thead>
                <tbody>         
                @foreach($genderlist as $key => $gender)
                    <tr>
                        <td>{{ ++$key }}</td>
                        <td>{{ $gender['gender_name'] }}</td>
                        <td>
                        <a id="status_{{ $gender['id'] }}" href="javascript:void(0);" onclick="callForAjax('{{ action('Admin\Gender\GenderController@changeStatus', $gender['id']) }}', 'status_{{ $gender['id'] }}')" class="{{($gender->status == 1)?'status active':'status inactive'}}">
                        {{ $gender['status'] }}
                        </a>
                        </td>
                        <td>    
                        @if($permission_arr['edit'] === true)
                            <a class="btn btn-default" href="{{ action('Admin\Gender\GenderController@edit', $gender['id']) }}">
                              @lang('common.edit')
                            </a>
                        @endif                                        
                        @if($permission_arr['delete'] === true) 
                            <form method="post" action="{{ action('Admin\Gender\GenderController@destroy', $gender['id']) }}" onsubmit="return confirm('Are you sure to delete this record ?');" class="inblock"> 
                                {{ csrf_field() }}
                                {{ method_field('DELETE') }}                             
                                <a class="btn btn-delete btn-danger" onclick="$(this).closest('form').submit();" data-toggle="modal">
                                   @lang('common.delete')
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
