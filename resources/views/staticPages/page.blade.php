
@extends('layouts.app')

@section('header_style')

@stop
@if($data->header_footer=='0' || $data->header_footer=='2')
	<style>
		#header{
			display:none !important;
		}
		#footer{
			display:none !important;
		}
	</style>
@endif
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