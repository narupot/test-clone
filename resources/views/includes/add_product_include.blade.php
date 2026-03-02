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
            <select name="unit" id="weightperunit" >
                <option value="">--@lang('common.select')--</option>
                <!-- {!!CustomHelpers::getPackagesOptain()!!} -->
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
                <input class="form-control" id="unitPrices" type="text" name="unit_price" placeholder="Unit Price">
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
<div class="row mb-2">
    <div class="col-lg-4">  
         <div class="form-group">
            <h3 class="title-prod"> น้ำหนักสินค้ารวมบรรจุภัณฑ์  <i class="red">*</i></h3>        
            <div class="input-sign">
                <input class="form-control" id="weightperpackage" value="0.00" onkeypress="return isNumericKey(event);"  type="text" name="weightperpackage" placeholder="น้ำหนักต่อบรรจุภัณฑ์...">
                <span class="curr-label" id="textunitshow">  </span>
            </div>
            <p id="error_weightperpackage" class="error"></p>
            @if($errors->has('weightperpackage'))
                <p class="error error-msg">{{ $errors->first('weightperpackage') }}</p>
            @endif
         </div>   
    </div>
</div>

<!-- <div class="row">
    <div class="col-lg-8">
        <h3 class="title-prod"> การจัดการ stock  </h3>
        <div class="row"> 
            <div class="col-md-6 form-group">
                <label class="radio-wrap">
                    <input type="radio" class="stock_status" id="optionstock" name="optionstock" checked="checked"  value="1"  onchange="Optionselectstock(this.value)" >
                    <span class="radio-mark"> ไม่จำกัดจำนวนสินค้า </span>
                </label>
            </div> 

            <div class="col-md-6 form-group">          
                <label class="radio-wrap">
                    <input type="radio" class="stock_status" id="optionstock"  name="optionstock" value="0"    onchange="Optionselectstock(this.value)" >
                    <span class="radio-mark"> ระบุจำนวนสินค้าใน Stock  </span>
                </label>
            </div> 
           
        </div>

        <div class="row" style="padding: 5px"> 
            <div class="col-lg-6" id="" >
            </div>
            <div class="col-lg-6" id="framenumstock" >
                <label> จำนวนสินค้าใน stock <span class="text-danger"> * (ระบุตัวเลขไม่เกิน 10,000) </span> </label>
                <input type="text" class="form-control" name="numstock" id="numstock"  value="1" onkeypress="return isNumericKey(event);" onchange="CheckMaxValue()" />
                <div id="msg_alert_numstock"> </div>
            </div>
        </div>

    </div>  
