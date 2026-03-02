@extends('layouts/admin/default')

@php
    $title_page = 'Export Shipping Address';
@endphp

@section('title')
    {{$title_page}}
@stop

@section('header_styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        .select2-container--default .select2-search--inline .select2-search__field {
            min-height: auto;
        }
        .select2-selection__choice {
            color: dimgray;
            font-size: small;
            border: none !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            border: 0px;
            margin-top: 1px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            padding-left: 15px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__clear {
            margin-top: 3px;
        }
        

    .calendar-input {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' fill='none' stroke='%23333' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='14' height='13' rx='2' ry='2'/%3E%3Cline x1='8' y1='2.5' x2='8' y2='6'/%3E%3Cline x1='12' y1='2.5' x2='12' y2='6'/%3E%3Cline x1='3' y1='9' x2='17' y2='9'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 18px;
    padding-right: 40px; /* กัน text ชน icon */
    cursor: pointer;
}

.calendar-input:focus {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' fill='none' stroke='%230d6efd' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='14' height='13' rx='2' ry='2'/%3E%3Cline x1='8' y1='2.5' x2='8' y2='6'/%3E%3Cline x1='12' y1='2.5' x2='12' y2='6'/%3E%3Cline x1='3' y1='9' x2='17' y2='9'/%3E%3C/svg%3E");
}

    </style>
@stop

@section('content')
    <div class="content">
        <div class="header-title">
            <h1 class="title">{{ $title_page }}</h1>
        </div>

        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu"></ul>
            </div>

            <div class="tab-content listing-tab">
                <div class="card shadow-sm p-3 p-lg-4 mb-4">
                    <form id="searchForm" name="searchForm" method="get">
                    <div class="row">
                        <div class="col-md-2">
                            <label for="search_type" class="form-label">ค้นหาจาก</label>
                            <select id="search_type" name="search_type" class="form-control">
                                <option value="create_date">วันที่สร้าง</option>
                                <option value="buyer_name">ชื่อผู้ซื้อสินค้า</option>
                            </select>
                        </div>
                       
                        <div class="col-md-3" id="date_filter">
                            <label for="create_date" class="form-label">เลือกวันที่</label>
                            <input type="text" class="form-control calendar-input" id="create_date" name="create_date" value="{{ $_REQUEST['create_date'] ?? now()->format('Y-m-d') }}" autocomplete="off">
                        </div>

                        <div class="col-md-3" style="display: none;">
                            <label for="buyer_name" class="form-label">ชื่อผู้ซื้อสินค้า</label>
                            <input type="text" class="form-control" id="buyer_name" placeholder="กรุณากรอกข้อมูลชื่อผู้รับสินค้า" name="buyer_name">
                        </div>

                        <div class="col-md-4">
                            <br>
                            <button type="submit" class="btn btn-primary" id="btnSearch">ค้นหา</button>
                            <button type="button" class="btn btn-warning" id="btnReset">ล้างข้อมูล</button>&nbsp;
                            <button type="button" class="btn btn-primary" id="btnExport">Export</button>
                        </div>
                    </div>
                    </form>
                </div>

                <div class="table-responsive card p-3 p-lg-4 mb-4">
                    <table class="table table-bordered" id="table">
                        <thead>
                            <tr class="filters text-center">
                                <th>ลำดับ</th>
                                <th>เลขที่ผู้รับสินค้า</th>
                                <th>ชื่อผู้ซื้อสินค้า</th>
                                <th>ที่อยู่</th>
                                <th>ตำบล / อำเภอ / จังหวัด / รหัสไปรษณีย์</th>
                                <th>เบอร์ติดต่อ</th>
                                <th>Email</th>
                                <th>สถานะ</th>
                                <th>Last Update</th>
                            </tr>
                        </thead>
                        <tbody class="small" id="show_list">
                        @include('admin.transaction._tableRowsShippingAddress', ['data' => $data])
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('footer_scripts')
    <script src="{{ Config('constants.js_url') }}flatpickr.min.js"></script>
    <script>
        $(document).ready(function() {


let searchTimeout;
let fp = flatpickr("#create_date", {
    dateFormat: "Y-m-d",
    allowInput: true,
    onChange: function (selectedDates, dateStr) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            $('#searchForm').submit();
        }, 300);
    }
});

