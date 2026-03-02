@extends('layouts.app') 

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/myaccount','css/bootstrap-select','css/ui-grid-unstable'],'css') !!}


@endsection

@section('header_script')
  var success = "@lang('common.success')";
  var currency = "@lang('common.baht')";
  var txt_no = "@lang('common.no')";
  var text_ok_btn = "@lang('common.ok_btn')";
  var text_success = "@lang('common.text_success')";
  var text_error = "@lang('common.text_error')";
  var text_yes_remove_it = "@lang('common.yes_remove_it')";
  var yes_delete_it = "@lang('common.yes_delete_it')";
  var txt_delete_confirm = "@lang('common.are_you_sure_to_delete_this_record')";
  var server_error = "@lang('common.something_went_wrong')";
  var now_ckeck = "@lang('common.please_select_at_least_one_recode')";
  var requiredBasePrice = '@lang('common.please_enter_base_unit_price')';
  var requiredUnitPrice = '@lang('common.please_enter_unit_price')';
  var rejectMessage = '@lang('common.are_u_want_to_sure_reject_this_offer')';
  var acceptMessage = '@lang('common.are_u_want_to_sure_accept_this_offer')';
  var addtocartMessage = '@lang('common.are_u_want_to_sure_you_want_to_add_to_cart_this_product')';
  var buyMessage = '@lang('common.are_u_want_to_sure_you_want_to_buy_this_product')';
  var addbargainMessage = '@lang('common.are_u_want_to_sure_you_want_to_add_bargain_at_product')';
  var rejectAllBargain = '{{action('Seller\BargainController@rejectAllBargain')}}';
