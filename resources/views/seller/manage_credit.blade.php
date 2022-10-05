@extends('layouts.app') 

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/dataTables.bootstrap','css/myaccount','css/bootstrap-select'],'css') !!}

@endsection

@section('header_script')
@endsection

@section('content')

@if(Session::has('verify_msg'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    {{Session::get('verify_msg')}}
</div>
@endif

@if(Session::has('not_verify_msg')) 
    <div class="alert alert-danger alert-dismissable margin5"> 
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button> 
        {{Session::get('not_verify_msg')}} 
    </div> 
@endif
<!-- page contents start -->
    <div class="row">
        <div class="col-sm-12">
            <h1 class="page-title title-border">@lang('shop.manage_credit') </h1>
            <div class="row">
                <div class="col-sm-12">

                    <ul class="nav tab-grey-list pl-3" id="product-rating-tab">
                        <li>                         
                            <a class="pdr-tablist active" data-toggle="tab" href="#overdueCredit">
                                @lang('shop.credit_usage')
                            </a>
                        </li>
                        <li>
                            <a class="pdr-tablist" data-toggle="tab" href="#creditList">
                                @lang('shop.credit_list')
                            </a>
                        </li>
                        <li>
                            <a class="pdr-tablist cr-countable" data-toggle="tab" href="#creditRequest">
                                @lang('shop.credit_request') <span class="cr-count">{{$total_credit_request}}</span>
                            </a>
                        </li>   
                                                       
                    </ul>                                                                                       
                    <div class="tab-content">
                        <div class="tab-pane active" id="overdueCredit">
                            <div class="prod-review-tbl">                                
                                <table class="table" id="overdue_credit_table">
                                    <thead>
                                        <tr>
                                            <th>@lang('shop.customer_name')</th>
                                            <th>@lang('shop.order_number')</th>
                                            <th>@lang('shop.last_credit_use')</th>
                                            <th>@lang('shop.status')</th>
                                            <th>@lang('shop.payment_period')</th>
                                            <th>@lang('shop.action')</th>
                                        </tr>
                                    </thead>
                                </table>                            
                            </div>  
                        </div>
                        
                        <div class="tab-pane" id="creditList">
                            <div class="prod-review-tbl">                                
                                <table class="table" id="credit_list_table">
                                    <thead>
                                        <tr>
                                            <th>@lang('shop.customer_name')</th>
                                            <th>@lang('shop.credit_limit')</th>
                                            <th>@lang('shop.credit_balance')</th>
                                            <th>@lang('shop.last_credit_use')</th>
                                            <th>@lang('shop.payment_period')</th>
                                            <th>@lang('shop.action')</th>
                                        </tr>
                                    </thead>
                                </table>                                
                            </div>
                        </div>

                        <div class="tab-pane" id="creditRequest">
                            <div class="prod-review-tbl">                                
                                <table class="table" id="credit_request_table">
                                    <thead>
                                        <tr>
                                            <th>@lang('shop.customer_name')</th>
                                            <th>@lang('shop.designated_name')</th>
                                            <th>@lang('shop.email')</th>
                                            <th>@lang('shop.telephone')</th>
                                            <th>@lang('shop.credit_request_date')</th>
                                            <th>@lang('shop.action')</th>
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
    @include('includes.seller_credit_popups')
<!-- page contents end -->

@endsection 
@section('footer_scripts') 
{!! CustomHelpers::combineCssJs(['js/jquery.dataTables','js/dataTables.bootstrap', 'js/user/myaccount','js/manage_credits'],'js') !!} 

<script>
    var txt_no = "@lang('common.no')";
    var text_reject_message = "@lang('common.reject_message')";
    var text_yes_reject_it = "@lang('common.yes_reject_it')";
    var are_you_sure = "@lang('common.are_you_sure')";
    var text_ok_btn = "@lang('common.ok_btn')";
    var text_success = "@lang('common.text_success')";
    var text_error = "@lang('common.text_error')";
    var text_yes_remove_it = "@lang('common.yes_remove_it')";
    var handle_credit_ajax_request_url = "{{action('Seller\CreditController@manageCreditAjaxRequest')}}";
    var check_credit_remove_url = "{{action('Seller\CreditController@willCreditRemove')}}";
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
    $(document).ready(function () {
        $('#overdue_credit_table').DataTable({
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
            ajax:{
                    "url": "{{ action('Seller\CreditController@getAllOverdueCredits') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{ _token: "{{csrf_token()}}"}
                   },
            columns: [
                { "data": "customer_name"},
                { "data": "order_number"},
                { "data": "last_credit_use"},
                { "data": "status","orderable":false},
                { "data": "payment_period"},
                { "data": "action","orderable":false}
            ]    

        });

        $('#credit_list_table').DataTable({
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
            ajax:{
                    "url": "{{ action('Seller\CreditController@getAllCredits') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{ _token: "{{csrf_token()}}"}
                   },
            columns: [
                { "data": "customer_name"},
                { "data": "credit_limit"},
                { "data": "credit_balance"},
                { "data": "last_credit_use"},
                { "data": "payment_period"},
                { "data": "action","orderable":false}
            ]    

        });

        $('#credit_request_table').DataTable({
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
            ajax:{
                    "url": "{{ action('Seller\CreditController@getCreditsRequest') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{ _token: "{{csrf_token()}}"}
                   },
            columns: [
                { "data": "customer_name"},
                { "data": "designated_name"},
                { "data": "email"},
                { "data": "telephone"},
                { "data": "credit_request_date"},
                { "data": "action","orderable":false}
            ]    

        });
    });


</script>

@endsection