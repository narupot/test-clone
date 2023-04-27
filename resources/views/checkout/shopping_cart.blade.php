@extends('layouts.app') 

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/myaccount','css/flickity'],'css') !!}

@endsection

@section('header_script')
    var error_msg ={
        txt_delete_confirm : "@lang('common.are_you_sure_to_delete_this_record')",
        yes_delete_it : "@lang('common.yes_delete_it')",
        txt_no : "@lang('common.no')",
        server_error : "@lang('common.something_went_wrong')",
        buynow_ckeck : "@lang('checkout.please_select_product')",
        max_quantity : "@lang('checkout.please_enter_quantity_less_or_equal')",
        quantity_blank_zero : "@lang('checkout.quantity_should_be_greater_than_zero')",
        buynow_title : "@lang('checkout.do_you_want_to_end_shopping_or_pay_only_for_the_products')",
        end_shopping : "@lang('checkout.end_shopping')",
        buynow : "@lang('checkout.buy_now')",
        update_price : "@lang('checkout.update_price')",
        pay_cerdit : "@lang('checkout.are_you_sure_want_to_pay')",
    }; 
    var removeCart = "{{action('Checkout\CartController@removeCart')}}";
    var removeOrder = "{{action('Checkout\CartController@removeOrder')}}";
    var updateCart = "{{action('Checkout\CartController@updateCart')}}";
    var payProduct = "{{action('Checkout\CartController@payProduct')}}";
    var addProductToBargain = "{{action('PopUpController@getCheckBargainPopUp')}}";
    var updateCartPrice = "{{ action('Checkout\CartController@updateCartPrice') }}";
@endsection

@section('content')
    
