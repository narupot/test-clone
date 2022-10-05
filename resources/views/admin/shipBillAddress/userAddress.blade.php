<link rel="stylesheet" type="text/css" href="{{Config('constants.css_url') }}sweetalert.css">
<link rel="stylesheet" type="text/css" href="{{Config('constants.css_url')}}jquery-editable-select.min.css" /> 

<div class="user-wrap">
    <h2 class="title-prod">@lang('admin_customer.address_infomation') </h2>
    <div class="account-box">
        <div class="default-address-wrap clearfix @if(empty($shipping_add) && empty($billing_add)) hide @endif">
            <div class="row">
                @if($address_type == 'customer')
                <div class="default-ship-address col-sm-6" data-attr="1">
                    <h2>@lang('admin_customer.shipping_address')</h2>
                    <ul class="ship-address address_drag">
                    @if(!empty($shipping_add))
                        <li class="bggreen">
                            <div class="user-address-wrap">
                                <div class="user-address-box">
                                    <div class="address-icon-hamburger clearfix"> <span class="fa fa-bars ui-draggable-handle float-right"></span></div>
                                    <span class="name"><b>{{ $shipping_add['0']->title }}</b></span>
                                    <address>
                                        {{ $shipping_add['0']->first_name }} {{ $shipping_add['0']->last_name }}<br/>

                                        {{ $shipping_add['0']->address }}<br/>

                                        @if(!empty($shipping_add['0']->city_district_id))
                                            {{ $shipping_add['0']->cityDesc->city_district_name }}<br/>
                                        @endif

                                        @if(!empty($shipping_add['0']->province_state_id))    
                                            {{ $shipping_add['0']->provinceDesc->province_state_name }} <br/>
                                        @endif

                                        @if(!empty($shipping_add['0']->country_id)) 
                                            {{ $shipping_add['0']->countryDesc->country_name }}  
                                        @endif

                                        {{ $shipping_add['0']->zip_code }}
                                    </address>
                                    <div class="tel">
                                        @lang('admin_customer.tel'). <a href="tel:{{ $shipping_add['0']->ph_number }}">{{ $shipping_add['0']->ph_number }}</a>
                                    </div>
                                    <a class="email" href="mailto:{{ $shipping_add['0']->email }}">{{ $shipping_add['0']->email }}</a>
                                    <div class="edit-del clearfix add-botttom">
                                        <a href="javascript:void(0)" onclick="deleteAddress({{$shipping_add['0']->id}});"><span class="glyphicon glyphicon-trash"></span> @lang('admin_common.delete')</a>
                                        @if($address_type == 'customer')
                                            <a href="javascript:void(0);" class="edit" onclick="callForAjax('{{action('Admin\Customer\UserController@editAddress',$shipping_add['0']->id)}}', 'popupdiv')">
                                                <span class="glyphicon glyphicon-edit"></span> @lang('admin_common.edit')  
                                            </a>
                                        @elseif($address_type == 'website')
                                            <a href="javascript:void(0);" class="edit" onclick="callForAjax('{{action('Admin\WebsiteManagement\BillingAddressController@editAddress',$shipping_add['0']->id)}}', 'popupdiv')">
                                                <span class="glyphicon glyphicon-edit"></span> @lang('admin_common.edit')  
                                            </a>                                        
                                        @endif
                                    </div>
                                </div>
                                <div class="default-txt">
                                    @lang('admin_customer.default_address')
                                </div>
                            </div>
                        </li>
                    @endif 
                    </ul>
                </div>
                @endif
                <div class="default-bill-address col-sm-6" data-attr="2">
                    <h2>@lang('admin_customer.billing_address')</h2>
                    <ul class="ship-address address_drag">
                    @if(!empty($billing_add))
                        <li class="bggreen">
                            <div class="user-address-wrap">
                                <div class="user-address-box">
                                    <div class="address-icon-hamburger clearfix"> <span class="fa fa-bars ui-draggable-handle float-right"></span></div>
                                    <span class="name"><b>{{ $billing_add['0']->title }}</b></span>
                                    <address>
                                        {{ $billing_add['0']->first_name }} {{ $billing_add['0']->last_name }}<br/>

                                        {{ $billing_add['0']->address }}<br/>

                                        @if(!empty($billing_add['0']->city_district_id))
                                            {{ $billing_add['0']->cityDesc->city_district_name }}<br/>
                                        @endif

                                        @if(!empty($billing_add['0']->province_state_id)) 
                                            {{ $billing_add['0']->provinceDesc->province_state_name }} <br/>
                                        @endif

                                        @if(!empty($billing_add['0']->country_id))  
                                            {{ $billing_add['0']->countryDesc->country_name }}  
                                        @endif

                                        {{ $billing_add['0']->zip_code  }}
                                    </address>
                                    <div class="tel">
                                        @lang('admin_customer.tel'). <a href="tel:{{ $billing_add['0']->ph_number }}">{{ $billing_add['0']->ph_number }}</a>
                                    </div>
                                    <a class="email" href="mailto:{{ $billing_add['0']->email }}">{{ $billing_add['0']->email }}</a>
                                    <div class="edit-del clearfix add-botttom">
                                        <a href="javascript:void(0)" onclick="deleteAddress({{$billing_add['0']->id}});"><span class="glyphicon glyphicon-trash"></span> @lang('admin_common.delete')</a>
                                        @if($address_type == 'customer')
                                            <a href="javascript:void(0);" class="edit" onclick="callForAjax('{{action('Admin\Customer\UserController@editAddress',$billing_add['0']->id)}}', 'popupdiv')">
                                                <span class="glyphicon glyphicon-edit"></span> @lang('admin_common.edit')  
                                            </a>
                                        @elseif($address_type == 'website')
                                            <a href="javascript:void(0);" class="edit" onclick="callForAjax('{{action('Admin\WebsiteManagement\BillingAddressController@editAddress',$billing_add['0']->id)}}', 'popupdiv')">
                                                <span class="glyphicon glyphicon-edit"></span> @lang('admin_common.edit')  
                                            </a>                                        
                                        @endif
                                    </div>
                                </div>
                                <div class="default-txt">
                                    @lang('admin_customer.default_address')
                                </div>
                            </div>
                        </li>
                    @endif
                    </ul>
                </div>
            </div>
        </div>
        <div class="user-list-wrap clearfix">
            <ul id="sortable">
                <li>
                    <a id="add_user_address" href="javascript:void(0);">
                        <div class="user-address-box add-user-address">
                            <div class="adduser-icon">
                                <span id="add-icon" class="add-icon icon-add3"></span>
                                <img id="icon_loader" src="{{ Config('constants.loader_url') }}Loading_icon.gif" style="display: none;">
                            </div>
                            <div class="btn clearfix add-botttom btn-spc">
                                @lang('admin_customer.add_new_address')
                            </div>
                        </div>
                    </a>  
                </li> 
                @if(!empty($all_address))
                    @foreach($all_address as $address)
                    <li id="{{ $address->id }}" class="drag" data-attr="{{ $address->id }}">
                        <div class="user-address-box">
                            <div class="address-icon-hamburger clearfix"> <span class="fa fa-bars ui-draggable-handle float-right"></span></div>
                            <span class="name"><b>{{ $address->title }}</b></span>
                            <address>
                                {{ $address->first_name }} {{ $address->last_name }}<br/>

                                {{ $address->address }}<br/>

                                @if(!empty($address->city_district_id))
                                    {{ $address->cityDesc->city_district_name }}<br/>
                                @endif

                                @if(!empty($address->province_state_id))  
                                    {{ $address->provinceDesc->province_state_name }} <br/>
                                @endif

                                @if(!empty($address->country_id))   
                                    {{ $address->countryDesc->country_name }}  
                                @endif

                                {{ $address->zip_code }}
                            </address>
                            <div class="tel">
                                @lang('admin_customer.tel'). <a href="tel:{{ $address->ph_number }}">{{ $address->ph_number }}</a>
                            </div>
                            <a class="email" href="mailto:{{ $address->email }}">{{ $address->email }}</a>
                            <div class="edit-del clearfix add-botttom">
                                <a href="javascript:void(0)" onclick="deleteAddress({{ $address->id }});"><span class="glyphicon glyphicon-trash"></span> @lang('admin_common.delete')</a>
                                @if($address_type == 'customer')
                                    <a href="javascript:void(0);" class="edit" onclick="callForAjax('{{action('Admin\Customer\UserController@editAddress',$address->id)}}', 'popupdiv')">
                                        <span class="glyphicon glyphicon-edit"></span> @lang('admin_common.edit')  
                                    </a>
                                @elseif($address_type == 'website')
                                    <a href="javascript:void(0);" class="edit" onclick="callForAjax('{{action('Admin\WebsiteManagement\BillingAddressController@editAddress',$address->id)}}', 'popupdiv')">
                                        <span class="glyphicon glyphicon-edit"></span> @lang('admin_common.edit')  
                                    </a>                                        
                                @endif
                            </div>
                        </div>                           
                    </li>
                    @endforeach
                @endif                        
            </ul> 
        </div>             
    </div> 
