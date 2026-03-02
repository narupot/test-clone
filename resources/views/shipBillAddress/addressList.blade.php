@extends('layouts.app')

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/myaccount', 'css/jquery-editable-select.min'], 'css') !!}
    <style>
        .map-picker-modal.modal { z-index: 1060 !important; }
        /* Autocomplete dropdown ต้องอยู่เหนือ modal */
        .pac-container { z-index: 10610 !important; }
        /* ป้องกันไม่ให้เลื่อนลงไม่ได้เมื่อหน้าจอเล็ก - เปิด scroll ใน modal แผนที่ */
        #map-picker-modal .modal-dialog { max-height: calc(100vh - 40px); display: flex; flex-direction: column; }
        #map-picker-modal .modal-content { max-height: calc(100vh - 40px); display: flex; flex-direction: column; overflow: hidden; }
        #map-picker-modal .modal-body { max-height: calc(100vh - 140px); overflow-y: auto; -webkit-overflow-scrolling: touch; }
    </style>
@stop

@section('header_script')
    var create_address_url = "{{ url('/user/address/create') }}";
    var store_address_url = "{{action('User\UserController@store')}}";
    var delete_url = "{{action('User\UserController@delete')}}";
    var sort_address_url = "{{action('User\UserController@updateSequence')}}";
    var set_default_address_url = "{{action('User\UserController@setDefaultAddress')}}";
    var address_dd_url = "{{ (isset($use_smm_address) && $use_smm_address) ? route('smm.address.dd') : action('AjaxController@getStateCityDD') }}";
    var use_smm_address = {{ (isset($use_smm_address) && $use_smm_address) ? 'true' : 'false' }};
    var map_picker_url = "{{ route('user.address.map-picker') }}";
    var check_delivery_zone_url = "{{ route('smm.check.delivery.zone') }}";

    var lang_json = {
        "ok":"@lang('common.ok')", 
        "success":"@lang('common.success')", 
        "are_you_sure_to_delete_this_record":"@lang('common.are_you_sure_to_delete_this_record')", 
        "yes_delete_it":"@lang('common.yes_delete_it')", "deleted":"@lang('common.deleted')", 
        "records_deleted_successfully":"@lang('common.records_deleted_successfully')", "records_updated_successfully":"@lang('common.records_updated_successfully')",
        "order_updated_successfully":"@lang('customer.order_updated_successfully')",
        "are_you_sure_to_set_it_as_default":"@lang('customer.are_you_sure_to_set_it_as_default')",
        "are_you_sure_shipping":@json(__('customer.are_you_sure_shipping')),
        "are_you_sure_to_billing":@json(__('customer.are_you_sure_to_billing')),
    };
@stop

@section('breadcrumbs')

@stop

@section('content')

