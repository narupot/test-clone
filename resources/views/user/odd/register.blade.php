@extends('layouts.app')

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/myaccount'],'css') !!}
@endsection

@section('header_script')
    var lang_json = {"ok":"@lang('common.ok')", "otp_resent_successfully":"@lang('customer.otp_resent_successfully')"};

@stop

@section('breadcrumbs')

@stop

@section('content')
<div class="profile-setting">
    <h1 class="page-title title-border">@lang('customer.register_odd_info')</h1>

    @if(Session::has('succMsg'))
        <div class="alert alert-success alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong>@lang('admin_common.success'):</strong> {{ Session::get('succMsg') }}
        </div>
    @elseif(Session::has('errorMsg'))
        <div class="alert alert-danger alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <strong>@lang('admin_common.error'):</strong> {{ Session::get('errorMsg') }}
        </div>    
    @endif
    <form method="post" id="" action="{{action('User\ODDController@oddToken')}}">
        {{ csrf_field() }}
        
        <div class="form-profile-setting">
            <div class="row">
                <div class="col-sm-4 mb-3 pr-0">
                    <div class="form-group">
                        <label>@lang('common.status') : 
                            @if(!empty($user_odd_info) && $user_odd_info->status=='1' && $user_odd_info->espa_id)
                                @lang('customer.odd_registered')
                            @else
                                @lang('customer.not_register')
                            @endif
                            </label>
                        
                    </div>
                </div>
                                              
            </div>
            @if(empty($user_odd_info) || $user_odd_info->espa_id=='')
                <div class="row">
                    <div class="col-sm-4 mb-3 pr-0">
                        <div class="form-group">
                            <label>@lang('customer.mobile_no')</label>
                            <input type="text" name="ph_number" value="{{$userDetail->ph_number}}" required="required">
                            @if ($errors->has('ph_number'))
                                <p class="error error-msg">{{ $errors->first('ph_number') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4 mb-3 pr-0">
                        <div class="form-group">
                            <label>@lang('customer.citizen_id')</label>
                            <input type="text" name="citizen_id" value="" required="required">
                            @if ($errors->has('citizen_id'))
                                <p class="error error-msg">{{ $errors->first('citizen_id') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <button type="submit" id="" class="btn">@lang('customer.register')</button>
                </div>
            @endif
        </div>
    </form>
</div>


@endsection

@section('footer_scripts')
    {!! CustomHelpers::combineCssJs(['js/user/myaccount'],'js') !!}   
@endsection