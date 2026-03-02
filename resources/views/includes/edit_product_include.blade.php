<div class="form-group">
    <label for="size">@lang('product.select_product_standard')<i class="red">*</i></label>
    <div class="row">
        <div class="col-md-3" >
            <select name="size" id="size">
                <option value="">@lang('product.select_size')</option>
                @foreach(CustomHelpers::getBadgeSize() as $key => $value)
                    <option @if($badge && $key == $badge->size) selected="selected" @endif value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 mb-1">
            <select name="grade" id="grade">
                <option value="">@lang('product.select_grade')</option>
                @foreach(CustomHelpers::getBadgeGrade() as $key => $value)
                    <option value="{{ $key }}"  @if($badge && $key == $badge->grade) selected="selected" @endif>{{ $value }}</option>
                @endforeach
            </select>
            <div class="size-popup mt-2"><a class="link-primary" href="javascript:;"  data-toggle="modal" data-target="#standard_size_popup">Standard Size Popup</a></div>
        </div>
        
        <p id="error_product_badge" class="error"></p>
        @if($errors->has('product_badge'))
            <p class="error error-msg">{{ $errors->first('product_badge') }}</p>
        @endif
    </div>
</div>

<div class="form-group">
    <label for="product_img_div">@lang('product.product_image')<i class="red">*</i></label>

    <ul class="upl-prod-img" id="product_img_div">
        <li class="upload-img-wrap">
            <span id="product_img_span">
                <input type="file" class="product_image" name="product_image[]" accept="image/*" multiple="multiple">
            </span>
            <span class="upload-cam"><i class="fas fa-camera"></i></span>
        </li>
        @if(isset($result->images) && count($result->images)>0 && $type == 'edit')
            @foreach($result->images??[] as $imageval)
                <li>
                    <div class="img-block">
                        <input type="hidden" name="product_image_id[]" value="{{$imageval->id}}">
                        <img src="{{$imageval->image ? getProductImageUrlRunTime($imageval->image,'original') : ''}}" width="119" height="119">
                    </div>
                    <div class="action-block">
                        <a href="javascript:void(0);" class="delete_image">Delete <i class="fas fa-times"></i></a>
                    </div>
                </li>
            @endforeach
        @endif
    </ul>

    <p id="error_product_image" class="error"></p>
    @if($errors->has('product_image'))
       <p class="error error-msg">{{ $errors->first('product_image') }}</p>
    @endif
</div>

<div class="form-group row">
    <div class="col-sm-6">
        <span class="d-none">froala-editor-apply</span>
        <label for="froala-editor">@lang('product.product_detail')<i class="red">*</i></label>
        @if($result->productDesc)
            <textarea name="description" class="" id="froala-editor">{{$result->productDesc->description}}</textarea>
        @else
            <textarea name="description" class="" id="froala-editor"></textarea>

        @endif

        <p id="error_description" class="error"></p>
        @if($errors->has('description'))
            <p class="error error-msg">{{ $errors->first('description') }}</p>
        @endif
    </div>
</div>

