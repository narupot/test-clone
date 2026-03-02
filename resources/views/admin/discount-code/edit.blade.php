@extends('layouts/admin/default')

@section('title')
    @lang('admin_discount_code.create')
@stop

@section('header_styles')
    {{-- <link rel="stylesheet" type="text/css" href="{{Config('constants.admin_css_url') }}flatpickr.min.css"> --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@stop
@php
    $discount_target =['purchase'=>'ส่วนลดการซื้อ','shipping'=>'ค่าส่ง'];    
    $discount_type =['fixed'=>'บาท','percentage'=>'เปอร์เซ็น'];    
@endphp
@section('content')
    <div class="content">
        @if(Session::has('succMsg'))
            <div class="alert alert-success alert-dismissable margin5">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <strong>@lang('admin_common.success'):</strong> {{ Session::get('succMsg') }}
            </div>
        @endif 
        <form id="discountCodeForm" action="{{ action('Admin\DiscountCode\DiscountCodeController@update', $discountCodeCriteria->id) }}" method="POST" class="form-horizontal form-bordered"  enctype="multipart/form-data">
            @method('PUT')
            {{ csrf_field() }}
            <div class="header-title">
                <h1 class="title">@lang('admin_discount_code.edit')</h1>
                <div class="float-right btn-groups">
                    <div class="btn-groups">
                        <a class="btn btn-back" href="{{ action('Admin\DiscountCode\DiscountCodeController@index') }}">@lang('common.back')</a>
                        <button type="submit" class="btn btn-save btn-success">@lang('common.submit')</button>
                    </div>
                </div>
            </div>       
            <div class="content-wrap">
                <div class="breadcrumb">
                    <ul class="bredcrumb-menu">
                        {{-- {!!getBreadcrumbAdmin('product','badge')!!} --}}
                    </ul>
                </div>

                <div class="container mt-4 bg-white shadow-sm p-4 p-lg-5">
                    <div class="row justify-content-between">
                        
                        <div class="form-group col-md-6">
                            <div class="w-auto pull-right">
                                <label>@lang('admin_discount_code.campaign') <i class="strick">*</i> </label>
                                {{-- <select name="campaign">
                                    <option value=""> -- เลือก --</option>
                                    @foreach($data['campaigns']??[] as $key => $value)
                                    <option value="{{ $value['id'] }}"
                                     {{$discountCodeCriteria->campaign_id??false == $value['id'] ? 'selected' :''}} >
                                     {{ $value['name'] }}</option>
                                    @endforeach
                                </select>
                                <p class="error" id="campaigns"></p> --}}
                                <input type="text" name="campaign" value="{{ $discountCodeCriteria->campaign->megacampaign->name??''}} : {{$discountCodeCriteria->campaign->name??''}}"  class="form-control" disabled>
                            </div>
                        </div>
                        
                        <div class="form-group col-md-2 ">
                            <label>@lang('admin_common.status')</label>
                            <label class="button-switch mt-3">
                                <input type="checkbox" id="autoRelated_status" name="status" value="1" 
                                class="switch switch-orange ng-valid ng-dirty ng-valid-parse ng-touched ng-not-empty" 
                                {{($discountCodeCriteria->status??false) ? 'checked' :''}} >                    
                                <span for="autoRelated_status" class="lbl-off">@lang('admin_common.off')</span>
                                <span for="autoRelated_status" class="lbl-on">@lang('admin_common.on')</span>
                            </label>
                        </div>

                    </div>

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>@lang('admin_discount_code.discount_code_type') <i class="strick">*</i></label>
                            <input type="text" name="discount_code_type" value="{{ $discountCodeCriteria->discount_code_type??'' }}"  class="form-control" disabled>
                        </div>
                        
                        <div class="form-group col-md-6">
                            <label>@lang('admin_discount_code.name') <i class="strick">*</i></label>
                            <input type="text" name="name" value="{{$discountCodeCriteria->name??false}}" {{$can_edit?'':'disabled'}}>
                            <p class="error" id="name"></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-6 d-flex ">
                            <div class="form-group">
                                <label for="autoRelated_is_limit">@lang('admin_discount_code.is_limit')</label>
                                    <input type="checkbox" name="is_limit" value="1" 
                                        class="switch_toggle form-control" 
                                        id="autoRelated_is_limit" 
                                        data-target-toggle='[name="quantity"]' 
                                        {{($discountCodeCriteria->is_limit??false) ? 'checked' :''}}  {{$can_edit?'':'disabled'}}>                  
                            </div>
                            <div class="input-wrapper form-group {{($discountCodeCriteria->is_limit??false) ? '' :'d-none'}} col">
                                <label>@lang('admin_discount_code.quantity') <i class="strick">*</i></label>
                                <input type="number" name="quantity" value="{{$discountCodeCriteria->quantity??''}}" 
                                    min="1" step="1" max="100000" oninput="validity.valid||(value=$(this).attr('max'));" {{$can_edit?'':'disabled'}}>
                                <p class="error" id="quantity"></p>
                            </div>
                        </div>
                        <div class="form-group col-md-6 d-flex">
                            <div class="form-group">
                                <label class="autoRelated_is_limit_per_account">@lang('admin_discount_code.is_limit_per_account')</label>
                                    <input type="checkbox" name="is_limit_per_account" value="1" 
                                        class="switch_toggle form-control" 
                                        id="autoRelated_is_limit_per_account" 
                                        data-target-toggle='[name="limit_per_account"]' 
                                        {{$discountCodeCriteria->limit_per_account??false ? 'checked' :''}}  {{$can_edit?'':'disabled'}}>
                            </div>

                            <div class="input-wrapper form-group {{($discountCodeCriteria->limit_per_account??false) ? '' :'d-none'}} col">
                                <label>@lang('admin_discount_code.limit_per_account') <i class="strick">*</i></label>
                                <input type="number" name="limit_per_account" value="{{$discountCodeCriteria->limit_per_account??''}}" 
                                    min="1" step="1" oninput="validity.valid||(value='0');" {{$can_edit?'':'disabled'}}>
                                <p class="error" id="limit_per_account"></p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-sm-6 col-md-6">
                            <label>@lang('admin_discount_code.start_date') <i class="strick">*</i></label>
                            <input type="text" name="start_date" autocomplete="off" class="date-select date-picker flatpickr" 
                            value="{{ ($discountCodeCriteria->start_date??'') ? \Carbon\Carbon::parse($discountCodeCriteria->start_date)->format('Y-m-d H:i') : '' }}" {{$can_edit?'':'disabled'}}>
                            <p class="error" id="start_date"></p>
                        </div>
                        <div class="form-group col-sm-6 col-md-6">
                            <label>@lang('admin_discount_code.end_date') <i class="strick">*</i></label>
                            <input type="text" name="end_date" autocomplete="off" class="date-select date-picker flatpickr" 
                            value="{{ ($discountCodeCriteria->end_date??'') ? \Carbon\Carbon::parse($discountCodeCriteria->end_date)->format('Y-m-d H:i') : '' }}" {{$can_edit?'':'disabled'}}>
                            
                            <p class="error" id="end_date"></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col">                    
                            <label>@lang('admin_discount_code.image')</label>
                            <div class="file-wrapper">
                                <div class="custom-img-file" style="position: relative; width: auto; display: inline-block;">
                                    <input type="file" name="file_image" class="file-upload" accept="image/*" {{$can_edit?'':'disabled'}}>
                                    <span class="file-img btn-default"><img src="images/file-upload.png"></span>
                                    <img class="upload-img" src="{{$discountCodeCriteria->image??''}}" style="max-height: 150px; {{$discountCodeCriteria->image?'':'display: none;'}}">
                                </div>                                                  
                            <p class="text-mute small">รอบรับการอัพโหลดไฟล์ไม่เกิน 10MB นามสกุล jpeg,png,jpg,gif,svg,webp เท่านั้น</p>
                            </div>
                        </div>
                    </div>

                    <hr class="mb-5">

                    {{-- <div class="row">
                        <div class="form-group col-md-6 ">
                            <label>@lang('admin_discount_code.source_type') <i class="strick">*</i></label>
                            <select name="source_type">
                                @foreach($data['source_type']??[] as $key => $value)
                                <option value="{{ $value }}"
                                {{($discountCodeCriteria->source_type??false) == $value ? 'selected' :''}}    
                                >{{ ucfirst($value) }}</option>
                                @endforeach
                            </select>
                            <p class="error" id="source_type"></p>
                        </div>
                    </div> --}}

                    <div class="row">
                        {{-- <div class="form-group col-sm-6 col-md-6">
                            <label>@lang('admin_discount_code.required_purchase_count') </label>
                            <input type="number" name="required_purchase_count" value="{{$discountCodeCriteria->required_purchase_count??''}}"  
                                step="1" oninput="validity.valid||(value='');" {{$can_edit?'':'disabled'}}>
                            <p class="error" id="required_purchase_count"></p>
                        </div> --}}
                        <div class="form-group col-sm-6 col-md-6 ">
                            <label>@lang('admin_discount_code.purchase_amount_threshold') <i class="strick">*</i></label>
                            <input type="number" name="purchase_amount_threshold" value="{{$discountCodeCriteria->purchase_amount_threshold??''}}"
                                min="1" step="1" max="100000" oninput="validity.valid||(value=$(this).attr('max'));" {{$can_edit?'':'disabled'}}>
                            <p class="error" id="purchase_amount_threshold"></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-sm-6 col-md-6">
                            <label>@lang('admin_discount_code.discount_target') <i class="strick">*</i></label>
                            <select name="discount_target" data-target-toggle='[name="is_free_shipping"]' {{$can_edit?'':'disabled'}} >
                                @foreach($data['discount_target']??[] as $key => $value)
                                <option value="{{ $value }}"
                                {{($discountCodeCriteria->discount_target??false) == $value ? 'selected' :''}}    
                                >{{ $discount_target[$value] }}</option>
                                @endforeach
                            </select>
                            <p class="error" id="discount_target"></p>
                        </div>
                        <div class="input-wrapper form-group col-md-6">
                            <label for="is_free_shipping">@lang('admin_discount_code.is_free_shipping')</label>
                                <input type="checkbox" name="is_free_shipping" value="1" data-default-value="{{($discountCodeCriteria->is_free_shipping) ? '1' :'0'}}"
                                class="form-control" id="autoRelated_is_free_shipping" 
                                {{($discountCodeCriteria->is_free_shipping??false) ? 'checked':''}}  {{$can_edit?'':'disabled'}}>    
                        </div>
                        
                    </div>

                    <div class="row">
                        
                        <div class="form-group col-sm-6 col-md-6">
                            <label>@lang('admin_discount_code.discount_type') <i class="strick">*</i></label>
                            <select name="discount_type" data-target-toggle='[name="is_max_discount"]' {{$can_edit?'':'disabled'}} >
                                @foreach($data['discount_type']??[] as $key => $value)
                                <option value="{{ $value }}"
                                {{($discountCodeCriteria->discount_type??false) == $value ? 'selected' :''}}    
                                >{{ $discount_type[$value] }}</option>
                                @endforeach
                            </select>
                            <p class="error" id="discount_type"></p>
                        </div>
                        
                        <div class="input-wrapper form-group col-md-6  {{($discountCodeCriteria->max_discount??false) ?'':'d-none'}}">
                            <div class="d-flex ">
                                <div >
                                    <label for="autoRelated_is_max_discount">@lang('admin_discount_code.is_max_discount')</label>
                                        <input type="checkbox" name="is_max_discount" value="1" data-default-value="{{($discountCodeCriteria->max_discount) ? '1' :'0'}}"
                                            class="switch_toggle form-control" 
                                            id="autoRelated_is_max_discount" 
                                            data-target-toggle='[name="max_discount"]' 
                                            {{($discountCodeCriteria->max_discount) ? 'checked' :''}}  {{$can_edit?'':'disabled'}}>
                                </div>

                                <div class="input-wrapper form-group {{($discountCodeCriteria->max_discount??false) ?'':'d-none'}} col">
                                    <label>@lang('admin_discount_code.max_discount') <i class="strick">*</i></label>
                                    <input type="number" name="max_discount" value="{{$discountCodeCriteria->max_discount??''}}" 
                                        min="1" step="1" max="100000" oninput="validity.valid||(value=$(this).attr('max'));" {{$can_edit?'':'disabled'}}>
                                    <p class="error" id="max_discount"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        
                        <div class="input-wrapper form-group col-sm-6 col-md-6">
                            <label>@lang('admin_discount_code.discount_value') <span id="discount_type_label"></span> <i class="strick">*</i></label>
                            <input type="number" name="discount_value" value="{{$discountCodeCriteria->discount_value??''}}"  
                                min="1" step="1" max="100000" oninput="validity.valid||(value=$(this).attr('max'));" {{$can_edit?'':'disabled'}} >
                            <p class="error" id="discount_value"></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col ">
                            <label>@lang('admin_common.description')</label>
                            <textarea name="desc" id="desc" cols="30" rows="10">{{$discountCodeCriteria->desc??''}}</textarea>
                            <p class="error" id="desc"></p>
                        </div>
                    </div>
                </div>  

                <div class="container mt-4 bg-white shadow-sm p-4 p-lg-5">
                    <div class="d-flex justify-content-end mb-3">
                        <button id="download-btn" class="btn btn-danger btn-sm align-end" data-id="{{$discountCodeCriteria->id}}">ดาวน์โหลด CSV</button>
                    </div>
                    <div class="table table-responsive">
                        <table class="table table-borderless ">
                            <thead>
                                <tr>
                                    <th>โค้ดส่วนลด</th>
                                    <th class="text-center">คงเหลือ</th>
                                    <th class="text-center">วันที่สร้าง</th>
                                    <th class="text-center">วันที่อัปเดต</th>
                                    <th class="text-center">สถานะ</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($discountCodeCriteria->discountCode??[] as $code)
                                <tr>
                                    <td>{{ $code->code??'' }}</td>
                                    @if ($code->criteria??false)
                                    <td class="text-center">{{ $code->criteria->is_limit?($code->remaining_quantity??''):'ไม่จำกัด' }}</td>
                                    @else
                                    <td class="text-center"></td>
                                    @endif
                                    <td class="text-center"><small>{{ $code->created_at??'' }}</small></td>
                                    <td class="text-center"><small>{{ $code->updated_at??'' }}</small></td>
                                    <td><span class="badge badge-{{ $code->status?'success':'danger' }}">{{ $code->status?'Active':'In Active' }}</span></td>
                                    {{-- <td><a href="{{ action('Admin\DiscountCode\DiscountCodeController@editCode', $code->id) }}">@lang('admin_common.edit')</a></td> --}}
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>                               
            </div>
        </form>

        
    </div>
      
@stop

@section('footer_scripts')
 
<!-- begining of page level js -->
<!-- end of page level js --> 

<script src="{{ Config('constants.js_url') }}jquery.validate.min.js" type="text/javascript"></script> 
<script src="{{ Config('constants.js_url') }}flatpickr.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/additional-methods.min.js"></script>
<script type="text/javascript">
    
    (function($){
        let digit = {
            random :{ max : 8 },
            prefix :{ max : 8 , minPrefix : 2, maxPrefix: 4 },
            custom :{ min : 6 , max : 12 },
        };
        let fadeTime = 50;

        let startDatePicker, endDatePicker;
        let $el_startDate = $('input[name="start_date"]');
        let $el_endDate = $('input[name="end_date"]');
        
        let $el_is_max_discount = $('[name="is_max_discount"]');
        let $el_max_discount = $('[name="max_discount"]');

        let $el_discount_value = $('[name="discount_value"]');
        let $el_discount_code_type = $('[name="discount_code_type"]');
        let $el_discount_target = $('[name="discount_target"]');
        let $el_discount_type = $('[name="discount_type"]');
        let $el_code = $('[name="code"]');
        
        let codeCustomRules = {
            code:{  
                required: true, 
                minlength: digit.custom.min, 
                maxlength: digit.custom.max, 
                pattern: /^[A-Z0-9]+$/
            }
        }
        
        let codePrefixRules = {
            code:{  
                required: true, 
                minlength: digit.prefix.minPrefix, 
                maxlength: digit.prefix.maxPrefix, 
                pattern: /^[A-Z0-9]+$/
            }
        }

        $.validator.addMethod("minDate2HoursAgo", function(value, element) {
            if (!value) return true; 
            const inputDate = new Date(value.replace(' ', 'T')); 
            const minDate = new Date(new Date().getTime() + 2 * 60 * 60 * 1000); 
            return inputDate >= minDate;
        }, "วันที่ต้องไม่เก่ากว่า 2 ชั่วโมงที่แล้ว");

        let rules= {
            campaign :  { required: true },
            code :      {  required: false},
            name :      { required: true, maxlength: 255  },
            start_date: { required: true, pattern: /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/ ,minDate2HoursAgo: true },
            end_date:   { required: true, pattern: /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/  },

            // source_type:{ required: true, },
            // required_purchase_count:    { number: true },
            purchase_amount_threshold:  { required: true, number: true, min: 1 , max: 100000 },
            discount_target:    { required: true },
            discount_type:      { required: true },
            discount_value:     { required: true, number: true , min: 1,max:100000},
            max_discount:       { required: false, number: true, max: 100000},
            quantity:          { required: true, number: true, max: 100000 },
            limit_per_account:  { required: true, number: true, max: 100 },
            create_amount:     { required: true, number: true , min: 1 ,max: 1000 },
        };

        let messages = {
            campaign: { required: "กรุณาเลือกแคมเปญ" },
            discount_code_type: {
                required: "กรุณาเลือกประเภทของโค้ดส่วนลด",
                maxlength: "ประเภทของโค้ดส่วนลดต้องไม่เกิน 50 ตัวอักษร"
            },
            code: { required: "กรุณากรอกโค้ดส่วนลด" },
            name: {
                required: "กรุณากรอกชื่อ",
                maxlength: "ชื่อยาวเกินไม่เกิน 255 ตัวอักษร"
            },
            start_date: {
                required: "กรุณาระบุวันเริ่มต้น",
                pattern: "รูปแบบวันเริ่มต้นไม่ถูกต้อง (เช่น 2025-12-31 23:59)"
            },
            end_date: {
                required: "กรุณาระบุวันสิ้นสุด",
                pattern: "รูปแบบวันสิ้นสุดไม่ถูกต้อง (เช่น 2025-12-31 23:59)"
            },
            // required_purchase_count: { number: "จำนวนครั้งที่ต้องซื้อควรเป็นตัวเลข" },
            purchase_amount_threshold: {
                required: "กรุณาระบุยอดขั้นต่ำในการสั่งซื้อ",
                number: "ยอดขั้นต่ำควรเป็นตัวเลข",
                min: "ยอดขั้นต่ำต้องมากกว่า 0"
            },
            discount_target: { required: "กรุณาเลือกรายการที่ต้องการลด" },
            discount_type: { required: "กรุณาเลือกประเภทส่วนลด" },
            discount_value: {
                required: "กรุณาระบุจำนวนส่วนลด",
                number: "จำนวนส่วนลดควรเป็นตัวเลข",
                min: "จำนวนส่วนลดต้องมากกว่า 0"
            },
            quantity: {
                required: "กรุณาระบุจำนวนคงเหลือ",
                number: "จำนวนคงเหลือควรเป็นตัวเลข"
            },
            limit_per_account: {
                required: "กรุณาระบุจำนวนที่สามารถใช้ต่อบัญชี",
                number: "ค่าที่ใช้ต่อบัญชีควรเป็นตัวเลข"
            },
            create_amount: {
                required: "กรุณาระบุจำนวนที่จะสร้าง",
                number: "ค่าที่จะสร้างควรเป็นตัวเลข",
                min: "จำนวนที่จะสร้างต้องมากกว่า 0",
                max: "จำนวนที่จะสร้างต้องไม่เกิน 1000"
            }
        };
        
        startDatePicker = flatpickr($el_startDate[0], {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            time_24hr: true,
            allowInput: true,
            // minDate: new Date(),
            minDate: new Date(new Date().getTime() + 2 * 60 * 60 * 1000),
            onChange: function(selectedDates, dateStr,instance) {
                if (!dateStr) {
                    this.input.value = '';
                    return;
                }
                $(this.input).valid();
                if (endDatePicker) {
                    endDatePicker.set('minDate', dateStr);
                }
            }
        });

        endDatePicker = flatpickr($el_endDate[0], {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            time_24hr: true,
            allowInput: true,
            minDate: new Date(),
            onChange: function(selectedDates, dateStr) {
                if (!dateStr) {
                    this.input.value = '';
                    return;
                }
                let startDate = startDatePicker.selectedDates[0];
                let endDate = selectedDates[0];

                if (startDate && endDate && endDate <= startDate) {
                    alert("วันสิ้นสุดต้องมากกว่าวันเริ่มต้น");
                    endDatePicker.clear();
                }
            }
        });

        const switch_toggle = (e)=>{
            let isChecked = $(e).is(':checked');
            let targetSelector = $(e).data('target-toggle'); 
            let $target = $(targetSelector);
            let $wrapper = $target.closest('.input-wrapper');

            if ($target.length) {
                if (isChecked) {
                    $wrapper.removeClass('d-none').fadeIn();
                    $target.val('0');
                } else {
                    $wrapper.fadeOut().addClass('d-none');
                    $target.val('0');
                }
            }
        }

        const inputFormActivity = (e)=>{
            let value = $(e).val();
            let toggleSelector = $(e).data('target-toggle'); 
            let $toggleTarget = $(toggleSelector);
            let $toggleWrapper = $toggleTarget.closest('.input-wrapper');
            
            let disableSelector = $(e).data('target-disable'); 
            let $disableTarget = $(disableSelector);

            let selector = $(e).data('target-selector');
            let $selectorTarget = $(selector);
            let $elm_discount_type_label = $('#discount_type_label');

           if(['random','prefix','custom'].includes(value)){
                if(value === 'random'){
                    $toggleWrapper.removeClass('d-none').fadeIn();
                    $disableTarget.attr('disabled', true);
                    $disableTarget.val('');
                }else if(value === 'prefix'){
                    $toggleWrapper.removeClass('d-none').fadeIn();
                    $disableTarget.attr('disabled', false);
                    $disableTarget.attr('maxlength', digit.prefix.maxPrefix);
                    let txt = $disableTarget.val();
                    $disableTarget.val(txt.substring(0, 4));
                }else if(value === 'custom'){
                    $toggleWrapper.fadeOut().addClass('d-none');
                    $disableTarget.attr('disabled', false);
                    $disableTarget.attr('maxlength', digit.custom.max);
                }else{
                    $toggleWrapper.fadeOut().addClass('d-none');
                    $disableTarget.attr('disabled', false);
                    $disableTarget.attr('maxlength', digit.custom.max);
                }
            }
            else if(['purchase','shipping'].includes(value)){
                if(value === 'purchase'){
                    $toggleWrapper.removeClass('d-none').fadeIn();
                if(!$toggleTarget.data('default-value')){
                    $toggleTarget.prop('checked', false);
                }
                }else if(value === 'shipping'){
                    $toggleWrapper.addClass('d-none').fadeIn();
                }
            }
            else if(['fixed','percentage'].includes(value)){
                
                let selectedText = $(e).find('option:selected').text();
                $elm_discount_type_label.text("("+selectedText+")");

                if(value === 'percentage'){
                    $toggleWrapper.removeClass('d-none').fadeIn();
                    $el_discount_value.attr('max',100).rules('remove');
                    $el_discount_value.rules('add', {
                        required: true, number: true, min: 1, max: 100,
                        messages: { max: "ส่วนลดแบบเปอร์เซ็นต์ต้องไม่เกิน 100" }
                    });
                }else{
                    $toggleTarget.trigger('change');
                    $toggleWrapper.fadeOut().addClass('d-none');
                    $el_discount_value.attr('max',10000).rules('remove');
                    $el_discount_value.rules('add', {
                        required: true, number: true, min: 1, max: 10000,
                        messages: { max: "ส่วนลดแบบเปอร์เซ็นต์ต้องไม่เกิน 10000" }
                    });

                }
                if(!$toggleTarget.data('default-value')){
                    $toggleTarget.prop('checked',false).trigger('change');
                }
            }

        }

        function downloadCsv(criteriaId) {
            let url = "{{ route('admin.discount_code.exportCsv', ['criteriaId' => '__ID__']) }}";
            url = url.replace('__ID__', criteriaId);

            let a = document.createElement('a');
            a.href = url;
            a.download = ''; 
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }


        $(document).ready(function(){
            
            $('#download-btn').on('click', function () {
                let id = $(this).data('id'); 
                // console.log('id : ',id);
                
                downloadCsv(id);
            });

            window.onload = function () {
                inputFormActivity($el_discount_target);
                inputFormActivity($el_discount_type);
                $('.switch_toggle').each((e)=>{
                    switch_toggle(e);
                });
            }

            $('.switch_toggle').change(function () {
                switch_toggle(this);
            });

            $el_discount_target.change(function(){
                inputFormActivity(this);

            });
            $el_discount_type.change(function(){
                inputFormActivity(this);
            });

            $el_is_max_discount.change(function(){
                if ($('#discountCodeForm').data('validator')) {
                    $el_max_discount.rules('remove');
                    if($el_is_max_discount.prop('checked')){
                        $el_max_discount.val(1);
                        $el_max_discount.rules('add', { required: true, number: true ,min: 1 });
                    }else{
                        $el_max_discount.val('');
                        $el_max_discount.rules('add', { required: false });
                    }
                    $el_max_discount.valid();
                }
            });


            // $('[name="submit_type"]').click(function(e){
                
            

                validateForm('discountCodeForm',rules,messages);
                

            // });

            

        });


    })(jQuery);
</script>  


@stop

