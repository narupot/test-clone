@extends('layouts.error')

<link rel="stylesheet" type="text/css" href="{{ Config::get('constants.theme_url').session('default_theme').'css/global.css' }}" />


@section('content')
<!-- <div id="content" class="warning-page">
    <div class="error-found"><img src="{{Config::get('constants.public_url')}}images/404.jpg" alt="404 error"></div>
</div> -->

{!! getStatickPage('404')  !!}
<!-- /#content --> 
@endsection


@section('footer_scripts')

@endsection
