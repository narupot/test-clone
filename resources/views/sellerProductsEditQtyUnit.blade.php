<script type="text/javascript">
  var lang_json = {"ok":"@lang('common.ok')", "success":"@lang('common.success')"};
  var weight_per_unit = "{{$productDetail->weight_per_unit}}";
  var orignal_unit_price = "{{$productDetail->unit_price}}";
  var currency = "@lang('common.baht')";
</script>
<div class="modal-header">          
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
                
                <div class="price-box">
                    @if(!$productDetail->show_price)
                        <span class="price">@lang('product.ask_the_price_from_the_store')</span>
                    @else
                        <span class="price">{{ numberFormat($productDetail-> unit_convert_price) }} @lang('common.baht')/{{ $productDetail->unit_name}}</span>
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
                                        {{$productDetail->quantity.' '. $productDetail->unit_name }}
                                    @else
                                        <span class="red outstock">@lang('product.out_of_stock')</span>
                                    @endif
                                    </div>
                                @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>  
    
    <form id="FormPrice" name="FormPrice" action="{{action('PopUpController@saveUnitPrice')}}" method="post">
        <span id="error_user_id" class="error"></span>
        <input type="hidden" name="_token" id="_token" value="{{csrf_token()}}">
        <div class="prod-orderDetail-box popup-bargain form-group"> 
            <div class="form-group row" id="price_div">
                <div class="col-sm-5">
                    <label>ราคาต่อหน่วย<i class="red">*</i></label>
                    <div class="input-sign">
                        <input type="text" name="unit_price" data-type="price" placeholder="Unit Price" value="{{$productDetail->unit_convert_price}}" class=""
                        onkeydown="if(event.key === 'Enter'){ event.preventDefault(); $('.saveProductPrice').trigger('click'); }">
                        <span class="curr-label">{{$productDetail->unit_name}}</span>
                    </div>
                    <p id="error_unit_price" class="error"></p>
                </div>
            </div> 
        </div> 
        <input type="hidden" name="product_id" id="product_id" value="{{$productDetail->id}}"> 
        <div class="form-group btn-group text-center">            
            <button type="button" class="btn saveProductPrice">@lang('common.update')</button>
        </div>
    </form>
</div>
{!! CustomHelpers::combineCssJs(['js/price_formatter','js/seller/product'],'js') !!}