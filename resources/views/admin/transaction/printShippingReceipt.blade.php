@extends('layouts/admin/default')
@section('content')
<br>
<br>
<br>
<br>
<br>
<div class="container-fluid" style="max-width: 1200px; margin: auto;">
    <h2>พิมพ์ใบเสร็จค่าขนส่ง (เฉพาะออเดอร์ที่สถานะ = ได้รับสินค้าแล้ว)</h2>
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="delivery_date" class="form-label">เลือกวันที่จัดส่ง</label>
            <input type="date" class="form-control" id="delivery_date" name="delivery_date" value="{{ $deliveryDate ?? now()->format('Y-m-d') }}" onchange="this.form.submit()">
        </div>
        <div class="col-md-3">
            <label>&nbsp;</label>
            <button type="button"
                    onclick="window.open('{{ route('admin.order.shipping-receipt-bulk', ['delivery_date' => $deliveryDate]) }}', '_blank')"
                    class="btn btn-success w-100">
                <i class="fa fa-print"></i> พิมพ์ใบเสร็จค่าขนส่งทั้งหมด
            </button>
        </div>
    </form>
    @php
    $allOrders = $ordersByPickupTime; // ไม่ต้องวน loop สร้างใหม่
@endphp

@if(count($allOrders))
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <strong>รายการออเดอร์วันที่จัดส่ง: {{ $deliveryDate }}</strong>
        </div>
        <div class="card-body" >
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>ชื่อผู้สั่งซื้อ</th>
                        <th>ค่าขนส่ง</th>
                        <th>ส่วนลดค่าขนส่ง</th>
                        <th>ค่าขนส่งสุทธิ</th>
                        <th>ค่าธรรมเนียมการโอน</th>                        
                        <th>วันที่สั่งซื้อ</th>
                        <th>วันที่จัดส่งสินค้า</th>
                        <th>เลขที่ใบเสร็จรับเงิน</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allOrders as $order)
                    <tr>
                        <td>{{ $order->formatted_id ?? $order->id }}</td>
                        <td>{{ $order->user_name ?? '-' }}</td>
                        <td>{{ number_format($order->total_shipping_cost ) }}</td>
                        <td>{{ number_format($order->dcc_shipping_discount, 2) }}</td>
                        <td>{{ number_format($order->total_shipping_cost - $order->dcc_shipping_discount, 2) }}</td>
                        <td>{{ number_format($order->transaction_fee ?? 0, 2) }}</td>                        
                        <td>{{ $order->created_at ? $order->created_at->format('Y-m-d H:i') : '-' }}</td>
                        <td>{{ $order->pickup_time ? date('Y-m-d H:i', strtotime($order->pickup_time)) : '-' }}</td>
                        <td>{{ $order->shipping_rept_no??'-'}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="alert alert-warning">ไม่พบข้อมูลออเดอร์สำหรับวันที่ที่เลือก</div>
@endif
@endsection
