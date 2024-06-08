@extends('layouts.app') 

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/flickity', 'css/jquery-editable-select.min'],'css') !!}
@endsection

@section('header_script')
    var error_msg ={
        select_shipping : "@lang('checkout.select_shipping_method')",
        select_payment : "@lang('checkout.select_payment_method')",
        select_shipping_address : "@lang('checkout.select_shipping_address')",
        select_billing_address : "@lang('checkout.select_billing_address')",
        select_shipping_error: "@lang('checkout.select_shipping_error')",
        select_pickup_time:"@lang('checkout.select_pickup_time')",
        ok : "@lang('common.ok')",
        txt_no : "@lang('common.no')",
        update_price : "@lang('checkout.update_price')",
        server_error : "@lang('common.something_went_wrong')",
        shipping_fee : "@lang('checkout.delivery_fee')",
        discount_shipping_fee : "@lang('checkout.discount_delivery_fee')",
        currency : "@lang('common.currency')",
        enter_phone_no : "@lang('checkout.enter_phone_no')"
    }; 
    var delivery_time_arr = {!! json_encode($delivery_time_arr) !!};
    var checkout_type = "{{ $checkout_type }}";
    var address_form_url = "{{ action('Checkout\CartController@cartAddress') }}";
    var address_dd_url = "{{action('AjaxController@getStateCityDD')}}";
    var save_address_url = "{{action('Checkout\CartController@saveAddress')}}";
    var change_ship_address = "{{action('Checkout\CartController@changeShipAddress')}}";
    var change_bill_address = "{{action('Checkout\CartController@changeBillAddress')}}";   
    var pickup_time_url = "{{ action('Checkout\CartController@pickupTime') }}" ;
    var tot_delivery_time = "{{ $delivery_details['item_pickup_time'] }}";
    var updateCartPrice = "{{ action('Checkout\CartController@updateCartPrice') }}";
    var checkCartUrl = "{{action('Checkout\CartController@checkCartExist')}}";
    var deletetemporder = "{{action('Checkout\CartController@deleteTempOrder')}}";
@endsection

@section('content')

