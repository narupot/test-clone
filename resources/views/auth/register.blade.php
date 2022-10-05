@extends('layouts.app')

@section('header_style')

@endsection

@section('header_script')
var register_by = 'buyer';
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
        <div id="register_form_div" style="display: block;" class="adj-mrgin">
            <div class="row login-wrap">
                <div class="col-sm-6">
                    <h2 class="title-gred">@lang('auth.be_a_seller')</h2>             
                    <div class="reg-link-wrap col-sm-12">
                        <p>@lang('auth.seller_register_page_text')</p>                        
                        <a href="{{ action('Auth\RegisterController@sellerRegister') }}" class="btn mb-3">@lang('auth.seller_register')</a>
                       
                    </div>                      
                    
                </div>
                <div class="col-sm-6 login-content">
                    <h2 class="title-gred2">@lang('auth.register_for_buyers')</h2>
                        @include('auth.register_form')
                </div>
            </div>
        </div>
         
    @endif
@endsection
@section('footer_scripts')
<script type="text/javascript" src="https://npmcdn.com/flatpickr@4.6.9/dist/l10n/th.js"></script>
@stop