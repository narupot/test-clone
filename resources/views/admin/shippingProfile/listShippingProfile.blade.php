@extends('layouts/admin/default')

@section('title')
    @lang('admin_shipping.shipping_profile')
@stop

@section('header_styles')
    
@stop

@section('content')
<div class="content">
    @if(Session::has('succMsg'))
        <div class="alert alert-success alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong>@lang('admin_common.success'):</strong> {{ Session::get('succMsg') }}
        </div>
    @elseif(Session::has('errorMsg'))
        <div class="alert alert-danger alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <strong>@lang('admin_common.error'):</strong> {{ Session::get('errorMsg') }}
        </div>    
    @endif 
    <div class="header-title">
        <h1 class="title">@lang('admin_shipping.shipping_profile')</h1>
        <div class="float-right">
            <a class="btn btn-back" href="{{ action('Admin\Config\SystemConfigController@show', 'setting') }}"> @lang('admin_common.back')</a> 
        </div>
    </div>
                    
    <div class="content-wrap">
        <div class="shipping-box-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config','shipping-table-rates','list')!!}
                </ul>
            </div>
            <div class="row">
                <div class="col-sm-5">
                    <h3>@lang('admin_shipping.shipping_method') </h3>
                    <p>@lang('admin_shipping.shipping_origin_desc').</p>
                    @if($permission_arr['add'] === true)
                        <a class="btn-md btn add-ship-btn" href="{{ action('Admin\ShippingProfile\ShippingProfileController@create') }}">@lang('admin_shipping.add_shipping_method') </a>
                    @endif
                </div>

                <div class="col-sm-7 float-right">
                @if(count($result_set) > 0)
                    @foreach($result_set as $shipping)
                    <div class="ems-form">
                        <div class="ems-header">
                            <div class="float-right">
                                @if($permission_arr['edit'] === true)
                                    <a href="{{ $shipping['edit_url'] }}" class="primcolor">@lang('admin_shipping.edit')</a>
                                @endif
                                <div class="onoffswitch">
                                    <input type="checkbox" name="onoffswitch" @if($shipping['status'] == '1') checked="checked" @endif class="onoffswitch-checkbox" id="myonoffswitch{{ $shipping['id'] }}" onChange="onOffCheckBox('{{action('Admin\ShippingProfile\ShippingProfileController@updateStatus',['id'=>$shipping['id']])}}', 'content');">
                                    <label class="onoffswitch-label" for="myonoffswitch{{ $shipping['id'] }}">
                                      <span class="onoffswitch-inner"></span>
                                      <span class="onoffswitch-switch"></span>
                                    </label>
                                </div>
                            </div>
                            <h4>{{ $shipping['shipping_name'] }}</h4>
                        </div>
                        <div class="ems-row more-view">
                          <strong>@lang('admin_shipping.send_for_country')</strong> : {{ $shipping['country_name'] }}
                        </div>
                        <div class="ems-row-price">
                          <strong class="primcolor"> {{ $shipping['fee'] }} {{ session('default_currency_code') }} </strong>
                        </div>
                        @if($shipping['rest_country'] == '1')
                            <div class="ems-row more-view">
                              <strong>@lang('admin_shipping.axcept')</strong> : {{ $shipping['country_name'] }}
                            </div>
                            <div class="ems-row border">
                              <strong>@lang('admin_shipping.for_the_rest_of_the_country')</strong> 
                              <strong class="float-right primcolor">{{ $shipping['rest_country_price'] }} {{ session('default_currency_code') }}</strong>
                            </div>
                        @endif
                        @if(!empty($shipping['except_product_str']))
                            <div class="ems-footer more-view">
                              <strong>@lang('admin_shipping.axcept_product')</strong> : {{ $shipping['except_product_str'] }}
                            </div>
                        @endif
                    </div>
                    @endforeach
                @endif
                </div>
            </div>
        </div>
    </div>    
</div>
@stop

@section('footer_scripts')
    
@stop
