
@extends('layouts.app')

@section('header_style')

@stop

@section('content')
	@if($preview == 'yes')

		@if(Cache::has('preview_page_data'))
			{!! Cache::get('preview_page_data') !!}
		@endif
	@else
    	{!! isset($data->staticPageDesc->page_desc)?$data->staticPageDesc->page_desc:'' !!}
    @endif
@endsection

@section('footer_scripts')

@endsection