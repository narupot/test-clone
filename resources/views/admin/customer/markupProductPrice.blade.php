@extends('layouts/admin/default')

@section('title')
    @lang('Markup Price')
@stop

@section('header_styles')    
    <link rel="stylesheet" type="text/css" href="{{ Config('constants.admin_css_url') }}order.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
      
    .select2-container--bootstrap-5 .select2-dropdown {
    --select2-dropdown-height: 300px !important; /* ปรับความสูงที่นี่ */
        }
        .old-markup-column {
        /* สไตล์ปกติ */
    }
    
    @media (max-width: 768px) {
        .old-markup-column {
            /* สไตล์สำหรับมือถือถ้าจำเป็น */
        }
    }
</style>


@stop

@section('content')
<div class="content">
    <div class="header-title">
        <h1 class="title">@lang('Markup Configuration')</h1>
    </div>
         
    <!-- Main content -->         
    <div class="content-wrap">
        <div class="col-md-12 pr-0"></div>
        <br>
        
        <div class="row" id="markets_markup">
            <div class="col-sm-3">
                <h3 class="status-heading">@lang('ตลาด') : </h3>
                <select id="dd_markets" name="dd_markets" style="width: 100%;" class="mr-2 dropdown-height">
                    <option value="">กรุณาเลือก</option>
                    @foreach($markets_data as $markets_d) 
                        <option value="{{$markets_d->market_code}}">{{$markets_d->market_name}}</option>
                    @endforeach                                                                           
                </select>
            </div>
            <!--div class="col-sm-4">
                <h3 class="status-heading">@lang('ร้านค้า') : </h3>
                <select id="store-search" class="form-control mr-2" style="width: 100%;">
                    <option value="">-- เลือกร้านค้า --</option>
                </select>
            </div-->
            <div class="col-sm-4">
                <h3 class="status-heading">ร้านค้า : </h3>
                <select id="store-search" class="form-select" style="width: 100%;">
                    <option value="">-- เลือกร้านค้า --</option>
                </select>
            </div>
            <div class="col">
                <h3 class="status-heading">@lang('Markup %') : </h3>
                <input type="text" id="percent_markup" placeholder="0%" >
            </div>
            <div class="col-sm-2">
                <h3 class="status-heading">@lang('วันที่เริ่มต้น') : </h3>
                <input value="{{ date('d/m/Y', strtotime('+1 day')) }}" name="effective_date" type="text" class="form-control datepicker" id="effective_date" autocomplete="off"> 
            </div>
            <div class="col-sm-2">
                <h3>&nbsp; &nbsp;</h3>
                <button type="button" class="btn btn-primary" id="btn-markupPrice">@lang('admin_common.save')</button>
            </div>
        </div>
        <br>

        <div class="table">
            <div class="table-header">
                <ul>
                    <li></li>
                    <li>ชื่อร้าน</li>
                    <li>เลขที่แผง</li>
                    <li class="old-markup-header">Markup % ปัจจุบัน</li>
                    <li class="new-markup-header">Markup %</li>
                    <li>วันที่เริ่มต้น</li>
                    <li>ผู้บันทึก</li>
                    <li>วันที่บันทึก</li>
                </ul>
            </div>
            <div class="table-content" id="shopTable">
                <!-- ข้อมูลจะถูกเพิ่มที่นี่ผ่าน JavaScript -->
            </div>
        </div>            
    </div>
</div>
@stop

