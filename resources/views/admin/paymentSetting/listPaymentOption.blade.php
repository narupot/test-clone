        
@extends('layouts/admin/default')

@section('title')
    @lang('admin_payment.payment_list')
@stop

@section('header_styles')
<link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css"/>
@stop

@section('content')
<div class="content">
    <div class="header-title">
        <h1 class="title">@lang('admin_payment.payment_list')</h1>
        @if($permission_arr['add'] === true)
            <div class="float-right">
                <!-- <a class="btn btn-create" href="{{ action('Admin\Config\PaymentOptionController@create') }}"> @lang('admin_common.create_new')</a> --> 
            </div>
        @endif
    </div>
        
    <!-- Main content -->         
       
    <div class="content-wrap">
    <div class="breadcrumb">
            <ul class="bredcrumb-menu">
                {!!getBreadcrumbAdmin('config','payment','list')!!}
            </ul>
        </div>  
        <table class="table table-bordered " id="table">
            <thead>
                <tr class="filters">
                    <th>@lang('admin_common.sno')</th>
                    <th>@lang('admin_common.name')</th>
                    <th>@lang('admin_common.image')</th>
                    <th>@lang('admin_common.type')</th>
                    <!-- <th>@lang('admin_payment.currency')</th> -->
                    <th>@lang('admin_common.created_at')</th>
                    <th>@lang('admin_common.updated_at')</th>
                    <th>@lang('admin_payment.mode')</th>
                    <th>@lang('admin_common.status')</th>
                    <th>@lang('admin_common.actions')</th>
                </tr>
            </thead>
            <tbody>

            @foreach ($pay_opt_arr as $key => $pay_opt)
            
                <tr>
                    <td>{{ ++$key }}</td>
                    <td>{{ $pay_opt['name'] }}</td>
                    <td>
                        @php
                            $payment_images = getMultiplePayImgUrls($pay_opt['image']);
                        @endphp
                        @if(count($payment_images) == 1)
                            <img src="{{ $payment_images[0] }}" style="max-width: 50px; max-height: 50px;">
                        @else
                            <div class="d-flex" style="gap: 5px;">
                                @foreach($payment_images as $img_url)
                                    <img src="{{ $img_url }}" style="max-width: 40px; max-height: 40px; object-fit: contain;">
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($pay_opt['payment_type'] == '1')
                            Online
                        @else 
                            Offline
                        @endif
                    </td>
                    <td>{{ getDateFormat($pay_opt['created_at'], '1') }}</td>
                    <td>{{ getDateFormat($pay_opt['updated_at'], '1') }}</td>
                    <td>
                    @if($pay_opt['payment_type'] == '1')
                        <a id="mode_{{ $pay_opt['id'] }}" href="javascript:void(0);" onclick="callForAjax('{{ action('Admin\Config\PaymentOptionController@changePayOptMode', $pay_opt['id']) }}', 'mode_{{ $pay_opt['id'] }}')">{{ $pay_opt['mode'] }}</a>
                    @endif
                    </td>
                    <td><a id="status_{{ $pay_opt['id'] }}" href="javascript:void(0);" onclick="callForAjax('{{ action('Admin\Config\PaymentOptionController@changePayOptStatus', $pay_opt['id']) }}', 'status_{{ $pay_opt['id'] }}')" class="{{($pay_opt['status'] =='Active')?'status active':'status inactive'}}">{{ $pay_opt['status'] }}</a></td>
                
                    <td>
                    @if($permission_arr['edit'] === true)
                        <a class="btn btn-dark" href="{{ action('Admin\Config\PaymentOptionController@edit', $pay_opt['id']) }}">
                           <i class="livicon" data-name="pen" data-loop="true" data-color="#000" data-hovercolor="black" data-size="14"></i>@lang('admin_common.edit')
                        </a> 
                    @endif                                       
                    </td>
                </tr>
                
             @endforeach 
             
             </tbody>
        </table>
    </div>
</div>
@stop

@section('footer_scripts')
<script src="{{ Config('constants.admin_js_url') }}jquery.dataTables.js"></script>
<script src="{{ Config('constants.admin_js_url') }}dataTables.bootstrap.js"></script>
<script>
$(document).ready(function() {
    $('#table').dataTable();
});
</script>
@stop
