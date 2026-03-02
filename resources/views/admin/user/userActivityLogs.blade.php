<link rel="stylesheet" type="text/css" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css" />
<table id="example" class="table table-striped table-bordered" width="100%" cellspacing="0">
<thead>
    <tr>
        <th>@lang('admin_common.slno')</th>
        <th>@lang('admin_customer.log_section_name')</th>
        <th>@lang('admin_customer.log_action')</th>
        <th>@lang('admin_customer.log_action_detail')</th>
        <th>@lang('admin_customer.log_old_data')</th>
        <th>@lang('admin_customer.log_new_data')</th>
        <th>@lang('admin_customer.log_ip_address')</th>
        <th>@lang('admin_customer.log_created_at')</th>
    </tr>
</thead>
<tbody>
    @foreach($admin_activity_logs as $key => $data)
    <tr>
        <td>{{$key+1}}</td>
        <td>{{ucwords($data['module_name'])}}</td>
        <td>{{ucfirst($data['action_type'])}}</td>
        <td>{{$data['action_detail']}}</td>
        <td>{{$data['old_data']}}</td>
        <td>{{$data['new_data']}}</td>
        <td>{{$data['ip_address']}}</td>
        <td>{{getDateFormat($data['created_at'],4)}}</td>
    </tr>
    @endforeach
</tbody>
</table>
<script src="{{ Config('constants.admin_js_url') }}dataTables.bootstrap.js"></script>
<script type="text/javascript">
  $(document).ready(function() {
    $('#example').DataTable({
        "autoWidth":false,
        "fixedColumns": true
    });
} );
</script>