@endsection
@section('content')
<div class="ng-cloak">     
    <div class="row">
        <div class="col-sm-12"> 

            @include('includes.seller_panel_top_menu')    
             
            <div class="tab-content">
                <div class="tab-pane @if($activetab == 'bargain') active show @endif" id="tab-seler2">
                  <div class="productbargain-block">
                  <ul class="sort-by-block nav">
                    <!-- <li>@lang('bargain.sort_product_by') :</li> -->
                    {{-- <li><a href="{{action('Seller\BargainController@index','bytime')}}" @if($sortby == 'bytime')class="active"@endif >@lang('bargain.sort_by_time')</a></li> --}} 
                    <li><a href="{{action('Seller\BargainController@index','bycustomer')}}" @if($sortby == 'bycustomer')class="active"@endif>@lang('bargain.sort_by_customer')</a></li>
                    <li><a href="{{action('Seller\BargainController@index','byproduct')}}" @if($sortby == 'byproduct')class="active"@endif>@lang('bargain.sort_by_product')</a></li>
                  </ul>

                  @if($sortby == 'bycustomer')
                    <div class="tab-pane" id="sort-by-customer">
                        <div class="form-group">
                            <div class="select-all">
                              <label class="chk-wrap checkedAll">
                                <input type="checkbox">
                                <span class="chk-mark">@lang('bargain.select_all')</span>
                              </label>
                              <button type="button" class="btn-dark-grey refuse_all_bargaining">@lang('bargain.refuse_all_bargaining')</button>
                            </div>
                        </div>
                        
                        @php($user_id = '')
                        @php($old_user_id = '')
                        @foreach($results as $result)
                          @if($result->user_id != $user_id)
                            @if(!empty($user_id))
                                 </div>
                                </div>
                             </div>
                           @endif
                          @php($user_id = $result->user_id) 

                        
                        <div class="table-responsive bargain-order-table rounded-0 shadow-none mb-3">
							@if(!isMobile())
                            <div class="customer-head">
                              <div class="wrap-head">
                                <!--label class="chk-wrap">
                                  <input type="checkbox" value="{{$result->bargain_id}}">
                                  <span class="chk-mark"></span>
                                </label-->
                                <img src="{{getUserImage($result->image)}}" alt="" width="40" height="40" class="rounded-circle">
                                <h4>{{$result->display_name}}</h4>
                                <div class="del-action ml-auto"><a href="javascript:void(0)" class="skyblue">@lang('bargain.hide') <i class="fas fa-chevron-down"></i></a></div>
                              </div>
                            </div>
                            @endif
                            <!-- Mobile header Start -->
                            @if(isMobile())
                            <div class="customer-head">
                              <div class="wrap-head">
                                <label class="chk-wrap">
						            <input type="checkbox" value="{{$result->bargain_id}}">
						            <span class="chk-mark"></span>
						        </label>
                                <img src="{{getUserImage($result->image)}}" alt="" width="40" height="40" class="rounded-circle">
                                <h4>{{$result->display_name}}</h4>
                                <div class="del-action ml-auto"><a href="javascript:void(0)" class="skyblue">@lang('bargain.hide') <i class="fas fa-chevron-down"></i></a></div>
                              </div>
                            </div>
                            @endif 
                            <!-- Mobile header end -->
                            <div class="table">
                              <div class="table-header bg-transparent border-bottom mb-hide">
                                <ul>
                                  <li class="rounded-0">@lang('bargain.product')</li>
                                  <li>@lang('bargain.qty')</li>
                                  <li>@lang('bargain.offer_to_buy')</li>
                                  <li>@lang('bargain.offer_to_sell')</li>
                                  <li>@lang('bargain.last_price_to_sell')</li>
                                </ul>   
                              </div>
                              <div class="table-content">
                              @endif
                              @php($curr_user_id = $result->user_id)

                                @if(!isMobile())
                                <ul class="seller-bargain-tbl">
                                    <li class="align-middle text-left">
                                        <div class="pd-box">
                                          <label class="chk-wrap">
                                            <input type="checkbox" value="{{$result->bargain_id}}">
                                            <span class="chk-mark"></span>
                                          </label>
                                          <div class="info-box">
                                            <!--h4 class="skyblue">{{$result->display_name}}</h4-->
                                            <div class="prod-img"><a href="{{action('ProductDetailController@display', [$result->caturl, $result->sku])}}"><img src="{{getProductImageUrlRunTime($result->thumbnail_image, 'thumb_93x79')}}" alt="" width="93" height="79"></a></div>
                                            <div class="wrap-name">
                                              <a href="{{action('ProductDetailController@display', [$result->caturl, $result->sku])}}"><span class="name">{{$result->category_name}}</span></a>
                                              <span class="qty">{{numberFormat($result->unit_price)}} @lang('common.baht') / {{ getPackageName($result->package_id) }}</span>
                                              <span class="date">@lang('common.update') {{getDateFormat($result->updated_at)}}</span>
                                              <span class="remark">@lang('product.remark') : 1 {{ $result->package_name }} = {{ $result->weight_per_unit }} {{ $result->unit_name }}</span>
                                              <span class="sb"><img src="{{getBadgeImageUrl($result->icon)}}" alt="" width="36" height="36"></span>
                                                      </div>
                                          </div>
                                        </div>
                                        
                                    </li>                             
                                      <!--li class="align-middle"><img src="{{getBadgeImageUrl($result->icon)}}" alt="" width="36" height="36"></li-->
                                    <li class="align-middle">
                                        <div class="qty-wrap">
                                          {{$result->qty}}/{{$result->package_name}}
                                        </div>
                                    </li>
                                    <li>
                                        <div class="offer-buy">
                                          <table>
                                            <tbody>
                                              <tr>
                                                <td class="grey-dark"></td>
                                                <td class="text-center">@lang('bargain.price')/@lang('bargain.base_unit')</td>
                                                <td class="text-center">@lang('bargain.price')/@lang('bargain.unit')</td>
                                                <td class="text-center">@lang('bargain.total_price')</td>
                                              </tr>
                                              
                                              @if(isset($productBargainDetailsOfBuyer[$result->bargain_id]) && count($productBargainDetailsOfBuyer[$result->bargain_id])>1)
                                                @foreach($productBargainDetailsOfBuyer[$result->bargain_id] as $key=>$productbargaindetail)
                                                  <tr>
                                                    @if($productbargaindetail['bar_status'] == '1')
                                                      <td class="text-left">@lang('bargain.last')</td>
                                                    @else 
                                                      <td class="text-left">@lang('bargain.previous')</td>
                                                    @endif
                                                    <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['base_unit_price'])}} <br>@lang('common.baht')</td>
                                                    <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['unit_price'])}} <br>@lang('common.baht')</td>
                                                    <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['total_price'])}} <br>@lang('common.baht')</td>
                                                  </tr>
                                                  
                                                  @if($productbargaindetail['bar_status'] == '1')
                                                    <tr>
                                                      <td colspan="3" class="text-center">
                                                        <a class="btn-dark-grey act-reject" rel="{{action('Seller\BargainController@rejectBargain',$productbargaindetail['id'])}}">@lang('bargain.reject')</a>
                                                        <a class="btn act-accept" rel="{{action('Seller\BargainController@acceptBargain',$productbargaindetail['id'])}}">@lang('bargain.accept')</a>
                                                      </td>
                                                    </tr>
                                                  @endif

                                                @endforeach
                                              @else
                                                <tr>
                                                  <td class="text-left">@lang('bargain.previous')</td>
                                                  <td class="text-center grey-dark">- @lang('common.baht')</td>                             
                                                  <td class="text-center grey-dark">- @lang('common.baht')</td>
                                                  <td class="text-center grey-dark">- @lang('common.baht')</td>
                                                </tr>
                                                @foreach($productBargainDetailsOfBuyer[$result->bargain_id] as $key=>$productbargaindetail)
                                                  <tr>
                                                    @if($productbargaindetail['bar_status'] == '1')
                                                      <td class="text-left">@lang('bargain.last')</td>
                                                    @else 
                                                      <td class="text-left">@lang('bargain.previous')</td>
                                                    @endif
                                                    <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['base_unit_price'])}} <br>@lang('common.baht')</td>
                                                    <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['unit_price'])}} <br>@lang('common.baht')</td>
                                                    <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['total_price'])}}<br> @lang('common.baht')</td>
                                                  </tr>
                                                  @if($productbargaindetail['bar_status'] == '1')
                                                    <tr>
                                                      <td colspan="3" class="text-center">
                                                        <a class="btn-dark-grey act-reject" rel="{{action('Seller\BargainController@rejectBargain',$productbargaindetail['id'])}}">@lang('bargain.reject')</a>
                                                        <a class="btn act-accept" rel="{{action('Seller\BargainController@acceptBargain',$productbargaindetail['id'])}}">@lang('bargain.accept')</a>
                                                      </td>
                                                    </tr>
                                                  @endif
                                                @endforeach
                                              @endif
                                            </tbody>
                                          </table>                          
                                        </div>
                                    </li>
                                    <li>
                                        <div class="offer-sell">
                                          <table>
                                            <tbody>
                                              <tr>
                                                <td class="grey-dark"></td>
                                                <td class="text-center">@lang('bargain.price')/@lang('bargain.base_unit')</td>
                                                <td class="text-center">@lang('bargain.price')/@lang('bargain.unit')</td>
                                                <td class="text-center">@lang('bargain.total_price')</td>
                                              </tr>
                                              @php($status = 1)
                                              @if(isset($productBargainDetailsOfSeller[$result->bargain_id]) && count($productBargainDetailsOfSeller[$result->bargain_id]))
                                                  @php ($countBargainDetailsOfSeller = count($productBargainDetailsOfSeller[$result->bargain_id])-1)
             
                                                 
                                                  @foreach($productBargainDetailsOfSeller[$result->bargain_id] as $key=>$productbargaindetail)
                                                    <tr>

                                                      @if($key == 0)
                                                        <td class="text-left">@lang('bargain.last')</td>
                                                      @else
                                                        <td class="text-left">@lang('bargain.previous')</td>
                                                      @endif

                                                      <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['base_unit_price'])}} <br>@lang('common.baht')</td>
                                                      <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['unit_price'])}} <br>@lang('common.baht')</td>
                                                      <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['total_price'])}} <br>@lang('common.baht')</td>
                                                    </tr>
                                                    @php($status = $productbargaindetail['bar_status'])
                                                @endforeach
                                                    @if($status == '2')
                                                        <tr>
                                                          <td class="text-left">@lang('bargain.status') </td>
                                                          <td class="text-center grey-dark" colspan="3"> @lang('bargain.accept_price')</td>
                                                        </tr>
                                                    @elseif($status == '3')
                                                      <tr class="status">
                                                        <td class="text-left">@lang('bargain.status') </td>
                                                        <td class="text-center grey-dark" colspan="3"> @lang('bargain.shop_reject')</td>
                                                      </tr>   
                                                    @else
                                                        <tr>
                                                          <td class="text-left">@lang('bargain.status') </td>
                                                          <td class="text-center grey-dark" colspan="3"> @lang('bargain.adjust_price')</td>
                                                        </tr>
                                                    @endif
                                                @else
                                                  <tr>
                                                    <td class="text-left"></td>
                                                    <td class="text-center grey-dark">- @lang('common.baht')</td>
                                                    <td class="text-center grey-dark">- @lang('common.baht')</td>
                                                    <td class="text-center grey-dark">- @lang('common.baht')</td>
                                                  </tr>
                                                 <tr>
                                                      <td class="text-left">@lang('bargain.status') </td>
                                                      <td class="text-center grey-dark" colspan="3"> @lang('bargain.customer_offer')</td>
                                                 </tr>
                                                    
                                                @endif
                                                <form name="submitAdjustprice" id="submitAdjustprice_{{$result->bargain_id}}" action="{{action('Seller\BargainController@adjustPriceFromSeller', $result->bargain_id)}}" method="post"> 
                                                 <input type="hidden" name="_token" id="_token" value="{{csrf_token()}}">
                                                  <tr class="adjustPricefrom_{{$result->bargain_id}}" style="display:none">
                                                    <td class="text-left">@lang('bargain.last')</td>
                                                     <input type="hidden" name="qty" value="{{$result->qty}}">
                                                     <input type="hidden" name="weight_per_unit" value="{{$result->weight_per_unit}}"> 
                                                    <td class="text-center">
                                                       <input type="text" class="text-center" name="base_unit_price" id="base_unit_price" data-type="price">
                                                    </td>
                                                    <td class="text-center"> 
                                                      <input type="text" class="text-center" name="unit_price" id="unit_price" data-type="price"></td>
                                                    <td class="text-center bargainTotal"></td>

                                                  </tr>
                                                  <!--tr>
                                                    <td class="text-left">@lang('bargain.status')</td>
                                                    <td colspan="2" class="text-left blue">@lang('bargain.customer_offer')</td>
                                                  </tr-->
                                                  <tr class="adjustPricefrom_{{$result->bargain_id}}" style="display:none">
                                                    <td colspan="3" class="text-center">
                                                      <a class="btn-dark-grey submitAdjustprice" rel="{{$result->bargain_id}}">@lang('bargain.submit')</a>
                                                      <a class="btn adjustPricehide" rel="{{$result->bargain_id}}">@lang('bargain.cancel')</a>
                                                    </td>
                                                  </tr>
                                                  <tr class="adjustPriceshow" id="adjustPriceshow_{{$result->bargain_id}}">
                                                    <td class="text-left"></td>
                                                    <td colspan="2" class="text-center">
                                                      <a class="btn-grey" rel="{{$result->bargain_id}}">@lang('bargain.adjust_price')</a>
                                                    </td>
                                                  </tr>
                                                </form>
                                               

                                            </tbody>
                                          </table>                          
                                        </div>
                                    </li>

                                    <li>
                                      <div class="last-sell-price">
                                        <table width="100%;">
                                          <tbody>
                                            <tr> 
                                                <td class="text-center">@lang('bargain.price')/@lang('bargain.base_unit')</td>                                         
                                                <td class="text-center">@lang('bargain.price')/@lang('bargain.unit')</td>
                                                <td class="text-center">@lang('bargain.total_price')</td>
                                            </tr>
                                            <tr> 
                                              <td class="text-center blue price-txt">{{numberFormat($result->base_unit_price)}}<br>@lang('common.baht')</td>                   
                                              <td class="text-center blue price-txt">{{numberFormat($result->curr_unit_price)}}<br>@lang('common.baht')</td>
                                              <td class="text-center blue total-txt">{{numberFormat($result->curr_total_price)}}<br>@lang('common.baht')</td>
                                            </tr>                                       
                                          </tbody>
                                        </table>                          
                                      </div>
                                    </li>
                                </ul>
                                @endif

                                <!-- Mobile table by customer start -->
	                      		@if(isMobile())
								<div class="bargain-tbl-mobile seller-tbl-mob-customer">
									<div class="bargin-tbl-row">
										<div class="bargin-inner-row">
											<div class="nameshop">
												<div class="nameshop-txt">
													<label class="chk-wrap">
										            	<input type="checkbox" value="{{$result->bargain_id}}">
										            	<span class="chk-mark"></span>
										          	</label>
											         <a href="{{action('ProductDetailController@display', [$result->caturl, $result->sku])}}">
											        	<h4 class="skyblue">{{$result->category_name}}</h4>
											        </a>
												</div>
												<span class="chat-wrap">
													<a href="javascript:void(0)" class="btn-default grey-dark"><i class="fas fa-comments"></i></a>
												</span>
											</div>
											<div class="product-col">
												<div class="pd-box">		                
											        <div class="wrap-product">
											          <a href="{{action('ProductDetailController@display', [$result->caturl, $result->sku])}}"><img src="{{getProductImageUrlRunTime($result->thumbnail_image, 'thumb_93x79')}}" alt="" width="" height="79"></a>
											        </div>
											        <div class="pd-desc">
											           	<div class="wrap-name">
											           		<span class="date">@lang('common.update') {{getDateFormat($result->updated_at)}}</span>
											              	<a href="{{action('ProductDetailController@display', [$result->caturl, $result->sku])}}"><span class="name">{{$result->category_name}}</span></a>
											                <div class="sb"><img src="{{getBadgeImageUrl($result->icon)}}" alt="" width="36" height="36">
											                </div>
											           </div>
											        </div>
											    </div>
											    <div class="price-qty">
											    	<span class="qty">{{numberFormat($result->unit_price)}} @lang('common.baht') / {{ getPackageName($result->package_id) }} </span>
											    	<div class="qty-wrap">	          
											          {{$result->qty}}/{{$result->package_name}}
											        </div>
											    </div>
											</div>	                          			
										</div>
										<div class="bargin-inner-row">	
											<div class="offer-buy">
												<h3>@lang('bargain.offer_to_buy')</h3>
												<table width="100%">            
										            <tr class="offer-buy-info">
										                <td class="grey-dark"></td>
											            <td class="text-center">@lang('bargain.price')/@lang('bargain.base_unit')</td>
											            <td class="text-center">@lang('bargain.price')/@lang('bargain.unit')</td>
											            <td class="text-center">@lang('bargain.total_price')</td>
										            </tr>             
										            @if(isset($productBargainDetailsOfBuyer[$result->bargain_id]) && count($productBargainDetailsOfBuyer[$result->bargain_id])>=1)
										              @foreach($productBargainDetailsOfBuyer[$result->bargain_id] as $key=>$productbargaindetail)
										                <tr>
										                  @if($key==0)
										                    <td class="text-left">@lang('bargain.last')</td>
										                  @else 
										                    <td class="text-left">@lang('bargain.previous')</td>
										                  @endif
										                  <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['base_unit_price'])}} <br>@lang('common.baht')</td>
										                  <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['unit_price'])}} <br>@lang('common.baht')</td>
										                  <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['total_price'])}} <br>@lang('common.baht')</td>
										                </tr>
										                
										                @if($productbargaindetail['bar_status'] == '1')
										                  <tr>
										                    <td colspan="4" class="text-center">
										                      <a class="btn-dark-grey act-reject" rel="{{action('Seller\BargainController@rejectBargain',$productbargaindetail['id'])}}">@lang('bargain.reject')</a>
										                      <a class="btn act-accept" rel="{{action('Seller\BargainController@acceptBargain',$productbargaindetail['id'])}}">@lang('bargain.accept')</a>
										                    </td>
										                  </tr>
										                @endif

										            @endforeach
										            
										            @else
										              <tr>
										                <td class="text-left">@lang('bargain.previous')</td>
										                <td class="text-center grey-dark">- @lang('common.baht')</td>                             
										                <td class="text-center grey-dark">- @lang('common.baht')</td>
										                <td class="text-center grey-dark">- @lang('common.baht')</td>
										              </tr>

										              @foreach($productBargainDetailsOfBuyer[$result->bargain_id] as $key=>$productbargaindetail)
										                <tr>
										                  @if($key == 0)
										                    <td class="text-left">@lang('bargain.last')</td>
										                  @else 
										                    <td class="text-left">@lang('bargain.previous')</td>
										                  @endif
										                  <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['base_unit_price'])}} <br>@lang('common.baht')</td>
										                  <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['unit_price'])}} <br>@lang('common.baht')</td>
										                  <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['total_price'])}}<br> @lang('common.baht')</td>
										                </tr>
										                @if($productbargaindetail['bar_status'] == '1')
										                  <tr>
										                    <td colspan="3" class="text-center">
										                      <a class="btn-dark-grey act-reject" rel="{{action('Seller\BargainController@rejectBargain',$productbargaindetail['id'])}}">@lang('bargain.reject')</a>
										                      <a class="btn act-accept" rel="{{action('Seller\BargainController@acceptBargain',$productbargaindetail['id'])}}">@lang('bargain.accept')</a>
										                    </td>
										                  </tr>
										                @endif
										              @endforeach
										            @endif							               
										        </table>
											</div>
										</div>
										<div class="bargin-inner-row">	
											<div class="offer-buy">
												<h3>@lang('bargain.offer_to_sell')</h3> 
												<table width="100%">
												  <tbody>
												    <tr>
												      <td class="grey-dark"></td>
												      <td class="text-center">@lang('bargain.price')/@lang('bargain.base_unit')</td>
												      <td class="text-center">@lang('bargain.price')/@lang('bargain.unit')</td>
												      <td class="text-center">@lang('bargain.total_price')</td>
												    </tr>
												    @php($status = 1)
												    @if(isset($productBargainDetailsOfSeller[$result->bargain_id]) && count($productBargainDetailsOfSeller[$result->bargain_id]))
												        @php ($countBargainDetailsOfSeller = count($productBargainDetailsOfSeller[$result->bargain_id])-1)
												        @foreach($productBargainDetailsOfSeller[$result->bargain_id] as $key=>$productbargaindetail)

												            <tr>
												                @if($key == 0)
												                  <td class="text-left">@lang('bargain.last')</td>
												                @else
												                  <td class="text-left">@lang('bargain.previous')</td>
												                @endif

												                <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['base_unit_price'])}} <br>@lang('common.baht')</td>
												                <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['unit_price'])}} <br>@lang('common.baht')</td>
												                <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['total_price'])}} <br>@lang('common.baht')</td>
												            </tr>
												            @php($status = $productbargaindetail['bar_status'])
												        @endforeach
												        
												      @else
												        <tr>
												          <td class="text-left"></td>
												          <td class="text-center grey-dark">- @lang('common.baht')</td>
												          <td class="text-center grey-dark">- @lang('common.baht')</td>
												          <td class="text-center grey-dark">- @lang('common.baht')</td>
												        </tr>
												        <tr>
												          <td class="text-left">@lang('bargain.status') </td>
												          <td class="text-center grey-dark" colspan="3"> @lang('bargain.customer_offer')</td>
												        </tr>
												          
												      @endif
												      <form name="submitAdjustprice" id="submitAdjustprice_{{$result->bargain_id}}" action="{{action('Seller\BargainController@adjustPriceFromSeller', $result->bargain_id)}}" method="post"> 
												       <input type="hidden" name="_token" id="_token" value="{{csrf_token()}}">
												        <tr class="adjustPricefrom_{{$result->bargain_id}}" style="display:none">
												          <td class="text-left">@lang('bargain.last')</td>
												           <input type="hidden" name="qty" value="{{$result->qty}}">
												           <input type="hidden" name="weight_per_unit" value="{{$result->weight_per_unit}}"> 
												          <td class="text-center">
												             <input type="text" class="text-center base_unit_price" name="base_unit_price" data-type="price">
												          </td>
												          <td class="text-center"> 
												            <input type="text" class="text-center unit_price" name="unit_price" data-type="price"></td>
												          <td class="text-center bargainTotal"></td>

												        </tr>
												        <!--tr>
												          <td class="text-left">@lang('bargain.status')</td>
												          <td colspan="2" class="text-left blue">@lang('bargain.customer_offer')</td>
												        </tr-->
												        <tr class="adjustPricefrom_{{$result->bargain_id}}" style="display:none">
												          <td colspan="3" class="text-center">
												            <a class="btn-grey submitAdjustprice" rel="{{$result->bargain_id}}">@lang('bargain.bargaining')</a>
												            <!--a class="btn-dark-grey btn adjustPricehide" rel="{{$result->bargain_id}}">@lang('bargain.cancel')</a-->
												          </td>
												        </tr>
												        <tr class="adjustPriceshow" id="adjustPriceshow_{{$result->bargain_id}}">
												          
												          <td colspan="4" class="text-center">
												            <a class="btn-default" rel="{{$result->bargain_id}}">@lang('bargain.adjust_price')</a>
												          </td>
												        </tr>
												      </form>
												      @if($status == '2')
												            <tr class="status">
												              <td class="text-right" colspan="2">@lang('bargain.status') </td>
												              <td class="text-left blue" colspan="2"> @lang('bargain.accept_price')</td>
												            </tr>
												        @else
												            <tr class="status">
												              <td class="text-right" colspan="2">@lang('bargain.status') </td>
												              <td class="text-left blue" colspan="2"> @lang('bargain.adjust_price')</td>
												            </tr>
												        @endif
												  </tbody>
												</table>
											</div>	
										</div>
										<div class="bargin-inner-row">
											<div class="offer-buy latest-offer-buy">
												<h3>@lang('bargain.last_price_to_sell')</h3>
												<div class="last-sell-price">
			                                        <table width="100%;">            
			                                            <tr> 
			                                                <td class="text-center">@lang('bargain.price')/@lang('bargain.base_unit')</td>
			                                                <td class="text-center">@lang('bargain.price')/@lang('bargain.unit')</td>
			                                                <td class="text-center">@lang('bargain.total_price')</td>
			                                            </tr>
			                                            <tr> 
			                                              <td class="text-center blue price-txt">{{numberFormat($result->base_unit_price)}}<br>@lang('common.baht')</td>
			                                              <td class="text-center blue price-txt">{{numberFormat($result->curr_unit_price)}}@lang('common.baht')</td>
			                                              <td class="text-center blue total-txt">{{numberFormat($result->curr_total_price)}}<br>@lang('common.baht')</td>
			                                            </tr>	                                
			                                        </table>                          
			                                    </div>
											</div>
										</div>
									</div>
								</div> 
	                      		@endif
	                      		<!-- Mobile table by customer End -->
                                
	                          @endforeach

	                        	</div>
                    		</div>                       		

                  		</div>        
                  </div>
                  
                  @elseif($sortby == 'byproduct')
                      <div class="tab-pane">
                          <div class="form-group">
                            <div class="select-all">
                              <label class="chk-wrap checkedAll">
                                <input type="checkbox">
                                <span class="chk-mark">@lang('bargain.select_all')</span>
                              </label>
                              <button type="button" class="btn-dark-grey refuse_all_bargaining">@lang('bargain.refuse_all_bargaining')</button>
                            </div>
                          </div>
                          @php($cat_id = '')
                          @foreach($results as $result)
                              @if($result->cat_id != $cat_id)
                                @if(!empty($cat_id))
                                      </div>
                                    </div>
                                 </div> 
                                @endif
                               @php($cat_id = $result->cat_id) 
                              <div class="table-responsive bargain-order-table rounded-0 shadow-none mb-3">
                              	@if(!isMobile())
                                <div class="customer-head">
                                  <div class="pd-box">
                                    <div class="wrap-product">
                                      <a href="{{action('ProductDetailController@display', [$result->caturl, $result->sku])}}"><img src="{{getProductImageUrlRunTime($result->thumbnail_image, 'thumb_93x79')}}" alt="" width="95" height="80" class="rounded"></a>
                                    </div>            
                                    <div class="pd-desc">
                                      <div class="wrap-name">
                                          <a href="{{action('ProductDetailController@display', [$result->caturl, $result->sku])}}"><span class="name mb-0">{{$result->category_name}}</span></a>
                                          <div class="d-flex align-items-center">
                                            <span class="pr-2">@lang('bargain.standard')</span>
                                            <span class="sb mb-0"><img src="{{getBadgeImageUrl($result->icon)}}" alt="" width="36" height="36"></span>
                                          </div>
                                          <span class="qty mb-0">{{numberFormat($result->unit_price)}} @lang('common.baht') / {{ getPackageName($result->package_id) }}</span>
                                           <span class="remark">@lang('product.remark') : 1 {{ $result->package_name }} = {{ $result->weight_per_unit }} {{ $result->unit_name }}</span>              
                                        </div>                
                                    </div>
                                    <div class="del-action ml-auto"><a href="javascript:void(0)" class="skyblue">@lang('bargain.hide') <i class="fas fa-chevron-down"></i></a></div>
                                  </div>
                                </div>
                                @endif
                                @if(isMobile())
	                            <div class="customer-head">
	                            	<div class="pd-box">
		                            	<label class="chk-wrap">
								            <input type="checkbox" value="{{$result->bargain_id}}"><span class="chk-mark"></span>
								        </label>
	                                    <div class="wrap-product">
	                                      <a href="{{action('ProductDetailController@display', [$result->caturl, $result->sku])}}"><img src="{{getProductImageUrlRunTime($result->thumbnail_image, 'thumb_93x79')}}" alt="" width="95" height="80" class="rounded"></a>
	                                    </div>            
	                                    <div class="pd-desc">
	                                      <div class="wrap-name mb-0">
	                                          <a href="{{action('ProductDetailController@display', [$result->caturl, $result->sku])}}"><span class="name mb-0">{{$result->category_name}}</span></a>
	                                          <div class="d-flex align-items-center mob-block">
	                                            <span class="pr-2">@lang('bargain.standard')</span>
	                                            <span class="sb mb-1"><img src="{{getBadgeImageUrl($result->icon)}}" alt="" width="36" height="36"></span>
	                                          </div>
	                                          <span class="qty mb-0">{{numberFormat($result->unit_price)}} @lang('common.baht') / {{ getPackageName($result->package_id) }}</span>
                                                         
	                                        </div>                
	                                    </div>
                                    	<div class="del-action ml-auto"><a href="javascript:void(0)" class="skyblue">@lang('bargain.hide') <i class="fas fa-chevron-down"></i></a></div>
                                  	</div>	                      
	                            </div>
	                            @endif 
                                <div class="table">
                                  <div class="table-header bg-transparent border-bottom mb-hide">
                                    <ul>
                                      <li class="rounded-0">@lang('bargain.product')</li> 
                                      <li>@lang('bargain.qty')</li>
                                      <li>@lang('bargain.offer_to_buy')</li>
                                      <li>@lang('bargain.offer_to_sell')</li>
                                      <li>@lang('bargain.last_price_to_sell')</li>
                                    </ul>   
                                  </div>
                                  <div class="table-content">
                                  @endif
                                    @php($curr_user_id = $result->user_id)
                                    @if(!isMobile())
                                    <ul class="seller-bargain-tbl"> 
                                        <li class="align-middle">     
                                          <div class="pd-box">
                                            <label class="chk-wrap">
                                                <input type="checkbox" value="{{$result->bargain_id}}">
                                                <span class="chk-mark"></span>
                                            </label>
                                            <div class="wrap-product">
                                              <img src="{{getUserImage($result->image)}}" alt="" width="50" height="50" class="rounded-circle">
                                            </div>            
                                            <div class="pd-desc"> 
                                              <div class="wrap-name">
                                                          <h4 class="name skyblue">{{$result->display_name}}</h4>
                                                          <span class="date">@lang('common.update') {{getDateFormat($result->updated_at)}}</span>
                                                            <span class="chat-wrap"> 
                                                              <a href="javascript:void(0)" class="btn-default grey-dark">
                                                            <i class="fas fa-comments"></i></a>  
                                                        </span>
                                                          </div>            
                                                  </div>
                                          </div>
                                        </li>
                                         <li class="align-middle">
                                        <div class="qty-wrap">
                                          {{$result->qty}}/{{$result->package_name}}
                                        </div>
                                      </li>
                                      <li>
                                        <div class="offer-buy">
                                          <table>
                                            <tbody>
                                              <tr>
                                                <td class="grey-dark"></td>
                                                <td class="text-center">@lang('bargain.price')/@lang('bargain.base_unit')</td>
                                                <td class="text-center">@lang('bargain.price')/@lang('bargain.unit')</td>
                                                <td class="text-center">@lang('bargain.total_price')</td>
                                              </tr>
                                              
                                              @if(isset($productBargainDetailsOfBuyer[$result->bargain_id]) && count($productBargainDetailsOfBuyer[$result->bargain_id])>1)
                                                @foreach($productBargainDetailsOfBuyer[$result->bargain_id] as $key=>$productbargaindetail)
                                                  <tr>
                                                    @if($productbargaindetail['bar_status'] == '1')
                                                      <td class="text-left">@lang('bargain.last')</td>
                                                    @else 
                                                      <td class="text-left">@lang('bargain.previous')</td>
                                                    @endif
                                                    <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['base_unit_price'])}} <br>@lang('common.baht')</td>
                                                    <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['unit_price'])}} <br>@lang('common.baht')</td>
                                                    <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['total_price'])}} <br>@lang('common.baht')</td>
                                                  </tr>
                                                  
                                                  @if($productbargaindetail['bar_status'] == '1')
                                                    <tr>
                                                      <td colspan="3" class="text-center">
                                                        <a class="btn-dark-grey act-reject" rel="{{action('Seller\BargainController@rejectBargain',$productbargaindetail['id'])}}">@lang('bargain.reject')</a>
                                                        <a class="btn act-accept" rel="{{action('Seller\BargainController@acceptBargain',$productbargaindetail['id'])}}">@lang('bargain.accept')</a>
                                                      </td>
                                                    </tr>
                                                  @endif

                                                @endforeach
                                              
                                              @else
                                                <tr>
                                                  <td class="text-left">@lang('bargain.previous')</td>
                                                  <td class="text-center grey-dark">- @lang('common.baht')</td>                             
                                                  <td class="text-center grey-dark">- @lang('common.baht')</td>
                                                  <td class="text-center grey-dark">- @lang('common.baht')</td>
                                                </tr>
                                                @foreach($productBargainDetailsOfBuyer[$result->bargain_id] as $key=>$productbargaindetail)
                                                  <tr>
                                                    @if($productbargaindetail['bar_status'] == '1')
                                                      <td class="text-left">@lang('bargain.last')</td>
                                                    @else 
                                                      <td class="text-left">@lang('bargain.previous')</td>
                                                    @endif
                                                    <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['base_unit_price'])}} <br>@lang('common.baht')</td>
                                                    <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['unit_price'])}}<br> @lang('common.baht')</td>
                                                    <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['total_price'])}}<br> @lang('common.baht')</td>
                                                  </tr>
                                                  @if($productbargaindetail['bar_status'] == '1')
                                                    <tr>
                                                      <td colspan="3" class="text-center">
                                                        <a class="btn-dark-grey act-reject" rel="{{action('Seller\BargainController@rejectBargain',$productbargaindetail['id'])}}">@lang('bargain.reject')</a>
                                                        <a class="btn act-accept" rel="{{action('Seller\BargainController@acceptBargain',$productbargaindetail['id'])}}">@lang('bargain.accept')</a>
                                                      </td>
                                                    </tr>
                                                  @endif
                                                @endforeach
                                              @endif
                                            </tbody>
                                          </table>                          
                                        </div>
                                      </li>
                                      <li>
                                        <div class="offer-sell">
                                          <table>
                                            <tbody>
                                              <tr>
                                                <td class="grey-dark"></td>
                                                <td class="text-center">@lang('bargain.price')/@lang('bargain.base_unit')</td>
                                                <td class="text-center">@lang('bargain.price')/@lang('bargain.unit')</td>
                                                <td class="text-center">@lang('bargain.total_price')</td>
                                              </tr>
                                              @php($status = 1)
                                              @if(isset($productBargainDetailsOfSeller[$result->bargain_id]) && count($productBargainDetailsOfSeller[$result->bargain_id]))
                                                  @php ($countBargainDetailsOfSeller = count($productBargainDetailsOfSeller[$result->bargain_id])-1)
             
                                                 
                                                  @foreach($productBargainDetailsOfSeller[$result->bargain_id] as $key=>$productbargaindetail)
                                                    <tr>

                                                      @if($key == 0)
                                                        <td class="text-left">@lang('bargain.last')</td>
                                                      @else
                                                        <td class="text-left">@lang('bargain.previous')</td>
                                                      @endif

                                                      <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['base_unit_price'])}} <br>@lang('common.baht')</td>
                                                      <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['unit_price'])}} <br>@lang('common.baht')</td>
                                                      <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['total_price'])}} <br>@lang('common.baht')</td>
                                                    </tr>
                                                    @php($status = $productbargaindetail['bar_status'])
                                                  @endforeach
                                                    @if($status == '2')
                                                        <tr>
                                                          <td class="text-left">@lang('bargain.status') </td>
                                                          <td class="text-center grey-dark" colspan="3"> @lang('bargain.accept_price')</td>
                                                        </tr>
                                                    @elseif($status == '3')
                                                      <tr class="status">
                                                        <td class="text-left">@lang('bargain.status') </td>
                                                        <td class="text-center grey-dark" colspan="3"> @lang('bargain.shop_reject')</td>
                                                      </tr>     
                                                    @else
                                                        <tr>
                                                          <td class="text-left">@lang('bargain.status') </td>
                                                          <td class="text-center grey-dark" colspan="3"> @lang('bargain.adjust_price')</td>
                                                        </tr>
                                                    @endif
                                                @else
                                                  <tr>
                                                    <td class="text-left"></td>
                                                    <td class="text-center grey-dark">- @lang('common.baht')</td>
                                                    <td class="text-center grey-dark">- @lang('common.baht')</td>
                                                    <td class="text-center grey-dark">- @lang('common.baht')</td>
                                                  </tr>
                                                  <tr>
                                                    <td class="text-left">@lang('bargain.status') </td>
                                                    <td class="text-center grey-dark" colspan="3"> @lang('bargain.customer_offer')</td>
                                                  </tr>
                                                @endif
                                                <form name="submitAdjustprice" id="submitAdjustprice_{{$result->bargain_id}}" action="{{action('Seller\BargainController@adjustPriceFromSeller', $result->bargain_id)}}" method="post"> 
                                                 <input type="hidden" name="_token" id="_token" value="{{csrf_token()}}">
                                                  <tr class="adjustPricefrom_{{$result->bargain_id}}" style="display:none">
                                                    <td class="text-left">@lang('bargain.last')</td>
                                                     <input type="hidden" name="qty" value="{{$result->qty}}">
                                                     <input type="hidden" name="weight_per_unit" value="{{$result->weight_per_unit}}"> 
                                                    <td class="text-center">
                                                       <input type="text" class="text-center" name="base_unit_price" id="base_unit_price" data-type="price">
                                                    </td>
                                                    <td class="text-center"> 
                                                      <input type="text" class="text-center" name="unit_price" id="unit_price" data-type="price"></td>
                                                    <td class="text-center bargainTotal"></td>

                                                  </tr>
                                                  <!--tr>
                                                    <td class="text-left">@lang('bargain.status')</td>
                                                    <td colspan="2" class="text-left blue">@lang('bargain.customer_offer')</td>
                                                  </tr-->
                                                  <tr class="adjustPricefrom_{{$result->bargain_id}}" style="display:none">
                                                    <td colspan="3" class="text-center">
                                                      <a class="btn-dark-grey submitAdjustprice" rel="{{$result->bargain_id}}">@lang('bargain.submit')</a>
                                                      <a class="btn adjustPricehide" rel="{{$result->bargain_id}}">@lang('bargain.cancel')</a>
                                                    </td>
                                                  </tr>
                                                  <tr class="adjustPriceshow" id="adjustPriceshow_{{$result->bargain_id}}">
                                                    <td class="text-left"></td>
                                                    <td colspan="2" class="text-center">
                                                      <a class="btn-grey" rel="{{$result->bargain_id}}">@lang('bargain.adjust_price')</a>
                                                    </td>
                                                  </tr>
                                                </form>
                                               

                                            </tbody>
                                          </table>                          
                                        </div>
                                      </li>
                                      <li>
                                        <div class="last-sell-price">
                                          <table width="100%;">
                                     
                                              <tr>  
                                                <td class="text-center">@lang('bargain.price')/@lang('bargain.base_unit')</td>                                        
                                                <td class="text-center">@lang('bargain.price')/@lang('bargain.unit')</td>
                                                <td class="text-center">@lang('bargain.total_price')</td>
                                              </tr>
                                              <tr>  
                                                <td class="text-center blue price-txt">{{numberFormat($result->base_unit_price)}}<br>@lang('common.baht')</td>                  
                                                <td class="text-center blue price-txt">{{numberFormat($result->curr_unit_price)}}<br>@lang('common.baht')</td>
                                                <td class="text-center blue total-txt">{{numberFormat($result->curr_total_price)}}<br>@lang('common.baht')</td>
                                              </tr>                                       
                                         
                                          </table>                          
                                        </div>
                                      </li>
                                    </ul>
                                    @endif
                                    <!-- Mobile table byProduct start -->
		                      		@if(isMobile())
									<div class="bargain-tbl-mobile seller-tbl-mob-customer">
										<div class="bargin-tbl-row">
											<div class="bargin-inner-row">
												<div class="nameshop">
													<div class="nameshop-txt">
														<label class="chk-wrap">
											            	<input type="checkbox" value="{{$result->bargain_id}}">
											            	<span class="chk-mark"></span>
											          	</label>
													</div>
													<span class="chat-wrap">
														<a href="javascript:void(0)" class="btn-default grey-dark"><i class="fas fa-comments"></i></a>
													</span>
												</div>
												<div class="product-col">
													<div class="pd-box">		                
												        <div class="wrap-product">
												          <a href="{{action('ProductDetailController@display', [$result->caturl, $result->sku])}}"><img src="{{getProductImageUrlRunTime($result->thumbnail_image, 'thumb_93x79')}}" alt="" width="" height="79"></a>
												        </div>
												        <div class="pd-desc">
												           	<div class="wrap-name">
												              	<a href="{{action('ProductDetailController@display', [$result->caturl, $result->sku])}}" class="skyblue"><span class="name">{{$result->category_name}}</span></a>
												              	<span class="date">@lang('common.update') {{getDateFormat($result->updated_at)}}</span>
												                <div class="sb"><img src="{{getBadgeImageUrl($result->icon)}}" alt="" width="36" height="36">
												                </div>
												           </div>
												        </div>
												    </div>
												    <div class="price-qty">
												    	<span class="qty">{{numberFormat($result->unit_price)}} @lang('common.baht') / {{ getPackageName($result->package_id) }} </span>
												    	<div class="qty-wrap">	          
												          {{$result->qty}}/{{$result->package_name}}
												        </div>
												    </div>
												</div>	                          			
											</div>
											<div class="bargin-inner-row">	
												<div class="offer-buy">
													<h3>@lang('bargain.offer_to_buy')</h3>
													<table width="100%">            
											            <tr class="offer-buy-info">
											                <td class="grey-dark"></td>
												            <td class="text-center">@lang('bargain.price')/@lang('bargain.base_unit')</td>
												            <td class="text-center">@lang('bargain.price')/@lang('bargain.unit')</td>
												            <td class="text-center">@lang('bargain.total_price')</td>
											            </tr>             
											            @if(isset($productBargainDetailsOfBuyer[$result->bargain_id]) && count($productBargainDetailsOfBuyer[$result->bargain_id])>=1)
											              @foreach($productBargainDetailsOfBuyer[$result->bargain_id] as $key=>$productbargaindetail)
											                <tr>
											                  @if($key==0)
											                    <td class="text-left">@lang('bargain.last')</td>
											                  @else 
											                    <td class="text-left">@lang('bargain.previous')</td>
											                  @endif
											                  <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['base_unit_price'])}} <br>@lang('common.baht')</td>
											                  <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['unit_price'])}} <br>@lang('common.baht')</td>
											                  <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['total_price'])}} <br>@lang('common.baht')</td>
											                </tr>
											                
											                @if($productbargaindetail['bar_status'] == '1')
											                  <tr>
											                    <td colspan="4" class="text-center">
											                      <a class="btn-dark-grey act-reject" rel="{{action('Seller\BargainController@rejectBargain',$productbargaindetail['id'])}}">@lang('bargain.reject')</a>
											                      <a class="btn act-accept" rel="{{action('Seller\BargainController@acceptBargain',$productbargaindetail['id'])}}">@lang('bargain.accept')</a>
											                    </td>
											                  </tr>
											                @endif

											            @endforeach
											            
											            @else
											              <tr>
											                <td class="text-left">@lang('bargain.previous')</td>
											                <td class="text-center grey-dark">- @lang('common.baht')</td>                             
											                <td class="text-center grey-dark">- @lang('common.baht')</td>
											                <td class="text-center grey-dark">- @lang('common.baht')</td>
											              </tr>

											              @foreach($productBargainDetailsOfBuyer[$result->bargain_id] as $key=>$productbargaindetail)
											                <tr>
											                  @if($key == 0)
											                    <td class="text-left">@lang('bargain.last')</td>
											                  @else 
											                    <td class="text-left">@lang('bargain.previous')</td>
											                  @endif
											                  <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['base_unit_price'])}} <br>@lang('common.baht')</td>
											                  <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['unit_price'])}} <br>@lang('common.baht')</td>
											                  <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['total_price'])}} <br>@lang('common.baht')</td>
											                </tr>
											                @if($productbargaindetail['bar_status'] == '1')
											                  <tr>
											                    <td colspan="3" class="text-center">
											                      <a class="btn-dark-grey act-reject" rel="{{action('Seller\BargainController@rejectBargain',$productbargaindetail['id'])}}">@lang('bargain.reject')</a>
											                      <a class="btn act-accept" rel="{{action('Seller\BargainController@acceptBargain',$productbargaindetail['id'])}}">@lang('bargain.accept')</a>
											                    </td>
											                  </tr>
											                @endif
											              @endforeach
											            @endif							               
											        </table>
												</div>
											</div>
											<div class="bargin-inner-row">	
												<div class="offer-buy">
													<h3>@lang('bargain.offer_to_sell')</h3> 
													<table width="100%">
													  <tbody>
													    <tr>
													      <td class="grey-dark"></td>
													      <td class="text-center">@lang('bargain.price')/@lang('bargain.base_unit')</td>
													      <td class="text-center">@lang('bargain.price')/@lang('bargain.unit')</td>
													      <td class="text-center">@lang('bargain.total_price')</td>
													    </tr>
													    @php($status = 1)
													    @if(isset($productBargainDetailsOfSeller[$result->bargain_id]) && count($productBargainDetailsOfSeller[$result->bargain_id]))
													        @php ($countBargainDetailsOfSeller = count($productBargainDetailsOfSeller[$result->bargain_id])-1)
													        @foreach($productBargainDetailsOfSeller[$result->bargain_id] as $key=>$productbargaindetail)

													            <tr>
													                @if($key == 0)
													                  <td class="text-left">@lang('bargain.last')</td>
													                @else
													                  <td class="text-left">@lang('bargain.previous')</td>
													                @endif

													                <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['base_unit_price'])}} <br>@lang('common.baht')</td>
													                <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['unit_price'])}} <br>@lang('common.baht')</td>
													                <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['total_price'])}} <br>@lang('common.baht')</td>
													            </tr>
													            @php($status = $productbargaindetail['bar_status'])
													        @endforeach
													        
													      @else
													        <tr>
													          <td class="text-left"></td>
													          <td class="text-center grey-dark">- @lang('common.baht')</td>
													          <td class="text-center grey-dark">- @lang('common.baht')</td>
													          <td class="text-center grey-dark">- @lang('common.baht')</td>
													        </tr>
													        <tr>
													          <td class="text-left">@lang('bargain.status') </td>
													          <td class="text-center grey-dark" colspan="3"> @lang('bargain.customer_offer')</td>
													        </tr>
													          
													      @endif
													      <form name="submitAdjustprice" id="submitAdjustprice_{{$result->bargain_id}}" action="{{action('Seller\BargainController@adjustPriceFromSeller', $result->bargain_id)}}" method="post"> 
													       <input type="hidden" name="_token" id="_token" value="{{csrf_token()}}">
													        <tr class="adjustPricefrom_{{$result->bargain_id}}" style="display:none">
													          <td class="text-left">@lang('bargain.last')</td>
													           <input type="hidden" name="qty" value="{{$result->qty}}">
													           <input type="hidden" name="weight_per_unit" value="{{$result->weight_per_unit}}"> 
													          <td class="text-center">
													             <input type="text" class="text-center base_unit_price" name="base_unit_price" data-type="price">
													          </td>
													          <td class="text-center"> 
													            <input type="text" class="text-center unit_price" name="unit_price" data-type="price"></td>
													          <td class="text-center bargainTotal"></td>

													        </tr>
													        <!--tr>
													          <td class="text-left">@lang('bargain.status')</td>
													          <td colspan="2" class="text-left blue">@lang('bargain.customer_offer')</td>
													        </tr-->
													        <tr class="adjustPricefrom_{{$result->bargain_id}}" style="display:none">
													          <td colspan="3" class="text-center">
													            <a class="btn-grey submitAdjustprice" rel="{{$result->bargain_id}}">@lang('bargain.bargaining')</a>
													            <!--a class="btn-dark-grey btn adjustPricehide" rel="{{$result->bargain_id}}">@lang('bargain.cancel')</a-->
													          </td>
													        </tr>
													        <tr class="adjustPriceshow" id="adjustPriceshow_{{$result->bargain_id}}">
													          
													          <td colspan="4" class="text-center">
													            <a class="btn-default" rel="{{$result->bargain_id}}">@lang('bargain.adjust_price')</a>
													          </td>
													        </tr>
													      </form>
													      @if($status == '2')
													            <tr class="status">
													              <td class="text-right" colspan="2">@lang('bargain.status') </td>
													              <td class="text-left blue" colspan="2"> @lang('bargain.accept_price')</td>
													            </tr>
				                                  @elseif($status == '3')
				                                    <tr class="status">
				                                      <td class="text-left">@lang('bargain.status') </td>
				                                      <td class="text-center grey-dark" colspan="3"> @lang('bargain.shop_reject')</td>
				                                    </tr>      
											        @else
											            <tr class="status">
											              <td class="text-right" colspan="2">@lang('bargain.status') </td>
											              <td class="text-left blue" colspan="2"> @lang('bargain.adjust_price')</td>
											            </tr>
											        @endif
													  </tbody>
													</table>
												</div>	
											</div>
											<div class="bargin-inner-row">
												<div class="offer-buy latest-offer-buy">
													<h3>@lang('bargain.last_price_to_sell')</h3>
													<div class="last-sell-price">
				                                        <table width="100%">
				                                              <tr>  
				                                                <td class="text-center">@lang('bargain.price')/@lang('bargain.base_unit')</td>                                        
				                                                <td class="text-center">@lang('bargain.price')/@lang('bargain.unit')</td>
				                                                <td class="text-center">@lang('bargain.total_price')</td>
				                                              </tr>
				                                              <tr>  
				                                                <td class="text-center blue price-txt">{{numberFormat($result->base_unit_price)}}<br>@lang('common.baht')</td>                  
				                                                <td class="text-center blue price-txt">{{numberFormat($result->curr_unit_price)}}<br>@lang('common.baht')</td>
				                                                <td class="text-center blue total-txt">{{numberFormat($result->curr_total_price)}}<br>@lang('common.baht')</td>
				                                              </tr>
				                                          	</table>                          
				                                        </div>
												</div>
											</div>
										</div>
									</div> 
		                      		@endif
	                      		<!-- Mobile table byProduct End -->
                          	@endforeach

                         </div>
                        </div>
                      </div>
                    </div>
                    
                  @else
                    <div class="tab-content">
                      <div class="tab-pane active" id="sort-by-time">
                          <div class="form-group">
                            <div class="select-all">
                              <label class="chk-wrap checkedAll">
                                <input type="checkbox" >
                                <span class="chk-mark">@lang('bargain.select_all')</span>
                              </label>
                              <button type="button" class="btn-dark-grey refuse_all_bargaining">@lang('bargain.refuse_all_bargaining')</button>
                            </div>
                          </div>
                          @if(!isMobile())
                          <div class="table-responsive bargain-order-table">
                            <div class="table">
                              <div class="table-header ">
                                <ul class="seller-bargain-head bargain-th">  
                                  <li class="rounded-0">@lang('bargain.buyer')/@lang('bargain.product')</li>                              
                                      <li>@lang('bargain.qty')</li>
                                      <li>@lang('bargain.offer_to_buy')</li>
                                      <li>@lang('bargain.offer_to_sell')</li>
                                      <li>@lang('bargain.last_price_to_sell')</li>
                                </ul>   
                              </div>
                              <div class="table-content">
                                  @foreach($results as $result)
                                    <ul class="seller-bargain-tbl">
                                        <li class="align-middle text-left">
                                          <div class="pd-box">
                                            <label class="chk-wrap">
                                              <input type="checkbox" value="{{$result->bargain_id}}">
                                              <span class="chk-mark"></span>
                                            </label>
                                            <div class="info-box">
                                              <h4 class="skyblue">{{$result->display_name}}</h4>
                                              <div class="prod-img">
                                                <a href="{{action('ProductDetailController@display', [$result->caturl, $result->sku])}}"><img src="{{getProductImageUrlRunTime($result->thumbnail_image, 'thumb_93x79')}}" alt="" width="93" height="79"></a>
                                              </div>
                                              <div class="wrap-name">
                                                <a href="{{action('ProductDetailController@display', [$result->caturl, $result->sku])}}"><span class="name">{{$result->category_name}}</span></a>
                                                <span class="qty">{{numberFormat($result->unit_price)}} @lang('common.baht') / {{ getPackageName($result->package_id) }}</span>
                                                <span class="date">@lang('common.update') {{getDateFormat($result->updated_at)}}</span>
                                                 <span class="remark">@lang('product.remark') : 1 {{ $result->package_name }} = {{ $result->weight_per_unit }} {{ $result->unit_name }}</span>
                                                <span class="sb"><img src="{{getBadgeImageUrl($result->icon)}}" alt="" width="36" height="36"></span>
                                              </div>
                                            </div>
                                          </div>
                                        </li>                             
                                        <li class="align-middle">
                                          <div class="qty-wrap">
                                            {{$result->qty}}/{{$result->package_name}}
                                          </div>
                                        </li>
                                        <li>
                                          <div class="offer-buy">
                                            <table>
                                              <tbody>
                                                <tr>
                                                  <td class="grey-dark"></td>
                                                  <td class="text-center">@lang('bargain.sub_unit_price')</td>
                                                  <td class="text-center">@lang('bargain.unit_price')</td>
                                                  <td class="text-center">@lang('bargain.total_price')</td>
                                                </tr>
                                                
                                                @if(isset($productBargainDetailsOfBuyer[$result->bargain_id]) && count($productBargainDetailsOfBuyer[$result->bargain_id])>=1)
                                                  @foreach($productBargainDetailsOfBuyer[$result->bargain_id] as $key=>$productbargaindetail)
                                                    <tr>
                                                      @if($key==0)
                                                        <td class="text-left">@lang('bargain.last')</td>
                                                      @else 
                                                        <td class="text-left">@lang('bargain.previous')</td>
                                                      @endif
                                                      <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['base_unit_price'])}} <br> @lang('common.baht')</td>
                                                      <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['unit_price'])}} <br>@lang('common.baht')</td>
                                                      <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['total_price'])}} <br>@lang('common.baht')</td>
                                                    </tr>
                                                    
                                                    @if($productbargaindetail['bar_status'] == '1')
                                                      <tr>
                                                        <td colspan="3" class="text-center">
                                                          <a class="btn-dark-grey act-reject" rel="{{action('Seller\BargainController@rejectBargain',$productbargaindetail['id'])}}">@lang('bargain.reject')</a>
                                                          <a class="btn act-accept" rel="{{action('Seller\BargainController@acceptBargain',$productbargaindetail['id'])}}">@lang('bargain.accept')</a>
                                                        </td>
                                                      </tr>
                                                    @endif

                                                  @endforeach
                                                
                                                @else
                                                  <tr>
                                                    <td class="text-left">@lang('bargain.previous')</td>
                                                    <td class="text-center grey-dark">- @lang('common.baht')</td>                             
                                                    <td class="text-center grey-dark">- @lang('common.baht')</td>
                                                    <td class="text-center grey-dark">- @lang('common.baht')</td>
                                                  </tr>

                                                  @foreach($productBargainDetailsOfBuyer[$result->bargain_id] as $key=>$productbargaindetail)
                                                    <tr>
                                                      @if($key == 0)
                                                        <td class="text-left">@lang('bargain.last')</td>
                                                      @else 
                                                        <td class="text-left">@lang('bargain.previous')</td>
                                                      @endif
                                                      <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['base_unit_price'])}} <br>@lang('common.baht')</td>
                                                      <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['unit_price'])}} <br>@lang('common.baht')</td>
                                                      <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['total_price'])}} <br>@lang('common.baht')</td>
                                                    </tr>
                                                    @if($productbargaindetail['bar_status'] == '1')
                                                      <tr>
                                                        <td colspan="3" class="text-center">
                                                          <a class="btn-dark-grey act-reject" rel="{{action('Seller\BargainController@rejectBargain',$productbargaindetail['id'])}}">@lang('bargain.reject')</a>
                                                          <a class="btn act-accept" rel="{{action('Seller\BargainController@acceptBargain',$productbargaindetail['id'])}}">@lang('bargain.accept')</a>
                                                        </td>
                                                      </tr>
                                                    @endif
                                                  @endforeach
                                                @endif
                                              </tbody>
                                            </table>                          
                                          </div>
                                        </li>
                                        <li>
                                          <div class="offer-sell">
                                            <table>
                                              <tbody>
                                                <tr>
                                                  <td class="grey-dark"></td>
                                                  <td class="text-center">@lang('bargain.sub_unit_price')</td>
                                                  <td class="text-center">@lang('bargain.unit_price')</td>
                                                  <td class="text-center">@lang('bargain.total_price')</td>
                                                </tr>
                                                @php($status = 1)
                                                @if(isset($productBargainDetailsOfSeller[$result->bargain_id]) && count($productBargainDetailsOfSeller[$result->bargain_id]))
                                                    @php ($countBargainDetailsOfSeller = count($productBargainDetailsOfSeller[$result->bargain_id])-1)
                                                    @foreach($productBargainDetailsOfSeller[$result->bargain_id] as $key=>$productbargaindetail)

                                                        <tr>
                                                            @if($key == 0)
                                                              <td class="text-left">@lang('bargain.last')</td>
                                                            @else
                                                              <td class="text-left">@lang('bargain.previous')</td>
                                                            @endif

                                                            <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['base_unit_price'])}} <br>@lang('common.baht')</td>
                                                            <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['unit_price'])}} <br>@lang('common.baht')</td>
                                                            <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['total_price'])}} <br>@lang('common.baht')</td>
                                                        </tr>
                                                        @php($status = $productbargaindetail['bar_status'])
                                                    @endforeach
                                                    @if($status == '2')
                                                        <tr>
                                                          <td class="text-left">@lang('bargain.status') </td>
                                                          <td class="text-center grey-dark" colspan="3"> @lang('bargain.accept_price')</td>
                                                        </tr>
                                                    @elseif($status == '3')
                                                      <tr class="status">
                                                        <td class="text-left">@lang('bargain.status') </td>
                                                        <td class="text-center grey-dark" colspan="3"> @lang('bargain.shop_reject')</td>
                                                      </tr>     
                                                    @else
                                                        <tr>
                                                          <td class="text-left">@lang('bargain.status') </td>
                                                          <td class="text-center grey-dark" colspan="3"> @lang('bargain.adjust_price')</td>
                                                        </tr>
                                                    @endif
                                                  @else
                                                    <tr>
                                                      <td class="text-left"></td>
                                                      <td class="text-center grey-dark">- @lang('common.baht')</td>
                                                      <td class="text-center grey-dark">- @lang('common.baht')</td>
                                                      <td class="text-center grey-dark">- @lang('common.baht')</td>
                                                    </tr>
                                                    <tr>
                                                      <td class="text-left">@lang('bargain.status') </td>
                                                      <td class="text-center grey-dark" colspan="3"> @lang('bargain.customer_offer')</td>
                                                    </tr>
                                                      
                                                  @endif
                                                  <form name="submitAdjustprice" id="submitAdjustprice_{{$result->bargain_id}}" action="{{action('Seller\BargainController@adjustPriceFromSeller', $result->bargain_id)}}" method="post"> 
                                                   <input type="hidden" name="_token" id="_token" value="{{csrf_token()}}">
                                                    <tr class="adjustPricefrom_{{$result->bargain_id}}" style="display:none">
                                                      <td class="text-left">@lang('bargain.last')</td>
                                                       <input type="hidden" name="qty" value="{{$result->qty}}">
                                                       <input type="hidden" name="weight_per_unit" value="{{$result->weight_per_unit}}"> 
                                                      <td class="text-center">
                                                         <input type="text" class="text-center base_unit_price" name="base_unit_price" data-type="price">
                                                      </td>
                                                      <td class="text-center"> 
                                                        <input type="text" class="text-center unit_price" name="unit_price" data-type="price"></td>
                                                      <td class="text-center bargainTotal"></td>

                                                    </tr>
                                                    <!--tr>
                                                      <td class="text-left">@lang('bargain.status')</td>
                                                      <td colspan="2" class="text-left blue">@lang('bargain.customer_offer')</td>
                                                    </tr-->
                                                    <tr class="adjustPricefrom_{{$result->bargain_id}}" style="display:none">
                                                      <td colspan="3" class="text-center">
                                                        <a class="btn-grey submitAdjustprice" rel="{{$result->bargain_id}}">@lang('bargain.bargaining')</a>
                                                        <!--a class="btn-dark-grey btn adjustPricehide" rel="{{$result->bargain_id}}">@lang('bargain.cancel')</a-->
                                                      </td>
                                                    </tr>
                                                    <tr class="adjustPriceshow" id="adjustPriceshow_{{$result->bargain_id}}">
                                                      <td class="text-left"></td>
                                                      <td colspan="2" class="text-center">
                                                        <a class="btn-grey" rel="{{$result->bargain_id}}">@lang('bargain.adjust_price')</a>
                                                      </td>
                                                    </tr>
                                                  </form>
                                                 

                                              </tbody>
                                            </table>                          
                                          </div>
                                        </li>
                                        <li>
                                          <div class="last-sell-price">
                                            <table>
                                              <tbody>
                                                <tr> 
                                                  <td class="text-center">@lang('bargain.sub_unit_price')</td>                                         
                                                  <td class="text-center">@lang('bargain.unit_price')</td>
                                                  <td class="text-center">@lang('bargain.total_price')</td>
                                                </tr>
                                                <tr> 
                                                  <td class="text-center blue price-txt">{{numberFormat($result->base_unit_price)}}<br>@lang('common.baht')</td>                   
                                                  <td class="text-center blue price-txt">{{numberFormat($result->curr_unit_price)}}<br>@lang('common.baht')</td>
                                                  <td class="text-center blue total-txt">{{numberFormat($result->curr_total_price)}}<br>@lang('common.baht')</td>
                                                </tr>                                       
                                              </tbody>
                                            </table>                          
                                          </div>
                                        </li>
                                    </ul>
                                    @endforeach
                              </div>
                            </div>
                          </div>
                          @endif

                          	<!-- Mobile table sortbytime -->
	                  		@if(isMobile())
							<div class="bargain-tbl-mobile pt-2">
								@foreach($results as $result)
	                        	<div class="bargin-tbl-row seller-bargain-tbl">
									<div class="bargin-inner-row">
										<div class="nameshop">
											<div class="nameshop-txt">
												<label class="chk-wrap">
								            	<input type="checkbox" value="{{$result->bargain_id}}">
								            	<span class="chk-mark"></span>
								            	</label>
								            	<h4 class="skyblue">{{$result->display_name}}</h4>
											</div>

											<span class="chat-wrap">
												<a href="javascript:void(0)" class="btn-default grey-dark"><i class="fas fa-comments"></i></a>
											</span>
										</div>
										<div class="product-col">
											<div class="pd-box">		                
									            <div class="wrap-product">
									            	<a href="{{action('ProductDetailController@display', [$result->caturl, $result->sku])}}"><img src="{{getProductImageUrlRunTime($result->thumbnail_image, 'thumb_95x80')}}" alt="" width="70"></a>
									            </div>
									            <div class="pd-desc">
									               	<div class="wrap-name">
									               		<span class="date">@lang('common.update') {{getDateFormat($result->updated_at)}}</span>
									                  	<a href="{{action('ProductDetailController@display', [$result->caturl, $result->sku])}}"><span class="name">{{$result->category_name}}</span></a>
									                    <div class="sb"><img src="{{getBadgeImageUrl($result->icon)}}" alt="" width="26" height="26">
									                    </div>
									               </div>
									            </div>
									        </div>
									        <div class="price-qty">
									        	{{$result->qty}}/{{$result->unit_name}}
									        </div>
										</div>	                          			
									</div>
									<div class="bargin-inner-row">	
										<div class="offer-buy">
											<h3>@lang('bargain.offer_to_buy')</h3>
											<table width="100%">            
									            <tr class="offer-buy-info">
									                <td class="grey-dark"></td>
										            <td class="text-center">@lang('bargain.price')/@lang('bargain.base_unit')</td>
										            <td class="text-center">@lang('bargain.price')/@lang('bargain.unit')</td>
										            <td class="text-center">@lang('bargain.total_price')</td>
									            </tr>             
									            @if(isset($productBargainDetailsOfBuyer[$result->bargain_id]) && count($productBargainDetailsOfBuyer[$result->bargain_id])>=1)
									              @foreach($productBargainDetailsOfBuyer[$result->bargain_id] as $key=>$productbargaindetail)
									                <tr>
									                  @if($key==0)
									                    <td class="text-left">@lang('bargain.last')</td>
									                  @else 
									                    <td class="text-left">@lang('bargain.previous')</td>
									                  @endif
									                  <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['base_unit_price'])}} <br>@lang('common.baht')</td>
									                  <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['unit_price'])}} <br>@lang('common.baht')</td>
									                  <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['total_price'])}} <br>@lang('common.baht')</td>
									                </tr>
									                
									                @if($productbargaindetail['bar_status'] == '1')
									                  <tr>
									                    <td colspan="4" class="text-center">
									                      <a class="btn-dark-grey act-reject" rel="{{action('Seller\BargainController@rejectBargain',$productbargaindetail['id'])}}">@lang('bargain.reject')</a>
									                      <a class="btn act-accept" rel="{{action('Seller\BargainController@acceptBargain',$productbargaindetail['id'])}}">@lang('bargain.accept')</a>
									                    </td>
									                  </tr>
									                @endif

									            @endforeach
									            
									            @else
									              <tr>
									                <td class="text-left">@lang('bargain.previous')</td>
									                <td class="text-center grey-dark">- @lang('common.baht')</td>                             
									                <td class="text-center grey-dark">- @lang('common.baht')</td>
									                <td class="text-center grey-dark">- @lang('common.baht')</td>
									              </tr>

									              @foreach($productBargainDetailsOfBuyer[$result->bargain_id] as $key=>$productbargaindetail)
									                <tr>
									                  @if($key == 0)
									                    <td class="text-left">@lang('bargain.last')</td>
									                  @else 
									                    <td class="text-left">@lang('bargain.previous')</td>
									                  @endif
									                  <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['base_unit_price'])}} <br>@lang('common.baht')</td>
									                  <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['unit_price'])}} <br>@lang('common.baht')</td>
									                  <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['total_price'])}} <br>@lang('common.baht')</td>
									                </tr>
									                @if($productbargaindetail['bar_status'] == '1')
									                  <tr>
									                    <td colspan="3" class="text-center">
									                      <a class="btn-dark-grey act-reject" rel="{{action('Seller\BargainController@rejectBargain',$productbargaindetail['id'])}}">@lang('bargain.reject')</a>
									                      <a class="btn act-accept" rel="{{action('Seller\BargainController@acceptBargain',$productbargaindetail['id'])}}">@lang('bargain.accept')</a>
									                    </td>
									                  </tr>
									                @endif
									              @endforeach
									            @endif							               
									        </table>
										</div>
									</div>
									<div class="bargin-inner-row">	
										<div class="offer-buy">
											<h3>@lang('bargain.offer_to_sell')</h3> 
											<table width="100%">
											  <tbody>
											    <tr>
											      <td class="grey-dark"></td>
											      <td class="text-center">@lang('bargain.price')/@lang('bargain.base_unit')</td>
											      <td class="text-center">@lang('bargain.price')/@lang('bargain.unit')</td>
											      <td class="text-center">@lang('bargain.total_price')</td>
											    </tr>
											    @php($status = 1)
											    @if(isset($productBargainDetailsOfSeller[$result->bargain_id]) && count($productBargainDetailsOfSeller[$result->bargain_id]))
											        @php ($countBargainDetailsOfSeller = count($productBargainDetailsOfSeller[$result->bargain_id])-1)
											        @foreach($productBargainDetailsOfSeller[$result->bargain_id] as $key=>$productbargaindetail)

											            <tr>
											                @if($key == 0)
											                  <td class="text-left">@lang('bargain.last')</td>
											                @else
											                  <td class="text-left">@lang('bargain.previous')</td>
											                @endif

											                <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['base_unit_price'])}} <br>@lang('common.baht')</td>
											                <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['unit_price'])}} <br>@lang('common.baht')</td>
											                <td class="text-center @if($key == 1)grey-light @else grey-light2 @endif">{{numberFormat($productbargaindetail['total_price'])}} <br>@lang('common.baht')</td>
											            </tr>
											            @php($status = $productbargaindetail['bar_status'])
											        @endforeach
											        
											      @else
											        <tr>
											          <td class="text-left"></td>
											          <td class="text-center grey-dark">- @lang('common.baht')</td>
											          <td class="text-center grey-dark">- @lang('common.baht')</td>
											          <td class="text-center grey-dark">- @lang('common.baht')</td>
											        </tr>
											        <tr>
											          <td class="text-left">@lang('bargain.status') </td>
											          <td class="text-center grey-dark" colspan="3"> @lang('bargain.customer_offer')</td>
											        </tr>
											          
											      @endif
											      <form name="submitAdjustprice" id="submitAdjustprice_{{$result->bargain_id}}" action="{{action('Seller\BargainController@adjustPriceFromSeller', $result->bargain_id)}}" method="post"> 
											       <input type="hidden" name="_token" id="_token" value="{{csrf_token()}}">
											        <tr class="adjustPricefrom_{{$result->bargain_id}}" style="display:none">
											          <td class="text-left">@lang('bargain.last')</td>
											           <input type="hidden" name="qty" value="{{$result->qty}}">
											           <input type="hidden" name="weight_per_unit" value="{{$result->weight_per_unit}}"> 
											          <td class="text-center">
											             <input type="text" class="text-center base_unit_price" name="base_unit_price" data-type="price">
											          </td>
											          <td class="text-center"> 
											            <input type="text" class="text-center unit_price" name="unit_price" data-type="price"></td>
											          <td class="text-center bargainTotal"></td>

											        </tr>
											        <!--tr>
											          <td class="text-left">@lang('bargain.status')</td>
											          <td colspan="2" class="text-left blue">@lang('bargain.customer_offer')</td>
											        </tr-->
											        <tr class="adjustPricefrom_{{$result->bargain_id}}" style="display:none">
											          <td colspan="3" class="text-center">
											            <a class="btn-grey submitAdjustprice" rel="{{$result->bargain_id}}">@lang('bargain.bargaining')</a>
											            <!--a class="btn-dark-grey btn adjustPricehide" rel="{{$result->bargain_id}}">@lang('bargain.cancel')</a-->
											          </td>
											        </tr>
											        <tr class="adjustPriceshow" id="adjustPriceshow_{{$result->bargain_id}}">
											          
											          <td colspan="4" class="text-center">
											            <a class="btn-default" rel="{{$result->bargain_id}}">@lang('bargain.adjust_price')</a>
											          </td>
											        </tr>
											      </form>
											      @if($status == '2')
											            <tr class="status">
											              <td class="text-right" colspan="2">@lang('bargain.status') </td>
											              <td class="text-left blue" colspan="2"> @lang('bargain.accept_price')</td>
											            </tr>
											        @else
											            <tr class="status">
											              <td class="text-right" colspan="2">@lang('bargain.status') </td>
											              <td class="text-left blue" colspan="2"> @lang('bargain.adjust_price')</td>
											            </tr>
											        @endif
											  </tbody>
											</table>
										</div>	
									</div>
									<div class="bargin-inner-row">
										<div class="offer-buy latest-offer-buy">
											<h3>@lang('bargain.last_price_to_sell')</h3>
											<div class="last-sell-price">
	                                            <table width="100%;">
	                                                <tr> 
	                                                  <td class="text-center">@lang('bargain.price')/@lang('bargain.base_unit')</td>                                         
	                                                  <td class="text-center">@lang('bargain.price')/@lang('bargain.unit')</td>
	                                                  <td class="text-center">@lang('bargain.total_price')</td>
	                                                </tr>
	                                                <tr> 
	                                                  <td class="text-center blue price-txt">{{numberFormat($result->base_unit_price)}}<br>@lang('common.baht')</td>                   
	                                                  <td class="text-center blue price-txt">{{numberFormat($result->curr_unit_price)}}<br>@lang('common.baht')</td>
	                                                  <td class="text-center blue total-txt">{{numberFormat($result->curr_total_price)}}<br>@lang('common.baht')</td>
	                                                </tr>  
	                                            </table>                          
	                                          </div>
										</div>
									</div>
								
									
								</div>
	                          	@endforeach
							</div> 
	                  		@endif
	                  		<!-- Mobile table sortbytime End -->

                        </div>
                    </div> 
                  @endif
                </div>
              </div> 
            </div> 
        </div>
    </div>  
</div>        
@endsection 

@section('footer_scripts')
    @include('includes.gridtablejsdeps')
    <script stype="text/javascript" src="{{Config('constants.js_url')}}angular/smmApp/controller/gridTableCtrl.js"></script>

    <script src="https://www.gstatic.com/firebasejs/7.15.5/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/7.15.5/firebase-auth.js"></script>
    <script src="https://www.gstatic.com/firebasejs/7.15.5/firebase-firestore.js"></script>
    <script src="https://www.gstatic.com/firebasejs/7.15.5/firebase-storage.js"></script>
    <script type="text/javascript">
        var u_id = "{{Auth::user()->u_id}}";
        var auto_start = "{{Auth::user()->u_id}}";
    </script>
    <script type="text/javascript" src="{{Config('constants.js_url')}}bargain-chat/firestore-config.js"></script>

    @if(Auth::check())
       <script type="text/javascript">
          firebase.auth().signInWithCustomToken('{{Auth::user()->chat_token}}');
       </script> 
    @endif
    <script type="text/javascript" src="{{Config('constants.js_url')}}bargain-chat/bargain-chat.js"></script>
    
    {!! CustomHelpers::combineCssJs(['js/price_formatter','js/seller/product'],'js') !!}
    

@stop