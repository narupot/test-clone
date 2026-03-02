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
            <h1 class="page-title title-border">@lang('shop.shop_rating') </h1>

            <div class="row">
                <div class="col-sm-12">
                    <div class="rating-review-wrap text-center">
                            {{-- <span class="total-review">@lang('shop.total') : {{ $rating_data['total_reviews'] ?? 0}} @lang('shop.reviews')</span> --}}
                            <div class="review-star star-lg mb-4 mt-3">
                                <div class="grey-stars"></div>
                                <div class="filled-stars" style="width: {{ $shop_details->avg_rating*20 }}%"></div>
                            </div>
                            <!-- <div class="rating-graph">
                                    <div class="inner-graph" style="width: {{$rating_data['total_percent']}}%;">
                                    </div>
                                <div class="outer-total">
                                    <div class="total-circle">
                                        <div class="circle-total">@lang('shop.total')</div>
                                        <div class="show-per">
                                        {{ $rating_data['total_percent']}}%</div>
                                    </div>
                                </div>
                            </div> -->
                            <div class="prod-review-star">
                                <ul>
                                    @foreach($rating_data['star_reviews'] as $key=>$unit_ratings)
                                    <li>
                                        <div class="product-review">
                                            <div class="review-star">
                                                <div class="grey-stars"></div>
                                                <div class="filled-stars" style="width: {{$key*20}}%"></div>
                                            </div>
                                        </div>
                                        <div class="review-count">{{$unit_ratings}} review</div>
                                    </li>
                                    @endforeach     
                                </ul>
                            </div>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-sm-12">
                    {{-- <ul class="nav tab-grey-list pl-3" id="product-rating-tab">
                        <li>                         
                            <a class="pdr-tablist active" data-toggle="tab" href="#product-review">
                                @lang('shop.product_review')
                            </a>
                        </li>
                        <li>
                            <a class="pdr-tablist" data-toggle="tab" href="#waiting-products">
                                @lang('shop.waiting_for_review')
                            </a>
                        </li>                          
                    </ul> --}}                                                                                       
                    <div class="tab-content">
                        <div class="tab-pane active" id="product-review">
                            <div class="prod-review-tbl">                                
                                <table class="table" id="product_review_table">
                                    <thead>
                                        <tr>
                                            <th>@lang('shop.product')</th>
                                            <th>@lang('shop.standard')</th>
                                            <th>@lang('shop.rating')</th>
                                            <th>@lang('shop.feedback')</th>
                                            <th>@lang('shop.review_date')</th>
                                            <th>@lang('shop.action')</th>
                                        </tr>
                                    </thead>
                                </table>                            
                            </div>  
                        </div>
                        
                        <div class="tab-pane" id="waiting-products">
                            <div class="prod-review-tbl">                            
                                <table class="table" id="waiting_products_table">
                                    <thead>
                                        <tr>
                                            <th>@lang('shop.product')</th>
                                            <th>@lang('shop.standard')</th>
                                            <th>@lang('shop.order_date')</th>
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
<!-- page contents end -->

@endsection 
@section('footer_scripts') 
{!! CustomHelpers::combineCssJs(['js/jquery.dataTables','js/dataTables.bootstrap', 'js/user/myaccount','js/manage_credits'],'js') !!} 

<script>

    var handle_credit_ajax_request_url = "{{action('Seller\CreditController@manageCreditAjaxRequest')}}";
    var check_credit_remove_url = "{{action('Seller\CreditController@willCreditRemove')}}";

    $(document).ready(function () {
        $('#product_review_table').DataTable({
            "processing": true,
            "serverSide": true,
            "lengthChange": false,
            "responsive":true,
            "info":false,
            "searching": false,
            ajax:{
                    "url": "{{ action('Seller\ReviewController@getProductRatings')}}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{ _token: "{{csrf_token()}}",'table':'reviewed'}
                   },
            columns: [
                { "data": "product_name"},
                { "data": "standard"},
                { "data": "rating"},
                { "data": "review"},
                { "data": "review_date"},
                { "data": "action"}
            ]    

        });

        $('#waiting_products_table').DataTable({
            "processing": true,
            "serverSide": true,
            "lengthChange": false,
            "responsive":true,
            "info":false,
            "searching": false,
            ajax:{
                    "url": "{{ action('Seller\ReviewController@getProductRatings') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{ _token: "{{csrf_token()}}",'table':'not-reviewed'}
                   },
            columns: [
                { "data": "product_name"},
                { "data": "standard"},                
                { "data": "ordered_date"}
            ]    

        });
        
    });


</script>

@endsection