@extends('layouts.app') 

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/myaccount','css/flickity'],'css') !!}

@endsection

@section('header_script')

@endsection

@section('content')
<div >
	
    {!!getStaticBlock('cart-order-removed')!!}
</div>

<script type="text/javascript">
    
</script>
@endsection