<div class="container1">         
    <div class="row">
        <div class="col-sm-12"> 
            @include('includes.buyer_shopping_tab',['purchased_products'=>$pur_prds_in_shop_list,'list_of_shopping_list'=>$total_prds_in_shop_list]) 

            <div class="tab-content">
                <div class="tab-pane active" id="tab-seler5">                               
                    <div class="row form-group">
                        <div class="col-sm-8 checkwrap-sel-all">
                            <!-- <label class="chk-wrap">
                                <input type="checkbox">
                                <span class="chk-mark">@lang('checkout.select_all')</span>
                            </label>   -->                                      
                        </div>
                        {{-- @if(!empty($orderInfo) && count($orderDetails)) 
                            <div class="col-sm-4 text-right">                                   
                                <a href="{{route('buy-now-end-shopping')}}" class="btn" >@lang('checkout.buy_now')</a>                                    
                            </div> 
                        @endif   --}}                           
                    </div>    
                    @if(!empty($orderInfo) && count($orderDetails))                         
                        <div class="table-responsive checkout-order-table tblseller-pannel">
                            <div class="table">
                                <div class="table-header">
                                    <ul>                                                    
                                        <li class="item-product">
                                        <span class="sel-pay-item" style="display: none;">@lang('checkout.click_to_select_payment_item')</span>
                                        @lang('checkout.product')</li>                                        
                                        <li>@lang('checkout.shop')</li>
                                        <li>@lang('checkout.unit_price')</li>
                                        <li>@lang('checkout.qty')</li>
                                        <li>@lang('checkout.price')</li>                                                  
                                        <li></li>
                                    </ul>
                                </div>
                                <div class="table-content">                     
                                    
                                    @php($totqty = 0)
                                    @foreach($orderDetails as $cartKey => $cartVal)
                                        @php($totqty = $totqty + 1)            
                                        <ul id="cart_{{ $cartVal->id }}">                                      
                                            <li class="product">
                                                <div class="dbox-flex">
                                                    <div class="dbox-flex">
                                                        
                                                        <a href="{{ action('ProductDetailController@display',[$cartVal->getCat->url,$cartVal->getPrd->sku])}}">
                                                        <span class="prod-img prod-134"><img src="{{ getProductImageUrlRunTime($cartVal->getPrd->thumbnail_image,'thumb') }}" width="134" height="100" alt=""></span> </a>
                                                    </div>

                                                    <div class="ml-2">
                                                        <span class="prod-name d-block mb-2"><a href="{{ action('ProductDetailController@display',[$cartVal->getCat->url,$cartVal->getPrd->sku])}}">{{ $cartVal->getCatDesc->category_name??'' }}</a></span>
                                                        <span class="la"><img src="{{ getBadgeImage($cartVal->getPrd->badge_id) }}" width="30"></span>
                                                    </div>
                                                </div>                                                
                                            </li>                           
                                            
                                            <li>
                                                <a href="{{ action('ShopController@index',$cartVal->getShop->shop_url)}}" class="link-skyblue">{{ $cartVal->getShopDesc->shop_name??'' }}</a>
                                            </li>
                                            <li class="price_li"><label class="prd-unit-price">{{convert_string($cartVal->cart_price) }} </label> @lang('common.baht') /{{ getPackageName($cartVal->getPrd->package_id) }}</li>
                                            <li class="add-rem-qty"><span class="spiner" data-cartid="{{$cartVal->id}}">
                                                    <span class="decrease fas fa-minus">  </span>
                                                    <input type="number" class="spinNum" value="{{ $cartVal->quantity }}" min="0" max="{{ $cartVal->getPrd->quantity }}" @if($cartVal->product_from=='bargain') readonly="readonly" @endif>
                                                    <span class="increase fas fa-plus">  </span>
                                                </span>
                                                <span class="qty-label">{{ getPackageName($cartVal->getPrd->package_id) }}</span>
                                            </li>
                                            <li>
                                                <label class="prd-total-price">{{convert_string($cartVal->total_price) }}</label> @lang('common.baht')
                                                @if($cartVal->product_from == 'bargain')
                                                    <div class="bargained-price grey">@lang('checkout.price_has_already_bargained')</div>
                                                @endif
                                            </li>                                                   
                                            <li>
                                                <div class="del-action cart-remove"><a href="javascript:;">@lang('common.delete') <i class="fas fa-times"></i></a></div>
                                                <div class="bargain-action">  
                                                    {{-- @if($cartVal->product_from == 'normal')    
                                                    <a href="{{action('PopUpController@getBargainPopUp', $cartVal->product_id)}}" rel="{{$cartVal->product_id}}" qty="{{ $cartVal->quantity }}" class="btn-default bargain">@lang('checkout.bargain')</a>                               
                                                    @endif --}}

                                                    @if(isset($user_credits[$cartVal->shop_id]) && $user_credits[$cartVal->shop_id]->remain_credit >= $cartVal->total_price)
                                                        @php($user_credits[$cartVal->shop_id]->remain_credit = $user_credits[$cartVal->shop_id]->remain_credit - $cartVal->total_price)
                                                        <button class="btn-blue2 all_pay_credit d-none" data-action="single_credit">@lang('checkout.pay_credit_term')</button>
                                                        
                                                    @endif
                                                </div>
                                            </li>
                                        </ul>
                                    @endforeach
                                    
                                </div>
                            </div>
                           
                        </div>
                         <div class="row checkout-table-footer clearfix">
                            <div class="col-sm-8">
                                {{-- @if(count($user_credits) && $show_credit)
                                    
                                    <h2>@lang('checkout.credit_balance')</h2>
                                    <ul class="balance-info">
                                        @foreach($user_credits as $key => $crd_val)
                                            @isset($shop_details[$crd_val->shop_id]['shop_url'])
                                                <li>
                                                    
                                                    <a href="{{ action('ShopController@index',$shop_details[$crd_val->shop_id]['shop_url'])}}" class="skyblue">
                                                        {{ $shop_details[$crd_val->shop_id]['shop_name'] ?? ''}}
                                                    </a>
                                                    
                                                    {{ convert_string($crd_val->tot_remain_credit) }} / {{ convert_string($crd_val->tot_credit) }}
                                                </li>
                                            @endif 
                                        @endforeach 
                                    </ul> 
                                    
                                @endif --}}
                            </div>
                            <div class="col-sm-4 float-right">
                                <div class="row">
                                    <span class="col-6">@lang('checkout.total_products')</span>
                                    <span class="col-6"><span id="tot_cart_items_cart">{{ $totqty }}</span> @lang('checkout.item')</span>
                                </div>                                                                                      
                                <div class="bg-grey">
                                    <div class="row">
                                        <span class="col-6">@lang('checkout.grand_total') </span>
                                        <span class="col-6"><span id="tot_order_amount">{{ convert_string($orderInfo->total_final_price) }} </span>@lang('common.baht')</span>
                                    </div>
                                </div> 
                                <div class="dbox-flex1 text-right">
                                    @if(count($user_credits) && $show_credit)
                                        <button class="btn-blue2 mr-3 all_pay_credit d-none" id="all_pay_credit">@lang('checkout.pay_credit_term')</button>
                                    @endif
                                    <a href="{{route('buy-now-end-shopping')}}" class="btn">@lang('checkout.buy_now')</a>
                                </div>                                             
                            </div>
                        </div>
                    @else
                        <div>No record found</div>
                    @endif
                </div>
            </div>     
        </div>
    </div>  
</div>
@endsection 

@section('footer_scripts') 

{!! CustomHelpers::combineCssJs(['js/cart/cart'],'js') !!}

@stop