<h3 class="step-title pl-3">@lang('product.price_package')</h3>
<h4 class="mb-3">You can't set it anywhere, the system will automatically calculate it.</h4>
<div class="row">
    <div class="col-md-4 form-group border-right unit-sel">
        <div class="icons mb-3" style="font-size: 36px;"><i class="fas fa-box"></i></div>
        <div class="form-group">
            <label for="weightperunit">@lang('product.package')</label>
            <select name="unit" id="weightperunit" >
                <option value="">--@lang('common.select')--</option>
                {!! CustomHelpers::getPackagesOptain($result->package_id ?? null, $result->cat_id ?? null) !!}
            </select>
            <p id="error_unit" class="error"></p>
            @if($errors->has('unit'))
                <p class="error error-msg">{{ $errors->first('unit') }}</p>
            @endif
        </div>
        <div class="form-group" id="price_div">
            <label for="unitPrices">@lang('product.show_price')<i class="red">*</i></label>
            <div class="input-sign">
                <input type="hidden" class="show_price" name="show_price"  value="1">
                <input type="text" id="unitPrices" name="unit_price" placeholder="Unit Price" value="{{$result->unit_price??null}}">
                <span class="curr-label">{{$currency_dtl->name??null}}</span>
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
            <label for="weightPerUnits">@lang('product.base_unit')<i class="red">*</i></label>
            <div class="prod-weight">
                <input type="text" id="weightPerUnits" name="weight_per_unit" onkeypress="return isNumericKey(event);" value="{{$result->weight_per_unit}}">
                <select name="baseunit" id="baseunit" class="ml-3">
                    {{!!CustomHelpers::getParentCatBaseUnitOption($result->cat_id??null, $result->base_unit_id??null)!!}}
                </select>
                
            </div>
            <p id="error_weight_per_unit" class="error"></p>
            @if($errors->has('weight_per_unit'))
                <p class="error error-msg">{{ $errors->first('weight_per_unit') }}</p>
            @endif
        </div>
        <div class="form-group">
            <label for="unitPerPrice">@lang('product.unit_price')</label>
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
            <h3 class="title-prod"> น้ำหนักสินค้ารวมบรรจุภัณฑ์ <i class="red">*</i> </h3>
            <div class="input-sign">
                <input class="form-control" id="weightperpackage"
                onkeypress="return isNumericKey(event);"
                value="{{$result->weight_per_package == null?0:$result->weight_per_package}}"
                type="number"  name="weightperpackage" placeholder="น้ำหนักต่อบรรจุภัณฑ์..." min="0.01" step="0.01">

                @if($errors->has('weightperpackage'))
                    <p class="error-msg text-danger mt-1">{{ $errors->first('weightperpackage') }}</p>
                @endif
                <span class="curr-label" id="textunitshow">
                </span>
            </div>
         </div>
    </div>
</div>

<!-- <div class="row">
    <div class="col-lg-8">
        <h3 class="title-prod"> การจัดการ stock  </h3>

        <div class="row">
             <div class="col-md-6 form-group">
                <label class="radio-wrap">
                    <input type="radio" class="stock_status" id="optionstock" name="optionstock" value="1"  {{$result->stock == 1 ?"checked" :""}}  onchange="Optionselectstock(this.value)" >
                    <span class="radio-mark"> ไม่จำกัดจำนวนสินค้า </span>
                </label>
            </div>

            <div class="col-md-6 form-group">
                <label class="radio-wrap">
                    <input type="radio" class="stock_status"  id="optionstock"
                    name="optionstock" value="0" {{$result->stock == 0 ?"checked" :""}}
                    onchange="Optionselectstock(this.value)" >
                    <span class="radio-mark"> ระบุจำนวนสินค้าใน Stock  </span>
                </label>
            </div>
        </div>

         <div class="row" style="padding: 5px">
            <div class="col-lg-6" id="" >
            </div>
                <div class="col-lg-6" id="framenumstock" @if ($result->stock == 0) style="display:inline" @else style="display:none" @endif   >
                        <label for="numstock"> จำนวนสินค้าใน stock <span class="text-danger">
                            * (ระบุตัวเลขไม่เกิน 100,000) </span> </label>  <br/>
                        <input type="text" class="form-control" name="numstock" id="numstock"
                        value="{{$result->stock == 1?1:$result->quantity}}"
                        onkeypress="return isNumericKey(event);"
                        onchange="CheckMaxValue()" />
                </div>
        </div>

    </div>
</div> -->


<!-- <div class="row">
    <div class="col-lg-8">
        <h3 class="title-prod"> การจัดการ stock </h3>
        <div class="row">
           <div class="col-md-6 form-group">
                <label class="radio-wrap">
                    <input type="radio" class="stock_status" name="optionstock" value="1" 
                           {{$result->stock == 1 ?"checked" :""}} 
                           onchange="Optionselectstock(this.value)">
                    ไม่จำกัดจำนวนสินค้า
                </label>
            </div>

            <div class="col-md-6 form-group">
                <label class="radio-wrap">
                    <input type="radio" class="stock_status" name="optionstock" value="0" 
                           {{$result->stock == 0 ?"checked" :""}}
                           onchange="Optionselectstock(this.value)">
                    <span class="radio-mark"></span>
                    ระบุจำนวนสินค้าใน Stock 
                </label>
            </div>
        </div>
        
        <div class="row" style="padding: 5px">
             <div class="col-lg-6" id="" >
             </div>
             <div class="col-lg-6" id="framenumstock" @if ($result->stock == 0) style="display:block" @else style="display:none" @endif   >
                <label for="numstock"> จำนวนสินค้าใน stock <span class="text-danger">
                        * (ระบุตัวเลขไม่เกิน 100,000) </span> </label>  <br/>
                <input type="text" class="form-control" name="numstock" id="numstock"
                       value="{{$result->stock == 1?99999999:$result->quantity}}"
                       onkeypress="return isNumericKey(event);"
                       onchange="CheckMaxValue()" />
             </div>
        </div>
    </div>
