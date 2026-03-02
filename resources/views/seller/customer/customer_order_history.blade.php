@extends('layouts.app') 

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/dataTables.bootstrap','css/myaccount','css/bootstrap-select'],'css') !!}

@endsection

@section('header_script')
var credit_paid_url = "{{action('Seller\CustomerController@paidCredit')}}";
var user_id = "{{$customerDetails->id}}";
var are_you_sure = "@lang('common.are_you_sure')";
var txt_no = "@lang('common.txt_no')";
var text_success = "@lang('common.success')";
var text_confirm_paid_message = "@lang('common.confirm_paid_message')";
var text_yes_send_it = "@lang('common.yes_reject_it')";
var text_yes_reject_it = "@lang('common.yes_reject_it')";
var text_yes_remove_it = "@lang('common.yes_remove_it')";
var text_reject_message = "@lang('common.reject_message')";
var text_yes_paid_it = "@lang('common.yes_paid_it')";
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
            <div class="title-border d-flex align-items-center">    
                <h1 class="page-title mt-2">@lang('shop.give_credit_to_regular_customer') </h1> 
                <a href="{{ url()->previous() }}" class="btn-grey ml-auto">@lang('common.back')</a>
            </div>
            <div class="user-desc">
                <div class="user-img">
                    <img src="{{getUserImageUrl($customerDetails->image)}}"  width="100" height="100" alt="">
                </div>
                <div class="user-body grey-bg">  
                    <div class="user-row">
                        <label>@lang('common.name')</label> : {{$customerDetails->display_name}}
                    </div>
                    <div class="user-row">
                        <label>@lang('common.email')</label> : {{$customerDetails->email}}
                    </div>
                    <div class="user-row">
                        <label>@lang('customer.tel')</label> : {{$customerDetails->ph_number}}
                    </div>
                    <div class="user-row">
                        <label>@lang('customer.customer_group')</label> : {{ $customerDetails->getCustGroupDesc->group_name ? $customerDetails->getCustGroupDesc->group_name : "NA" }}
                    </div>
                    @if($userCredit!=null)
                    <!-- <div class="user-row">
                        <label>@lang('customer.time_to_pay_credit')</label> : 
                        @if(isset($userCredit->payment_period))
                        {{$userCredit->payment_period}} @lang('common.days')
                        @else
                        {{'NA'}}
                        @endif
                    </div>
                    <div class="user-row">
                        @lang('common.amount') : 
                        @if(isset($userCredit->credited_amount))
                        {{numberFormat($userCredit->credited_amount)}} @lang('common.baht')
                        @else
                        {{'NA'}}
                        @endif

                    </div>
                    <div class="user-row">
                        <a href="javascript://" class="edit" data-toggle="modal" data-target="#giveCredit" data-select_options="{{$select_option}}" data-credited_amount="{{$userCredit->credited_amount}}" data-id="{{$userCredit->id}}" data-customer_email="{{$customerDetails->email}}" data-customer_name="{{$customerDetails->display_name}}" data-image="{{$img_url}}"><i class="fas fa-edit"></i> @lang('shop.edit_credit')</a>
                    </div>
                    <div>
                        <label class="button-switch-sm ml-6" @if($userCredit->amount_paid=='1') style="pointer-events:none" @endif >
                            <input type="checkbox" name="credit_paid" class="switch switch-orange credit_paid" @if($userCredit->amount_paid=='1') checked="checked" @endif >
                                <span for="autoRelated" class="lbl-off">@lang('common.paid_all')</span>
                                <span for="autoRelated" class="lbl-on">@lang('common.paid_all')</span>
                        </label>
                    </div> -->
                    @endif
                </div>
            </div>

            <h3>@lang('shop.order_history')</h3>
            <div class="table-responsive track-order-table">
                <div class="scrollertable">
                    <table class="table " id="overdue_credit_table">
                        <thead>
                            <tr>
                                <th>@lang('shop.order_number')</th>
                                <th>@lang('shop.date')</th>
                                <th>@lang('shop.order_status')</th>
                                <th>@lang('shop.shipping_method')</th>
                                <th>@lang('shop.action')</th>
                            </tr>
                        </thead>
                    </table>
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
        var user_id = "{{$customerDetails->id}}";
        $('#overdue_credit_table').DataTable({
            "processing": true,
            "serverSide": true,
            "lengthChange": false,
            "responsive":true,
            "info":false,
            "searching":false,
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
                    "url": "{{ action('Seller\CustomerController@getUserOrderList') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{ _token: "{{csrf_token()}}",user_id:user_id}
                   },
            columns: [
                { "data": "order_number"},
                { "data": "date"},
                { "data": "credit_status"},
                { "data": "shipping_method"},
                { "data": "action"}
            ]    
        });
    });
</script>

@endsection