@section('footer_scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script type="text/javascript">
    $('#store-search').css('height', '40px');
    var shopDataUrl = "{{action('Admin\Customer\SellerController@getShopData')}}";
    var getShopInMarketURL = "{{action('Admin\Customer\SellerController@getMarkupPriceByMC')}}";
    var saveMarkupUrl = "{{action('Admin\Customer\SellerController@saveMarkupPrice')}}";

    $(document).ready(function() {
    var shopDataUrl = "{{action('Admin\Customer\SellerController@getShopData')}}";

    $(".datepicker").datepicker({
            dateFormat: 'dd/mm/yy', // รูปแบบวันที่
            changeMonth: true, // สามารถเลือกเดือนได้
            changeYear: true, // สามารถเลือกปีได้
            yearRange: '2020:+5', // ช่วงปีที่สามารถเลือกได้
            showButtonPanel: true, // แสดงปุ่ม panel
            currentText: 'วันนี้', // ข้อความปุ่มวันนี้
            closeText: 'ปิด', // ข้อความปุ่มปิด
            dayNames: ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'],
            dayNamesMin: ['อา.', 'จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.'],
            monthNames: ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'],
            monthNamesShort: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.']
        });
    
    
    $('#dd_markets').on('change', function() {
        var marketCode = $(this).val();
        $('#store-search').val(null).trigger('change'); // Clear the store dropdown
        fetchStoreData(marketCode); // Fetch and display store data
    });

    /*
    $('#store-search').select2({
        placeholder: '-- เลือกร้านค้า --',
        */
    $('#store-search').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#store-search').parent(),
  
        ajax: {
            url: shopDataUrl,
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term, // User input
                    market: $('#dd_markets').val() // Market filter
                };
            },
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return {
                            id: item.id,
                            text: item.shop_name
                        };
                    })
                };
            },
            cache: true
        },
        minimumInputLength: 1
    });

    function fetchStoreData(marketCode) {
    if (!marketCode) {
        $('#shopTable').html(''); // Clear the table if no market is selected
        return;
    }
    
    $.ajax({
        url: getShopInMarketURL,
        dataType: 'json',
        data: { market: marketCode },
        success: function(data) {
            var tableContent = '';
            var currentDate = new Date();
            currentDate.setHours(0, 0, 0, 0);
            var hasFutureDate = false;
            
            // Check if any shop has future effective date
            data.forEach(function(shops) {
                var effectiveDate = new Date(shops.effective_date);
                if (effectiveDate < currentDate) {
                    hasFutureDate = true;
                }
            });
            
            // Update header labels based on date
            if (hasFutureDate) {
                $('.table-header li.old-markup-header').text('Markup % เก่า');
                $('.table-header li:nth-child(5)').text('Markup % ปัจจุบัน');
            } else {
                $('.table-header li.old-markup-header').text('Markup % ปัจจุบัน');
                $('.table-header li:nth-child(5)').text('Markup % ใหม่');
            }
            
            // Build table content
            data.forEach(function(shops,index) {
                tableContent += '<ul>';
                tableContent += '<li>' + (index + 1) + '</li>';
                tableContent += '<li>' + shops.shop_name + '</li>';
                tableContent += '<li>' + shops.panel_no + '</li>';
                tableContent += '<li>' + shops.old_markup_price + '%</li>';
                tableContent += '<li>' + shops.percent_markup_product_price + '%</li>';
                tableContent += '<li>' + shops.effective_date + '</li>';
                tableContent += '<li>' + shops.updated_by + '</li>';
                tableContent += '<li>' + shops.updated_date + '</li>';
                tableContent += '</ul>';
            });
            
            $('#shopTable').html(tableContent);
        },
        error: function() {
            $('#shopTable').html('<div class="table-row"><div class="table-cell">Error fetching data</div></div>');
        }
    });
}

    $('#btn-markupPrice').on('click', function() {
        var marketCode = $('#dd_markets').val();
        var shopId = $('#store-search').val();
        var markupPercent = $('#percent_markup').val();
        var effectiveDate = $('#effective_date').val();

            // Validate data
        if (!marketCode) {
            alert('กรุณาเลือกตลาด');
            return;
        }
        /*if (!shopId) {
            alert('กรุณาเลือกร้านค้า');
            return;
        }*/
        if (!markupPercent || isNaN(markupPercent)) {
            alert('กรุณากรอก Markup % ให้ถูกต้อง');
            return;
        }
        if (!effectiveDate) {
            alert('กรุณาเลือกวันที่เริ่มต้น');
            return;
        }

        // Convert date format to YYYY-MM-DD for database
        var dateParts = effectiveDate.split('/');
        var dbDate = dateParts[2] + '-' + dateParts[1] + '-' + dateParts[0];

        // Send data to server
        $.ajax({
            url: saveMarkupUrl,
            type: 'POST',
            data: {
                market_code: marketCode,
                shop_id: shopId,
                markup_percent: markupPercent,
                effective_date: dbDate,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    alert('บันทึกข้อมูลสำเร็จ');
                    // Refresh the table
                    fetchStoreData(marketCode);
                } else {
                    alert('เกิดข้อผิดพลาด: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์ ' + marketCode + ' ' + shopId + ' ' + xhr.responseText);
                console.error(xhr.responseText);
            }
        });
    });
});

</script>
@stop