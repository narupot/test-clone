@extends('layouts.app')

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/dataTables.bootstrap', 'css/myaccount', 'css/cropper.min'],'css') !!}
@endsection

@section('header_script')      
@stop
@section('breadcrumbs')
@stop
@section('content')
<div class="row">
        <div class="col-sm-12">
            <h1 class="page-title">@lang('shop.credit_requests')</h1>  
            <div class="row favorite-shop">
                <div class="col-sm-12">
                    <ul class="nav tab-grey-list pl-3" id="product-rating-tab">
                        <li>                         
                            <a class="pdr-tablist active" data-toggle="tab" href="#waiting_approval">
                                @lang('shop.wait_for_approve')
                            </a>
                        </li>
                        <li>
                            <a class="pdr-tablist" data-toggle="tab" href="#cancelled">
                                @lang('shop.cancel')
                            </a>
                        </li>                               
                    </ul>                                                                                       
                    <div class="tab-content">
                        <div class="tab-pane active" id="waiting_approval">
                            <div class="prod-review-tbl">                                
                                <table class="table table-bordered " id="table_waiting_approval">
                                    <thead>
                                        <tr class="filters">
                                            <th class="text-center">@lang('shop.credit_shop_store')</th>
                                            <th class="text-center">@lang('shop.credit_request_date')</th>
                                            <th class="text-center">@lang('shop.credit_response_date')</th>
                                        </tr>
                                    </thead>
                                </table>                            
                            </div>  
                        </div>
                        
                        <div class="tab-pane" id="cancelled">
                            <div class="prod-review-tbl">                                
                                <table class="table dataTable table-bordered" id="table_cancelled">
                                    <thead>
                                        <tr class="filters">
                                            <th class="text-center">@lang('shop.credit_shop_store')</th>
                                            <th class="text-center">@lang('shop.credit_request_date')</th>
                                            <th class="text-center">@lang('shop.credit_response_date')</th>
                                        </tr>
                                    </thead>
                                </table>                                
                            </div>
                        </div>
                    </div>
                </div>
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
        $('#table_waiting_approval').DataTable({
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
                    "url": "{{ action('User\CreditController@getAllCredits') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{ _token: "{{csrf_token()}}",status:"Pending"}
                   },
            "columns": [
                { "data": "shop_store" },
                { "data": "request_date" },
                { "data": "response_date" }
            ]    

        });

        $('#table_cancelled').DataTable({
            "processing": true,
            "serverSide": true,
            "lengthChange": false,
            "responsive":true,
            "info":false,
            "ajax":{
                    "url": "{{ action('User\CreditController@getAllCredits') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{ _token: "{{csrf_token()}}",status:"Rejected"}
                   },
            "columns": [
                { "data": "shop_store" },
                { "data": "request_date" },
                { "data": "response_date" }
            ]    

        });
    });
</script>
    <!-- end of page level js -->
@endsection