@extends('layouts/admin/default')

@section('title')
    @lang('admin_customer.billing_address_list')
@stop

@section('header_styles')
 
@stop

@section('content')
    <div class="content">
        <div class="form-horizontal form-bordered">
            <div class="header-title clearfix">
                @if(Session::has('succMsg'))
                <div class="alert alert-success alert-dismissable margin5">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <strong>@lang('admin_common.success'):</strong> {{ Session::get('succMsg') }}
                </div>
                @endif
                <h1 class="title">@lang('admin_customer.billing_address_list')</h1>        
            </div>
            <div class="content-wrap">
                <div class="content-right">
                    <div class="tab-content">
                        <div id="tabaddress" class="tab-pane fade show active">
                            @include('admin.shipBillAddress.userAddress')                     
                        </div>
                    </div>
                </div>
            </div>
        </div>      
    </div>
@stop

@section('footer_scripts')

@stop
