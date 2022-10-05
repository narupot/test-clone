@extends(session('default_theme').'.layouts.app')

@section('header_style')
    <link rel="stylesheet" type="text/css" href="{{ themeUrl('css','static-page.css') }}" />
@stop   

@section('content')
    
    @if(count($data->staticPageDesc) > 0)
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">@lang('common.home')</a></li>
            <li class="breadcrumb-item active">{{ $data->staticPageDesc->page_title }}</li>
        </ol>
        <div class="content col-sm-12">
        {!! $data->staticPageDesc->page_desc !!}
        </div>

    @endif

@endsection

@section('footer_scripts')

@endsection