<style type="text/css">
    .map-picker-modal {
        z-index: 9999;
    }
    .pac-container { z-index: 10610 !important; }
    /* ป้องกันไม่ให้เลื่อนลงไม่ได้เมื่อหน้าจอเล็ก - เปิด scroll ใน modal แผนที่ */
    #map-picker-modal .modal-dialog { max-height: calc(100vh - 40px); display: flex; flex-direction: column; }
    #map-picker-modal .modal-content { max-height: calc(100vh - 40px); display: flex; flex-direction: column; overflow: hidden; }
    #map-picker-modal .modal-body { max-height: calc(100vh - 140px); overflow-y: auto; -webkit-overflow-scrolling: touch; }
    .map-container {
        height: 500px;
        width: 100%;
        margin-bottom: 15px;
        position: relative;
        overflow: hidden;
        border-radius: 8px;
    }
    .map-picker-modal .modal-header {
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }
    .map-picker-modal .modal-header h3 {
        margin: 0;
        flex-shrink: 0;
    }
    .map-picker-modal .header-search-wrap {
        flex: 1;
        min-width: 200px;
        max-width: 350px;
    }
    .map-picker-modal .header-search-wrap .search-box {
        width: 100%;
    }
    .search-box {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
    }
    .coordinate-info {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 15px;
        background: #f5f5f5;
        border-radius: 5px;
        margin-bottom: 15px;
    }
    .coordinate-info .coord-item {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .coordinate-info .coord-label {
        font-weight: 600;
        color: #555;
    }
    .coordinate-info .coord-value {
        font-family: monospace;
        color: #000;
        font-size: 14px;
    }
    .btn-group-map {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }
</style>

<div id="map-picker-modal" class="modal fade map-picker-modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3>เลือกพิกัดบนแผนที่</h3>
                <div class="header-search-wrap">
                    <input id="search-box" class="search-box" type="text" placeholder="ค้นหาสถานที่...">
                </div>
                <span class="close far fa-times" data-dismiss="modal"></span>
            </div>
            <div class="modal-body">
                <div class="map-container">
                    <div id="map" style="width: 100%; height: 100%;"></div>
                </div>
                
                <div class="coordinate-info">
                    <div class="coord-item">
                        <span class="coord-label">Lat:</span>
                        <span class="coord-value" id="selected-lat">{{ $default_lat }}</span>
                    </div>
                    <div class="coord-item">
                        <span class="coord-label">Long:</span>
                        <span class="coord-value" id="selected-long">{{ $default_long }}</span>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="goToCurrentLocation();" id="btn-current-location">
                        <i class="fas fa-map-marker-alt"></i> พิกัดปัจจุบัน
                    </button>
                </div>
                <div id="map-address-display" class="mt-2 mb-2" style="display:none;">
                    <div style="font-weight: 600; margin-bottom: 8px;"><i class="fas fa-map-pin"></i> ที่อยู่จาก Google</div>
                    <div id="map-address-parts" style="padding: 12px; background: #f8f9fa; border-radius: 6px; font-size: 13px; line-height: 1.9;">
                        <div><span style="color:#666; display:inline-block; width:110px;">แขวง/ตำบล</span><span id="map-formatted-sub-val" style="font-weight:500;"></span></div>
                        <div><span style="color:#666; display:inline-block; width:110px;">เขต/อำเภอ</span><span id="map-formatted-dist-val" style="font-weight:500;"></span></div>
                        <div><span style="color:#666; display:inline-block; width:110px;">จังหวัด</span><span id="map-formatted-prov-val" style="font-weight:500;"></span></div>
                        <div><span style="color:#666; display:inline-block; width:110px;">รหัสไปรษณีย์</span><span id="map-formatted-zip-val" style="font-weight:500;"></span></div>
                    </div>
                </div>
                <div id="delivery-zone-warning" class="alert alert-warning mt-2" style="display:none;">
                    <i class="fas fa-exclamation-triangle"></i> {{ \Lang::get('customer.outside_delivery_zone') }}
                </div>
                <div class="btn-group-map">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary" id="map-picker-confirm-btn" onclick="confirmMapLocation();">ยืนยันตำแหน่ง</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    if (typeof check_delivery_zone_url === 'undefined') {
        check_delivery_zone_url = "{{ route('smm.check.delivery.zone') }}";
    }
    var edit_address_url = @json($edit_address_url ?? null);
    var mapPickerCheckZoneTimer = null;
    var mapPickerIsOutsideDeliveryZone = false;
    function mapPickerCheckDeliveryZone(lat, lng) {
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
                    $('#map-formatted-sub-val').text(parts.sub_district || '-');
                    $('#map-formatted-dist-val').text(parts.district || '-');
                    $('#map-formatted-prov-val').text(parts.province || '-');
                    $('#map-formatted-zip-val').text(parts.zip_code || '-');
                    $('#map-address-parts div').show();
                    $('#map-address-display').show();
                } else if (res && res.formatted_address) {
                    $('#map-formatted-sub-val').text(res.formatted_address);
                    $('#map-formatted-sub-val').parent().show();
                    $('#map-formatted-dist-val').parent().hide();
                    $('#map-formatted-prov-val').parent().hide();
                    $('#map-formatted-zip-val').parent().hide();
                    $('#map-address-display').show();
                } else {
                    $('#map-address-display').hide();
                }
                if (res && res.in_delivery_zone === false) {
                    mapPickerIsOutsideDeliveryZone = true;
                    $('#delivery-zone-warning').show();
                    $('#map-picker-confirm-btn').prop('disabled', true);
                } else {
                    mapPickerIsOutsideDeliveryZone = false;
                    $('#delivery-zone-warning').hide();
                    $('#map-picker-confirm-btn').prop('disabled', false);
                }
            },
            error: function() {
                $('#map-address-display').hide();
                mapPickerIsOutsideDeliveryZone = false;
                $('#delivery-zone-warning').hide();
                $('#map-picker-confirm-btn').prop('disabled', false);
            }
        });
    }
    function mapPickerDebouncedCheck(lat, lng) {
        if (mapPickerCheckZoneTimer) clearTimeout(mapPickerCheckZoneTimer);
        mapPickerCheckZoneTimer = setTimeout(function() { mapPickerCheckDeliveryZone(lat, lng); }, 300);
    }
    let map;
    let marker;
    let selectedLat = {{ $default_lat }};
    let selectedLong = {{ $default_long }};

    function initMap() {
        // สร้างแผนที่
        const defaultPosition = { lat: selectedLat, lng: selectedLong };
        
        map = new google.maps.Map(document.getElementById('map'), {
            center: defaultPosition,
            zoom: 15,
            mapTypeControl: true,
            fullscreenControl: true,
            streetViewControl: false,
            zoomControl: false,
            panControl: false,
            scaleControl: false,
            rotateControl: false,
            gestureHandling: "greedy"
        });

        // วาง marker เริ่มต้น
        marker = new google.maps.Marker({
            position: defaultPosition,
            map: map,
            draggable: true,
            animation: google.maps.Animation.DROP
        });

        // เมื่อคลิกบนแผนที่
        map.addListener('click', function(event) {
            updateMarkerPosition(event.latLng);
        });

        // เมื่อลาก marker
        marker.addListener('dragend', function(event) {
            updateMarkerPosition(event.latLng);
        });

        // ใช้ Autocomplete แทน SearchBox เพื่อความแม่นยำของพิกัด (จำกัดประเทศไทย)
        const defaultBounds = new google.maps.LatLngBounds(
            new google.maps.LatLng(5.6, 97.3),
            new google.maps.LatLng(20.5, 105.6)
        );
        const autocomplete = new google.maps.places.Autocomplete(
            document.getElementById('search-box'),
            {
                bounds: defaultBounds,
                componentRestrictions: { country: 'th' },
                types: ['geocode']
            }
        );
        autocomplete.addListener('place_changed', function() {
            const place = autocomplete.getPlace();
            const loc = place.geometry && place.geometry.location;
            if (loc) {
                const lat = (typeof loc.lat === 'function') ? loc.lat() : loc.lat;
                const lng = (typeof loc.lng === 'function') ? loc.lng() : loc.lng;
                const latLng = new google.maps.LatLng(lat, lng);
                map.setCenter(latLng);
                map.setZoom(17);
                updateMarkerPosition(latLng);
            }
        });
        map.addListener('bounds_changed', function() {
            autocomplete.setBounds(map.getBounds());
        });

        mapPickerDebouncedCheck(selectedLat, selectedLong);
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const userPos = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                if (selectedLat === {{ $default_lat }} && selectedLong === {{ $default_long }}) {
                    map.setCenter(userPos);
                    updateMarkerPosition(userPos);
                }
            });
        }
    }

    function updateMarkerPosition(latLng) {
        marker.setPosition(latLng);
        selectedLat = latLng.lat();
        selectedLong = latLng.lng();
        document.getElementById('selected-lat').textContent = selectedLat.toFixed(8);
        document.getElementById('selected-long').textContent = selectedLong.toFixed(8);
        mapPickerDebouncedCheck(selectedLat, selectedLong);
    }

    function goToCurrentLocation() {
        var btn = document.getElementById('btn-current-location');
        if (!navigator.geolocation) {
            alert('เบราว์เซอร์ของคุณไม่รองรับการระบุตำแหน่ง');
            return;
        }
        if (btn) btn.disabled = true;
        navigator.geolocation.getCurrentPosition(
            function(position) {
                var userPos = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                map.setCenter(userPos);
                map.setZoom(17);
                updateMarkerPosition(userPos);
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

    function confirmMapLocation() {
        if (mapPickerIsOutsideDeliveryZone) return;
        $('#map-picker-modal').modal('hide');
        
        // เรียกฟังก์ชันเพื่อโหลดฟอร์ม address พร้อมส่งพิกัด
        loadAddressFormWithCoordinates(selectedLat, selectedLong);
    }

    function loadAddressFormWithCoordinates(lat, long) {
        var editUrl = typeof edit_address_url !== 'undefined' ? edit_address_url : null;
        var ajax_url = editUrl || (typeof create_address_url !== 'undefined' ? create_address_url : '/user/address/create');
        // ใช้ GET (route รับเฉพาะ GET) ส่ง params เป็น query string
        var params = {call_type: 'ajax_data', lat: lat, long: long};
        var loadUrl = ajax_url + (ajax_url.indexOf('?') >= 0 ? '&' : '?') + $.param(params);
        $('#popupdiv').load(loadUrl, function(response, status){
            if (typeof setCoordinates === 'function') {
                setCoordinates(lat, long);
            }
        });
    }

    (function($){
        $('#map-picker-modal').modal('show');
        $('#map-picker-confirm-btn').prop('disabled', true);
        $('#map-picker-modal').on('shown.bs.modal', function() {
            $('#delivery-zone-warning').hide();
            $('#map-picker-confirm-btn').prop('disabled', true);
            if (typeof map !== 'undefined' && map) {
                mapPickerDebouncedCheck(selectedLat, selectedLong);
            }
        });
    })(jQuery);
</script>

<script src="https://maps.googleapis.com/maps/api/js?key={{ $google_api_key }}&libraries=places&callback=initMap" async defer></script>
