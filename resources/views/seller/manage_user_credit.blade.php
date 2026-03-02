@extends('layouts.app') 

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/dataTables.bootstrap','css/myaccount','css/bootstrap-select'],'css') !!}

@endsection

@section('header_script')
var txt_no = "@lang('common.no')";
var are_you_sure = "@lang('common.are_you_sure')";
var text_ok_btn = "@lang('common.ok_btn')";
var text_success = "@lang('common.text_success')";
var text_error = "@lang('common.text_error')";
var text_yes_remove_it = "@lang('common.yes_remove_it')";
@endsection

@section('content')

@if(Session::has('verify_msg'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    {{Session::get('verify_msg')}}
</div>
@endif

@if(Session::has('not_verify_msg')) 
    <div class="alert alert-danger alert-dismissable margin5"> 
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button> 
        {{Session::get('not_verify_msg')}} 
    </div> 
@endif
<!-- page contents start -->
    <div class="row">
        <div class="container">
            <h1 class="page-title title-border">@lang('shop.manage_credit')</h1>
           <form name="giveCredits" id="giveCredits" action="{{action('Seller\CreditController@giveCredit')}}" method="POST">
                {{ csrf_field() }}
                <input type="hidden" name="id" value="{{$creditData->id}}">
                <input type="hidden" name="customer_name" id="cust_name" value="{{$creditData->getUser->display_name}}">
                <input type="hidden" name="customer_email" id="cust_email" value="{{$creditData->getUser->email}}">
                <div class="reg-customer-form">
                    <div class="form-group">
                        <div class="user-block">
                        <div class="user-img">
                            <a href="#">
                                <img id="cust_image" src="{{getUserImageUrl($creditData->getUser->image)}}" width="50" alt="">
                            </a>
                        </div>
                        <div class="user-body">
                            <div class="customer-name">{{$creditData->getUser->display_name}}</div>                          
                        </div>
                    </div>
                    </div>                  

                    <div class="form-group">
                        <label>@lang('shop.payment_method')</label>
                        <select name="payment_period" id="payment_period">
                            @foreach($paymentOptions as $key => $option)
                                <option value="{{$option->value}}" @if($option->value==$creditData->payment_period) selected='selected' @endif>{{$option->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>@lang('shop.credit_limit')</label>
                        <input type="number" name="credited_amount" value="{{$creditData->credited_amount}}" id="credited_amount">
                    </div>
                    <div class="form-group">
                        <button class="btn" type="button" id="give_credit">@lang('shop.give_credit')</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection 
@section('footer_scripts') 
{!! CustomHelpers::combineCssJs(['js/jquery.dataTables','js/dataTables.bootstrap', 'js/user/myaccount','js/manage_credits'],'js') !!} 
@endsection