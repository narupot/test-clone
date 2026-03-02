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

<h1 class="page-title">@lang('order.order_history_buyer')</h1>
<a class="btn" href="{{ action('User\OrderController@orderHistory') }}">@lang('order.by_main_order')</a> 
<a class="btn-grey d-none" href="{{ action('User\OrderController@sellerOrderHistory') }}">@lang('order.by_seller_order_buyer')</a>
<div class="order-history-buyer mb-4">
    <div class="table-responsive">
        <table class="table order-history-buyer" id="order_history_buyer">
            <thead>
                <tr>
                    <th>@lang('order.end_shopping_date')</th>
                    <th>@lang('order.order_no_buyer')</th>
                    <th>@lang('common.status')</th>
                    <th>@lang('order.shipping_method')</th>
                    <th>&nbsp;</th>                                                 
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>  
</div>

<!-- page contents end -->

@endsection 
@section('footer_scripts') 
{!! CustomHelpers::combineCssJs(['js/jquery.dataTables','js/dataTables.bootstrap'],'js') !!} 

<script>

    $(document).ready(function () {
        $('#order_history_buyer').DataTable({
         
            "processing": true,
            "serverSide": true,
            "lengthChange": false,
            "responsive":true,
            "info":false,
            "order": [[ 0, "desc" ]],
            ajax:{
                    "url": "{{ action('User\OrderController@orderHistoryData') }}",
                    "dataType": "json",
                    "type": "post",
                    "data":{ _token: "{{csrf_token()}}"}
                   },
            columns: [
                { "data": "end_shopping_date"},
                { "data": "formatted_id"},
                { "data": "status"},
                { "data": "shipping_method"},
                { "data": "action"}
            ],
            'columnDefs': [ {
                'targets': [4], /* column index */
                'orderable': false, /* true or false */
            }]

        });

    });


</script>

@endsection