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
    <form method="post" id="" action="{{action('User\ODDController@oddConditionStore')}}">
        {{ csrf_field() }}
        
        <div class="form-profile-setting">
            <div class="row">
                <div class="col-sm-12">
                    
                    {!! getStaticBlock('odd-register') !!}
                </div>
                                              
            </div>
            
                <div class="row">
                    <div class="col-sm-4 mb-3 pr-0">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <input type="checkbox" name="term_cond" value="1" required="required">
                            @if ($errors->has('term_cond'))
                                <p class="error error-msg">{{ $errors->first('term_cond') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" id="" class="btn">@lang('common.submit')</button>
                </div>
            
        </div>
    </form>
</div>


@endsection

@section('footer_scripts')
    {!! CustomHelpers::combineCssJs(['js/user/myaccount'],'js') !!}   
@endsection