@php($tot_amount = 0)
<div class="checkout-wrap">
    @if(!empty($orderInfo))
        <h1 class="page-title d-flex">@lang('checkout.end_shopping_step') <a href="{{ action('Checkout\CartController@shoppingCart') }}" class="btn-grey back">@lang('common.back')</a> </h1>
        
        <form id="checkout_form" method="post" action="{{ action('Checkout\CartController@store') }}">
            <input type="hidden" name="checkout_type" value="{{ $checkout_type }}">
            @if($checkout_type == 'end-shopping' || $checkout_type == 'buy-now-end-shopping')
                <div class="step-title">
                    <span class="step-num">1</span>
                    <h3>@lang('checkout.select_shipping_method')</h3>
                </div>
                <div class="ship-method">
                    
                    {{ csrf_field() }}
                    <input type="hidden" name="order_id" value="{{ $orderInfo->formatted_order_id }}">
                    <ul class="nav" id="shipTab">
                        <li>
                            <a class="ship-method-list active" data-toggle="tab" href="#select-address" id="delivery_at_the_address">
                                <input type="radio" value="3" name="ship_method" id="ship-address"  checked="checked">
                                <i class="fas fa-truck"></i> <span>@lang('checkout.delivery_at_the_address')</span>
                            </a>
                        </li>
                        <li>                         
                            <a class="ship-method-list" data-toggle="tab" href="#pick_up_center" id="pick_up_at_center">
                                <input type="radio" value="1" name="ship_method" id="ship-center">
                                <i class="fas fa-cubes"></i> <span>@lang('checkout.pick_up_at_center')</span>
                            </a>
                        </li>
                        {{--
                        <li>
                            <a class="ship-method-list" data-toggle="tab" href="#shop_address" id="pick_up_at_the_store">
                                <input type="radio" value="2" name="ship_method" id="ship-store">
                                <i class="fas fa-warehouse"></i> <span>@lang('checkout.pick_up_at_the_store')</span>
                            </a>
                        </li>
                        --}}

                    </ul>
                    <p id="e_ship_method" class="error"></p>
                    <div class="tab-content">
                        <div class="tab-pane active" id="select-address">
                            <div class="step-title">
                                <span class="step-num">2</span>
                                <h3>@lang('checkout.select_address')</h3>
                            </div>
                            <div class="row">
                                <div class="col-sm-4 address-border">
                                    <div class="form-group">
                                        <label>@lang('checkout.select_shipping_address')<i class="red">*</i></label>
                                        <div class="block-add-address">
                                            <select class="selectpicker" name="ship_address" id="dd_shipping">
                                                <option value="">@lang('checkout.select_address')</option>
                                                @if(count($user_address))
                                                    @foreach($user_address as $skey => $sval)
                                                        <option value="{{ $sval->id}}" @if($shipping_address && $shipping_address->id == $sval->id) selected="selected" @endif>{{ $sval->title }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <a href="javascript:void(0);" class="btn-grey add_address">+@lang('checkout.add_new_address')</a>
                                        </div>
                                        <p class="error" id="e_ship_address"></p>
                                    </div>
                                    <address class="post-address" id="shipping_address">
                                    @if($shipping_address)
                                        <p>{{$shipping_address->first_name.' '.$shipping_address->last_name}}</p>
                                        <p>{{$shipping_address->address.', '.$shipping_address->road}}</p>
                                        <p>{{$shipping_address->city_district.', '.$shipping_address->province_state.', '.$shipping_address->zip_code}}</p>
                                        <p>@lang('customer.tel'). {{$shipping_address->ph_number}}</p>
                                    @endif
                                    </address>                      
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>@lang('checkout.select_billing_address')<i class="red">*</i></label>
                                        <div class="block-add-address">
                                            <select class="selectpicker" name="bill_address" id="dd_billing">
                                                <option value="">@lang('checkout.select_address')</option>
                                                @if(count($user_address))
                                                    @foreach($user_address as $bkey => $bval)
                                                        <option value="{{ $bval->id}}"  @if($billing_address && $billing_address->id == $bval->id) selected="selected" @endif>{{ $bval->title }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <a href="javascript:void(0);" class="btn-grey add_address">+@lang('checkout.add_new_address')</a>
                                        </div>
                                        <p class="error" id="e_ship_address"></p>
                                    </div>
                                    <address class="post-address" id="billing_address">
                                    @if($billing_address)
                                        <p>{{$billing_address->first_name.' '.$billing_address->last_name}}</p>
                                        <p>{{$billing_address->address.', '.$billing_address->road}}</p>
                                        <p>{{$billing_address->city_district.', '.$billing_address->province_state.', '.$billing_address->zip_code}}</p>
                                        <p>@lang('customer.tel'). {{$billing_address->ph_number}}</p>
                                    @endif
                                    </address>                      
                                </div>
                                
                            </div>
                        </div>
                        <div class="tab-pane" id="pick_up_center">
                            <div class="ship-info">
                                @if($pickup_center_address)
                                    @if($delivery_details['item_pickup_time'])
                                        {{-- <p >@lang('checkout.can_pick_up_the_product_within') {{ $delivery_details['item_pickup_time'] }} @lang('checkout.hours')</p> --}}
                                    @endif
                                    <p>{{ $pickup_center_address['name']??'' }}</p>
                                    <address>
                                        {{ $pickup_center_address['location']??'' }} <br/>
                                        <a href="tel:{{ $pickup_center_address['contact']??'' }}">{{ $pickup_center_address['contact']??'' }}</a>
                                    </address>
                                @endif
                                
                            </div>
                        </div>
                        {{-- <!--hide because this shipping is not available
                            6615925039-->
                            <div class="tab-pane" id="shop_address">
                            <div class="ship-info">
                                @if(count($shop_address))
                                    @foreach($shop_address as $val)
                                        <p><span class="label">@lang('checkout.shop_name') : </span> {{ $val['shop_name'] }}</p>
                                        <address>
                                            <span class="label">@lang('checkout.panel_no') : </span> {{ $val['panel_no'].' '.$val['market'] }} 
                                            @if($val['ph_number']) <br/>
                                            <span class="label">@lang('checkout.contact') : </span>
                                                @if($val['ph_number'])
                                                    @foreach(explode(',',$val['ph_number']) as $phno)
                                                        <a href="tel:{{$phno}}">{{$phno}}</a> &nbsp;
                                                    @endforeach
                                                @endif
                                                
                                            @endif
                                        </address>
                                    @endforeach
                                @endif
                            </div>
                        </div>--}}
                        
                    </div>      
                </div>
            @endif

            <div class="form-row" id="user_phone_no_div" style="display: none;">
                <div class="col-sm-4">
                    <label>@lang('checkout.phone_no')</label>
                    <input type="text" name="phone_no" id="phone_no">
                    <p class="error" id="e_phone_no"></p>
                    
                </div>
            </div>

            <div class="form-row">
                <div class="col-sm-4">
                    <label>@lang('checkout.pickup_time')<i class="red">*</i></label>
                    <select class="" name="pickup_time" id="pickup_time">
                        <option value="">@lang('checkout.select_pickup_time')</option>
                        {{--@for($i=7; $i<=23;$i++)
                            @if($i > $delivery_details['cal_hour'] || $delivery_details['tomorrow'])
                                <option value="{{ $i }}">{{ $delivery_details['tomorrow']?'Next Day':'' }} {{ $i.':00'}}</option>
                            @endif
                        @endfor--}}
                        @foreach($time_arr as $val)
                            <option value="{{ $val }}">{{ (strrpos($val,'_n')!==false) ? str_replace('_n','',$val).':00 '. (date('d')+1).' '.date('M'):$val.':00' }} </option>
                        @endforeach
                    </select>
                    <p class="error" id="e_pickup_time"></p>
                    <input type="hidden" name="nexday" value="{{ $delivery_details['tomorrow'] }}">
                </div>
                
            </div>
            <div class="table-responsive checkout-order-table cartpage-tbl">
                @if(!empty($orderInfo) && count($orderDetails))
                    <h2>@lang('checkout.ordered_items')</h2>
                    <div class="table">
                        <div class="table-header">
                            <ul>
                                <li class="sel-tbl">@lang('checkout.seller')</li>
                                <li class="goods-tbl">@lang('checkout.product')</li>
                                <li class="num-tbl">@lang('checkout.qty')</li>
                                <li class="unit-tbl">@lang('checkout.unit_price')</li>
                                <li class="total-tbl">@lang('checkout.total_price')</li>
                            </ul>
                        </div>
                        <div class="table-content">     
                            @php($totqty = 0)
                            
                            @foreach($orderDetails as $cartKey => $cartVal)
                                @php($totqty = $totqty + $cartVal->quantity)
                                @php($tot_amount = $tot_amount + $cartVal->total_price)                
                                <ul id="cart_{{ $cartVal->id }}">
                                    <li class="product-shop sel-tbl">
                                        <a href="{{ action('ShopController@index',$cartVal->getShop->shop_url)}}">
                                        <span class="prod-img"><img src="{{getImgUrl($cartVal->getShop->logo,'logo')}}" width="50" height="50" alt=""></span>
                                        <a class="shopname" href="{{ action('ShopController@index',$cartVal->getShop->shop_url)}}">{{ $cartVal->getShopDesc->shop_name??'' }}</a>
                                        </a>
                                    </li>
                                    <li class="product goods-tbl">
                                        <div class="dbox-flex">
                                            <a href="{{ action('ProductDetailController@display',[$cartVal->getCat->url,$cartVal->getPrd->sku])}}">
                                                <span class="prod-img prod-80"><img src="{{ getProductImageUrlRunTime($cartVal->getPrd->thumbnail_image,'thumb') }}" width="80" height="80" alt=""></span>
                                            </a>
                                            <div class="ml-2">
                                                <a href="{{ action('ProductDetailController@display',[$cartVal->getCat->url,$cartVal->getPrd->sku])}}">                            
                                                    <span class="prod-name d-block mb-2">{{ $cartVal->getCatDesc->category_name??'' }}</span>
                                                </a>
                                                <div class="la"><img src="{{ getBadgeImage($cartVal->getPrd->badge_id) }}" width="30"></div>
                                            </div>
                                        </div>
                                    </li>                           
                                    
                                    <li class="num-tbl">{{ $cartVal->quantity }} {{ getpackageName($cartVal->getPrd->package_id) }}</li>
                                    <li class="price_li unit-tbl">{{convert_string($cartVal->cart_price) }} @lang('common.baht') /{{ getpackageName($cartVal->getPrd->package_id) }}</li>                            
                                    <li class="total-tbl">{{convert_string($cartVal->total_price) }} @lang('common.baht')</li>
                                </ul>
                            @endforeach
                            
                        </div>
                    </div>
                @endif
                @if(!empty($main_order) && count($paid_product)) 
                    <h2>@lang('checkout.already_paid')</h2>
                    <div class="table">
                        <div class="table-header">
                            <ul>
                                <li class="sel-tbl">@lang('checkout.seller')</li>
                                <li class="goods-tbl">@lang('checkout.product')</li>
                                <li class="unit-tbl">@lang('checkout.unit_price')</li>
                                <li class="num-tbl">@lang('checkout.qty')</li>
                                <li class="total-tbl">@lang('checkout.price')</li>
                                <li class="paymethod-tbl">@lang('checkout.payment_method')</li>      
                            </ul>
                        </div>
                        <div class="table-content">                     
                            @php($totqty = 0)
                            @foreach($paid_product as $key => $val)
                                @php($totqty = $totqty + $val->quantity)   
                                @php($detail_json = jsonDecodeArr($val->order_detail_json))
                                @php($shop_url = action('ShopController@index',$detail_json['shop_url'] ??''))
                                @php($prd_url = action('ProductDetailController@display',[$detail_json['cat_url']??'',$val->sku]))      

                                <ul>
                                    <li class="product-shop sel-tbl">
                                        <a href="{{ $shop_url }}">
                                        <span class="prod-img"><img src="{{getImgUrl($detail_json['logo']??'','logo')}}" width="50" height="50" alt=""></span>
                                        <span class="shopname"><a href="{{ $shop_url }}">{{ $detail_json['shop_name'][session('default_lang')]??'' }}</a></span>
                                        </a>
                                    </li>                                      
                                    <li class="product goods-tbl">
                                        <div class="dbox-flex">
                                            <div class="dbox-flex">
                                                
                                                <a href="{{ $prd_url }}">
                                                <span class="prod-img prod-134"><img src="{{ getProductImageUrlRunTime($detail_json['thumbnail_image']??'','thumb') }}" width="134" height="100" alt=""></span> </a>
                                            </div>

                                            <div class="ml-2">
                                                <span class="prod-name d-block mb-2"><a href="{{ $prd_url }}">{{ $detail_json['name'][session('default_lang')]??$val->category_name }}</a></span>
                                                <span class="la"><img src="{{ getBadgeImageUrl($detail_json['badge']['icon'] ?? '' )}}" width="30"></span>
                                            </div>
                                        </div>                                                
                                    </li>
                                    <li class="unit-tbl">{{convert_string($val->last_price) }} @lang('common.baht') /{{ $detail_json['package'][session('default_lang')] ?? $val->package_name }}</li>
                                    <li class="add-rem-qty num-tbl">
                                        {{ $val->quantity }} {{ $detail_json['package'][session('default_lang')] ?? $val->package_name }}
                                    </li>
                                    <li class="total-tbl">
                                        {{convert_string($val->total_price) }} @lang('common.baht')
                                    </li>  
                                    <li class="paymethod-tbl">{{$detail_json['payment_method'][session('default_lang')] ?? str_replace('_',' ',strtoupper($val->payment_slug)) }}</li>                                           
                                </ul>
                            @endforeach
                            
                        </div>
                    </div>
                @endif
                <input type="hidden" name="check_pay_method" id="check_pay_method" value="{{ ($tot_amount > 0) ? 1 : 0}}">
                <div id="payment_method_div" @if($tot_amount <= 0) style="display: none;" @endif>
                    <div class="step-title">
                            <span class="step-num">2</span>
                            <h3>@lang('checkout.select_payment_method')<i class="red">*</i></h3>
                    </div>
                  
                    <div class="sel-pay-method">
                        <ul>
                            @if(count($payment_option))
                                @foreach($payment_option as $pkey => $pval)
                                    
                                    <li>
                                        <a href="javascript:void(0)">
                                            <label for="bank1">
                                                @if($pval->slug!='odd' ||($pval->slug=='odd' && !empty($user_odd_info) && $user_odd_info->espa_id!=''))
                                                    <input type="radio" name="payment_method" value="{{ $pval->id }}">
                                                @else
                                                    <input type="radio" name="" id="odd_radio" value="{{ $pval->id }}">
                                                @endif
                                                <div class="bank-img-block">
                                                    <img src="{{ getPayImgUrl($pval->image_name) }}" alt="">
                                                </div>
                                                <div class="bank-name">{!! $pval->paymentOptName->payment_option_name??'' !!}</div>
                                            </label>
                                        </a>
                                    </li>
                                    
                                @endforeach
                            @endif
                        </ul>
                        <p id="e_payment_method" class="error"></p>
                    </div>
                </div>
                <div class="checkout-table-footer clearfix">
                    <div class="col-sm-5 float-right">
                        @if($checkout_type == 'buy-now' || $checkout_type == 'buy-now-end-shopping')
                            <div class="row border-bottom">
                                <span class="col-6">@lang('checkout.total')</span>
                                <span class="col-6">{{ convert_string($tot_amount) }} @lang('common.baht')</span>
                            </div>
                        @endif
                        
                        <div id="delvery_fee_div">
                        </div>
                        <div class="bg-grey">
                            <div class="row">
                                <span class="col-6">@lang('checkout.grand_total') </span>
                                <span class="col-6">
                                    <span id="tot_order_amount">{{convert_string($tot_amount) }}</span> @lang('common.baht')</span>
                            </div>
                        </div>
                        <div class="red text-center pb-2">
                             {!! getStaticBlock('before-checkout-notifiction') !!}
                        </div>
                        <div class="row">                               
                            <button type="button" class="col-12 btn-blue2" id="btn_checkout">@lang('checkout.confirm_order_to_end_shopping')</button>
                        </div>
                        
                    </div>
                </div>
            </div>
        </form>
    @else
        <div> No record found </div>
    @endif
</div>
@endsection 

@section('footer_scripts') 

{!! CustomHelpers::combineCssJs(['js/jquery-ui.min', 'js/jquery-editable-select.min', 'js/cart/cart', 'js/user/user_address'],'js') !!}
<script type="text/javascript">
    
    $('#odd_radio').click(function(e){
        swal({
            title : "@lang('checkout.are_you_sure_want_to_register_odd')",
            text : "@lang('checkout.you_have_not_register_odd')",
            type : 'warning',
            confirmButtonText:lang_yes,
            cancelButtonText:lang_cancel,
            showCloseButton : true,
            showConfirmButton : true,
            showCancelButton: true,
        }).then(res=>{
            window.location.href = "{{action('User\ODDController@oddCondition')}}";
            
        }, rej=>{
            console.log;
        });
    });

    setInterval(check_cart_exist, 5000);

    function check_cart_exist(){
       callAjax(checkCartUrl, 'GET', {}, result=>{
            if(result.status=='notexist'){
                window.location.href=result.url;
            }
        });
    }
</script>
@stop