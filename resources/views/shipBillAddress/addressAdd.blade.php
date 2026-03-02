<style type="text/css">
    .show{display:block;}
    /* ป้องกันไม่ให้เลื่อนลงไม่ได้เมื่อหน้าจอเล็กกว่าข้อมูล - เปิด scroll ใน modal */
    #add-address .modal-dialog { max-height: calc(100vh - 40px); display: flex; flex-direction: column; }
    #add-address .modal-content { max-height: calc(100vh - 40px); display: flex; flex-direction: column; overflow: hidden; }
    #add-address .modal-body { max-height: calc(100vh - 180px); overflow-y: auto; -webkit-overflow-scrolling: touch; }
    .address-section-title { font-size: 1rem; font-weight: 600; margin: 20px 0 8px 0; color: #333; }
    .address-section-title:first-of-type { margin-top: 0; }
    .address-section-divider { border: none; border-top: 1px solid #ddd; margin: 0 0 16px 0; }
    /* สไตล์ส่วนพิกัดแผนที่ตามรูปที่ 2 - กล่องสีเขียวอ่อนกรอบประ */
    .coordinate-box {
        padding: 12px 16px;
        border-radius: 6px;
        border: 2px dashed #81c784;
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 48px;
    }
    .coordinate-box.selected {
        background: #e8f5e9;
    }
    .coordinate-box.not-selected {
        background: #fff3e0;
        border-color: #ffb74d;
    }
    .coordinate-box .coord-icon {
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .coordinate-box.selected .coord-icon {
        color: #2e7d32;
    }
    .coordinate-box.not-selected .coord-icon {
        color: #e65100;
    }
    .coordinate-box .coord-text {
        flex: 1;
    }
    .coordinate-box .coord-value {
        font-weight: 500;
        color: #1b5e20;
    }
</style>
<script type="text/javascript">
    var txt_addr ={
        road : "@lang('customer.road')",
        city_district : "@lang('customer.district')",
        province_state : "@lang('customer.proviance')",
        zip_code : "@lang('common.zip')"
    }; 
</script>

<div id="add-address" class="modal fade">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="font-weight-bold">@lang('customer.add_new_address')</h3>
                <span class="close far fa-times" data-dismiss="modal"></span>              
            </div> 
            <form id="addess_frm" method="Post" class="formone-size">

                {{ csrf_field() }}
                <input type="hidden" name="user_id" value="{{$user_detail->id}}">
                <input type="hidden" name="lat" id="lat" value="{{ $lat ?? '' }}">
                <input type="hidden" name="long" id="long" value="{{ $long ?? '' }}">
                @if(isset($use_smm_address) && $use_smm_address)
                <input type="hidden" name="use_smm_address" value="1">
                @endif               
                <div class="modal-body">
                    <h4 class="address-section-title">ที่อยู่จัดส่ง</h4>
                    <hr class="address-section-divider">
                    <div class="form-group">                          
                        <label>@lang('customer.location_name')<i class="red">*</i></label>
                        <input type="text" name="title" autocomplete="off">
                        <span id="error_title" class="error-msg"></span>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-6">
                            <label>@lang('common.name')<i class="red">*</i></label>
                            <input type="text" name="first_name" value="{{$user_detail->first_name}}">
                            <span id="error_first_name" class="error-msg"></span>
                        </div>
                        <div class="col-sm-6">
                            <label>@lang('common.last_name')<i class="red">*</i></label>
                            <input type="text" name="last_name" value="{{$user_detail->last_name}}" />
                            <span id="error_last_name" class="error-msg"></span>
                        </div>                      
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-6">
                            <label>@lang('common.address')<i class="red">*</i></label>
                            <input type="text" id="address" name="address" value=" " />
                            <span id="error_address" class="error-msg"></span>
                        </div>
                        <div class="col-sm-6">
                            <label>@lang('customer.road')<i class="red">*</i></label>
                            <input type="text" name="road" value=" " />
                            <span id="error_road" class="error-msg"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        
                        <div class="col-sm-6">
                            <label><span id="province_state_level">@lang('customer.proviance')</span><i class="red">*</i></label>
                            <select class="address_dd" name="province_state" id="address_dd_1" address_seq="1">
                                {!! $ship_province_str !!}
                            </select>
                            <span id="error_province_state" class="error-msg"></span>
                        </div>
                        <div class="col-sm-6">
                            <label><span id="city_district_level">@lang('customer.district')</span><i class="red">*</i></label>
                            <select class="address_dd" name="city_district" id="address_dd_2" address_seq="2">
                                <option value="">--@lang('common.select')--</option>
                            </select>
                            <span id="error_city_district" class="error-msg"></span>
                        </div>
                    </div>
                    @if(isset($use_smm_address) && $use_smm_address)
                    <div class="form-group row" id="sub_district_row">
                        <div class="col-sm-6">
                            <label>แขวง/ตำบล<i class="red">*</i></label>
                            <select class="address_dd" name="sub_district" id="address_dd_3" address_seq="3">
                                <option value="">--@lang('common.select')--</option>
                            </select>
                            <span id="error_sub_district" class="error-msg"></span>
                        </div>
                        <div class="col-sm-6">
                            <label>@lang('common.zip')<i class="red">*</i></label>
                            <input type="text" name="zip_code" id="zip_code" readonly placeholder="เลือกตำบล/แขวงเพื่อ auto-fill">
                            <span id="error_zip_code" class="error-msg"></span>
                        </div>
                    </div>
                    @else
                    <div class="form-group row">
                        <div class="col-sm-6">
                            <label>@lang('common.zip')<i class="red">*</i></label>
                            <select class="address_dd" name="zip_code" id="zip_code" address_seq="5"></select>
                            <span id="error_zip_code" class="error-msg"></span>
                        </div>
                        <div class="col-sm-6">
                            <label>@lang('common.phone_number')<i class="red">*</i></label>
                            <input type="text" name="ph_number">
                            <span id="error_ph_number" class="error-msg"></span>
                        </div>
                    </div>
                    @endif
                    @if(isset($use_smm_address) && $use_smm_address)
                    <div class="form-group row">
                        <div class="col-sm-6">
                            <label>@lang('common.phone_number')<i class="red">*</i></label>
                            <input type="text" name="ph_number">
                            <span id="error_ph_number" class="error-msg"></span>
                        </div>
                    </div>
                    @endif
                    <div class="form-group coordinate-display" id="coordinate_display">
                        <label class="coordinate-label mb-2">
                            <i class="fas fa-map-marker-alt"></i> พิกัดแผนที่
                        </label>
                        <div id="coordinate_box" class="coordinate-box not-selected">
                            <i class="coord-icon fas fa-map-marker-alt" id="coord_icon"></i>
                            <div class="coord-text">
                                <span id="coordinate_status_text">ยังไม่ได้เลือกพิกัด</span>
                                <span id="coordinate_value" class="coord-value" style="display:none;"> <span id="display_lat">-</span>, <span id="display_long">-</span></span>
                            </div>
                        </div>
                        <button type="button" class="btn btn-danger btn-sm mt-2" onclick="openMapPicker();">
                            <i class="fas fa-map-marker-alt"></i> <span id="coordinate_btn_text">เลือกตำแหน่งบนแผนที่</span>
                        </button>
                        <div id="add-address-delivery-zone-warning" class="alert alert-warning mt-2" style="display:none;">
                            <i class="fas fa-exclamation-triangle"></i> @lang('customer.outside_delivery_zone')
                        </div>
                    </div>
                    <h4 class="address-section-title">@lang('customer.tax_invoice')</h4>
                    <hr class="address-section-divider">
                    <div class="form-group" id="company_detail">
                        <div class="form-group row"> 
                            <div class="col-sm-6">                          
                                <label>@lang('customer.company_name')</label>
                                <input type="text" name="company_name" id="company_name">
                                <span id="error_company_name" class="error-msg"></span>
                            </div>
                            <div class="col-sm-6">
                                <label>@lang('customer.branch')</label>
                                <input type="text" name="branch">
                                <span id="error_branch" class="error-msg"></span>
                            </div>                                     
                        </div>
                        <div class="form-group">
                            <label>@lang('customer.tax_id')</label>
                            <input type="text" name="tax_id" id="tax_id" value="">
                            <span id="error_tax_id" class="error-msg"></span>
                        </div>
                        <div class="form-group">                          
                            <label>@lang('customer.company_address')</label>
                            <textarea name="company_address" id="company_address"></textarea>
                            <span id="error_company_address" class="error-msg"></span>
                        </div>
                        <div class="form-group">
                            <label class="chk-wrap">
                                <input type="checkbox" id="same_as_address" value="1">
                                <span class="chk-mark">@lang('customer.link_to_delivery_address')</span>
                            </label>
                        </div>
                    </div>                                                            
                    <div class="form-group">
                        <label class="chk-wrap">
                            <input type="checkbox" name="default[shipping_address]" value="1">
                            <span class="chk-mark">@lang('customer.set_as_defult_shipping_address')</span>
                        </label>
                        <label class="chk-wrap d-block mt-2">
                            <input type="checkbox" name="default[billing_address]" value="1">
                            <span class="chk-mark">@lang('customer.set_as_defult_billing_address')</span>
                        </label>
                    </div>
                    <div class="form-group text-center">
                    @if(isset($address_from) && $address_from == 'cart')
                        <input type="hidden" name="address_type" value="{{$address_type}}">
                        <button type="button" class="btn btn-danger" onclick="SubmitCartAddressForm();">@lang('common.submit')</button>
                    @else
                        <button type="button" class="btn btn-danger" onclick="SubmitAddressForm();">@lang('common.submit')</button>
                    @endif
                    </div>
                </div>
            </form>           
        </div>
    </div>
</div>

<script type="text/javascript">
    if (typeof map_picker_url === 'undefined') {
        map_picker_url = '{{ url("/user/address/map-picker") }}';
    }
    var geocode_to_address_url = "{{ route('smm.geocode.to.address') }}";
    var check_delivery_zone_url = "{{ route('smm.check.delivery.zone') }}";
    (function($){
        $('#add-address').modal('show');

        // Auto-fill รหัสไปรษณีย์เมื่อเลือกแขวง/ตำบล (ใช้ data-zip จาก option)
        $(document).off('change.smmZip', '#address_dd_3').on('change.smmZip', '#address_dd_3', function(){
            var zip = $(this).find('option:selected').attr('data-zip');
            if(zip) $('#zip_code').val(zip);
            checkDeliveryZoneByAddress();
        });

        // ตรวจสอบว่ามีพิกัดส่งมาหรือไม่
        var lat = $('#lat').val();
        var long = $('#long').val();
        
        if (lat && long) {
            setCoordinates(lat, long);
            if (typeof use_smm_address !== 'undefined' && use_smm_address && $('#address_dd_3').length) {
                autoFillAddressFromCoordinates(lat, long);
            }
            checkDeliveryZoneForAddressForm(lat, long);
        } else {
            updateCoordinateUI(false);
            $('#add-address-delivery-zone-warning').hide();
        }
    })(jQuery);

    // เรียก API แปลงพิกัดเป็นที่อยู่ แล้ว auto-fill dropdown
    function autoFillAddressFromCoordinates(lat, long) {
        var url = typeof geocode_to_address_url !== 'undefined' ? geocode_to_address_url : (typeof window.smmGeocodeUrl !== 'undefined' ? window.smmGeocodeUrl : '{{ url("/smm-geocode-to-address") }}');
        var data = { lat: lat, long: long, _token: (typeof window.Laravel !== 'undefined' && window.Laravel.csrfToken) ? window.Laravel.csrfToken : $('meta[name="csrf-token"]').attr('content') };
        var done = function(res) {
            var r = (typeof res === 'string') ? JSON.parse(res) : res;
            if (!r) return;
            if (r.status === 'success' || r.status === 'partial') {
                var $dd1 = $('#address_dd_1'), $dd2 = $('#address_dd_2'), $dd3 = $('#address_dd_3');
                if (r.province_id) $dd1.val(r.province_id);
                if (r.districts_opt_str && $dd2.length) {
                    $dd2.html(r.districts_opt_str);
                    if (r.district_id) $dd2.val(r.district_id);
                }
                if (r.sub_districts_opt_str && $dd3.length) {
                    $dd3.html(r.sub_districts_opt_str);
                    if (r.sub_district_id) $dd3.val(r.sub_district_id);
                }
                if (r.zip_code && $('#zip_code').length) $('#zip_code').val(r.zip_code);
            }
        };
        if (typeof callAjaxRequest === 'function') {
            callAjaxRequest(url, 'post', { lat: lat, long: long }, done);
        } else {
            jQuery.ajax({ url: url, type: 'POST', data: data, success: done });
        }
    }
    
    function setCoordinates(lat, long) {
        $('#lat').val(lat);
        $('#long').val(long);
        $('#display_lat').text(parseFloat(lat).toFixed(8));
        $('#display_long').text(parseFloat(long).toFixed(8));
        updateCoordinateUI(true);
        checkDeliveryZoneForAddressForm(lat, long);
    }
    
    function checkDeliveryZoneForAddressForm(lat, lng) {
        if (typeof check_delivery_zone_url === 'undefined') return;
        var $warn = $('#add-address-delivery-zone-warning');
        var $submitBtns = $('#addess_frm button[onclick*="Submit"]');
        jQuery.ajax({
            url: check_delivery_zone_url,
            type: 'POST',
            data: {
                lat: lat,
                long: lng,
                _token: (typeof window.Laravel !== 'undefined' && window.Laravel.csrfToken) ? window.Laravel.csrfToken : $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                if (res && res.in_delivery_zone === false) {
                    $warn.show();
                    $submitBtns.prop('disabled', true);
                } else {
                    $warn.hide();
                    $submitBtns.prop('disabled', false);
                }
            },
            error: function() {
                $warn.hide();
                $submitBtns.prop('disabled', false);
            }
        });
    }

    function checkDeliveryZoneByAddress() {
        if (typeof check_delivery_zone_url === 'undefined') return;
        var subId = $('#address_dd_3').val();
        var zip = $('#zip_code').val();
        if (!subId || !zip) {
            $('#add-address-delivery-zone-warning').hide();
            $('#addess_frm button[onclick*="Submit"]').prop('disabled', false);
            return;
        }
        var $warn = $('#add-address-delivery-zone-warning');
        var $submitBtns = $('#addess_frm button[onclick*="Submit"]');
        jQuery.ajax({
            url: check_delivery_zone_url,
            type: 'POST',
            data: {
                sub_district_id: subId,
                zip_code: zip,
                _token: (typeof window.Laravel !== 'undefined' && window.Laravel.csrfToken) ? window.Laravel.csrfToken : $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                if (res && res.in_delivery_zone === false) {
                    $warn.show();
                    $submitBtns.prop('disabled', true);
                } else {
                    $warn.hide();
                    $submitBtns.prop('disabled', false);
                }
            },
            error: function() {
                $warn.hide();
                $submitBtns.prop('disabled', false);
            }
        });
    }
    
    function updateCoordinateUI(hasCoordinates) {
        var $box = $('#coordinate_box');
        var $icon = $('#coord_icon');
        if (hasCoordinates) {
            $box.removeClass('not-selected').addClass('selected');
            $icon.removeClass('fa-map-marker-alt').addClass('fa-paper-plane');
            $('#coordinate_status_text').text('พิกัดถูกเลือกแล้ว');
            $('#coordinate_value').show();
            $('#coordinate_btn_text').text('แก้ไขตำแหน่ง');
        } else {
            $box.removeClass('selected').addClass('not-selected');
            $icon.removeClass('fa-paper-plane').addClass('fa-map-marker-alt');
            $('#coordinate_status_text').text('ยังไม่ได้เลือกพิกัด');
            $('#coordinate_value').hide();
            $('#coordinate_btn_text').text('เลือกตำแหน่งบนแผนที่');
        }
    }
    
    function openMapPicker() {
        if ($('#map-picker-modal').length) {
            $('#add-address').modal('hide');
            if (typeof mapPickerLat !== 'undefined') {
                var lat = $('#lat').val() || 13.9562464;
                var lng = $('#long').val() || 100.6154008;
                mapPickerLat = parseFloat(lat);
                mapPickerLong = parseFloat(lng);
                $('#selected-lat').text(mapPickerLat.toFixed(8));
                $('#selected-long').text(mapPickerLong.toFixed(8));
            }
            $('#map-picker-modal').modal('show');
        } else {
            $('#add-address').modal('hide');
            var ajax_url = (typeof map_picker_url !== 'undefined') ? map_picker_url : '/user/address/map-picker';
            var params = {call_type: 'ajax_data', lat: $('#lat').val() || 13.9562464, long: $('#long').val() || 100.6154008};
            var loadUrl = ajax_url + (ajax_url.indexOf('?') >= 0 ? '&' : '?') + $.param(params);
            $('#popupdiv').load(loadUrl, function(response, status){
                if (status === 'error') {
                    $('#add-address').modal('show');
                    if (typeof swal !== 'undefined') swal('เกิดข้อผิดพลาด', 'ไม่สามารถโหลดแผนที่ได้', 'error');
                    else alert('ไม่สามารถโหลดแผนที่ได้');
                } else if (status === 'success') {
                    $('#map-picker-modal').modal('show');
                }
            });
        }
    }
</script>