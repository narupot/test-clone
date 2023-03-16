<style type="text/css">
    .show{display:block;}
</style>
<script type="text/javascript">
    var txt_addr ={
        road : "@lang('customer.road')",
        city_district : "@lang('customer.district')",
        province_state : "@lang('customer.proviance')",
        zip_code : "@lang('common.zip')"
    }; 
</script>

<div id="add-address" class="modal fade">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h3>@lang('customer.add_new_address')</h3>
                <span class="close far fa-times" data-dismiss="modal"></span>              
            </div> 
            <form id="addess_frm" method="Post" class="formone-size">

                {{ csrf_field() }}
                <input type="hidden" name="user_id" value="{{$user_detail->id}}">               
                <div class="modal-body">
                    <div class="form-group">                          
                        <label>@lang('customer.location_name')<i class="red">*</i></label>
                        <input type="text" name="title" autocomplete="off">
                        <span id="error_title" class="error-msg"></span>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-6">
                            <label>@lang('common.name')<i class="red">*</i></label>
                            <input type="text" name="first_name" value="{{$user_detail->first_name}}">
                            <span id="error_first_name" class="error-msg"></span>
                        </div>
                        <div class="col-sm-6">
                            <label>@lang('common.last_name')<i class="red">*</i></label>
                            <input type="text" name="last_name" value="{{$user_detail->last_name}}" />
                            <span id="error_last_name" class="error-msg"></span>
                        </div>                      
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-6">
                            <label>@lang('common.address')<i class="red">*</i></label>
                            <input type="text" id="address" name="address" value=" " />
                            <span id="error_address" class="error-msg"></span>
                        </div>
                        <div class="col-sm-6">
                            <label>@lang('customer.road')<i class="red">*</i></label>
                            <input type="text" name="road" value=" " />
                            <span id="error_road" class="error-msg"></span>
                        </div>                            
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-6">
                            <label><span id="province_state_level">@lang('customer.proviance')</span><i class="red">*</i></label>
                            <select class="address_dd" name="province_state" id="address_dd_1" address_seq="1">
                                {!! $ship_province_str !!}
                            </select>
                            <span id="error_province_state" class="error-msg"></span>
                        </div>
                        <div class="col-sm-6">
                            <label><span id="city_district_level">@lang('customer.district')</span><i class="red">*</i></label>
                            <select class="address_dd" name="city_district" id="address_dd_2" address_seq="2">
                                <option value="" >--@lang('common.select')--</option>';
                            </select>
                            <span id="error_city_district" class="error-msg"></span>
                        </div>                            
                    </div>
                    <div class="form-group row"> 
                        <div class="col-sm-6">                          
                            <label>@lang('common.zip')<i class="red">*</i></label>
                            <!-- <input type="text" name="zip_code" id="zip_code"> -->
                            <select class="address_dd" name="zip_code" id="zip_code" address_seq="5"></select>
                            <span id="error_zip_code" class="error-msg"></span>
                        </div>
                        <div class="col-sm-6">
                            <label>@lang('common.phone_number')<i class="red">*</i></label>
                            <input type="text" name="ph_number">
                            <span id="error_ph_number" class="error-msg"></span>
                        </div>                                     
                    </div>
                    <div class="form-group box-grey">
                        <label class="chk-wrap">
                            <input type="checkbox" id="tax_invoice" name="tax_invoice" value="1">
                            <span class="chk-mark">@lang('customer.tax_invoice')</span>
                        </label>
                    </div>
                    <div class="form-group" id="company_detail" style="display: none;">
                        <div class="form-group row"> 
                            <div class="col-sm-6">                          
                                <label>@lang('customer.company_name')<i class="red">*</i></label>
                                <input type="text" name="company_name" id="company_name">
                                <span id="error_company_name" class="error-msg"></span>
                            </div>
                            <div class="col-sm-6">
                                <label>@lang('customer.branch')</label>
                                <input type="text" name="branch">
                                <span id="error_branch" class="error-msg"></span>
                            </div>                                     
                        </div>
                        <div class="form-group">
                            <label>@lang('customer.tax_id')<i class="red">*</i></label>
                            <input type="text" name="tax_id" id="tax_id" value="">
                            <span id="error_tax_id" class="error-msg"></span>
                        </div>
                        <div class="form-group">                          
                            <label>@lang('customer.company_address')<i class="red">*</i></label>
                            <textarea name="company_address" id="company_address"></textarea>
                            <span id="error_company_address" class="error-msg"></span>
                        </div>
                        <div class="form-group">
                            <label class="chk-wrap">
                                <input type="checkbox" id="same_as_address" value="1">
                                <span class="chk-mark">@lang('customer.link_to_delivery_address')</span>
                            </label>
                        </div>
                    </div>                                                            
                    <div class="form-group">
                        <label class="chk-wrap">
                            <input type="checkbox" name="default[shipping_address]" value="1">
                            <span class="chk-mark">@lang('customer.set_as_defult_shipping_address')</span>
                        </label>
                        <label class="chk-wrap d-block mt-2">
                            <input type="checkbox" name="default[billing_address]" value="1">
                            <span class="chk-mark">@lang('customer.set_as_defult_billing_address')</span>
                        </label>
                    </div>
                    <div class="form-group text-center">
                    @if(isset($address_from) && $address_from == 'cart')
                        <input type="hidden" name="address_type" value="{{$address_type}}">
                        <button type="button" class="btn" onclick="SubmitCartAddressForm();">@lang('common.submit')</button>
                    @else
                        <button type="button" class="btn" onclick="SubmitAddressForm();">@lang('common.submit')</button>
                    @endif
                    </div>
                </div>
            </form>           
        </div>
    </div>
</div>

<script type="text/javascript">
    (function($){
        $('#add-address').modal('show');
    })(jQuery);
</script>