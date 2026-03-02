@extends('layouts/admin/default')

@section('title')
    @lang('translation.add_language_module')
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
            <h1 class="title">@lang('translation.add_language_module')</h1>
        </div>
        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config','translation-module')!!}
                </ul>
            </div>
            <div class="row">
                {!! Form::open(['url' => action('Admin\Translation\TranslationModuleController@store'),  'class'=>'col-sm-6']) !!}
                    <div class="form-group @if ($errors->has('module_name')) error @endif">
                        <label for="form-text-input">@lang('translation.module_name')</label>
                        {!! Form::text('module_name', old('module_name'), ['class'=>'form-control'])
                              !!}
                        @if ($errors->has('module_name'))
                            <p id="name-error" class="red">{{ $errors->first('module_name') }}</p>
                        @endif  
                    </div>
                    <div class="form-group">
                        <label for="form-text-input">@lang('common.remark')</label>
                        {!! Form::textarea('remark', old('remark'), ['class'=>'form-control'])
                                  !!}
                    </div>
                    <div class="form-group">
                        <label for="form-text-input">@lang('common.status')</label>
                        {!! Form::select('status', ['1'=>Lang::get('common.active'), '0'=>Lang::get('common.inactive')],  null) !!}     
                    </div>
                    <div class="form-group btns-group">
                        <a class="btn btn-back" href="{{ action('Admin\Translation\TranslationModuleController@index') }}">@lang('common.back')</a>
                        <button type="submit" class="btn btn-save btn-secondary">@lang('common.submit')</button>                        
                    </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
      
@stop

@section('footer_scripts')   
    
@stop
