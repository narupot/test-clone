<script type="text/javascript">
  var lang_json = {"ok":"@lang('common.ok')", "success":"@lang('common.success')"};
  var weight_per_unit = "{{$productDetail->weight_per_unit}}";
  var orignal_unit_price = "{{$productDetail->unit_price}}";
  var currency = "@lang('common.baht')";
</script>
<div class="modal-header">          
    <h2 class="mb-0">{{ $productDetail->getShopDesc->shop_name??'' }}</h2>
    <span class="close fas fa-times" data-dismiss="modal"></span>
</div>
<div class="modal-body">  
    <div class="product-information">
        <div class="productPopup">            
            <span class="prod-img">
                <img src="{{getProductImageUrlRunTime($productDetail->thumbnail_image, 'thumb_159x126')}}" width="169" height="126" alt="">
            </span>
            <div class="product-info">
                <h2 class="prod-name mb-0">{{ $productDetail->categorydesc->category_name??''}} </h2> 
                
                <!--span class="la">LA</span-->
                <div class="product-review">              
                    <div class="review-star">
                        <div class="grey-stars"></div>
                        <div class="filled-stars" style="width: {!! $productDetail->avg_rating*20 !!}%">
                        </div>
                        <!--div class="filled-stars" style="width: 60%"></div-->
                   </div>
                </div>
                <!--div class="grade">
                    <span class="la"><img src="{{getBadgeImageUrl($productDetail->getbadge->icon)}}" width="36" height="36"></span> <span> Size : L </span> &nbsp;  <span> Standard : A  </span>
                </div-->
                
                <div class="price-box">
                    @if(!$productDetail->show_price)
                        <span class="price">@lang('product.ask_the_price_from_the_store')</span>
                    @else
                        <span class="price">{{ numberFormat($productDetail->unit_price) }} @lang('common.baht')/{{ $productDetail->package_name }}</span>
                    @endif

                    <span class="remark">@lang('product.remark') : 1 {{ $productDetail->package_name }} = {{ $productDetail->weight_per_unit }} {{$productDetail->unit_name}}</span>

                    @if($productDetail->tierPrices)
                        <div class="price-list">
                                <table>
                                    <tbody>
                                        @foreach($productDetail->tierPrices as $tkey => $tval)
                                            <tr>
                                                <td>{{ $tval->start_qty }} - {{ $tval->end_qty }} {{ $productDetail->package_name }}</td>
                                                <td>{{ numberFormat($tval->unit_price) }} @lang('common.baht')/{{$productDetail->package_name}}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @if($productDetail->order_qty_limit == 0)
                                    <div class="min-order">@lang('product.minimun_order') {{ $productDetail->min_order_qty }} {{ $productDetail->package_name }}</div>
                                @endif

                                @if($productDetail->stock == 1)
                                    <div class="stock"><span>@lang('product.stock')</span>:  @lang('product.unlimited')</div>
                                @else
                                    <div class="stock"><span>@lang('product.stock')</span>:  
                                    @if($productDetail->quantity)
                                        {{$productDetail->quantity.' '. $productDetail->package_name }}
                                    @else
                                        <span class="red outstock">@lang('product.out_of_stock')</span>
                                    @endif
                                    </div>
                                @endif
                            </div>
                    @endif
                    @if(!empty($productDetail->getShop->line_link))
                        <div class="line"><strong class="d-block mb-1">@lang('product.contact_the_store_to_bargain')</strong>
                        	<span class="line-btn btn-green"><i class="fab fa-line"></i> Add Friends <a href="{{$productDetail->getShop->line_link}}">{{$productDetail->getShop->line_link}}</a></span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>  
    {{-- <div class="need-bargain">       
        <a href="#" class="btn">@lang('product.you_need_to_bargaining')?</a>
    </div> --}}
    <form id="FormBargain" name="FormBargain" action="{{action('PopUpController@saveBargain')}}" method="post">
        <span id="error_user_id" class="error"></span>
        <input type="hidden" name="_token" id="_token" value="{{csrf_token()}}">
        <div class="prod-orderDetail-box popup-bargain form-group"> 
            <div class="form-group">              
                <label>@lang('product.qty') :</label>
                <span class="spiner">
                    <span class="decrease fas fa-minus">  </span>
                    @php( $qty = 1)
                    @if($productDetail->order_qty_limit == 0)
                       @php( $qty = $productDetail->min_order_qty? $productDetail->min_order_qty:1)
                    @endif
                    
                    <input type="number" class="spinNum qtyvalue" name="qty" id="qty" value="{{$qty}}">
                    <span class="increase fas fa-plus">  </span>
                </span>
                <span class="qty-label">/ {{ $productDetail->package_name }}</span> 
                {{-- @if(empty($productDetail->order_qty_limit))
                    <span class="qty-label">@lang('product.minimum') {{$productDetail->min_order_qty}} {{ $productDetail->unit_name }}</span>
                @endif  --}}
                <span id="error_qty" class="error"></span>
            </div>  
        </div> 

        <div class="original-pricebox" style="display: none;">
            <h3 class="blue">@lang('product.original_price')</h3>
            <table class="table">                    
                <tr>
                    <th>@lang('common.price')/{{$productDetail->package_name}}</th>
                    <th>@lang('product.base_unit')/{{$productDetail->unit_name}}</th>
                    <th>@lang('common.total')</th>
                </tr>
                <tr>
                    <td>{{ numberFormat($productDetail->unit_price) }} @lang('common.baht')</td>
                    @php( $unitPrice = $productDetail->unit_price/$productDetail->weight_per_unit)
                    <td>{{numberFormat($unitPrice)}} @lang('common.baht')</td>
                    <td class="originalTotalPrice">{{numberFormat($qty*$productDetail->unit_price)}} @lang('common.baht')</td>
                </tr>
            </table>
        </div>

        <div class="original-pricebox">
            <h3 class="red">@lang('product.bargaining') </h3>
            <table class="table no-border">                    
                <tr>
                	<th>@lang('product.bargaining_base_unit')  {{$productDetail->unit_name}}</th>
                    <th>@lang('product.bargaining_price')/{{$productDetail->package_name}}</th>
                    <th>@lang('common.total')</th>
                </tr>
                <tr>
                	<td class="base-unit">
                        <div class="input-sign">
                            <input type="text" placeholder="@lang('common.base_unit_price')" name="base_unit_price" id="base_unit_price" value="{{numberFormat($unitPrice)}}" data-type="price">
                            <span class="curr-label">@lang('common.baht')</span>
                        </div>
                        <span id="error_base_unit_price" class="error"></span>
                    </td>
                    <td class="price-pkg">
                        <div class="input-sign">
                            <input type="text" placeholder="@lang('common.unit_price')" name="unit_price" id="unit_price" value="{{numberFormat($productDetail->unit_price)}}" data-type="price">
                            <span class="curr-label">@lang('common.baht')</span>
                        </div>
                        <span id="error_base_unit_price" class="error"></span>
                    </td>
                    
                    <td class="bargainTotal">{{numberFormat($qty*$productDetail->unit_price)}} @lang('common.baht')</td>
                </tr>
            </table>
        </div>

        <input type="hidden" name="unit_id" id="unit_id" value="{{$productDetail->base_unit_id}}">
        <input type="hidden" name="product_id" id="product_id" value="{{$productDetail->id}}"> 
        


        <div class="form-group btn-group text-center">            
            <button type="button" class="btn addBargin">@lang('common.bargain')</button>
        </div>
    </form>
</div>
{!! CustomHelpers::combineCssJs(['js/price_formatter','js/user/bargain'],'js') !!}