<div class="form-group">
    <label>@lang('product.select_product_standard')<i class="red">*</i></label>
    <div class="row">
        <div class="col-md-3 mb-2">
            <select name="size" id="size">
                <option value="">@lang('product.select_size')</option>
                @foreach(CustomHelpers::getBadgeSize() as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="grade" id="grade">
                <option value="">@lang('product.select_grade')</option>
                @foreach(CustomHelpers::getBadgeGrade() as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
            <div class="size-popup mt-2"><a class="link-primary" href="javascript:;"  data-toggle="modal" data-target="#standard_size_popup">Standard Size Popup</a></div>
        </div>
        
        <p id="error_product_badge" class="error"></p>
        @if($errors->has('product_badge'))
            <p class="error error-msg">{{ $errors->first('product_badge') }}</p>
        @endif  
    </div>    

    <!--div class="sel-product-standard">
        <ul class="filter-select">
        @if(count($prod_badge) > 0)
            @foreach($prod_badge as $badge)
                <li>
                    <label class="radio-wrap">
                        <input type="radio" name="product_badge" value="{{$badge->id}}">
                        <span class="radio-mark">
                            <span class="img-upload">
                                <img src="{{getBadgeImageUrl($badge->icon)}}" width="76" height="57" alt="">
                            </span>
                            <span class="chk-text">{{$badge->badgedesc->badge_name}}</span>
                        </span>
                    </label>                        
                </li>
            @endforeach
        @endif
        </ul>
        <p id="error_product_badge" class="error"></p>
        @if($errors->has('product_badge'))
            <p class="error error-msg">{{ $errors->first('product_badge') }}</p>
        @endif  
    </div-->                  
</div>

<div class="form-group">
    <label>@lang('product.product_image')<i class="red">*</i></label>
    <ul class="upl-prod-img" id="product_img_div">
        <li class="upload-img-wrap">
            <span id="product_img_span">
                <input type="file" class="product_image" name="product_image[]" accept="image/*" multiple="multiple">
            </span>
            <span class="upload-cam"><i class="fas fa-camera"></i></span>
        </li>
    </ul>
    <p id="error_product_image" class="error"></p>
    @if($errors->has('product_image'))
        <p class="error error-msg">{{ $errors->first('product_image') }}</p>
    @endif 
</div>

<div class="form-group row">
    <div class="col-sm-6">
        <label>@lang('product.product_detail')<i class="red">*</i></label>
        <textarea name="description"></textarea>
        <p id="error_description" class="error"></p>
        @if($errors->has('description'))
            <p class="error error-msg">{{ $errors->first('description') }}</p>
        @endif
    </div>
</div>
<h3 class="title-prod">@lang('product.price_package')</h3>
<h4 class="mb-3">You can't set it anywhere, the system will automatically calculate it.</h4>
<div class="row">
    <div class="col-md-4 form-group border-right unit-sel">
        <div class="icons mb-3" style="font-size: 36px;"><i class="fas fa-box"></i></div>
        <div class="form-group">
            <label>@lang('product.package')</label>
            <select name="unit">
                <option value="">--@lang('common.select')--</option>
                {!!CustomHelpers::getPackagesOptain()!!}
            </select>
            <p id="error_unit" class="error"></p>
            @if($errors->has('unit'))
                <p class="error error-msg">{{ $errors->first('unit') }}</p>
            @endif
        </div>
        <div class="form-group" id="price_div">
            <label>@lang('product.show_price')<i class="red">*</i></label>
            <div class="input-sign">
                <input type="hidden" class="show_price" name="show_price"  value="1"> 
                <input class="form-control" id="unitPrices" type="text" name="unit_price" data-type="price" placeholder="Unit Price">
                <span class="curr-label">{{$currency_dtl->name}}</span>
            </div>
            <p id="error_unit_price" class="error"></p>
            @if($errors->has('unit_price'))
                <p class="error error-msg">{{ $errors->first('unit_price') }}</p>
            @endif
        </div>  
    </div>
    <div class="col-md-4 form-group">
        <div class="icons mb-3" style="font-size: 36px;"><i class="fas fa-lemon"></i></div>
        <div class="form-group">
            <label>@lang('product.base_unit')<i class="red">*</i></label>
            <div class="prod-weight">
                <input type="text" id="weightPerUnits" name="weight_per_unit" onkeypress="return isNumericKey(event);">            
                <select name="baseunit" id="baseunit" class="ml-4">
                    <option value="">--@lang('common.select')--</option>
                </select>
                
            </div>
            <p id="error_weight_per_unit" class="error"></p>
            @if($errors->has('weight_per_unit'))
                <p class="error error-msg">{{ $errors->first('weight_per_unit') }}</p>
            @endif
        </div>
        <div class="form-group">
            <label>@lang('product.unit_price')</label>
            <div class="input-sign">
                <input class="form-control" id="unitPerPrice" type="text" name="unit_perprice" placeholder="Unit Price">
                <span class="curr-label">Per</span>
            </div>
        </div>
    </div>
</div>

{{--<div class="row d-none">
    <div class="form-group col-sm-3 unit-sel">
        <label>@lang('product.package')</label>
        <select name="unit">
            <option value="">--@lang('common.select')--</option>
            {!!CustomHelpers::getPackagesOptain()!!}
        </select>
        <p id="error_unit" class="error"></p>
        @if($errors->has('unit'))
            <p class="error error-msg">{{ $errors->first('unit') }}</p>
        @endif
    </div>
    <!--div class="col-sm-3">
        <label>@lang('product.product_weight_in_kg')/@lang('product.unit')<i class="red">*</i></label>
        <div class="prod-weight">
            <input type="text" name="weight_per_unit" onkeypress="return isNumericKey(event);">
            <span class="prod-wgt">(@lang('product.kilogram')) / @lang('product.unit')</span>
        </div>
        <p id="error_weight_per_unit" class="error"></p>
        @if($errors->has('weight_per_unit'))
            <p class="error error-msg">{{ $errors->first('weight_per_unit') }}</p>
        @endif
    </div-->
    
    <div class="form-group col-sm-3">
        <label>@lang('product.base_unit')<i class="red">*</i></label>
        <div class="prod-weight">
            <input type="text" name="weight_per_unit" onkeypress="return isNumericKey(event);">            
            <select name="baseunit" id="baseunit" class="ml-4">
                <option value="">--@lang('common.select')--</option>
            </select>
            
        </div>
        <p id="error_weight_per_unit" class="error"></p>
        @if($errors->has('weight_per_unit'))
            <p class="error error-msg">{{ $errors->first('weight_per_unit') }}</p>
        @endif
    </div>
</div>--}}
{{--<div class="form-group">
    <label>@lang('product.price')<i class="red">*</i></label>
    <div class="row">
        <div class="col-sm-3">
            <!--div class="form-group">
                <label class="radio-wrap">
                    <input type="radio" class="show_price" name="show_price"  value="1">
                    <span class="radio-mark">@lang('product.show_price')</span>
                </label>
            </div>
            <p id="error_show_price" class="error"></p-->
            @if($errors->has('show_price'))
               <!--p class="error error-msg">{{ $errors->first('show_price') }}</p-->
            @endif 
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4 form-group" id="price_div">
            <!--label>@lang('product.show_price')<i class="red">*</i></label-->
            <div class="input-sign">
                <input type="hidden" class="show_price" name="show_price"  value="1"> 
                <input class="form-control" type="text" name="unit_price" data-type="price" placeholder="Unit Price">
                <span class="curr-label">{{$currency_dtl->name}}</span>
            </div>
            <p id="error_unit_price" class="error"></p>
            @if($errors->has('unit_price'))
                <p class="error error-msg">{{ $errors->first('unit_price') }}</p>
            @endif
        </div>  
    </div>
        <!--div class="col-sm-3">           
            <div class="form-group">
                <label class="radio-wrap">
                    <input type="radio" class="show_price" name="show_price" value="0">
                    <span class="radio-mark">@lang('product.not_show')</span>
                </label>
            </div>
        </div-->
    </div>--}}
 
   <h3 class="title-prod">@lang('product.stock')</h3>
    <div class="row">
        <!--div class="form-group">
            <label>@lang('product.stock')<i class="red">*</i></label>
            <label class="chk-wrap">
                <input type="checkbox" id="stock" name="stock" value="1">
                <span class="chk-mark">@lang('product.unlimited')</span>
            </label>
        </div-->
        <!--div class="form-group col-sm-3">
            <label>@lang('product.quantity')<i class="red">*</i></label>
            <div class="input-sign">
                <input type="text" name="quantity" placeholder="quantity" onkeypress="return isNumberKey(event);">
                <span class="curr-label">@lang('product.unit')</span>
            </div>  
            <p id="error_quantity" class="error"></p>
            @if($errors->has('quantity'))
                <p class="error error-msg">{{ $errors->first('quantity') }}</p>
            @endif
        </div--> 
        <div class="col-md-3 form-group">
            <label class="radio-wrap">
                <input type="radio" class="stock_status" name="stock" value="1" checked="checked">
                <span class="radio-mark">@lang('product.in_stock')</span>
            </label>
        </div>  
        <div class="col-md-3 form-group">
            <label class="radio-wrap">
                <input type="radio" class="stock_status" name="stock"  value="0">
                <span class="radio-mark">@lang('product.out_stock')</span>
            </label>
        </div>   
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>@lang('product.minimum_order')</label>
                <label class="chk-wrap">
                    <input type="checkbox" id="order_qty_limit" name="order_qty_limit" value="1">
                    <span class="chk-mark">@lang('product.no_limit')</span>
                </label>
            </div>
            <div class="form-group ml-md-4" id="min_order_qty_div">
                <label>@lang('product.min_order_quantity')<i class="red">*</i></label>
                <div class="form-group">
                    <div class="input-sign">
                        <input class="form-control" type="text" name="min_order_qty" onkeypress="return isNumberKey(event);" placeholder="quantity">
                        <span class="curr-label">@lang('product.unit')</span>
                    </div>
                    <p id="error_min_order_qty" class="error"></p>
                    @if($errors->has('min_order_qty'))
                        <p class="error error-msg">{{ $errors->first('min_order_qty') }}</p>
                    @endif
                </div>
            </div>    
        </div>
    </div>

<!--div class="form-group row">
    <div class="col-sm-12 col-md-10 col-lg-6">
        <div class="form-group">
            <label class="chk-wrap">
                <input type="checkbox" id="is_tier_price" name="is_tier_price" value="1">
                <span class="chk-mark">@lang('product.tier_price_setting')</span>
            </label>
        </div>
        <div class="tier-price-box" id="tier_price_div" style="display: none;">
            <label class="tier-label">@lang('product.tier_price')</label>
            <div id="tier_price_rows">
                <div class="tier-input-group" data-attr="new">
                    <div class="tier-col initial-num">
                        <label>@lang('product.initial_number')</label>
                        <div class="input-sign">
                            <input type="text" name="tier_price[min_qty][]" onkeypress="return isNumberKey(event);" placeholder="Qty">
                            <span class="curr-label">@lang('product.unit')</span>
                        </div>
                    </div>
                    <div class="tier-col end-num">
                        <label>@lang('product.end_number')</label>
                        <div class="input-sign">
                            <input type="text" name="tier_price[max_qty][]" onkeypress="return isNumberKey(event);" placeholder="Qty">
                            <span class="curr-label">@lang('product.unit')</span>
                            <span class="minus">-</span>
                        </div>
                    </div>
                    <div class="tier-col unit-price">
                        <label>@lang('product.unit_price')</label>
                        <div class="input-sign">
                            <input type="text" name="tier_price[tier_unit_price][]" data-type="price" placeholder="Price">
                            <span class="curr-label">{{$currency_dtl->name}}</span>
                            <span class="minus">=</span>
                        </div>
                    </div>
                    <div class="tier-col">
                        <label class="d-none d-sm-none d-md-block">&nbsp;</label>
                        <button type="button" class="btn remove_more_btn btn-danger">@lang('product.remove')</button>
                    </div>
                </div>
            </div>
            <p id="error_tier_price" class="error"></p>
            @if($errors->has('tier_price'))
                <p class="error error-msg">{{ $errors->first('tier_price') }}</p>
            @endif                            
            <div class="tier-col">                            
                <button type="button" class="btn-grey btn-primary" id="add_more_btn">@lang('product.add_more')</button>
            </div>                         
        </div>
    </div>
</div-->



<div class="row">
    <div class="form-group mb-3 col-md-4">
        <label>@lang('product.status')</label>
        <select name="status" id="status">
            <option value="1">@lang('common.active')</option>
            <option value="0">@lang('common.inactive')</option>
        </select>
        
    </div>
</div>
@section('footer_scripts_include')
    <script type="text/javascript">
    $(function(){
        $('#weightPerUnits, #unitPrices').keyup(function(){
           var unitWeight = parseFloat($('#weightPerUnits').val()) || 0;
           var unitPrice = parseFloat($('#unitPrices').val()) || 0;
           $('#unitPerPrice').val(unitPrice / unitWeight);
        });

        $('#weightPerUnits, #unitPerPrice').keyup(function(){
           var unitWeight = parseFloat($('#weightPerUnits').val()) || 0;
           var unitPerPrice = parseFloat($('#unitPerPrice').val()) || 0;
           $('#unitPrices').val(unitPerPrice * unitWeight);
        });

        $('#weightPerUnits, #unitPrices').keyup(function(){
           var unitWeight = parseFloat($('#weightPerUnits').val()) || 0;
           var unitPrice = parseFloat($('#unitPrices').val()) || 0;
           $('#unitPerPrice').val(unitPrice / unitWeight);
        });

        $('#weightPerUnits, #unitPerPrice').keyup(function(){
           var unitWeight = parseFloat($('#weightPerUnits').val()) || 0;
           var unitPerPrice = parseFloat($('#unitPerPrice').val()) || 0;
           $('#unitPrices').val(unitPerPrice * unitWeight);
        });
    });    
   </script>
    <script type="text/javascript">
        $('body').on('click','#add_more_btn',function(){
            //var row_str = $('#tier_price_rows div').first();
            var row_str = '<div class="tier-input-group" data-attr="new">'+
                                    '<div class="tier-col initial-num">'+
                                        '<label>@lang('product.initial_number')</label>'+
                                        '<div class="input-sign">'+
                                            '<input type="text" name="tier_price[min_qty][]" onkeypress="return isNumberKey(event);" placeholder="Qty" value="">'+
                                            '<span class="curr-label">@lang('product.unit')</span>'+
                                        '</div>'+
                                    '</div>'+
                                    '<div class="tier-col end-num">'+
                                        '<label>@lang('product.end_number')</label>'+
                                        '<div class="input-sign">'+
                                            '<input type="text" name="tier_price[max_qty][]" onkeypress="return isNumberKey(event);" placeholder="Qty" value="">'+
                                            '<span class="curr-label">@lang('product.unit')</span>'+
                                            '<span class="minus">-</span>'+
                                        '</div>'+
                                    '</div>'+
                                    '<div class="tier-col unit-price">'+
                                        '<label>@lang('product.unit_price')</label>'+
                                        '<div class="input-sign">'+
                                            '<input type="text" name="tier_price[tier_unit_price][]" data-type="price" placeholder="Price" value="">'+
                                            '<span class="curr-label">{{$currency_dtl->name}}</span>'+
                                            '<span class="minus">=</span>'+
                                        '</div>'+
                                    '</div>'+
                                    '<div class="tier-col" style="display: none;">'+
                                        '<label class="d-none d-sm-none d-md-block">&nbsp;</label>'+
                                        '<button type="button" class="btn remove_more_btn">@lang('product.remove')</button>'+
                                    '</div>'+
                                '</div>';

            $('#tier_price_rows').append(row_str);
            $('#tier_price_rows div').last().show();
        });
    </script>
@stop