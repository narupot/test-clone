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

    <h1 class="page-title title-border">@lang('seller_report.bill_from_market')</h1>
    <div class="row">
        <div class="col-sm-12">
        <ul class="nav tab-grey-list pl-3" id="product-rating-tab">
            <li>                         
                <a class="pdr-tablist active show" data-toggle="tab" href="#bill_from_market_outstanding">Outstanding balance</a>
            </li>
            <li>
                <a class="pdr-tablist" data-toggle="tab" href="#bill_from_market_paid_amt">Paid Amount</a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active show" id="bill_from_market_outstanding">
                <div class="prod-review-tbl">
                    <div class="table-responsive">
                        <table class="table tbl-order" id="order_history">
                            <thead class="table-header">
                                <tr>
                                    <th>&nbsp;</th>
                                    <th>@lang('order.order_no').</th>
                                    <th>@lang('order.buyer_name').</th>
                                    <th>@lang('order.total')</th>
                                    <th>&nbsp;</th>                                              
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>  
                </div>
            </div>
            <div class="tab-pane" id="bill_from_market_paid_amt">
                Upcomig Table
            </div>
        </div>
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
                    "url": "{{ action('Seller\OrderController@orderOutstandingBalanceData') }}",
                    "dataType": "json",
                    "type": "post",
                    "data":{ _token: "{{csrf_token()}}"}
                   },
            columns: [
                { "data": "sno"},
                { "data": "shop_formatted_id"},
                { "data": "buyer_name"},
                { "data": "total"},
                { "data": "action"}
            ],
            'columnDefs': [ {
                'targets': [4], /* column index */
                'orderable': false, /* true or false */
            }] ,
            //$('.dataTables_filter input').attr("placeholder", "Search");
             

        });
        $('.dataTables_filter input').attr("placeholder", "ค้นหาจากเลขใบสั่งซื้อ"); 

    });


</script>

@endsection