<div class="address-wrap kanban-demo">
    <h1 class="page-title">@lang('customer.shipping_billing_addresses')</h1>                 
    <div class="row" @if(empty($shipping_add) && empty($billing_add)) style="display: none;" @endif>
    @if(!empty($shipping_add))
        <div class="col-sm-6">                          
            <ul class="address-row">
                <li>
                    <h4>@lang('customer.default_shipping_address')</h4>
                    <p>{{$shipping_add['0']->title}}</p>
                    <p>{{$shipping_add['0']->first_name.' '.$shipping_add['0']->last_name}}</p>
                    <p>{{$shipping_add['0']->address.', '.$shipping_add['0']->road}}</p>
                    <p>{{ implode(', ', array_filter([$shipping_add['0']->sub_district ?? null, $shipping_add['0']->city_district ?? null, $shipping_add['0']->province_state ?? null])) }} {{ $shipping_add['0']->zip_code ?? '' }}</p>
                    <p>@lang('customer.tel'). {{$shipping_add['0']->ph_number}}</p>
                    <span class="action-wrap">
                        <a href="javascript:void(0);" onclick="deleteAddress({{ $shipping_add['0']->id }});"><i class="fas fa-times"></i></a>
                        <a href="javascript:void(0);" onclick="callForAjax('{{ action('User\UserController@edit',$shipping_add['0']->id ) }}', 'popupdiv')"><i class="fas fa-edit"></i></a>
                    </span> 
                </li>
            </ul>
        </div>
    @endif    
    @if(!empty($billing_add))
        <div class="col-sm-6">
            <ul class="address-row">
                <li>
                    <h4>@lang('customer.default_billing_address')</h4>
                    <p>{{$billing_add['0']->title}}</p>
                    <p>{{$billing_add['0']->first_name.' '.$billing_add['0']->last_name}}</p>
                    <p>{{$billing_add['0']->address.', '.$billing_add['0']->road}}</p>
                    <p>{{ implode(', ', array_filter([$billing_add['0']->sub_district ?? null, $billing_add['0']->city_district ?? null, $billing_add['0']->province_state ?? null])) }} {{ $billing_add['0']->zip_code ?? '' }}</p>
                    <p>@lang('customer.tel'). {{$billing_add['0']->ph_number}}</p>
                    <span class="action-wrap">
                        <a href="javascript:void(0);" onclick="deleteAddress({{ $billing_add['0']->id }});"><i class="fas fa-times"></i></a>
                        <a href="javascript:void(0);" onclick="callForAjax('{{ action('User\UserController@edit',$billing_add['0']->id ) }}', 'popupdiv')"><i class="fas fa-edit"></i></a>
                    </span>                             
                </li>
            </ul>
        </div>
    @endif
    </div>  
    
    <ul class="address-row" id="sortable">                        
        <li>
            <div class="add-new-address">
                <a id="add_user_address" href="javascript:void(0);"><i class="fas fa-plus"></i></a> <img id="icon_loader" src="{{ Config('constants.site_loader_url') }}site_loader_image.gif" style="display: none;"> <span>@lang('customer.add_address_button')</span>
            </div>
        </li>
        @if(!empty($all_address))
            @foreach($all_address as $address)
                <li class="board-item ui-sortable-handle" id="{{$address->id}}" data-attr="{{$address->id}}">
                    <span class="drag-bar"><i class="fas fa-bars"></i></span>
                    <p>{{$address->title}}</p>
                    <p>{{$address->first_name.' '.$address->last_name}}</p>
                    <p>{{$address->address.', '.$address->road}}</p>
                    <p>{{ implode(', ', array_filter([$address->sub_district ?? null, $address->city_district ?? null, $address->province_state ?? null])) }} {{ $address->zip_code ?? '' }}</p>
                    <p>@lang('customer.tel'). {{$address->ph_number}}</p>
                    <div class="link-wrap">
                        <a href="javascript:void(0);" onclick="setDefault('1', {{$address->id}});">@lang('customer.set_as_defult_shipping_address')</a>
                        <a href="javascript:void(0);" onclick="setDefault('2', {{$address->id}});">@lang('customer.set_as_defult_billing_address')</a>
                    </div>            
                    <span class="action-wrap">
                        <a href="javascript:void(0);" onclick="deleteAddress({{$address->id}});"><i class="fas fa-times"></i></a>
                        <a href="javascript:void(0);" onclick="callForAjax('{{action('User\UserController@edit',$address->id )}}', 'popupdiv')"><i class="fas fa-edit"></i></a>
                    </span>
                </li>
            @endforeach
        @endif                
    </ul>
</div>

