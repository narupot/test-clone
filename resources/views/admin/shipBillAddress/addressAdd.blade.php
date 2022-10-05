<style type="text/css">
    .show{display:block;}
</style>

<div id="add-address" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">@lang('admin_customer.fill_your_address')</h2>
                <span class="close far fa-times" data-dismiss="modal"></span>                  
            </div> 
            <form id="addess_frm" method="Post" class="formone-size">
                {{ csrf_field() }}
                <input type="hidden" name="user_id" value="{{$user_detail->id}}">               
                <div class="modal-body">
                    <div class="form-group">                          
                        <label>@lang('admin_customer.location_name')<i class="red">*</i></label>
                        <input type="text" name="title" autocomplete="off">
                        <span id="error_title" class="error-msg"></span>
                    </div>
                    <div class="form-group">                         
                        <label>@lang('admin_common.salutation')<i class="red">*</i></label>
                        <select name="salutation">
                            <option value="" >@lang('admin_common.select')</option>
                            <option value="Mr." @if($user_detail->salutation == 'Mr.') selected="selected" @endif>@lang('admin_common.mr')</option>
                            <option value="Mrs." @if($user_detail->salutation == 'Mrs.') selected="selected" @endif>@lang('admin_common.mrs')</option>
                            <option value="Miss." @if($user_detail->salutation == 'Miss.') selected="selected" @endif>@lang('admin_common.miss')</option>
                        </select>
                        <span id="error_salutation" class="error-msg"></span>
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label>@lang('common.first_name')<i class="red">*</i></label>
                            <input type="text" name="first_name" value="{{$user_detail->first_name}}">
                            <span id="error_first_name" class="error-msg"></span>
                        </div>
                        <div class="form-group col-sm-6">
                            <label>@lang('common.last_name')<i class="red">*</i></label>
                            <input type="text" name="last_name" value="{{$user_detail->last_name}}" />
                            <span id="error_last_name" class="error-msg"></span>
                        </div>                      
                    </div>
                    <div class="form-group">
                        <label>@lang('common.email')<i class="red">*</i></label>
                        <input type="email" name="email" value="{{$user_detail->email}}" />
                        <span id="error_email" class="error-msg"></span>
                    </div>
                    <div class="form-group">
                        <label>@lang('common.address')<i class="red">*</i></label>
                        <input type="text" name="address" value=" " />
                        <span id="error_address" class="error-msg"></span>
                    </div>
                    @if(getConfigValue('ADDRESS_TYPE') == 'autofill')
                        <div class="form-group">
                            <label>@lang('common.country')<i class="red">*</i></label>
                            <select name="country" id="shipping_country">
                                <option isd_code="" value="">--@lang('common.select')--</option>
                                {!! CustomHelpers::getCountryDorpDown($def_country_dtl->id) !!}
                            </select>
                            <span id="error_country" class="error-msg"></span>
                        </div>
                        <div class="form-group">
                            <label><span id="province_state_level">{{ $def_country_dtl->countryName->province_state_header }}</span><i class="red">*</i></label>
                            <input type="text" name="province_state" id="province_state" class="autofill">
                            <span id="error_province_state" class="error-msg"></span>
                        </div>
                        <div class="form-group">
                            <label><span id="city_district_level">{{ $def_country_dtl->countryName->city_district_header }}</span><i class="red">*</i></label>
                            <input type="text" name="city_district" id="city_district" class="autofill">
                            <span id="error_city_district" class="error-msg"></span>
                        </div>
                        <div id="sub_district_div" class="form-group @if($def_country_dtl->short_code != 'TH') hide @endif">
                            <label><span id="sub_district_level">{{ $def_country_dtl->countryName->sub_district_header }}</span><i class="red">*</i></label>
                            <input type="text" name="sub_district" id="sub_district" class="autofill">
                            <span id="error_sub_district" class="error-msg"></span>
                        </div>
                    @else
                        <div class="form-group" id="address_div_1">
                            <label>@lang('common.country')<i class="red">*</i></label>
                            <select name="country" class="country_dd" id="address_dd_1" address_seq="1">
                                <option isd_code="" value="">--@lang('common.select')--</option>
                                {!! CustomHelpers::getCountryDorpDown($def_country_dtl->id) !!}
                            </select>
                            <span id="error_country" class="error-msg"></span>
                        </div>
                        <div class="form-group" id="address_div_2">
                            <label><span id="province_state_level">{{$def_country_dtl->countryName->province_state_header}}</span><i class="red">*</i></label>
                            <select class="address_dd" name="province_state" id="address_dd_2" address_seq="2">
                                {!! $ship_province_str !!}
                            </select>
                            <span id="error_province_state" class="error-msg"></span>
                        </div>
                        <div class="form-group" id="address_div_3">
                            <label><span id="city_district_level">{{$def_country_dtl->countryName->city_district_header}}</span><i class="red">*</i></label>
                            <select class="address_dd" name="city_district" id="address_dd_3" address_seq="3">
                            </select>                                                                  
                            <span id="error_city_district" class="error-msg"></span>
                        </div>
                        <div class="form-group @if($def_country_dtl->short_code != 'TH') hide @endif" id="address_div_4">
                            <label><span id="sub_district_level">{{$def_country_dtl->countryName->sub_district_header}}</span><i class="red">*</i></label>
                            <select class="address_dd" name="sub_district" id="address_dd_4" address_seq="4">
                            </select>                             
                            <span id="error_sub_district" class="error-msg"></span>     
                        </div>
                    @endif
                    <div class="form-group">                           
                        <label>@lang('common.zip')<i class="red">*</i></label>
                        <input type="text" name="zip_code" id="zip_code" class="autofill">
                        <span id="error_zip_code" class="error-msg"></span>           
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-6">
                            <label>@lang('common.phone_number')<i class="red">*</i></label>
                            <!-- <input type="text" name="isd_code" class="isd_code" value="+{{ $def_country_dtl->country_isd }}" readonly> -->
                            <input type="text" name="ph_number">
                            <span id="error_ph_number" class="error-msg"></span>
                        </div>
                        <div class="col-sm-6">
                            <label>@lang('common.reserve_phone_number')</label>
                            <!-- <input type="text" class="isd_code" value="+{{ $def_country_dtl->country_isd }}" readonly> -->
                            <input type="text" name="reserve_ph_number">
                            <span id="error_reserve_ph_number" class="error-msg"></span>
                        </div>                      
                    </div> 

                    <div class="form-group address-checkbox-group">
                        @if($address_type == 'customer')
                            <label class="check-wrap">
                                <input type="checkbox" name="default[shipping_address]" value="1">
                                <span class="chk-label">@lang('customer.set_defult_shipping_address')</span>
                            </label>
                        @endif
                        <label class="check-wrap d-block">
                            <input type="checkbox" name="default[billing_address]" value="1">
                            <span class="chk-label">@lang('customer.set_defult_billing_address')</span>
                        </label>
                    </div>
                    <div class="form-group">
                    <button type="button" class="btn-primary" onclick="SubmitAddressForm();">@lang('common.submit')</button>
                    </div>
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
                // console.log($(this), li.val() + '. ' + li.text());
                dropDownHandler(li.val(), $(this).attr('name'), $(this).attr('address_seq'));
            });
        };
        $.fn.applyEditableSelect($('.address_dd'));
    })(jQuery);
</script>