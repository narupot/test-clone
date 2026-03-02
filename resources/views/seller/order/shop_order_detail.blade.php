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
    <div class="container1">
        <div class="track-order-num-wrap border-bottom pb-2">
            <div class="cust-ord-num">
                <div class="float-right btn-group">
                    <a href="{{ $previous_url ?? 'javacript:;' }}" class="btn-grey"><i><</i> @lang('common.back')</a>
                    <!-- <button class="btn-default print">@lang('common.print')</button> -->
                </div>
                <!--<h2>@lang('order.shop_order_no') : {{$orderShopData->shop_formatted_id}}</h2>-->
                <h2>@lang('order.shop_order_no') : {{ substr($orderShopData->order_id, -4) }}</h2>
                
            </div>
            <div class="track-status">
                <button class="btn- btn">@lang('shop.status') : {{$orderShopData->getOrderStatus->status ?? "NA" }}</button>                                 
                <span class="ship-track-time">{{getDateFormat($orderShopData->updated_at,7)}}</span>
            </div>
        </div>
        <div class="title-track-info">
            <h3 class="skyblue">@lang('order.shipping_method')</h3>
            <div class="track-ship-name">
                {{ GeneralFunctions::getShippingMethod($orderShopData->shipping_method) }}  @if(strtotime($main_ord->pickup_time)) <span class="red">(@lang('order.expected_time_to_sending') : {{ getDateFormat($main_ord->pickup_time,8) }})</span> @endif

                @if($orderShopData->shipping_method!=3 && $main_ord->user_phone_no !='')
                    <br> @lang('checkout.phone_no') : <span class="red"> {{$main_ord->user_phone_no}}</span>
                @endif
            </div>
        </div>
        <div class="order-step-wrap mb-4">
            <ul>
                
                @if($orderShopData->order_status ==1 || $orderShopData->order_status ==2)
                    <li class="active">
                    <div class="current-status">
                        <div class="d-block">@lang('order.current_status_is_here')</div>
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                @else
                    <li class="completed">
                @endif
                    <span class="step-icon">
                        <i class="fas fa-store"></i>
                    </span>
                    <span class="step-name">@lang('order.prepare_products_at_the_store')</span>
                </li>

                @if($orderShopData->shipping_method != 2)
                    <li @if($orderShopData->order_status > 2) @if($orderShopData->order_status ==5)class="active" @else class="completed" @endif @endif>
                        @if($orderShopData->order_status ==5)
                            <div class="current-status">
                                <div class="d-block">@lang('order.current_status_is_here')</div>
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                        @endif
                        <span class="step-icon">
                            <i class="far fa-warehouse-alt"></i>
                        </span>
                        <span class="step-name">@lang('order.products_at_the_center')</span>
                    </li>
                @endif

                @if($orderShopData->shipping_method == 3)
                    <li @if($orderShopData->order_status != 5 && $orderShopData->order_status > 2) class="completed" @endif>
                        @if($orderShopData->order_status ==6)
                            <div class="current-status">
                                <div class="d-block">@lang('order.current_status_is_here')</div>
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                        @endif
                        <span class="step-icon">
                            <i class="fas fa-truck"></i>
                        </span>
                        <span class="step-name">@lang('order.shipping_now')</span>
                    </li>
                @endif

                @if($orderShopData->order_status !=4)
                    <li @if($orderShopData->order_status ==3) class="active" @endif>
                        @if($orderShopData->order_status ==3)
                            <div class="current-status">
                                <div class="d-block">@lang('order.current_status_is_here')</div>
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                        @endif
                        <span class="step-icon">
                            <i class="far fa-thumbs-up"></i>
                        </span>
                        <span class="step-name">@lang('order.recieved_the_product')</span>
                    </li>
                @endif

                @if($orderShopData->order_status ==4)
                    <li class="active cancel">
                        <div class="current-status">
                            <div class="d-block">@lang('order.current_status_is_here')</div>
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <span class="step-icon">
                            <i class="far fa-times"></i>
                        </span>
                        <span class="step-name">@lang('common.cancel')</span>
                    </li>
                @endif
            </ul>
        </div>
        <h2>@lang('order.order_items')</h2>
        <div class="table-responsive track-order-table mb-3">
            <div class="table">
                <div class="table-header">
                    <ul>
                        <li>@lang('product.product_name')</li>
                        <li>@lang('product.standered')</li>
                        <li>@lang('product.qty')</li>
                        <li>@lang('product.price_per_item')</li>
                        <li>@lang('product.price')</li>
                        <li>@lang('product.payment_by')</li>
                        <!-- <li>@lang('product.tracking_id')</li> -->
                        <li>@lang('order.item_status')</li>
                    </ul>
                </div>
                <div class="table-content">
                    
                    @if(count($orderItems))
                        @foreach($orderItems as $key =>$item)

                        @php
                            $detail_json = jsonDecodeArr($item->order_detail_json);
                            $prd_url = action('ProductDetailController@display',[$detail_json['cat_url']??'',$item->sku]);
                        @endphp
                        <ul>
                            <li class="product">
                                <a href="{{ $prd_url}}"><span class="prod-img"><img src="{{ getProductImageUrlRunTime($detail_json['thumbnail_image'],'thumb') }}" width="80" height="80" alt=""></span>
                                <span class="prod-name">{{ $detail_json['name'][session('default_lang')]??$item->category_name }}</span></a>
                            </li>
                            <li><span class="mr"><img src="{{ getBadgeImageUrl($detail_json['badge']['icon'] ?? '' )}}" width="30"></span></li>
                            <li> {{ $item->quantity }} {{ $detail_json['package'][session('default_lang')] ?? $item->package_name }}</li>
                            <li>{{numberFormat($item->last_price) }} @lang('common.baht')</li>
                            <li>{{numberFormat($item->total_price) }} @lang('common.baht')</li>
                            <li>{{$detail_json['payment_method'][session('default_lang')]}}</li>
                            <!-- <li>---</li> -->
                            <li>{{ $item->getOrderStatus->status ?? '' }}</li>
                        </ul>
                        @endforeach
                    @endif
                </div>
            </div>

            <div class="table-footer clearfix">               
                <div class="col-sm-6 float-right">
                    <!-- <div class="row">
                        <span class="col-6">@lang('order.total_weight')</span>
                        <span class="col-6">---</span>
                    </div>
                    <div class="row">
                        <span class="col-6">@lang('order.total_shipping_cast')</span>
                        <span class="col-6">----</span>
                    </div> -->
                    <div class="bg">
                        <div class="row">
                            <span class="col-6">@lang('order.grand_total')</span>
                            <span class="col-6">{{$orderShopData->total_final_price}} @lang('common.baht')</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>  

        <div class="box-grey col-sm-3">
            <div class="form-group">
                <label class="radio-wrap">
                    <input type="radio" name="ord-radio" value="prepare" @if($orderShopData->seller_status=='prepare') checked="checked" @endif>
                    <span class="radio-mark">@lang('order.prepare')</span>
                </label>
            </div>
            <!-- <div class="form-group">
                <label class="radio-wrap">
                    <input type="radio" name="ord-radio" value="ready" @if($orderShopData->seller_status=='ready') checked="checked" @endif>
                    <span class="radio-mark">@lang('order.ready')</span>
                </label>
            </div> -->
            <div class="form-group">
                <label class="radio-wrap">
                    <input type="radio" name="ord-radio" value="sent" @if($orderShopData->seller_status=='sent') checked="checked" @endif>
                    <span class="radio-mark">@lang('order.sent')</span>
                </label>
            </div>
            <div class="form-group">
                <button class="btn- btn" id="btn_submit">Submit</button>
            </div>
        </div>

        <div class="track-buyer-info border-top-0 mt-3">
            <div class="title-track-info">
                <h3 class="skyblue">@lang('customer.buyer_information')</h3>
            </div>
            <div class="track-info-detail">
                <div class="tInfo-row">
                    <span class="label">@lang('customer.name') :</span> {{$orderShopData->user_name}}
                </div>
                <div class="tInfo-row">
                    <span class="label">@lang('customer.email') :</span> {{$orderShopData->user_email}}
                </div>
                <div class="tInfo-row">
                    <span class="label">@lang('customer.telephone') :</span>  {{$orderShopData->ph_number}}
                </div>
                @if($orderShopData->shipping_method == 1)
                    <div class="tInfo-row">
                        <h4><strong>@lang('admin_order.center_address') : </strong></h4>
                        {!! CustomHelpers::centerAddress($orderShopData->order_json) !!}
                    </div>
                @elseif($orderShopData->shipping_method == 2)
                    <div class="tInfo-row">
                        <h4><strong>@lang('admin_order.store_address') : </strong></h4>
                        {!! CustomHelpers::storeAddress($orderShopData->order_json) !!}
                    </div>
                @else
                    <div class="tInfo-row">
                        <h4><strong>@lang('checkout.shipping_address') : </strong></h4>
                        {{ CustomHelpers::buyerShipBillTo($orderShopData->order_json,'shipping_address') }}
                    </div>
                    <div class="tInfo-row">
                        <h4><strong>@lang('checkout.billing_address') : </strong></h4>
                        {{ CustomHelpers::buyerShipBillTo($orderShopData->order_json,'billing_address') }}
                    </div>
                @endif
                
            </div>
            
        </div>      
    </div>
    
