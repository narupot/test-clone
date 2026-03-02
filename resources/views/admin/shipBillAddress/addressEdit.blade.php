<style type="text/css">
    .show{display:block;}
</style>

<div id="add-address" class="modal fade" role="dialog"> 
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header line-default">
                <h3 class="modal-title">@lang('admin_customer.fill_your_address')</h3>
                <span class="close far fa-times" data-dismiss="modal"></span>
            </div>
            <form id="addess_frm" method="Post" class="formone-size">
                {{ csrf_field() }}
                <input type="hidden" name="address_id" value="{{$address->id}}">
                <input type="hidden" name="user_id" value="{{$address->user_id}}">            
                <div class="modal-body">
                    <div class="form-group">                          
                          <label>@lang('admin_customer.location_name')<i class="red">*</i></label>
                          <input type="text" name="title" value="{{ $address->title }}">
                          <span id="error_title" class="error-msg"></span>
                    </div>
                    <div class="form-group">                         
                        <label>@lang('admin_common.salutation')<i class="red">*</i></label>
                        <select name="salutation">
                            <option value="" >@lang('admin_common.select')</option>
                            <option value="Mr." @if($address->salutation=='Mr.') selected='selected' @endif >@lang('admin_common.mr')</option>
                            <option value="Mrs." @if($address->salutation=='Mrs.') selected='selected' @endif >@lang('admin_common.mrs')</option>
                            <option value="Miss." @if($address->salutation=='Miss.') selected='selected' @endif >@lang('admin_common.miss')</option>
                        </select>
                        <span id="error_salutation" class="error-msg"></span>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label>@lang('admin_common.first_name')<i class="red">*</i></label>
                            <input type="text" name="first_name" value="{{ $address->first_name }}">
                            <span id="error_first_name" class="error-msg"></span>
                        </div>
                        <div class="col-md-6">
                            <label>@lang('admin_common.last_name')<i class="red">*</i></label>
                            <input type="text" name="last_name" value="{{ $address->last_name }}"/>
                            <span id="error_last_name" class="error-msg"></span>
                        </div> 
                    </div>                   
                    <div class="form-group">
                         <label>@lang('admin_common.email')<i class="red">*</i></label>
                          <input type="email" name="email" value="{{ $address->email}} " />
                          <span id="error_email" class="error-msg"></span>
                    </div>
                    <div class="form-group">
                         <label>@lang('admin_common.address')<i class="red">*</i></label>
                          <input type="text" name="address" value="{{ $address->address }} " />
                          <span id="error_address" class="error-msg"></span>
                    </div>
                    @if(getConfigValue('ADDRESS_TYPE') == 'autofill')
                        <div class="form-group">
                            <label>@lang('admin_common.country')<i class="red">*</i></label>
                            <select name="country" id="shipping_country">
                                <option isd_code="" value="">--@lang('admin_common.select')--</option>
                                {!! CustomHelpers::getCountryDorpDown($def_country_dtl->id) !!}
                            </select>
                            <span id="error_country" class="error-msg"></span>
                        </div>
                        <div class="form-group">
                            <label><span id="province_state_level">@lang('customer.district')</span><i class="red">*</i></label>
                            <input type="text" name="province_state" id="province_state" class="autofill" value="{{ $address->province_state }}">
                            <span id="error_province_state" class="error-msg"></span>
                        </div>
                        <div class="form-group">
                            <label><span id="city_district_level">{{ $def_country_dtl->countryName->city_district_header }}</span><i class="red">*</i></label>
                            <input type="text" name="city_district" id="city_district" class="autofill" value="{{ $address->city_district }}">
                            <span id="error_city_district" class="error-msg"></span>
                        </div>
                    @else
                        <div class="form-group" id="address_div_1">
                            <label>@lang('admin_common.country')<i class="red">*</i></label>
                            <select name="country" class="country_dd" id="address_dd_1" address_seq="1">
                                <option isd_code="" value="">--@lang('admin_common.select')--</option>
                                {!! CustomHelpers::getCountryDorpDown($address->country_id) !!}
                            </select>
                            <span id="error_country" class="error-msg"></span>
                        </div>
                        <div class="form-group" id="address_div_2">
                            <label><span id="province_state_level">{{$def_country_dtl->countryName->province_state_header}}</span><i class="red">*</i></label>
                            <select class="address_dd" name="province_state" id="address_dd_2" address_seq="2">
                                {!! CustomHelpers::getProvinceStateDD($address->country_id, $address->province_state_id) !!}
                            </select>
                            <span id="error_province_state" class="error-msg"></span>
                        </div>
                        <div class="form-group" id="address_div_3">
                            <label><span id="city_district_level">{{$def_country_dtl->countryName->city_district_header}}</span><i class="red">*</i></label>
                            <select class="address_dd" name="city_district" id="address_dd_3" address_seq="3">
                                {!! CustomHelpers::getCityDistrictDD($address->province_state_id, $address->city_district_id) !!}
                            </select>
                            <span id="error_city_district" class="error-msg"></span>
                        </div>
                        <div class="form-group @if($def_country_dtl->short_code != 'TH') hide @endif"" id="address_div_4">
                            <label><span id="sub_district_level">{{$def_country_dtl->countryName->sub_district_header}}</span><i class="red">*</i></label>
                            <select class="address_dd" name="sub_district" id="address_dd_4" address_seq="4">
                                {!! CustomHelpers::getSubDistrictDD($address->city_district_id, $address->sub_district_id) !!}
                            </select>
                            <span id="error_sub_district" class="error-msg"></span>     
                        </div>
                    @endif
                    <div class="form-group">                           
                        <label>@lang('admin_common.zip')<i class="red">*</i></label>
                        <input type="text" name="zip_code" value="{{ $address->zip_code }}" id="zip_code" class="autofill">
                        <span id="error_zip_code" class="error-msg"></span>           
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-6">
                            <label>@lang('admin_common.phone_number')<i class="red">*</i></label>
                            <!-- <input type="text" name="isd_code" class="isd_code" value="+{{ $def_country_dtl->country_isd }}" readonly> -->
                            <input type="text" name="ph_number" value="{{ $address->ph_number }}">
                            <span id="error_ph_number" class="error-msg"></span>
                        </div>
                        <div class="col-sm-6">
                            <label>@lang('admin_common.reserve_phone_number')</label>
                            <!-- <input type="text" class="isd_code" value="+{{ $def_country_dtl->country_isd }}" readonly> -->
                            <input type="text" name="reserve_ph_number" value="{{ $address->reserve_ph_number }}">
                            <span id="error_reserve_ph_number" class="error-msg"></span>
                        </div>                      
                    </div>
                    <div class="form-group address-checkbox-group">
                        @if($address_type == 'customer')
                            <label class="chk-wrap d-block">
                                <input type="checkbox" name="default[shipping_address]" value="1" @if($address->shipping_address == 1) checked="checked" @endif> 
                                <span class="chkmark"><i class="cr-icon fa fa-check"></i></span>@lang('admin_customer.set_defult_shipping_address')
                            </label>
                        @endif
                        <label class="chk-wrap mt-2">
                            <input type="checkbox" name="default[billing_address]" value="1" @if($address->billing_address == 1) checked="checked" @endif>
                            <span class="chkmark"><i class="cr-icon fa fa-check"></i></span>@lang('admin_customer.set_defult_billing_address')
                        </label>
                    </div>
                    <button type="button" class="btn-primary" onclick="SubmitAddressForm();">@lang('admin_common.submit')</button>
                </div>  
            </form>           
        </div>
    </div>  
</div>
<script type="text/javascript">
    (function($){
        $('#add-address').modal('show');

        $.fn.applyEditableSelect = function($element){
            $element.editableSelect().on('select.editable-select', function (e, li){
                dropDownHandler(li.val(), $(this).attr('name'), $(this).attr('address_seq'));
            });
        };
        $.fn.applyEditableSelect($('.address_dd'));
    })(jQuery);
</script>