@extends('layouts/admin/default')

@section('title')
    @lang('admin.edit_language_module')
@stop

@section('header_styles')
    
@stop

@section('content')

    <div class="content">
        @if(Session::has('succMsg'))    
            <script type="text/javascript">               
                _toastrMessage('success', "{{ Session::get('succMsg') }}");    
            </script>                             
        @endif         
        <div class="header-title">
            <h1 class="title">@lang('translation.edit_language_module') :  @if(isset($result->module_name)) {{$result->module_name}} @else {{'N/A'}} @endif </h1>
        </div>
        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config','translation-module')!!}
                </ul>
            </div>
            <div class="row">

                {!! Form::open(['url' => action('Admin\Translation\TranslationModuleController@update', $result->id),'method'=>'PUT', 'class'=>'col-sm-6']) !!}

                    <div class="form-group @if ($errors->has('module_name')) error @endif">
                        <label for="form-text-input">@lang('translation.module_name')</label>
                        {!! Form::text('module_name', old('module_name', $result->module_name), ['class'=>'form-control']) !!}
                        @if ($errors->has('module_name'))
                            <p id="name-error" class="red">{{ $errors->first('module_name') }}</p>
                        @endif  
                    </div>
                    <div class="form-group">
                        <label for="form-text-input">@lang('translation.file_name')</label>
                        {!! Form::text('lang_file_name', old('lang_file_name', $result->lang_file_name), ['class'=>'form-control', 'readonly' => 'readonly']) !!} 
                    </div>
                    <div class="form-group">
                        <label for="form-text-input">@lang('common.remark')</label>
                        {!! Form::textarea('remark', old('remark', $result->remark), ['class'=>'form-control']) !!}
                    </div>
                    <div class="form-group">
                        <label for="form-text-input">@lang('common.status')</label>
                        {!! Form::select('status', ['1'=>Lang::get('common.active'), '0'=>Lang::get('common.inactive')],  $result->status) !!}     
                    </div>
                    <div class="form-group btns-group">
                        <a class="btn btn-back" href="{{ action('Admin\Translation\TranslationModuleController@index') }}">@lang('common.back')</a>
                        <button type="submit" class="btn btn-primary">@lang('common.update')</button>
                        
                    </div>
                {!! Form::close() !!}
            </div>
        </div>   
    </div>
      
@stop

@section('footer_scripts')  
    
@stop
