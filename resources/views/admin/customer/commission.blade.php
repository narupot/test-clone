@extends('layouts/admin/default')

    @section('title')
       
        @lang('commission list')
    @stop

    @section('header_styles')

    
    
    @stop

    @section('content')
    <div class="content">
        <div class="header-title">
            <h1 class="title">@lang('Markup Configuration')</h1>
            
        </div>
             
        <!-- Main content -->         
           
        <div class="content-wrap">
            <div class="col-md-12 pr-0">
                
            </div>
            <br>
            <div class="order-create pt-0"  >
                
                    <div class="row">
                        <div class="col">
                            <h3 class="status-heading">@lang('ตลาด') : </h3>
                            <select id="order_status_id" name="order_status_id" style="max-width: 170px;" class="mr-2">
                                <option value="">กรุณาเลือก</option>                                                                            
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <h3 class="status-heading">@lang('ร้านค้า') : </h3>
                            <input type="text" id="store-search" placeholder="ค้นหาร้านค้า...">
                            <ul id="store-list"></ul> 
                        </div>
                        <div class="col">
                            <h3 class="status-heading">@lang('Markup %') : </h3>
                            <input type="text" id="percent_markup" placeholder="0%" >
                        </div>
                        <div class="col">
                            <h3 class="status-heading">@lang('วันที่เริ่มต้น') : </h3>
                            <input value="" name="pickup_time" type="text" style="" class="pq-grid-hd-search-field pq-search-txt ui-corner-all pq-from hasDatepicker" autocomplete="off" id="dp1743749036717"> 
                        </div>
                        <div class="col-sm-7">
                        &nbsp;<br>
                        <a href="http://202.44.218.45/admin/order/SMM250314121311/detail" class="btn-primary">View</a>
                        </div>
                        
                    </div>
                
            </div>            
        </div>
    </div>
    @stop


