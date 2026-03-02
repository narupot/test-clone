@extends('layouts/admin/default')

@section('title')
    @lang('admin.add_new_member')
@stop

@section('header_styles')  
    <link rel="stylesheet" type="text/css" href="{{Config('constants.css_url') }}flatpickr.min.css"> 
    <link rel="stylesheet" type="text/css" href="{{ Config('constants.css_url') }}select.css" />
    <style type="text/css">
        .chosen-container .chosen-results li {
            text-align: left;
        }
        .chosen-container-single .chosen-search {
            display: block;
        }
    </style>
@stop

@section('content')
<form id="add_seller_form" method="post" action="{{ action('Admin\Customer\SellerController@saveSeller') }}" enctype="multipart/form-data" autocomplete="off">
    {{ csrf_field() }}

    <div class="content">
          
        <div class="header-title">
            <h1 class="title">@lang('admin_customer.add_new_seller')</h1>
            <div class="float-right">
                <a class="btn btn-back" href="{{ action('Admin\Customer\UserController@index') }}">@lang('common.back')</a>
                <button type="submit" name="submit_type" value="save_and_continue" class="btn btn-secondary">@lang('common.save_and_continue')</button>
                <button type="submit" name="submit_type" value="save" class="btn btn-save btn-success">@lang('common.save')</button>
            </div>
        </div>
        <div class="content-wrap clearfix">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('seller')!!}
                </ul>
            </div>
            <div class="team-title">
                <h3>@lang('admin_customer.manage_seller')</h3>
                <p>@lang('admin_customer.add_seller_and_manage_thier_details').</p>
            </div>
            <div class="content-right container">
                {{ csrf_field() }}
                <h3 class="mb-3">@lang('admin_customer.seller_information')</h3>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label>@lang('admin_customer.first_name')<i class="red">*</i></label>
                            <input type="text" name="first_name" value="{{ old('first_name') }}" placeholder="First name">
                            <p id="first_name" class="error">{{ $errors->first('first_name') }}</p>
                        </div>
                        <div class="form-group">
                        <label>@lang('admin_customer.last_name')<i class="red">*</i></label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" placeholder="Last name">
                        <p id="last_name" class="error">{{ $errors->first('last_name') }}</p>
                        </div>
                        <div class="form-group">
                            <label>@lang('admin_customer.dob')<i class="red">*</i></label>
                            <input type="text" class="date-select flatpickr-input" name="dob" value="{{ old('dob') }}" placeholder="14-06-2019" readonly="readonly">
                            <p id="dob" class="error">{{ $errors->first('dob') }}</p>
                        </div>
                        <div class="form-group">
                            <label>@lang('admin_customer.login_info_as_well')<i class="red">*</i></label>
                            <div class="radio-group">
                                <label class="radio-wrap">
                                <input type="radio" name="loginuse" value="email" checked="checked">
                                <span class="radio-label">@lang('admin_common.email')</span>
                                </label>
                                <label class="radio-wrap">
                                <input type="radio" name="loginuse" value="ph_no">
                                <span class="radio-label">@lang('admin_customer.ph_number')</span>
                                </label>                                    
                            </div>
                        </div>
                        <div class="form-group" id="emaildiv">
                            <label>@lang('admin_common.email')<i class="red">*</i></label>
                            <input type="text" name="email" value="{{ old('email') }}" placeholder="example@xyz.com">
                            <p id="email" class="error">{{ $errors->first('email') }}</p>
                        </div>
                        <div class="form-group" id="ph_numberdiv" style="display: none;">
                            <label>@lang('admin_customer.ph_number')<i class="red">*</i></label>
                            <input type="text" name="ph_number" value="{{ old('ph_number') }}" placeholder="6932890004">
                            <p id="ph_number" class="error">{{ $errors->first('ph_number') }}</p>
                        </div>
                        <div class="form-group">
                            <label>@lang('admin_common.password')<i class="red">*</i></label>
                            <input type="password" name="password" value="{{ old('password') }}" placeholder="**********">
                            <p id="password" class="error">{{ $errors->first('password') }}</p>
                        </div>
                        <div class="form-group">
                            <label>@lang('admin_common.confirm_password')<i class="red">*</i></label>
                            <input type="password" name="password_confirm" value="{{ old('password_confirm') }}" placeholder="**********">
                            <p id="password_confirm" class="error">{{ $errors->first('password_confirm') }}</p>
                        </div>
                    </div>
                </div>
                <h3 class="team-title">@lang('admin_customer.shop_information')</h3>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>@lang('admin_customer.panel')<i class="red">*</i></label>
                                <input type="text" name="panel_no" value="{{ old('panel_no') }}" placeholder="123">
                                <p id="panel_no" class="error">{{ $errors->first('panel_no') }}</p>
                            </div>
                            <div class="form-group">
                                <label>@lang('admin_shop.store_url')<i class="red">*</i></label>
                                <input type="text" name="shop_url" value="{{ old('shop_url') }}" placeholder="thanutshop">
                                <p id="shop_url" class="error">{{ $errors->first('shop_url') }}</p>
                            </div>
                            <div class="form-group">
                                <label>@lang('admin_shop.shop_name')<i class="red">*</i></label>
                                <input type="text" name="shop_name" value="{{ old('shop_name') }}" placeholder="thanut shop">
                                <p id="shop_name" class="error">{{ $errors->first('shop_name') }}</p>
                            </div>
                            <div class="form-group">
                                <label>@lang('admin_customer.citizen_id')<i class="red">*</i></label>
                                <input type="text" name="citizen_id" value="{{ old('citizen_id') }}" placeholder="123456789">
                                <p id="citizen_id" class="error">{{ $errors->first('citizen_id') }}</p>
                            </div>
                            <div class="form-group">
                                <label>@lang('admin_customer.citizen_id_image')<i class="red">*</i></label>                     
                                <div class="file-wrapper">
                                    <div class="custom-img-file">
                                        <input type="file" name="citizen_id_image" accept="image/*" id="citizen_id_image">
                                        <p id="" class="error">{{ $errors->first('citizen_id_image') }}</p>
                                        <span class="file-img btn-default"><img src="images/file-upload.png"></span>
                                    </div> 
                                    <span class="image-preview"><img id="blah1" src=""/></span>
                                </div>
                            </div> 
                            <div class="form-group">
                                <label>@lang('admin_customer.vendor_code')<i class="red">*</i></label>
                                <input type="text" name="seller_unique_id" value="{{ old('seller_unique_id') }}" placeholder="123">
                                <p id="seller_unique_id" class="error">{{ $errors->first('seller_unique_id') }}</p>
                            </div>
                            <div class="form-group">
                                <label>@lang('admin_customer.seller_description')</label>
                                <textarea name="seller_description" placeholder="">{{ old('seller_description') }}</textarea>
                                <p id="seller_description" class="error">{{ $errors->first('seller_description') }}</p>
                            </div> 
                        </div>
                    </div>
                <h3 class="team-title">@lang('admin_shop.bank_information')</h3>

                <div class="row">
                    <div class="col-sm-4"> 

                        <div class="form-group seller-paybanktab" id="slrbankTab">
                            <label>@lang('admin_shop.bank')<i class="red">*</i></label>
                             <select data-placeholder="Choose Bank List..." class="my-select" style="width:250px;" tabindex="2" name="bank_id">
                            @if(count($bank_list))
                                @foreach($bank_list as $key => $val)
                                        <option value="{{$val->id}}" data-img-src="{{ getBankImageUrl($val->bank_image) }}">{{ isset($val->paymentBankName)?$val->paymentBankName->bank_name:'' }}</option> 
                                @endforeach
                            @endif
                            </select>
                            <p class="error" id="e_bank_id">{{ $errors->first('bank_id') }}</p> 
                        </div>

                        <div class="form-group">
                            <label>@lang('admin_shop.branch')<i class="red">*</i></label>
                            <select data-placeholder="Choose Branch List..." id="branch_select" style="width:250px;" tabindex="2" name="branch_id">
                            <option value="">@lang('admin_shop.select_branch')</option>
                            </select>
                            <p class="error" id="e_branch_id">{{ $errors->first('branch_id') }}</p>
                        </div>
                        
                        {{--@if(count($bank_list))
                        <ul class="nav seller-paybanktab" id="slrbankTab">
                            @foreach($bank_list as $key => $val)
                            <li>                                        
                                <a>
                                    <span class="bank-img"><img src="{{ getBankImageUrl($val->bank_image) }}" alt=""></span>
                                    <span class="bank-name">{{ isset($val->paymentBankName)?$val->paymentBankName->bank_name:'' }}</span>
                                    <input type="radio" value="{{ $val->id }}" name="bank_id">
                                </a>
                            </li>
                            @endforeach
                        </ul>
                        <p class="error" id="e_bank_id">{{ $errors->first('bank_id') }}</p> 
                        @endif--}}
                        
                        <div class="form-group">
                            <label>@lang('admin_shop.account_name')<i class="red">*</i></label>
                            <input type="text" name="account_name" value="{{ old('account_name') }}">
                            <p class="error" id="e_account_name">{{ $errors->first('account_name') }}</p>
                        </div>
                        <div class="form-group">
                            <label>@lang('admin_shop.account_no')<i class="red">*</i></label>
                            <input type="text" name="account_no" value="{{ old('account_no') }}">
                            <p class="error" id="e_account_no">{{ $errors->first('account_no') }}</p>
                        </div>
                        <div class="form-group">
                            <label>@lang('admin_shop.branch')<i class="red">*</i></label>
                            <input type="text" name="branch" value="{{ old('branch') }}">
                            <p class="error" id="e_branch">{{ $errors->first('branch') }}</p>
                        </div>
                        <div class="form-group">
                            <label>@lang('admin_shop.branch_code')<i class="red">*</i></label>
                            <input type="text" name="branch_code" value="{{ old('branch_code') }}">
                            <p class="error" id="e_branch_code">{{ $errors->first('branch_code') }}</p>
                        </div>
                        <div class="form-group">
                            <label>@lang('admin_shop.account_image')<i class="red">*</i></label>                     
                            <div class="file-wrapper">
                                <div class="custom-img-file">
                                    <input type="file" name="account_image" accept="image/*" id="account_image">
                                    <p id="" class="error">{{ $errors->first('account_image') }}</p>
                                    <span class="file-img btn-default"><img src="images/file-upload.png"></span>
                                </div> 
                                <span class="image-preview"><img id="blah" src=""/></span>
                            </div>
                        </div> 
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>        
@stop

