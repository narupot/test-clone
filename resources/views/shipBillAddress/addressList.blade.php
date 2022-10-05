@extends('layouts.app')

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/myaccount', 'css/jquery-editable-select.min'], 'css') !!}
@stop

@section('header_script')
    var create_address_url = "{{action('User\UserController@create')}}";
    var store_address_url = "{{action('User\UserController@store')}}";
    var delete_url = "{{action('User\UserController@delete')}}";
    var sort_address_url = "{{action('User\UserController@updateSequence')}}";
    var set_default_address_url = "{{action('User\UserController@setDefaultAddress')}}";
    var address_dd_url = "{{action('AjaxController@getStateCityDD')}}";

    var lang_json = {
        "ok":"@lang('common.ok')", 
        "success":"@lang('common.success')", 
        "are_you_sure_to_delete_this_record":"@lang('common.are_you_sure_to_delete_this_record')", 
        "yes_delete_it":"@lang('common.yes_delete_it')", "deleted":"@lang('common.deleted')", 
        "records_deleted_successfully":"@lang('common.records_deleted_successfully')", "records_updated_successfully":"@lang('common.records_updated_successfully')",
        "order_updated_successfully":"@lang('customer.order_updated_successfully')",
        "are_you_sure_to_set_it_as_default":"@lang('customer.are_you_sure_to_set_it_as_default')",
        "are_you_sure_shipping":"@lang('customer.are_you_sure_shipping')",
        "are_you_sure_to_billing":"@lang('customer.are_you_sure_to_billing')",
    };
@stop

@section('breadcrumbs')

@stop

@section('content')

<div class="address-wrap kanban-demo">
    <h1 class="page-title">@lang('customer.shipping_billing_addresses')</h1>                 
    <div class="row" @if(empty($shipping_add) && empty($billing_add)) style="display: none;" @endif>
    @if(!empty($shipping_add))
        <div class="col-sm-6">                          
            <ul class="address-row">
                <li>
                    <h4>@lang('customer.default_shipping_address')</h4>
                    <p>{{$shipping_add['0']->title}}</p>
                    <p>{{$shipping_add['0']->first_name.' '.$shipping_add['0']->last_name}}</p>
                    <p>{{$shipping_add['0']->address.', '.$shipping_add['0']->road}}</p>
                    <p>{{$shipping_add['0']->city_district.', '.$shipping_add['0']->province_state.', '.$shipping_add['0']->zip_code}}</p>
                    <p>@lang('customer.tel'). {{$shipping_add['0']->ph_number}}</p>
                    <span class="action-wrap">
                        <a href="javascript:void(0);" onclick="deleteAddress({{ $shipping_add['0']->id }});"><i class="fas fa-times"></i></a>
                        <a href="javascript:void(0);" onclick="callForAjax('{{ action('User\UserController@edit',$shipping_add['0']->id ) }}', 'popupdiv')"><i class="fas fa-edit"></i></a>
                    </span> 
                </li>
            </ul>
        </div>
    @endif    
    @if(!empty($billing_add))
        <div class="col-sm-6">
            <ul class="address-row">
                <li>
                    <h4>@lang('customer.default_billing_address')</h4>
                    <p>{{$billing_add['0']->title}}</p>
                    <p>{{$billing_add['0']->first_name.' '.$billing_add['0']->last_name}}</p>
                    <p>{{$billing_add['0']->address.', '.$billing_add['0']->road}}</p>
                    <p>{{$billing_add['0']->city_district.', '.$billing_add['0']->province_state.', '.$billing_add['0']->zip_code}}</p>
                    <p>@lang('customer.tel'). {{$billing_add['0']->ph_number}}</p>
                    <span class="action-wrap">
                        <a href="javascript:void(0);" onclick="deleteAddress({{ $billing_add['0']->id }});"><i class="fas fa-times"></i></a>
                        <a href="javascript:void(0);" onclick="callForAjax('{{ action('User\UserController@edit',$billing_add['0']->id ) }}', 'popupdiv')"><i class="fas fa-edit"></i></a>
                    </span>                             
                </li>
            </ul>
        </div>
    @endif
    </div>  
    
    <ul class="address-row" id="sortable">                        
        <li>
            <div class="add-new-address">
                <a id="add_user_address" href="javascript:void(0);"><i class="fas fa-plus"></i></a> <img id="icon_loader" src="{{ Config('constants.site_loader_url') }}site_loader_image.gif" style="display: none;"> <span>@lang('customer.add_new_address')</span>
            </div>
        </li>
        @if(!empty($all_address))
            @foreach($all_address as $address)
                <li class="board-item ui-sortable-handle" id="{{$address->id}}" data-attr="{{$address->id}}">
                    <span class="drag-bar"><i class="fas fa-bars"></i></span>
                    <p>{{$address->title}}</p>
                    <p>{{$address->first_name.' '.$address->last_name}}</p>
                    <p>{{$address->address.', '.$address->road}}</p>
                    <p>{{$address->city_district.', '.$address->province_state.', '.$address->zip_code}}</p>
                    <p>@lang('customer.tel'). {{$address->ph_number}}</p>
                    <div class="link-wrap">
                        <a href="javascript:void(0);" onclick="setDefault('1', {{$address->id}});">@lang('customer.set_as_defult_shipping_address')</a>
                        <a href="javascript:void(0);" onclick="setDefault('2', {{$address->id}});">@lang('customer.set_as_defult_billing_address')</a>
                    </div>            
                    <span class="action-wrap">
                        <a href="javascript:void(0);" onclick="deleteAddress({{$address->id}});"><i class="fas fa-times"></i></a>
                        <a href="javascript:void(0);" onclick="callForAjax('{{action('User\UserController@edit',$address->id )}}', 'popupdiv')"><i class="fas fa-edit"></i></a>
                    </span>
                </li>
            @endforeach
        @endif                
    </ul>
</div>
        
@endsection

@section('footer_scripts')
    {!! CustomHelpers::combineCssJs(['js/jquery-ui.min', 'js/user/myaccount', 'js/user/user_address'], 'js') !!}
@endsection