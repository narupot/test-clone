@extends('layouts/admin/default')
@php
    $title_page ='WMS BCP';
    
@endphp
@section('title')
    {{$title_page}}
@stop

@section('header_styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        .select2-container--default .select2-search--inline .select2-search__field{
            min-height: auto;
        }
        .select2-selection__choice{
            color: dimgray;
            font-size: small;
            border: none !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove{
            border: 0px;
            margin-top: 1px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice{
            padding-left: 15px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__clear{
            margin-top: 3px;
        }
    </style>

@stop

@section('content')
    <div class="content">
        <div class="header-title">
            <h1 class="title">WMS BCP</h1>
        </div>
           
        <div class="content-wrap ">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                </ul>
            </div>
            
            <div class="tab-content listing-tab">
                <div class="card shadow-sm p-3 p-lg-4 mb-4">
                    <div class="row">
                        <div class="col-md-2">
                            <label for="pickup_date" class="form-label">เลือกวันที่</label>
                            <input type="date" class="form-control" id="pickup_date" name="pickup_date" value="{{ $_REQUEST['pickup_date'] ?? now()->format('Y-m-d') }}">
                        </div>
                        {{-- dropdown เลือก เวลา --}}
                        <div class="col-md-2">
                            <label for="pickup_time"></label>
                            <select id="pickup_time" name="pickup_time" class="form-control">
                                <option value="">-- กรุณาเลือกเวลา --</option>
                                @foreach($pickup_time as $time)
                                <option value="{{ $time }}">{{ $time }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="shipping_method"></label>
                            <select id="shipping_method" name="shipping_method" class="form-control">
                                <option value="">-- กรุณาเลือกวิธีการจัดส่ง --</option>
                                <option value="3">จัดส่งตามที่อยู่</option>
                                <option value="1">มารับที่ศูนย์</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <br>
                            <button type="button" class="btn btn-warning" id="btnReset">ล้างข้อมูล</button>&nbsp;
                            <button type="button" class="btn btn-primary" id="btnExport">Export</button>
                        </div>
                    </div> 
                </div>
            </div>
        </div>
    </div>
@stop

@section('footer_scripts')
    <script src="{{ Config('constants.js_url') }}flatpickr.min.js"></script>
<script>
$(document).ready(function() {
    let token = window.Laravel.csrfToken;

    // Reset Fillter
    $('#btnReset').click(function () {
        $('#pickup_date').val('{{ now()->format("Y-m-d") }}');
        $('#pickup_time').val('').trigger('change');
        $('#shipping_method').val('').trigger('change');
    });
    // Export Excel
    $('#btnExport').click(function () {
        let pickup_date = $('#pickup_date').val();
        let pickup_time = $('#pickup_time').val();
        let shipping_method = $('#shipping_method').val();

    // ตรวจสอบค่าว่าง
    if (!pickup_time) {
        // แจ้งเตือนผู้ใช้
        alert("กรุณาเลือกเวลา");
        return;  // หยุดการทำงาน
    }
    if (!shipping_method) {
        // แจ้งเตือนผู้ใช้
        alert("กรุณาเลือกวิธีการจัดส่ง");
        return;  // หยุดการทำงาน
    }

        let url = '{{ route("export.excel") }}?pickup_date=' + encodeURIComponent(pickup_date) + '&pickup_time=' + encodeURIComponent(pickup_time) + '&shipping_method=' + encodeURIComponent(shipping_method);
        window.open(url, '_blank');
    });
});
</script>
@stop