@section('footer_scripts')
    <!-- begining of page level js -->
    <!-- for file upload -->    
    <script src="{{ Config('constants.admin_js_url') }}bootstrapValidator.min.js"></script>   
    <script src="{{ Config('constants.admin_js_url') }}flatpickr.min.js"></script> 
    <script src="{{ Config('constants.admin_js_url') }}chosen.jquery.min.js"></script>
    <script type="text/javascript">
        var branch_list_url = "{{action('Auth\SellerRegisterController@getBranchList')}}";
        $(document).ready(function() {
            // Date time Pickers
            $(".date-select").flatpickr();  

            $(".my-select").chosen({
                no_results_text: "Oops, nothing found!"
            });

            $('.seller-paybanktab li a').click(function(){
                $('.seller-paybanktab li a').removeClass('active');
                $(this).addClass('active');
            });

            jQuery('input[name="loginuse"]').click(function(e){
                var loginval = jQuery(this).val();
                showloginuse(loginval);
            });

            if ($('input[name="loginuse"]').length > 0){
                var loginuseval = jQuery('input[name="loginuse"]:checked').val();
                showloginuse(loginuseval);
            }

            function showloginuse(loginval){
                if(loginval=='email'){
                    jQuery('#emaildiv').show();
                    jQuery('#ph_numberdiv').hide();
                }else{
                    jQuery('#ph_numberdiv').show();
                    jQuery('#emaildiv').hide();
                }
            }

            jQuery('select[name="bank_id"]').change(function(e){

                var _this = jQuery(this);
                var bank_id = $.trim(_this.val());
                if(bank_id == '' || bank_id == undefined){
                    _this.focus();
                    return false;
                }
                var data = {bank_id:bank_id};

                callAjaxRequest(branch_list_url,'post',data,function(result){
                    if(result.status=='success'){
                        var opt_html='';
                        $.each(result.data, function(key,val){

                            opt_html +='<option value="'+val.id+'##'+val.branch_code+'">'+val.branch_name.branch_name+'</option>';
                          
                        });
                        $('#branch_select').html(opt_html);
                    }else{
                        swal({
                                title: 'oops..', 
                                type: "warning", 
                                html : '<div class="alert alert-danger">'+result.msg+'</div>',
                            });
                        jQuery('#e_store_name').html(result.msg);
                    }
                });
            });

            jQuery('select[name="branch_id"]').change(function(e){
                var val_arr = $(this).val().split("##");
                var branch_name = $(this).find('option:selected').text();
                if(val_arr){
                    $('input[name="branch"]').val(branch_name);
                    $('input[name="branch_code"]').val(val_arr[1]);
                }else{
                    $('input[name="branch"]').val('');
                    $('input[name="branch_code"]').val('');
                }
                });

        });
    </script>  
    <script type="text/javascript">
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                
                reader.onload = function (e) {
                    $('#blah').attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        $("#account_image").change(function(){
            readURL(this);
        });
        function readURLS(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                
                reader.onload = function (e) {
                    $('#blah1').attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        $("#citizen_id_image").change(function(){
            readURLS(this);
        });
    </script>  
    <!-- end of page level js -->   
    
@stop
