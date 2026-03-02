@extends('layouts/admin/default')

@section('title')
    @lang('admin_discount_code.create')
@stop

@section('header_styles')
    {{-- <link rel="stylesheet" type="text/css" href="{{Config('constants.admin_css_url') }}flatpickr.min.css"> --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        input.error {
            margin-top:0px;
        }
    </style>
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
        <form id="discountCodeForm"  method="post" action="{{ action('Admin\DiscountCode\DiscountCodeController@store') }}" class="form-horizontal form-bordered" novalidate="novalidate" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="header-title">
                <h1 class="title">@lang('admin_discount_code.create')</h1>
                <div class="float-right">
                    <a class="btn btn-back" href="{{ action('Admin\DiscountCode\DiscountCodeController@index') }}"><span><</span>@lang('admin_common.back')</a>
                    <button type="submit" name="submit_type" value="submit_continue" class="btn static-block-save btn-secondary" data-action="submit_continue">@lang('admin_common.save_and_continue')</button>
                    
                    <!-- <button type="button" name="submit_type" value="preview" class="btn static-block-save" style="background: #38c1ff;" data-action="preview">@lang('admin_common.priview')</button> -->                    
                    <button type="submit" name="submit_type" value="submit" class="btn btn-save static-block-save btn-success" data-action="submit">@lang('common.save')</button>
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
                                <select name="campaign">
                                    <option value=""> -- เลือก --</option>
                                    @foreach($data['campaigns']??[] as $key => $value)
                                    <option value="{{ $value->id??'' }}">{{ $value->megacampaign->name??''}} : {{ $value->name??'' }}</option>
                                    @endforeach
                                </select>
                                <p class="error" id="campaigns"></p>
                            </div>
                        </div>
                        <div class="form-group col-md-2 ">
                            <label>@lang('admin_common.status')</label>
                            <label class="button-switch mt-3">
                                <input type="checkbox" name="status" value="1" class="switch switch-orange ng-valid ng-dirty ng-valid-parse ng-touched ng-not-empty" id="autoRelated_status" checked="checked">                        
                                <span for="autoRelated_status" class="lbl-off">@lang('admin_common.off')</span>
                                <span for="autoRelated_status" class="lbl-on">@lang('admin_common.on')</span>
                            </label>
                        </div>
                        
                    </div>

                    <div class="row">
                        <div class="form-group col-md-3">
                            <label>@lang('admin_discount_code.discount_code_type') <i class="strick">*</i></label>
                            <select name="discount_code_type" 
                            data-target-toggle='[name="create_amount"],[name="random_length"]'
                            data-target-disable='[name="code"]'
                            >
                                <option value="not_selected"> -- เลือก --</option>
                                @foreach($data['discount_code_type']??[] as $key => $value)
                                <option value="{{ $value }}">{{ ucfirst($value) }}</option>
                                @endforeach
                            </select>
                            <p class="error" id="discount_code_type"></p>
                        </div>
                        <div class="input-wrapper form-group col-sm-3 col-md-3 d-none">
                            <label>จำนวนตัวอักษรสุ่ม <i class="strick">*</i></label>
                            <input type="number" name="random_length" value="4" 
                            min="4" step="1" oninput="validity.valid||(value='1');">
                            <p class="error" id="random_length"></p>
                        </div>
                        <div class="input-wrapper form-group col-sm-6 col-md-3 d-none">
                            <label>@lang('admin_discount_code.create_amount') <i class="strick">*</i></label>
                            <input type="number" name="create_amount" value="1" 
                            min="1" max="1000" step="1" oninput="validity.valid||(value=$(this).attr('max'));">
                            <p class="error" id="create_amount"></p>
                        </div>
                        {{-- <div class="input-wrapper form-group col-sm-6 col-md-3 d-none">
                            <label>@lang('admin_discount_code.random_length') <i class="strick">*</i></label>
                            <input type="number" name="random_length" value="1" 
                            min="1" step="1" oninput="validity.valid||(value='1');">
                            <p class="error" id="random_length"></p>
                        </div> --}}
                    </div>

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>@lang('admin_discount_code.code') <i class="strick">*</i></label>
                            <input type="text" name="code" value="" maxlength="12" oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '')">
                            <p class="error" id="code"></p>
                            <div class="strick small d-none" id="fill_note_custom"> * ตัวอักษร 6-12 ตัวอักษร * กรอกเฉพาะตัวอักษร A-Z 0-9 เท่านั้น</div>
                            <div class="strick small d-none" id="fill_note_prefix"> * กรณีเลือกแบบ Prefix ให้กรอกเฉพาะตัวอักษร A-Z 0-9 ตั้งแต่ 2-4 ตัวอักษรเท่านั้น </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label>@lang('admin_discount_code.name') <i class="strick">*</i></label>
                            <input type="text" name="name" value="" >
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
                                        data-target-toggle='[name="quantity"]' >                  
                            </div>
                            <div class="input-wrapper form-group d-none col">
                                <label>@lang('admin_discount_code.quantity') <i class="strick">*</i></label>
                                <input type="number" name="quantity" value="1" 
                                    min="1" step="1" max="100000" oninput="validity.valid||(value=$(this).attr('max'));">
                                <p class="error" id="quantity"></p>
                            </div>
                        </div>
                            
                        <div class="form-group col-md-6 d-flex">
                            <div class="form-group">
                                <label class="autoRelated_is_limit_per_account">@lang('admin_discount_code.is_limit_per_account')</label>
                                    <input type="checkbox" name="is_limit_per_account" value="1" 
                                        class="switch_toggle form-control" 
                                        id="autoRelated_is_limit_per_account" 
                                        data-target-toggle='[name="limit_per_account"]' >   
                            </div>

                            <div class="input-wrapper form-group d-none col">
                                <label>@lang('admin_discount_code.limit_per_account') <i class="strick">*</i></label>
                                <input type="number" name="limit_per_account" value="1" 
                                    min="1" step="1" oninput="validity.valid||(value='1');">
                                <p class="error" id="limit_per_account"></p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-sm-6 col-md-6">
                            <label>@lang('admin_discount_code.start_date') <i class="strick">*</i></label>
                            <input type="text" name="start_date" autocomplete="off" class="date-select date-picker flatpickr" value="" >
                            <p class="error" id="start_date"></p>
                        </div>
                        <div class="form-group col-sm-6 col-md-6">
                            <label>@lang('admin_discount_code.end_date') <i class="strick">*</i></label>
                            <input type="text" name="end_date" autocomplete="off" class="date-select date-picker flatpickr" value="" >
                            <p class="error" id="end_date"></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col">                    
                            <label>@lang('admin_discount_code.image')</label>
                            <div class="file-wrapper">
                                <div class="custom-img-file" style="position: relative; width: auto; display: inline-block;">
                                    <input type="file" name="file_image" class="file-upload" accept="image/*">
                                    <span class="file-img btn-default"><img src="images/file-upload.png"></span>
                                    <img class="upload-img" src="" style="height: 50px; display: none;">
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
                                <option value="{{ $value }}">{{ ucfirst($value) }}</option>
                                @endforeach
                            </select>
                            <p class="error" id="source_type"></p>
                        </div>
                    </div> --}}

                    <div class="row">
                        {{-- <div class="form-group col-sm-6 col-md-6">
                            <label>@lang('admin_discount_code.required_purchase_count')</label>
                            <input type="number" name="required_purchase_count" value=""  
                             step="1" oninput="validity.valid||(value='');">
                            <p class="error" id="required_purchase_count"></p>
                        </div> --}}
                        <div class="form-group col-sm-6 col-md-6 ">
                            <label>@lang('admin_discount_code.purchase_amount_threshold') <i class="strick">*</i></label>
                            <input type="number" name="purchase_amount_threshold" value="" 
                                min="1" step="1" max="100000" oninput="validity.valid||(value=$(this).attr('max'));" >
                            <p class="error" id="purchase_amount_threshold"></p>
                        </div>
                    </div>


                    <div class="row">
                        <div class="form-group col-sm-6 col-md-6">
                            <label>@lang('admin_discount_code.discount_target') <i class="strick">*</i></label>
                            <select name="discount_target" data-target-toggle='[name="is_free_shipping"]' >
                                @foreach($data['discount_target']??[] as $key => $value)
                                <option value="{{ $value }}">{{ $discount_target[$value] }}</option>
                                @endforeach
                            </select>
                            <p class="error" id="discount_target"></p>
                        </div>
                        
                        <div class="input-wrapper form-group col-sm-6 col-md-6">
                            <label for="is_free_shipping">@lang('admin_discount_code.is_free_shipping')</label>
                                <input type="checkbox" name="is_free_shipping" value="1" class="form-control" id="autoRelated_is_free_shipping" >    
                        </div>
                    </div>
                    <div class="row">
                        
                        <div class="form-group col-sm-6 col-md-6">
                            <label>@lang('admin_discount_code.discount_type') <i class="strick">*</i></label>
                            <select name="discount_type" data-target-toggle='[name="is_max_discount"]' >
                                @foreach($data['discount_type']??[] as $key => $value)
                                <option value="{{ $value }}">{{ $discount_type[$value] }}</option>
                                @endforeach
                            </select>
                            <p class="error" id="discount_type"></p>
                        </div>
                        
                        <div class="input-wrapper form-group col-md-6 d-none">
                            <div class="d-flex ">
                                <div class="">
                                    <label for="autoRelated_is_max_discount">@lang('admin_discount_code.is_max_discount')</label>
                                    <input type="checkbox" name="is_max_discount" value="1" 
                                        class="switch_toggle form-control" 
                                        id="autoRelated_is_max_discount" 
                                        data-target-toggle='[name="max_discount"]' > 
                                </div>

                                <div class="input-wrapper form-group d-none col">
                                    <label>@lang('admin_discount_code.max_discount') <i class="strick">*</i></label>
                                    <input type="number" name="max_discount" value="1" 
                                        min="1" step="1" max="100000" oninput="validity.valid||(value=$(this).attr('max'));" >
                                    <p class="error" id="max_discount"></p>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        
                        <div class="input-wrapper form-group col-sm-6 ">
                            <label>@lang('admin_discount_code.discount_value') <span id="discount_type_label"></span><i class="strick">*</i></label>
                            <input type="number" name="discount_value" value=""  
                                min="1" step="1" max="100000" oninput="validity.valid||(value=$(this).attr('max'));" >
                            <p class="error" id="discount_value"></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col ">
                            <label>@lang('admin_common.description')</label>
                            <textarea name="desc" id="desc" cols="30" rows="10">{{old('desc')}}</textarea>
                            <p class="error" id="desc"></p>
                        </div>
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
        
        let $el_is_free_shipping = $('[name="is_free_shipping"]');
        let $el_is_max_discount = $('[name="is_max_discount"]');
        let $el_max_discount = $('[name="max_discount"]');

        let $el_discount_value = $('[name="discount_value"]');
        let $el_discount_code_type = $('[name="discount_code_type"]');
        let $el_discount_target = $('[name="discount_target"]');
        let $el_discount_type = $('[name="discount_type"]');
        let $el_code = $('[name="code"]');
        let $elm_random_length = $('[name="random_length"]');

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
            discount_code_type : {  required: true, maxlength: 50,pattern: /^(custom|random|prefix)$/  },
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
                required: "กรุณาระบุประเภทของโค้ดส่วนลด",
                maxlength: "ประเภทของโค้ดส่วนลดต้องไม่เกิน 50 ตัวอักษร",
                pattern: "กรุณาเลือกประเภทของโค้ดส่วนลด"
            },
            code: { required: "กรุณากรอกโค้ดส่วนลด" },
            name: {
                required: "กรุณากรอกชื่อโค้ดส่วนลด",
                maxlength: "ชื่อโค้ดส่วนลดต้องไม่เกิน 255 ตัวอักษร"
            },
            start_date: {
                required: "กรุณาระบุวันเริ่มต้น",
                pattern: "รูปแบบวันที่ไม่ถูกต้อง (เช่น 2025-12-31 23:59)"
            },
            end_date: {
                required: "กรุณาระบุวันสิ้นสุด",
                pattern: "รูปแบบวันที่ไม่ถูกต้อง (เช่น 2025-12-31 23:59)"
            },
            // required_purchase_count: { number: "จำนวนครั้งที่ต้องซื้อควรเป็นตัวเลข" },
            purchase_amount_threshold: {
                required: "กรุณาระบุยอดสั่งซื้อขั้นต่ำ",
                number: "ยอดสั่งซื้อขั้นต่ำควรเป็นตัวเลข",
                min: "ยอดสั่งซื้อขั้นต่ำต้องมากกว่า 0"
            },
            discount_target: { required: "กรุณาระบุประเภทสินค้าที่ต้องการลด" },
            discount_type: { required: "กรุณาระบุรูปแบบส่วนลด" },
            discount_value: {
                required: "กรุณาระบุมูลค่าส่วนลด",
                number: "มูลค่าส่วนลดต้องเป็นตัวเลข",
                min: "มูลค่าส่วนลดต้องมากกว่า 0"
            },
            quantity: {
                required: "กรุณาระบุจำนวนคงเหลือของโค้ด",
                number: "จำนวนคงเหลือต้องเป็นตัวเลข"
            },
            limit_per_account: {
                required: "กรุณาระบุจำนวนครั้งที่อนุญาตให้ใช้ต่อบัญชี",
                number: "ค่าจำนวนครั้งต้องเป็นตัวเลข"
            },
            create_amount: {
                required: "กรุณาระบุจำนวนโค้ดที่จะสร้าง",
                number: "ค่าที่ระบุต้องเป็นตัวเลข",
                min: "จำนวนโค้ดต้องมากกว่า 1",
                max: "จำนวนโค้ดต้องไม่เกิน 1000"
            },
            random_length: {
                required: "กรุณาระบุจำนวนสุ่มตัวอักษร",
                number: "ค่าที่ระบุต้องเป็นตัวเลข",
                min: "จำนวนสุ่มตัวอักษรต้องมากกว่า 4",
                max: "จำนวนสุ่มตัวอักษรต้องไม่เกิน 8"
            }
        };

        
        startDatePicker = flatpickr($el_startDate[0], {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            time_24hr: true,
            allowInput: true,
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
                    $target.val('1');
                } else {
                    $wrapper.fadeOut().addClass('d-none');
                    $target.val('1');
                }
            }
        }
        
        const inputFormActivity = (e)=>{
            let $elm_fill_note_custom = $("#fill_note_custom");
            let $elm_fill_note_prefix = $("#fill_note_prefix");

            let value = $(e).val();
            let toggleSelector = $(e).data('target-toggle'); 
            let $toggleTarget = $(toggleSelector);
            let $toggleWrapper = $toggleTarget.closest('.input-wrapper');
            
            let disableSelector = $(e).data('target-disable'); 
            let $disableTarget = $(disableSelector);

            let selector = $(e).data('target-selector');
            let $selectorTarget = $(selector);
            let $elm_discount_type_label = $('#discount_type_label');

            if(['random','prefix','custom','not_selected'].includes(value)){
                if(value === 'random'){
                    $toggleWrapper.removeClass('d-none').fadeIn();
                    $disableTarget.attr('disabled', true);
                    $disableTarget.val('');
                    $elm_fill_note_custom.addClass('d-none');
                    $elm_fill_note_prefix.addClass('d-none');
                }else if(value === 'prefix'){
                    $toggleWrapper.removeClass('d-none').fadeIn();
                    $disableTarget.attr('disabled', false);
                    $disableTarget.attr('maxlength', digit.prefix.maxPrefix);
                    let txt = $disableTarget.val();
                    $disableTarget.val(txt.substring(0, 4));
                    $elm_fill_note_custom.addClass('d-none');
                    $elm_fill_note_prefix.removeClass('d-none');
                }else if(value === 'custom'){
                    $toggleWrapper.fadeOut().addClass('d-none');
                    $disableTarget.attr('disabled', false);
                    $disableTarget.attr('maxlength', digit.custom.max);
                    $elm_fill_note_custom.removeClass('d-none');
                    $elm_fill_note_prefix.addClass('d-none');
                }else{
                    $toggleWrapper.fadeOut().addClass('d-none');
                    $disableTarget.attr('disabled', false);
                    $disableTarget.attr('maxlength', digit.custom.max);
                    $elm_fill_note_custom.addClass('d-none');
                    $elm_fill_note_prefix.addClass('d-none');
                }
            }
            
            else if(['purchase','shipping'].includes(value)){
                if(value === 'purchase'){
                    $toggleWrapper.removeClass('d-none').fadeIn();
                    $toggleTarget.prop('checked', false);
                }else if(value === 'shipping'){
                    $toggleWrapper.addClass('d-none').fadeIn();
                }
            }
            else if(['fixed','percentage'].includes(value)){

                let selectedText = $(e).find('option:selected').text();
                $elm_discount_type_label.text("("+selectedText+")");

                if(value === 'percentage'){
                    $toggleWrapper.removeClass('d-none').fadeIn();
                    $el_discount_value.val(1).attr('max',100).rules('remove');
                    $el_discount_value.rules('add', {
                        required: true, number: true, min: 1, max: 100,
                        messages: { max: "ส่วนลดแบบเปอร์เซ็นต์ต้องไม่เกิน 100" }
                    });
                }else{
                    $toggleTarget.trigger('change');
                    $toggleWrapper.fadeOut().addClass('d-none');
                    $el_discount_value.val(1).attr('max',10000).rules('remove');
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

        $(document).ready(function(){
            window.onload = function () {
                inputFormActivity($el_discount_code_type);
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
            $el_discount_code_type.change(function(){
                inputFormActivity(this);
                if ($('#discountCodeForm').data('validator')) {
                    let value = $(this).val();
                    let maxlength = digit.prefix.max;
                    $el_code.rules('remove');
                    if (value === 'custom') {
                        $el_code.rules('add', codeCustomRules.code);
                    } else if (value === 'prefix') {
                        $el_code.rules('add', codePrefixRules.code);
                        $elm_random_length.rules('add', {
                            required: true, number: true, min: 4, max: digit.prefix.max
                        });
                        $el_code.on('keyup.code', function () {
                            let val = $(this).val();
                            let maxRandomLength = maxlength - val.length;
                            if (val.length - digit.prefix.maxPrefix) {
                                $elm_random_length.rules('add', {
                                    required: true, number: true, min: 4, max: maxRandomLength
                                });
                                $elm_random_length.val(4);
                                $elm_random_length.valid();

                            }
                            
                        });
                    } else if (value === 'random') {
                        $elm_random_length.rules('remove');
                        $elm_random_length.rules('add', {
                            required: true, 
                            number: true, 
                            min: 6, 
                            max: digit.random.max,
                            messages: {
                                min: "ต้องไม่น้อยกว่า 6 หลัก",
                                max: "ต้องไม่เกิน " + digit.random.max + " หลัก"
                            }
                        });
                        $elm_random_length.val(6);
                        $elm_random_length.valid();
                    }else{
                        $el_code.rules('remove');
                    }
                    // $el_code.valid();
                }
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
