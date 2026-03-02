@extends('layouts.app')

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/dataTables.bootstrap', 'css/myaccount', 'css/cropper.min'],'css') !!}
@endsection

@section('header_script')      
@stop
@section('breadcrumbs')
@stop
@section('content')
<h1 class="page-title">@lang('shop.credit_balance')</h1>                   
<div class="favorite-shop">
    <div class="table-responsive">
        <div class="track-order-table">
            <table class="table table-bordered " id="table">
                    <thead>
                        <tr class="filters">
                            <th class="text-center">@lang('shop.credit_shop_store')</th>
                            <th class="text-center">@lang('shop.remaining_credits')</th>
                            <th class="text-center">@lang('shop.overdue_action')</th>
                        </tr>
                    </thead>
            </table>
        </div>
    </div>
</div>
                
@endsection

@section('footer_scripts')
    {!! CustomHelpers::combineCssJs(['js/jquery.dataTables','js/dataTables.bootstrap', 'js/user/myaccount'],'js') !!}   
    <!-- begining of page level js -->
    <script type="text/javascript">
        var lang = {
            zeroRecords : "@lang('data_table.zeroRecords')",
            emptyTable : "@lang('data_table.emptyTable')",
            search : "@lang('data_table.search')",
            display : "@lang('data_table.display')",
            entries : "@lang('data_table.entries')", 
            filtered_from : "@lang('data_table.filtered_from')",
            total_entries : "@lang('data_table.total_entries')",
            loadingRecords : "@lang('data_table.loadingRecords')",
            paginate_first : "@lang('data_table.paginate_first')",
            paginate_last : "@lang('data_table.paginate_last')",
            paginate_next : "@lang('data_table.paginate_next')",
            paginate_previous : "@lang('data_table.paginate_previous')",
            processing : "@lang('data_table.processing')",
            showing : "@lang('data_table.showing')",
            to : "@lang('data_table.to')",
            of : "@lang('data_table.of')",
        };
    </script>
    <script>
    $(document).ready(function () {
        $('#table').DataTable({
            "processing": true,
            "serverSide": true,
            "lengthChange": false,
            "responsive":true,
            "info":false,
            "language": {
                "processing": lang.processing,
                "zeroRecords": lang.zeroRecords,
                "emptyTable": lang.emptyTable,
                "info": lang.showing + "_START_"+ lang.to +"_END_"+ lang.of +"_TOTAL_"+ lang.entries,
                "infoEmpty" : lang.showing+" 0 "+ lang.to + " 0 " + lang.of + " 0 "+ lang.entries,
                "search" : lang.search,
                "lengthMenu" : lang.display+" _MENU_ "+lang.entries,
                "infoFiltered" :   "("+lang.filtered_from+" _MAX_ "+lang.total_entries+")",
                "loadingRecords" : lang.loadingRecords,
                "paginate" : {
                    "first" : lang.paginate_first,
                    "last" : lang.paginate_last,
                    "next" : lang.paginate_next,
                    "previous" : lang.paginate_previous
                },
            },
            "ajax":{
                    "url": "{{ action('User\CreditController@getAllCreditBalance') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{ _token: "{{csrf_token()}}"}
                   },
            "columns": [
                { "data": "shop_store" },
                { "data": "remaining_credits" },
                { "data": "action" }
            ]    

        });
    });
</script>
    <!-- end of page level js -->
@endsection