@extends('layouts/admin/default')

@section('title')
    @lang('admin.edit_gender')
@stop

@section('header_styles')
 
@stop

@section('content')

    <div class="content">        
        <!-- Main content -->
        <form action="{{ action('Admin\Gender\GenderController@update', $gender_dtls->id) }}" method="post" enctype="multipart/form-data">
            {{ csrf_field() }} 
            {{ method_field('PUT') }}       
            <div class="header-title">
                <h1 class="title">@lang('admin.edit_gender') : @if(isset($gender_dtls->genderDesc->gender_name)) {{$gender_dtls->genderDesc->gender_name}} @else {{'N/A'}} @endif</h1>
                <span class="float-right">
                    <a class="btn btn-back" href="{{ action('Admin\Gender\GenderController@index') }}">@lang('common.back')</a>
                    <button type="submit" name="submit" value="update" class="btn btn-secondary">@lang('common.update')</button>
                </span>                
            </div>
            <div class="content-wrap"> 
                <div class="row">
                    <div class="col-sm-5">          
                        <div class="form-group @if($errors->has('gender_name')) error @endif">
                            {!! CustomHelpers::fieldstabWithLanuageEdit([['field'=>'text', 'name'=>'gender_name', 'label'=>Lang::get('admin.gender_name').' <i class="strick">*</i>', 'errorkey'=>'gender_name']], '1','gender_id', $gender_dtls->id, $tblGenderDesc, $errors) !!}                   
                        </div> 
                        
                        <div class="form-group">
                            <label>@lang('common.status')</label>
                            <select name="status">
                                <option value="1" @if(old('status') == '1' || $gender_dtls->status == '1') selected="selected" @endif>@lang('common.active')</option>
                                <option value="0" @if(old('status') == '0' || $gender_dtls->status == '0') selected="selected" @endif>@lang('common.inactive')</option>
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