</div> -->


<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                
                <h4 class="mb-4 text-dark font-weight-bold">
                    <i class="fas fa-cubes text-primary mr-2"></i>การจัดการ Stock
                </h4>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label class="stock-selector-card h-100">
                            <input type="radio" name="optionstock" value="1" 
                                   class="stock-radio"
                                   onclick="handleStockChange(this.value)"
                                   {{-- Logic เช็คค่าเดิม --}}
                                   @if((isset($result) && $result->stock == '1') || !isset($result)) checked @endif>
                                   
                            <div class="card-content">
                                <div class="icon-wrapper bg-soft-success text-success">
                                    <i class="fas fa-infinity fa-2x"></i>
                                </div>
                                <div class="text-content">
                                    <h6 class="font-weight-bold mb-1">ไม่จำกัดจำนวน</h6>
                                    <small class="text-muted">สินค้าพร้อมขายตลอด</small>
                                </div>
                                <div class="check-icon"><i class="fas fa-check-circle"></i></div>
                            </div>
                        </label>
                    </div>

                    <div class="col-md-6">
                        <label class="stock-selector-card h-100">
                            <input type="radio" name="optionstock" value="0" 
                                   class="stock-radio"
                                   onclick="handleStockChange(this.value)"
                                   @if(isset($result) && $result->stock == '0') checked @endif>

                            <div class="card-content">
                                <div class="icon-wrapper bg-soft-primary text-primary">
                                    <i class="fas fa-box-open fa-2x"></i>
                                </div>
                                <div class="text-content">
                                    <h6 class="font-weight-bold mb-1">ระบุจำนวนสินค้า</h6>
                                    <small class="text-muted">ตัดสต็อกตามจริง</small>
                                </div>
                                <div class="check-icon"><i class="fas fa-check-circle"></i></div>
                            </div>
                        </label>
                    </div>
                </div>

                <div id="stock_input_area" style="display: none;">
                    <div class="bg-light p-3 rounded border">
                        <label for="numstock" class="text-dark font-weight-bold mb-2">
                            จำนวนคงเหลือ <span class="text-danger">*</span>
                        </label>
                        
                        <div class="input-group" style="max-width: 250px;">
                           <input type="number" class="form-control form-control-lg border-1 text-center"
                                name="numstock" id="numstock"
                                value="{{ old('numstock', (isset($result->numstock) && $result->numstock !== null) ? $result->numstock : 0) }}"
                                placeholder="0"
                                min="0"
                                onkeypress="return isNumericKey(event);"
                                onchange="CheckMaxValue()">
                            <div class="input-group-append">
                                <span class="input-group-text border-0 bg-white text-muted pl-3 pr-3">ชิ้น</span>
                            </div>
                        </div>
                        <small class="text-muted mt-2 d-block">
                            <i class="fas fa-info-circle text-warning"></i> สูงสุดไม่เกิน 100,000 ชิ้น
                        </small>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="form-group">
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="order_qty_limit">@lang('product.minimum_order')</label>
                <label class="chk-wrap" style="display: none" >
                    <input type="checkbox" id="order_qty_limit" name="order_qty_limit" value="1" @if($result->order_qty_limit == '1')checked="checked"@endif>
                    <span class="chk-mark">@lang('product.no_limit')</span>
                </label>
            </div>
            <div class="form-group ml-md-4" id="min_order_qty_div" >
                <label for="min_order_qty">@lang('product.min_order_quantity')<i class="red">*</i></label>
                <div class="form-group">
                    <div class="input-sign">
                        <input type="text" name="min_order_qty" onkeypress="return isNumberKey(event);" placeholder="quantity" value="{{$result->min_order_qty}}">
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

<!-- <h3 class="title-prod">@lang('product.stock')</h3>
<div class="row">
    <div class="col-sm-4">
        <div class="form-group mb-3">
            <label class="radio-wrap">
                <input type="radio" class="stock_status" name="product_status" value="1" @if($result->status == '1') checked="checked" @endif>
                <span class="radio-mark">@lang('product.in_stock')</span>
            </label>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="form-group mb-3">
            <label class="radio-wrap">
                <input type="radio" class="stock_status" name="product_status" value="0" @if($result->status == '0') checked="checked" @endif>
                <span class="radio-mark">@lang('product.out_stock')</span>
            </label>
        </div>
    </div>