// คลิก icon = เปิดปฏิทิน
$('#calendar-icon').on('click', function () {
    fp.open();
});


    // 🔍 SEARCH (กด Enter หรือกดปุ่ม)
    $('#searchForm').on('submit', function (e) {
        e.preventDefault(); // กัน reload

        let search_type = $('#search_type').val();
        let data_val = '';

        if (search_type === 'create_date') {
            data_val = $('#create_date').val();
            if (!data_val) {
                alert('กรุณาเลือกวันที่');
                return;
            }
        } else {
            data_val = $('#buyer_name').val();
            if (!data_val) {
                alert('กรุณากรอกชื่อผู้ซื้อสินค้า');
                return;
            }
        }

        $('#show_list').html('<tr><td colspan="9" class="text-center">กำลังโหลดข้อมูล...</td></tr>');

        $.ajax({
            url: '{{ action("Admin\\Transaction\\ExportShippingController@listdata") }}',
            type: 'GET',
            data: {
                search_type: search_type,
                data_val: data_val
            },
            success: function (res) {
                $('#show_list').html(res);
            },
            error: function (xhr) {
                alert('เกิดข้อผิดพลาด');
                console.log(xhr.responseText);
            }
        });
    });

    
            // Reset Filter
            $('#btnReset').click(function () {
                $('#create_date').val('{{ now()->format("Y-m-d") }}');
                $('#buyer_name').val('');
                $('#date_filter').show();
                $('#buyer_name').parent().hide();
                $('#search_type').val('create_date');

                let data_val = $('#create_date').val();
                let search_type = $('#search_type').val();

                $('#show_list').html('<tr><td colspan="9" class="text-center">กำลังโหลดข้อมูล...</td></tr>');

                $.ajax({
            url: '{{ action("Admin\\Transaction\\ExportShippingController@listdata") }}',
            type: 'GET',
            data: {
                search_type: search_type,
                data_val: data_val
            },
            success: function (res) {
                $('#show_list').html(res);
            },
            error: function (xhr) {
                alert('เกิดข้อผิดพลาด');
                console.log(xhr.responseText);
            }
        });
            });

            // Export Excel
            $('#btnExport').click(function () {
                let create_date = $('#create_date').val();
                let buyer_name = $('#buyer_name').val();
                let search_type = $('#search_type').val();
                let data_val = '';

                if (search_type === 'create_date') {
                    data_val = '&data_val=' + encodeURIComponent(create_date);
                } else {
                    if (!buyer_name) {
                        alert('กรุณากรอกข้อมูลชื่อผู้รับสินค้า');
                        return false;
                    }
                    data_val = '&data_val=' + encodeURIComponent(buyer_name);
                }

                let url = '{{ route("eexport.excel") }}?search_type=' + encodeURIComponent(search_type) + data_val;
                window.open(url, '_blank');
            });

    $('#search_type').on('change', function () {
    let search_type = $(this).val();

    if (search_type === 'create_date') {
        // แสดงช่องวันที่
        $('#date_filter').show();
        $('#buyer_name').val('').parent().hide();

        // ตั้งวันที่เป็นวันนี้
        let today = '{{ now()->format("Y-m-d") }}';
        $('#create_date').val(today);

        // 🔥 โหลดรายการตามวันที่ทันที
        $('#show_list').html('<tr><td colspan="9" class="text-center">กำลังโหลดข้อมูล...</td></tr>');

        $.ajax({
            url: '{{ action("Admin\\Transaction\\ExportShippingController@listdata") }}',
            type: 'GET',
            data: {
                search_type: 'create_date',
                data_val: today
            },
            success: function (res) {
                $('#show_list').html(res);
            },
            error: function (xhr) {
                alert('เกิดข้อผิดพลาด');
                console.log(xhr.responseText);
            }
        });

    } else if (search_type === 'buyer_name') {
        $('#buyer_name').parent().show();
        $('#date_filter').hide();
    }
});

        });
    </script>
@stop