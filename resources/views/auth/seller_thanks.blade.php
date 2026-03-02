@extends('layouts.app')

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

                <div class="thankyou-wrap">
                    <div class="thankyou-title text-center">
                       @lang('shop.thank_you_for_using_the_four_corners_market')
                    </div>
                    @if(Auth::guest())
                        <h4>@lang('shop.you_have_successfully_registered_please_sign_in_to_customize_your_store').</h4>
                        <div class="form-group mt-4">
                            <a href="{{ action('Auth\RegisterController@login') }}" class="btn">@lang('common.sign_in_to_access')</a>
                        </div>
                    @endif
                </div>
            </div>
   
    
    @endif
@endsection
@section('footer_scripts')
@stop