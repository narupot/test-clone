@extends('layouts/admin/default')

@section('title')
Search Order
@stop

@section('header_styles')
  
@stop

@section('content')
    <div class="content">
        <div class="header-title">
            <h1 class="title">Search Order</h1>
            {{-- <button class="btn btn-outline-primary" type="button" name="export_order_pdf" onclick="generateOrderPdf('export_order_pdf')">@lang('admin_common.export_order_pdf')</button> --}}
        </div>
             
        <!-- Main content -->         
           
        <div class="content-wrap">
             <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin()!!}<li>Search Order</li>
                </ul>
            </div>
            <div class="container">
                <div class="card ">
                    <div class="card-body">
                        <form action="{{request()->fullUrl()}}" method="GET" class="mb-3">
                            @csrf
                            <div>
                                <label for="search_order" class="mb-1">Order number</label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" id="search_order" name="search_order"  aria-describedby="btn_search_order" placeholder="กรอกรหัส ORDER" value="{{request('search_order')}}">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary" id="btn_search_order">ค้นหา</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        @if (request('search_order')&&!$order)
                        <div class="alert-danger p-3">
                            <div>ไม่พบ Order</div>
                        </div>
                        @endif
                        
                    </div>
                </div>

                

            </div>
        </div>
    </div>
@stop

@section('footer_scripts')
  
@stop