{{-- Map Picker Modal (ฝังในหน้าเพื่อแสดงเป็น popup ไม่ต้องโหลดผ่าน AJAX) --}}
<div id="map-picker-modal" class="modal fade map-picker-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                <h3 style="margin: 0; flex-shrink: 0;">เลือกพิกัดบนแผนที่</h3>
                <div style="flex: 1; min-width: 200px; max-width: 350px;">
                    <input id="map-search-box" class="form-control" type="text" placeholder="ค้นหาสถานที่..." style="width: 100%;">
                </div>
                <span class="close far fa-times" data-dismiss="modal"></span>
            </div>
            <div class="modal-body">
                <div class="map-container" style="height: 500px; width: 100%; margin-bottom: 15px; position: relative; overflow: hidden; border-radius: 8px;">
                    <div id="map" style="width: 100%; height: 100%; overflow: hidden;"></div>
                </div>
                <div class="coordinate-info" style="display: flex; align-items: center; justify-content: space-between; padding: 15px; background: #f5f5f5; border-radius: 5px; margin-bottom: 15px;">
                    <div><span style="font-weight: 600;">Lat:</span> <span id="selected-lat">{{ $map_default_lat }}</span></div>
                    <div><span style="font-weight: 600;">Long:</span> <span id="selected-long">{{ $map_default_long }}</span></div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btn-current-location-map">
                        <i class="fas fa-map-marker-alt"></i> พิกัดปัจจุบัน
                    </button>
                </div>
                <div id="map-address-display" class="mt-2 mb-2" style="display:none;">
                    <div style="font-weight: 600; margin-bottom: 8px;"><i class="fas fa-map-pin"></i> ที่อยู่จาก Google</div>
                    <div id="map-address-parts" style="padding: 12px; background: #f8f9fa; border-radius: 6px; font-size: 13px; line-height: 1.9;">
                        <div><span style="color:#666; display:inline-block; width:110px;">แขวง/ตำบล</span><span id="map-addr-sub-val" style="font-weight:500;"></span></div>
                        <div><span style="color:#666; display:inline-block; width:110px;">เขต/อำเภอ</span><span id="map-addr-dist-val" style="font-weight:500;"></span></div>
                        <div><span style="color:#666; display:inline-block; width:110px;">จังหวัด</span><span id="map-addr-prov-val" style="font-weight:500;"></span></div>
                        <div><span style="color:#666; display:inline-block; width:110px;">รหัสไปรษณีย์</span><span id="map-addr-zip-val" style="font-weight:500;"></span></div>
                    </div>
                </div>
                <div id="delivery-zone-warning" class="alert alert-warning mt-2" style="display:none;">
                    <i class="fas fa-exclamation-triangle"></i> <span id="delivery-zone-warning-text">@lang('customer.outside_delivery_zone')</span>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary" id="confirm-map-location">ยืนยันตำแหน่ง</button>
                </div>
            </div>
        </div>
    </div>
</div>
        
@endsection

