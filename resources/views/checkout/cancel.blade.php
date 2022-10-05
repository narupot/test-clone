@extends('layouts.app') 

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/myaccount','css/flickity'],'css') !!}

@endsection

@section('header_script')

@endsection

@section('content')
<div >
	@if($code && $code !='')
		<h3>@lang('checkout.cancel_reference_code') : {{$code}}</h3>>
	@endif
    {!!getStaticBlock('cancel-order')!!}
</div>

<script type="text/javascript">
    
</script>
@endsection