<!-- page contents end -->

@endsection 
@section('footer_scripts') 

<script type="text/javascript">
    var status_url = "{{ action('Seller\OrderController@updateShopOrdStatus') }}";
    var deliver_url = "{{action('Seller\OrderController@deliveryList')}}";
    var deliver_ready_url = "{{action('Seller\OrderController@deliveryList',['section'=>'ready'])}}";
    $('#btn_submit').click(function(e){
        if($('input[name="ord-radio"]:checked').length>0){
            var status = $('input[name="ord-radio"]:checked').val();
            var section = status; // หรือจะ Mapping ตามที่ต้องการ เช่น status เป็น ready หรือ prepare ก็ใช้เป็น section
            
            var data = {ord_status: status, ord_id: {{ $orderShopData->id }} };
            
            callAjaxRequest(status_url, 'post', data, function(result){
                if(result.status == 'success'){
                    swal({
                        type: "success", 
                        title: lang_success, 
                        text: result.msg,
                        confirmButtonText : lang_ok,
                    }).then(function(){
                        const rsSection = result.section || section; 
                        // console.log("Section:", rsSection);
                        
                        if (rsSection === 'sent') {
                            window.location.href = deliver_ready_url + '?section=' + rsSection;
                        }else{
                            window.location.href = deliver_url + '?section=' + rsSection;
                        }
                    });
                }else{
                    showSweetAlertError(result.msg);
                }
            });
        }
    });

</script>
@endsection