@extends('layouts/admin/default')

@section('title')
    @lang('admin_discount_code.create')
@stop

@section('header_styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@stop

@section('content')
    <div class="content">
        <div class="header-title">
            <h1 class="title">Test Discount code</h1>
        </div> 
        <div class="content-wrap">
            <form id="discountCodeForm" action="{{ action('Admin\DiscountCode\DiscountCodeController@calulateDiscount') }}"  method="POST" class="form-horizontal form-bordered"  enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="container mt-4 bg-white shadow-sm p-4 p-lg-5 my-5">
                    <h2 class=" mb-4">* ทดสอบ โค้ดส่วนลด สามารถใช้งานได้หรือไม่</h2>
                    <div class="row mb-4">
                        <div class="col-4">
                            <label for="discount_code">ราคาสินค้า</label>
                            <input type="text" name="purchase" id="purchase" class="form-control" value="{{old('purchase')}}">
                        </div>
                        <div class="col-4">
                            <label for="discount_code">ราคาค่าส่ง</label>
                            <input type="text" name="shippingCost" id="shippingCost" class="form-control" value="{{old('shippingCost')}}">
                        </div>
                        <div class="col-4">
                            <label for="code">Code</label>
                            <input type="text" name="code" id="code" class="form-control" value="{{old('code')}}">
                        </div>
                        
                    </div>
                    <div class="row">
                        <div class="col text-right">
                            <button type="submit" class="btn btn-save btn-success">@lang('common.submit')</button>
                        </div>
                    </div>
                </div>
            </form>
        
            <div class="container mt-4 bg-white shadow-sm p-4 p-lg-5">
                <div class="row">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>โค้ด</th>
                                <th>คงเหลือ</th>
                                <th>เริ่มใช้</th>
                                <th>สิ้นสุด</th>
                                <th>เงื่อนไขซื้อครบ</th>
                                <th>วันที่สร้าง</th>
                                <th>สถานะ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($discountCode as $code)
                                <tr>
                                    <td>{{ $code->code }}</td>
                                    <td>{{ $code->remaining_quantity }}</td>
                                    <td>{{ $code->criteria->start_date??'' }}</td>
                                    <td>{{ $code->criteria->end_date??'' }}</td>
                                    <td>{{ $code->criteria->purchase_amount_threshold??'' }}</td>
                                    <td>{{ $code->created_at??'' }}</td>
                                    <td>{{ $code->status }}</td>
                                </tr>
                            @endforeach
                    </table>
                </div>
            </div>
        </div>
            
    </div>
      
@stop

@section('footer_scripts')
 
<!-- begining of page level js -->
<!-- end of page level js --> 

<script type="text/javascript">
    
    (function($){
        
        $(document).ready(function(){
            
           

        });


    })(jQuery);
</script>  


@stop
