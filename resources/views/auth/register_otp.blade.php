@extends('layouts.app')

@section('header_style')

@endsection

@section('header_script')
var register_by = "{{ $register_by }}";
var register_user_id = {{ $user_info->id }};
var login_use_by = "{{ $user_info->login_use }}";
var txt_verify_success = "@lang('auth.account_verification')";
@endsection

@section('breadcrumbs')
<!-- Breadcrum section start -->
<div class="container">
    <div class="breadcrumb">
        <ul class="bredcrumb-menu">
            {!! $breadcrumb !!}
            <li>{{'Register'}}</li>
        </ul>
    </div>
</div>
@stop

@section('content')
    @if(Session::has('verify_msg'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        {{Session::get('verify_msg')}}
    </div>
    @else  

    <div class="content-wrap">
        @include('auth.otp')      
    </div>  
    
    @endif
@endsection
@section('footer_scripts')
@stop