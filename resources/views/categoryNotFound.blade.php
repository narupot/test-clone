@extends('layouts.app') 

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/magicscroll','css/magiczoomplus'],'css') !!}
@endsection

@section('header_script')
    
@endsection

@section('content')
    <div>
        {{-- {!! getStaticBlock('category-not-found') !!} --}}
        <x-not-found />
    </div>
@endsection 

@section('footer_scripts') 

{!! CustomHelpers::combineCssJs(['js/magicscroll','js/magiczoomplus'],'js') !!}
@stop