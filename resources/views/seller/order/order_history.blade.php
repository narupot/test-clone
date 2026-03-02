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

<h1 class="page-title">@lang('order.seller_order_history')</h1>
<div class="adj-search">
    <div class="table-responsive">
        <table class="table tbl-order" id="order_history">
            <thead class="head-red">
                <tr>
                    <th>@lang('common.date')</th>
                    <th>@lang('order.order_no')</th>
                    <th>@lang('order.buyer_name')</th>
                    <th>@lang('order.shipping_method')</th>
                    <th>@lang('common.status')</th>
                    
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
        $('#order_history').DataTable({
            "processing": true,
            "serverSide": true,
            "lengthChange": false,
            "responsive":true,
            "info":false,
            "order": [[ 0, "desc" ]],
            ajax:{
                    "url": "{{ action('Seller\OrderController@orderHistoryData') }}",
                    "dataType": "json",
                    "type": "post",
                    "data":{ _token: "{{csrf_token()}}"}
                   },
            columns: [
                { "data": "end_shopping_date"},
                { "data": "shop_formatted_id"},
                { "data": "buyer_name"},
                { "data": "shipping_method"},
                { "data": "status"},
                { "data": "action"}
            ],
            'columnDefs': [ {
                'targets': [5], /* column index */
                'orderable': false, /* true or false */
            }] ,
            //$('.dataTables_filter input').attr("placeholder", "Search");
             

        });
        $('.dataTables_filter input').attr("placeholder", "ค้นหาจากเลขใบสั่งซื้อ"); 

    });


</script>

@endsection