@extends('layouts/admin/default')

@section('title')
    @lang('admin.add_gender')
@stop

@section('header_styles')
    
@stop

@section('content')

    <div class="content">    
        @if(Session::has('succMsg'))    
            <script type="text/javascript">               
                _toastrMessage('success', "{{ Session::get('succMsg') }}");    
            </script>
        @elseif(Session::has('errorMsg'))    
            <script type="text/javascript">               
                _toastrMessage('error', "{{ Session::get('errorMsg') }}");    
            </script>                            
        @endif       
        <!-- Main content -->
        <form action="{{ action('Admin\Gender\GenderController@store') }}" method="post" enctype="multipart/form-data">
            {{ csrf_field() }}        
            <div class="header-title">
                <h1 class="title">@lang('admin.add_gender')</h1>
                <span class="float-right">
                    <a class="btn btn-back" href="{{ action('Admin\Gender\GenderController@index') }}">@lang('common.back')</a>
                    <button type="submit" name="submit" value="save" class="btn btn-save btn-secondary">@lang('common.save')</button>
                </span>                
            </div>
            <div class="content-wrap"> 
                <div class="row">
                    <div class="col-sm-5">

                        <div class="form-group @if($errors->has('gender_name')) error @endif">
                            {!! CustomHelpers::fieldstabWithLanuage([['field'=>'text', 'name'=>'gender_name', 'label'=>Lang::get('admin.gender_name').' <i class="strick">*</i>','errorkey'=>'gender_name']], '1', $errors) !!}    
                        </div> 
                                 
                        <div class="form-group">
                            <label>@lang('common.status')</label>
                            <select name="status">
                                <option value="1" @if(old('status') == '1') selected="selected" @endif>@lang('common.active')</option>
                                <option value="0" @if(old('status') == '0') selected="selected" @endif>@lang('common.inactive')</option>
                            </select>
                        </div> 

                        
                    </div>
                </div>

                
            </div>
        </form>
    </div>
      
@stop

@section('footer_scripts')    
    
@stop
