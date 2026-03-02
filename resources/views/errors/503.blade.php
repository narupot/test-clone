@extends('layouts.error')
@section('content')

<!-- <div id="content" class="error-page">
    <div class="error-top">
        <img src="{{Config::get('constants.public_url')}}images/505-icon.png" alt="">
        <h2>Error 503</h2>
        <h1>Something went wrong</h1>
    </div>

    <div class="container error-bottom">
        <p>Try that again, and If it still dosen’t work, let us know. <br/> Our status page is currently reporting a status of All Systems Operational</p>
        <p><a href="#" class="btn-lg btn">Let us Know</a></p>
        <span class="img-error"><img src="{{Config::get('constants.public_url')}}images/icon505.png" alt=""></span>
    </div>  
</div> -->

{!! getStatickPage('503')  !!}

<!-- /#content --> 
@endsection


@section('footer_scripts')

@endsection