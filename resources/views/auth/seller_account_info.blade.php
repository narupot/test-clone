@extends('layouts.app')

@section('header_style')
{!! CustomHelpers::combineCssJs(['css/myaccount'],'css') !!}
    <link rel="stylesheet" href="{{ Config('constants.css_url') }}chosen.min.css"/>
    <link rel="stylesheet" href="{{ Config('constants.css_url') }}chosenImage.css"/>
    <style type="text/css">
        .chosenImage-container .chosen-results li, .chosenImage-container .chosen-single span {
            text-align: left;
        }
        .chosen-container-single .chosen-single {
            height: 50px; padding: 5px 0 5px 10px; line-height: 40px;
            background-image: url(../images/arrow-select.png) !important;
            background-position: right 10px top 20px;
            background-repeat: no-repeat;
        }
        .chosen-container-single .chosen-single span {
            background-position:0 10px !important;
        }
        .chosen-container-single .chosen-single div b {
            background-position: 0 10px;
        }
        .chosen-container-single .chosen-single div b { display:none !important; }
    </style>
@endsection

@section('header_script')

var register_by = 'seller';
var seller_user_id = {{ $user_info->id }};
var branch_list_url = "{{action('Auth\SellerRegisterController@getBranchList')}}";
@endsection

@section('breadcrumbs')

@stop

@section('content')
    @if(Session::has('verify_msg'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        {{Session::get('verify_msg')}}
    </div>
    @else  

    
            <div class="register-step">
                @include('auth.register_step')

                <form action="{{ action('Auth\SellerRegisterController@insertAccountInfo')}}" method="post" id="shopAccountForm" enctype="multipart/form-data">    
                    {{ csrf_field() }}                            

                    <div class="form-group seller-paybanktab" id="slrbankTab">
                             <select data-placeholder="Choose Bank List..." class="my-select" style="width:100%;" tabindex="2" name="bank_id">
                    @if(count($bank_list))
                        @foreach($bank_list as $key => $val)
                                <option value="{{$val->id}}" data-img-src="{{ getBankImageUrl($val->bank_image) }}">{{ isset($val->paymentBankName)?$val->paymentBankName->bank_name:'' }}</option> 
                        @endforeach
                    @endif
                            </select>
                            <p class="error" id="e_bank_id"></p> 
                    </div>

                    <div class="form-group seller-paybanktab" id="slrbankBranchTab">
                             <select data-placeholder="Choose Branch List..." id="branch_select" tabindex="2" name="branch_id">
                                 <option value="">@lang('shop.select_branch')</option>     
                            </select>
                            <p class="error" id="e_branch_id"></p> 
                    </div>

                    <div class="form-group">
                        <label class="chk-wrap">
                            <input type="checkbox" name="" id="branchCheck">
                            <span class="chk-mark">@lang('shop.other_branches_branch_not_found_in_the_list')</span>
                        </label>
                        <p class="error" id=""></p>
                    </div>                    
                    <div class="form-group">
                        <label>@lang('shop.branch_code')<i class="red">*</i></label>
                        <input type="text" name="branch_code" value="">
                        <p class="error" id="e_branch_code"></p>
                    </div>
                    <div class="form-group" id="branch_names" style="display:none;">
                        <label>@lang('shop.branch')<i class="red">*</i></label>
                        <input type="text" name="branch" value="">
                        <p class="error" id="e_branch"></p>
                    </div>

                    <div class="form-group">
                        <label>@lang('shop.account_name')<i class="red">*</i></label>
                        <input type="text" name="account_name">
                        <p class="error" id="e_account_name"></p>
                    </div>
                    <div class="form-group">
                        <label>@lang('shop.account_no')<i class="red">*</i></label>
                        <input type="text" name="account_no">
                        <p class="error" id="e_account_no"></p>
                    </div>
                    
                    
                    <div class="form-group">
                        <label>@lang('common.attach') @lang('shop.book_bank')</label>                     
                        <div class="file-wrapper">
                            <div class="custom-img-file">
                                    <input type="file" name="account_image" accept="image/*" id="img-input">
                                    <span class="file-img btn-default"><img src="images/file-upload.png"></span>
                            </div> 
                            <span class="image-preview"><img id="blah" src=""/></span>                         
                        </div>
                    </div> 
                    <div class="form-group text-center">
                        <button type="button" id="btn_account_info" class="btn">@lang('common.next')</button>
                    </div>                                                                     
                </form>
            </div>

<script src="{{ Config('constants.js_url') }}chosen.jquery.min.js"></script>
<script src="{{ Config('constants.js_url') }}chosenImage.jquery.js"></script>

<script type="text/javascript">

   var langMsg = {
        "select_branch":"@lang('shop.select_branch')"        
    };
    $(document).ready(function(){
        $(".my-select").chosenImage({
          disable_search_threshold: 10 
        });
        //Active bank select
        $('.seller-paybanktab li a').click(function(){
            $('.seller-paybanktab li a').removeClass('active');
            $(this).addClass('active');
        });

        //Active bank select
            function readURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();                        
                    reader.onload = function (e) {
                        $('#blah').attr('src', e.target.result);
                    }
                    
                    reader.readAsDataURL(input.files[0]);
                }
            }                
            $("#img-input").change(function(){
                readURL(this);
            });

         // hide show field 
         $(function () {
            $("#branchCheck").click(function () {
                if ($(this).is(":checked")) {
                    $("#branch_names").show();
                    $('#slrbankBranchTab select').addClass('disabled');
                } else {
                    $("#branch_names").hide();
                    $('#slrbankBranchTab select').removeClass('disabled');
                }
            });
        });   
    });
</script>
    
    @endif
@endsection
@section('footer_scripts')
{!! CustomHelpers::combineCssJs(['js/seller/seller_register'],'js') !!}
@stop