</div> -->
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                
                <h4 class="mb-4 text-dark font-weight-bold">
                    <i class="fas fa-cubes text-primary mr-2"></i>การจัดการ Stock
                </h4>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label class="stock-selector-card h-100">
                            <input type="radio" name="optionstock" value="1" 
                                   class="stock-radio" 
                                   onchange="toggleStockInput(this.value)" checked>
                            <div class="card-content">
                                <div class="icon-wrapper bg-soft-success text-success">
                                    <i class="fas fa-infinity fa-lg"></i>
                                </div>
                                <div class="text-content">
                                    <h6 class="font-weight-bold mb-1">ไม่จำกัดจำนวน</h6>
                                    <small class="text-muted">สินค้ามีขายตลอด</small>
                                </div>
                                <div class="check-icon"><i class="fas fa-check-circle"></i></div>
                            </div>
                        </label>
                    </div>

                    <div class="col-md-6">
                        <label class="stock-selector-card h-100">
                            <input type="radio" name="optionstock" value="0" 
                                   class="stock-radio" 
                                   onchange="toggleStockInput(this.value)">
                            <div class="card-content">
                                <div class="icon-wrapper bg-soft-primary text-primary">
                                    <i class="fas fa-box-open fa-lg"></i>
                                </div>
                                <div class="text-content">
                                    <h6 class="font-weight-bold mb-1">ระบุจำนวน</h6>
                                    <small class="text-muted">ตัด Stock ตามจริง</small>
                                </div>
                                <div class="check-icon"><i class="fas fa-check-circle"></i></div>
                            </div>
                        </label>
                    </div>
                </div>

                <div id="framenumstock" class="stock-input-wrapper" style="display:none;">
                    <div class="bg-light p-3 rounded border">
                        <label for="numstock" class="text-dark font-weight-bold mb-2">
                            จำนวนสินค้าใน Stock 
                        </label>
                        
                        <div class="input-group" style="max-width: 220px;">
                            <input type="text" class="form-control form-control-lg border-0 text-center" 
                                   name="numstock" id="numstock"
                                   value="1"
                                   placeholder="0"
                                   onkeypress="return isNumericKey(event);"
                                   onchange="CheckMaxValue()">
                            <div class="input-group-append">
                                <span class="input-group-text border-0 bg-white text-muted pl-3 pr-3">ชิ้น</span>
                            </div>
                        </div>

                        <small class="text-danger mt-2 d-block">
                            <i class="fas fa-info-circle"></i> ระบุตัวเลขไม่เกิน 100,000
                        </small>
                    </div>
                </div>

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
 

    <!-- <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>@lang('product.minimum_order')</label>
                <label class="chk-wrap" style="display:none">
                    <input type="checkbox" id="order_qty_limit" name="order_qty_limit" value="1">
                    <span class="chk-mark">@lang('product.no_limit')</span>
                </label>
            </div>
            <div class="form-group ml-md-4" id="min_order_qty_div">
                <label>@lang('product.min_order_quantity')<i class="red">*</i></label>
                <div class="form-group">
                    <div class="input-sign">
                        <input class="form-control" type="text" name="min_order_qty" onkeypress="return isNumberKey(event);" value="1" placeholder="quantity">
                        <span class="curr-label">@lang('product.unit')</span>
                    </div>
                    <p id="error_min_order_qty" class="error"></p>
                    @if($errors->has('min_order_qty'))
                        <p class="error error-msg">{{ $errors->first('min_order_qty') }}</p>
                    @endif
                </div>
            </div>    
        </div>
    </div> -->

    <div class="form-group">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="order_qty_limit">@lang('product.minimum_order')</label>
                    <label class="chk-wrap" style="display: none" >
                        <input type="checkbox" id="order_qty_limit" name="order_qty_limit" value="1">
                        <span class="chk-mark">@lang('product.no_limit')</span>
                    </label>
                </div>
                <div class="form-group ml-md-4" id="min_order_qty_div" >
                    <label for="min_order_qty">@lang('product.min_order_quantity')<i class="red">*</i></label>
                    <div class="form-group">
                        <div class="input-sign">
                            <input type="text" name="min_order_qty" onkeypress="return isNumberKey(event);" placeholder="quantity" value="1">
                            <span class="curr-label">Unit</span>
                        </div>
                        <p id="error_min_order_qty" class="error"></p>
                        @if($errors->has('min_order_qty'))
                            <p class="error error-msg">{{ $errors->first('min_order_qty') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    
   <h3 class="title-prod">@lang('product.stock')</h3>
    <!-- <div class="row">
        div class="form-group">
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
        </div
        <div class="col-md-4 form-group">
            <label class="radio-wrap">
                <input type="radio" class="stock_status" name="product_status" value="1" checked="checked">
                <span class="radio-mark">@lang('product.in_stock')</span>
            </label>
        </div>  
        <div class="col-md-4 form-group">
            <label class="radio-wrap">
                <input type="radio" class="stock_status" name="product_status"  value="0">
                <span class="radio-mark">@lang('product.out_stock')</span>
            </label>
        </div>   
    </div> -->

    <div class="row">
        <div class="col-sm-12">
            <div class="form-group mb-3">
                <label class="mb-2 font-weight-bold">สถานะสินค้า</label>
                <div class="product-status-wrapper">
                    
                    <input type="radio" class="btn-check" name="product_status" id="status_instock" value="1" checked>
                    <label class="status-btn success-btn" for="status_instock">
                        <i class="fas fa-check-circle"></i> @lang('product.in_stock')
                    </label>

                    <input type="radio" class="btn-check" name="product_status" id="status_outstock" value="0">
                    <label class="status-btn danger-btn" for="status_outstock">
                        <i class="fas fa-times-circle"></i> @lang('product.out_stock')
                    </label>

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



<!-- <div class="row">
    <div class="form-group mb-3 col-md-4">
        <label>@lang('product.status')</label>
        <select name="status" id="status">
            <option value="1">@lang('common.active')</option>
            <option value="0">@lang('common.inactive')</option>
        </select>
        
    </div>
</div> -->
@section('footer_scripts_include')
    <script type="text/javascript">
    $(function(){
        function safeFixed(val) {
            val = parseFloat(val);
            return (isNaN(val) || !isFinite(val)) ? 0 : val;
        }

        function formatDecimal(val) {
            return safeFixed(val).toFixed(2);
        }
        $('#weightPerUnits, #unitPrices').keyup(function(){
           var unitWeight = formatDecimal($('#weightPerUnits').val()) || 0;
           var unitPrice = formatDecimal($('#unitPrices').val()) || 0;
           $('#unitPerPrice').val(formatDecimal(unitPrice / unitWeight)) || 0;
        });

        $('#weightPerUnits, #unitPerPrice').keyup(function(){
           var unitWeight = formatDecimal($('#weightPerUnits').val()) || 0;
           var unitPerPrice = formatDecimal($('#unitPerPrice').val()) || 0;
           $('#unitPrices').val(formatDecimal(unitPerPrice * unitWeight));
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

        
    const getWeightPerPackage = (weightperpackage) => {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken } });
        $.ajax({
            type: "get",
            url: "{{ route('ajaxController.weightperpackage') }}",
            data: { 
                packageid: weightperpackage,                            
            },
            success: function( result ) {
                $("#textunitshow").text(result.message);
            },  error: function(xhr, status, error) {
            }
        }); 
    };

    $(document).ready(function() {
        getWeightPerPackage($("#weightperunit").val());
        $("#weightperunit").on( "change", function() {
            var weightperpackage = $(this).val();
            if(weightperpackage != '') {
                getWeightPerPackage(weightperpackage);
            } else {
                $("#textunitshow").text('');
            }
        });
    });

    </script>
        <script>
         // A $( document ).ready() block.
            $(document).ready(function() {
                $("#weightperunit").on( "change", function() {

                   var weightperpackage = $(this).val();
                   $.ajax({
                        type: "get",
                        url: "{{ url('/seller/weightperpackage') }}",
                        data: { 
                            packageid: weightperpackage,                            
                        },
                        success: function( msg ) {
                            $("#textunitshow").text(msg);
                            //alert(msg);
                        },                      
                    }); 
                });
            });            
    </script>

    <script>
    // A $( document ).ready() block.
    $(document).ready(function() {
        $("#framenumstock").hide();
    });

    function Optionselectstock(value){
        
       if (value == 0) {            
           // $("#numstock").val("2000000");
           // $("#framenumstock").hide();
           $("#numstock").val("1");
           $("#framenumstock").show();
          // $("#numstock").max(10000);
           //$("input#numstock").attr({"max" : 10000});

       } else  {
            //$("#framenumstock").show();
            $("#numstock").val("1");
            $("#framenumstock").hide();
            //$("#numstock").val("100");
       }
    }

    function CheckMaxValue(){
        $inputoptionstock = $("#optionstock:checked").val();
        $numstock = $("#numstock").val();
        if ($inputoptionstock == 0 &&  $numstock > 100000 ) {
            $numstock = $("#numstock").val(1);
            //alert("ไม่เกิน 100000");
        }
    }

    
  
</script>
<script>
    function toggleStockInput(val) {
        var inputFrame = document.getElementById('framenumstock');
        var inputField = document.getElementById('numstock');

        if (val == '0') {
            inputFrame.style.display = 'block';
            inputField.focus();
            if(inputField.value === '') inputField.value = 1;
        } else {
            inputFrame.style.display = 'none';
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        var checkedRadio = document.querySelector('input[name="optionstock"]:checked');
        if(checkedRadio) {
            toggleStockInput(checkedRadio.value);
        }
    });
</script>
@stop