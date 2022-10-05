@extends('layouts.error')
<link rel="stylesheet" type="text/css" href="{{ Config::get('constants.theme_url').session('default_theme').'/css/global.css' }}" />

@section('content')

<!-- <div id="content" class="error-page">
    <div class="error-top">
        <img src="{{Config::get('constants.public_url')}}assets/images/505-icon.png" alt="">
        <h2>Error 500</h2>
        <h1>Something went wrong</h1>
    </div>

    <div class="container error-bottom">
        <p>Try that again, and If it still dosen’t work, let us know. <br/> Our status page is currently reporting a status of All Systems Operational</p>
        <p><a href="#" class="btn btn-lg btn-skyblue">Let us Know</a></p>
        <span class="img-error"><img src="{{Config::get('constants.public_url')}}assets/images/icon505.png" alt=""></span>
    </div>  
</div> -->

{!! getStatickPage('500')  !!}
<!-- /#content --> 
@endsection


@section('footer_scripts')

@endsection