@section('footer_scripts')
    {!! CustomHelpers::combineCssJs(['js/jquery-ui.min', 'js/user/myaccount', 'js/user/user_address'], 'js') !!}
    <script src="https://maps.googleapis.com/maps/api/js?key={{ $google_api_key }}&libraries=places&callback=onGoogleMapsLoaded" async defer></script>
    <script>
    var mapPickerMap, mapPickerMarker;
    var mapPickerDefaultLat = {{ $map_default_lat }};
    var mapPickerDefaultLong = {{ $map_default_long }};
    var mapPickerLat = mapPickerDefaultLat;
    var mapPickerLong = mapPickerDefaultLong;
    var checkDeliveryZoneTimer = null;
    var isOutsideDeliveryZone = false;
    
    // เคลียร์ค่าทั้งหมดเมื่อเปิดแผนที่ใหม่
    function resetMapPickerValues() {
        mapPickerLat = mapPickerDefaultLat;
        mapPickerLong = mapPickerDefaultLong;
        $('#map-search-box').val('');
        $('#map-address-display').hide();
        $('#map-addr-sub-val, #map-addr-dist-val, #map-addr-prov-val, #map-addr-zip-val').text('');
        $('#delivery-zone-warning').hide();
        $('#selected-lat').text(mapPickerLat.toFixed(8));
        $('#selected-long').text(mapPickerLong.toFixed(8));
        isOutsideDeliveryZone = false;
        if (mapPickerMap && mapPickerMarker) {
            var defaultPos = { lat: mapPickerLat, lng: mapPickerLong };
            mapPickerMap.setCenter(defaultPos);
            mapPickerMarker.setPosition(defaultPos);
        }
    }
    
    function onGoogleMapsLoaded() {
        window.googleMapsReady = true;
    }
    
    function checkDeliveryZoneForMap(lat, lng) {
        if (typeof check_delivery_zone_url === 'undefined') return;
        $.ajax({
            url: check_delivery_zone_url,
            type: 'POST',
            data: {
                lat: lat,
                long: lng,
                _token: (typeof window.Laravel !== 'undefined' && window.Laravel.csrfToken) ? window.Laravel.csrfToken : ($('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val())
            },
            success: function(res) {
                var parts = res && res.address_parts ? res.address_parts : null;
                if (parts && (parts.sub_district || parts.district || parts.province || parts.zip_code)) {
                    $('#map-addr-sub-val').text(parts.sub_district || '-');
                    $('#map-addr-dist-val').text(parts.district || '-');
                    $('#map-addr-prov-val').text(parts.province || '-');
                    $('#map-addr-zip-val').text(parts.zip_code || '-');
                    $('#map-address-parts div').show();
                    $('#map-address-display').show();
                } else if (res && res.formatted_address) {
                    $('#map-addr-sub-val').text(res.formatted_address);
                    $('#map-addr-sub-val').parent().show();
                    $('#map-addr-dist-val').parent().hide();
                    $('#map-addr-prov-val').parent().hide();
                    $('#map-addr-zip-val').parent().hide();
                    $('#map-address-display').show();
                } else {
                    $('#map-address-display').hide();
                }
                if (res && res.in_delivery_zone === false) {
                    isOutsideDeliveryZone = true;
                    $('#delivery-zone-warning').show();
                    $('#confirm-map-location').prop('disabled', true);
                } else {
                    isOutsideDeliveryZone = false;
                    $('#delivery-zone-warning').hide();
                    $('#confirm-map-location').prop('disabled', false);
                }
            },
            error: function() {
                $('#map-address-display').hide();
                isOutsideDeliveryZone = false;
                $('#delivery-zone-warning').hide();
                $('#confirm-map-location').prop('disabled', false);
            }
        });
    }
    
    function debouncedCheckDeliveryZone(lat, lng) {
        if (checkDeliveryZoneTimer) clearTimeout(checkDeliveryZoneTimer);
        checkDeliveryZoneTimer = setTimeout(function() {
            checkDeliveryZoneForMap(lat, lng);
        }, 300);
    }
    
    function initMapPicker() {
        if (mapPickerMap) {
            google.maps.event.trigger(mapPickerMap, 'resize');
            mapPickerMap.setCenter({ lat: mapPickerLat, lng: mapPickerLong });
            return;
        }
        var defaultPos = { lat: mapPickerLat, lng: mapPickerLong };
        mapPickerMap = new google.maps.Map(document.getElementById('map'), {
            center: defaultPos,
            zoom: 15,
            disableDefaultUI: true,
            mapTypeControl: true,
            fullscreenControl: true,
            streetViewControl: false,
            zoomControl: false,
            panControl: false,
            scaleControl: false,
            rotateControl: false,
            gestureHandling: "greedy"
        });
        mapPickerMarker = new google.maps.Marker({
            position: defaultPos,
            map: mapPickerMap,
            draggable: true,
            animation: google.maps.Animation.DROP
        });
        mapPickerMap.addListener('click', function(e) {
            mapPickerMarker.setPosition(e.latLng);
            mapPickerLat = e.latLng.lat();
            mapPickerLong = e.latLng.lng();
            $('#selected-lat').text(mapPickerLat.toFixed(8));
            $('#selected-long').text(mapPickerLong.toFixed(8));
            debouncedCheckDeliveryZone(mapPickerLat, mapPickerLong);
        });
        mapPickerMarker.addListener('dragend', function(e) {
            mapPickerLat = e.latLng.lat();
            mapPickerLong = e.latLng.lng();
            $('#selected-lat').text(mapPickerLat.toFixed(8));
            $('#selected-long').text(mapPickerLong.toFixed(8));
            debouncedCheckDeliveryZone(mapPickerLat, mapPickerLong);
        });
        // ใช้ Autocomplete แทน SearchBox เพื่อความแม่นยำของพิกัด (จำกัดประเทศไทย)
        var defaultBounds = new google.maps.LatLngBounds(
            new google.maps.LatLng(5.6, 97.3),
            new google.maps.LatLng(20.5, 105.6)
        );
        var autocomplete = new google.maps.places.Autocomplete(
            document.getElementById('map-search-box'),
            {
                bounds: defaultBounds,
                componentRestrictions: { country: 'th' },
                types: ['geocode']
            }
        );
        autocomplete.addListener('place_changed', function() {
            var place = autocomplete.getPlace();
            var loc = place.geometry && place.geometry.location;
            if (loc) {
                var lat = (typeof loc.lat === 'function') ? loc.lat() : loc.lat;
                var lng = (typeof loc.lng === 'function') ? loc.lng() : loc.lng;
                var latLng = { lat: lat, lng: lng };
                mapPickerMap.setCenter(latLng);
                mapPickerMap.setZoom(17);
                mapPickerMarker.setPosition(latLng);
                mapPickerLat = lat;
                mapPickerLong = lng;
                $('#selected-lat').text(mapPickerLat.toFixed(8));
                $('#selected-long').text(mapPickerLong.toFixed(8));
                debouncedCheckDeliveryZone(mapPickerLat, mapPickerLong);
            }
        });
        mapPickerMap.addListener('bounds_changed', function() {
            autocomplete.setBounds(mapPickerMap.getBounds());
        });
    }
    
    function goToCurrentLocationMap() {
        var btn = document.getElementById('btn-current-location-map');
        if (!navigator.geolocation) {
            alert('เบราว์เซอร์ของคุณไม่รองรับการระบุตำแหน่ง');
            return;
        }
        if (btn) btn.disabled = true;
        navigator.geolocation.getCurrentPosition(
            function(position) {
                var userPos = { lat: position.coords.latitude, lng: position.coords.longitude };
                mapPickerMap.setCenter(userPos);
                mapPickerMap.setZoom(17);
                mapPickerMarker.setPosition(userPos);
                mapPickerLat = userPos.lat;
                mapPickerLong = userPos.lng;
                $('#selected-lat').text(mapPickerLat.toFixed(8));
                $('#selected-long').text(mapPickerLong.toFixed(8));
                debouncedCheckDeliveryZone(mapPickerLat, mapPickerLong);
                if (btn) btn.disabled = false;
            },
            function(error) {
                if (btn) btn.disabled = false;
                var msg = 'ไม่สามารถระบุตำแหน่งปัจจุบันได้';
                if (error.code === 1) msg = 'ผู้ใช้ปฏิเสธการเข้าถึงตำแหน่ง';
                else if (error.code === 2) msg = 'ไม่พบตำแหน่ง';
                else if (error.code === 3) msg = 'หมดเวลารอตำแหน่ง';
                alert(msg);
            }
        );
    }
    
    jQuery(document).ready(function($) {
        $('#add_user_address').on('click', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            $('#map-picker-modal').modal('show');
            return false;
        });
        
        $('#btn-current-location-map').on('click', goToCurrentLocationMap);
        
        $('#map-picker-modal').on('shown.bs.modal', function() {
            resetMapPickerValues();
            $('#confirm-map-location').prop('disabled', true);
            if (typeof google !== 'undefined' && google.maps) {
                initMapPicker();
                debouncedCheckDeliveryZone(mapPickerLat, mapPickerLong);
            } else {
                var checkGoogle = setInterval(function() {
                    if (typeof google !== 'undefined' && google.maps) {
                        clearInterval(checkGoogle);
                        initMapPicker();
                        debouncedCheckDeliveryZone(mapPickerLat, mapPickerLong);
                    }
                }, 100);
                setTimeout(function() { clearInterval(checkGoogle); }, 10000);
            }
        });
        $('#confirm-map-location').on('click', function() {
            if (isOutsideDeliveryZone) return;
            window.mapPickerConfirmed = true;
            $('#map-picker-modal').modal('hide');
            // ใช้ GET (route รับเฉพาะ GET) ส่ง lat/long เป็น query string
            var loadUrl = create_address_url + '?call_type=ajax_data&lat=' + encodeURIComponent(mapPickerLat) + '&long=' + encodeURIComponent(mapPickerLong);
            $('#popupdiv').load(loadUrl, function(response, status) {
                if (status === 'success' && typeof setCoordinates === 'function') {
                    setCoordinates(mapPickerLat, mapPickerLong);
                }
            });
        });
        $('#map-picker-modal').on('hidden.bs.modal', function() {
            if (!window.mapPickerConfirmed && $('#add-address').length) {
                $('#add-address').modal('show');
            }
            window.mapPickerConfirmed = false;
        });
    });
    </script>
@endsection