</div> -->

<h3 class="title-prod">@lang('product.stock')</h3>
<div class="row">
    <div class="col-sm-12">
        <div class="form-group mb-3">
            <label class="mb-2 font-weight-bold">สถานะสินค้า</label>
            <div class="product-status-wrapper">
                
                <input type="radio" class="btn-check" name="product_status" id="status_instock" value="1" @if($result->status == '1') checked="checked" @endif>
                <label class="status-btn success-btn" for="status_instock">
                    <i class="fas fa-check-circle"></i> @lang('product.in_stock')
                </label>

                <input type="radio" class="btn-check" name="product_status" id="status_outstock" value="0" @if($result->status == '0') checked="checked" @endif>
                <label class="status-btn danger-btn" for="status_outstock">
                    <i class="fas fa-times-circle"></i> @lang('product.out_stock')
                </label>

            </div>
        </div>
    </div>
</div>


@section('footer_scripts_include')

<script>
    function Optionselectstock(value){
       if (value == 0) {
            $("#framenumstock").css("display","inline");
            @if ($result->stock == 0)
                 $("#numstock").val({{ $result->quantity}});
                $("#numstock").show();
            @else
                $("#numstock").val("1");
                $("#numstock").show();
            @endif

       } else  {
            $("#framenumstock").css("display","none");
            $("#numstock").val("9999999");
            $("#numstock").hide();
       }

    }

    function CheckMaxValue(){
        $inputoptionstock = $("#optionstock:checked").val();
        $numstock = $("#numstock").val();
        if ($inputoptionstock == 0 &&  $numstock > 100000 ) {
            $numstock = $("#numstock").val({{ $result->quantity }});
        }
    }
  
    function safeFixed(val) {
        val = parseFloat(val);
        return (isNaN(val) || !isFinite(val)) ? 0 : val;
    }

    function formatDecimal(val) {
        return safeFixed(val).toFixed(2);
    }
    $(function(){
       var unitWeight = {{$result->weight_per_unit??0}};
       var unitPrice = {{$result->unit_price}}
       $('#unitPerPrice').val(formatDecimal(unitPrice / unitWeight));

    });
   
    $(function(){
        $('#weightPerUnits, #unitPrices').keyup(function(){
           var unitWeight = formatDecimal($('#weightPerUnits').val()) || 0;
           var unitPrice = formatDecimal($('#unitPrices').val()) || 0;
           $('#unitPerPrice').val(formatDecimal(unitPrice / unitWeight));
        });

        $('#unitPerPrice').keyup(function(){
           var unitWeight = formatDecimal($('#weightPerUnits').val()) || 0;
           var unitPerPrice = formatDecimal($('#unitPerPrice').val()) || 0;
           $('#unitPrices').val(formatDecimal(unitPerPrice * unitWeight));
        });
    });
    
    $('body').on('click','#add_more_btn',function(){
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
        getWeightPerPackage({{ $result->package_id }});
        Optionselectstock({{ $result->stock }});
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
        function handleStockChange(val) {

            var inputArea = document.getElementById('stock_input_area');
            var inputField = document.getElementById('numstock');

            if (val == '0') {
                inputArea.style.display = 'block';
                inputField.style.display = 'block';
                inputField.focus();
                if(inputField.value === '' || inputField.value === '9999999') {
                    inputField.value = 1;
                    
                }
            } else {
                inputArea.style.display = 'none';
            
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            var checkedRadio = document.querySelector('input[name="optionstock"]:checked');
            
            if (checkedRadio) {
                handleStockChange(checkedRadio.value);
            } else {
                handleStockChange('1');
            }
        });

        function CheckMaxValue() {
            var input = document.getElementById('numstock');
            var max = 100000;
            var cleanVal = parseInt(input.value.replace(/,/g, '')) || 0;
            if(cleanVal > max) {
                alert('ระบุตัวเลขไม่เกิน ' + max.toLocaleString());
                input.value = max;
            }
        }
        function isNumericKey(evt) {
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            return !(charCode > 31 && (charCode < 48 || charCode > 57));
        }
    </script>
@stop