</div>

<script type="text/javascript">
@if($address_type == 'customer')
    var create_address_url = "{{action('Admin\Customer\UserController@createAddress',$user->id)}}";
    var store_address_url = "{{action('Admin\Customer\UserController@storeAddress')}}";
    var delete_url = "{{action('Admin\Customer\UserController@deleteAddress')}}";
    var sort_address_url = "{{action('Admin\Customer\UserController@updateSequence')}}";
    var set_default_address_url = "{{action('Admin\Customer\UserController@setDefaultAddress')}}";
@elseif ($address_type == 'website')
    var create_address_url = "{{action('Admin\WebsiteManagement\BillingAddressController@createAddress')}}";
    var store_address_url = "{{action('Admin\WebsiteManagement\BillingAddressController@storeAddress')}}";
    var delete_url = "{{action('Admin\WebsiteManagement\BillingAddressController@deleteAddress')}}";
    var sort_address_url = "{{action('Admin\WebsiteManagement\BillingAddressController@updateSequence')}}";
    var set_default_address_url = "{{action('Admin\WebsiteManagement\BillingAddressController@setDefaultAddress')}}";
@endif
    var country_dtl_url = "{{action('AjaxController@getCountryDetail')}}";
    var address_fill_url = "{{action('AjaxController@getAutofillAddress')}}";
    var address_dd_url = "{{action('AjaxController@getStateCityDropDown')}}";
</script>

<script src="{{Config('constants.admin_js_url')}}lang/{{session('lang_code')}}.lang.js"></script>
<script src="{{Config('constants.js_url')}}sweetalert.min.js"></script>
<script src="{{Config('constants.js_url')}}jquery-editable-select.min.js"></script>
<script src="{{Config('constants.admin_js_url')}}user_address.js" ></script>