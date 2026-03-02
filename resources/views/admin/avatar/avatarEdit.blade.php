@extends('layouts/admin/default')

@section('title')
    @lang('admin.edit_avatar_image')
@stop

@section('header_styles')
<link rel="stylesheet" type="text/css" href="{{Config('constants.admin_css_url') }}cropper.min.css">  
@stop

@section('content')

    <div class="content">        
        <!-- Main content -->
        <form action="{{ action('Admin\Config\AvatarController@update', $avatar_detail->id) }}" method="post" enctype="multipart/form-data">
            {{ csrf_field() }} 
            {{ method_field('PUT') }}       
            <div class="header-title">
                <h1 class="title">@lang('admin.edit_avatar_image') : @if(isset($avatar_detail->title)) {{$avatar_detail->title}} @else {{'N/A'}} @endif</h1>
                <span class="float-right">
                    <a class="btn btn-back" href="{{ action('Admin\Config\AvatarController@index') }}">@lang('common.back')</a>
                    <button type="submit" name="submit" value="update" class="btn btn-primary">@lang('common.update')</button>
                </span>                
            </div>
            <div class="content-wrap"> 
                <div class="breadcrumb">
                    <ul class="bredcrumb-menu">
                        {!!getBreadcrumbAdmin('avatar')!!}
                    </ul>
                </div>
                <div class="row">
                    <div class="col-sm-5">
                        <div class="form-group @if($errors->has('avatarimage')) error @endif">
                            <label for="form-file-input">@lang('common.select_image') <i class="strick">*</i></label>
                            <img src="{{ Config::get('constants.avtar_images_url').$avatar_detail->name }}" height="50px" width="50px">
                                              
                        </div>

                        <div class="form-group">
                            {{-- <label for="form-text-input">@lang('cms.select_image') <i class="strick">*</i></label> --}}
                            <input type="hidden" name="avatar_image" value="" id="avatar_image_input">
                            @include('admin.includes.avatar_image_upload')
                            <div>                      
                             {{-- {!! Form::file('avatar_image') !!} --}}
                             
                             @if ($errors->has('avatarimage'))
                               <p id="avatar_image-error" class="error error-msg">{{ $errors->first('avatarimage') }}</p>
                             @endif
                            </div>
                        </div>             
                        <div class="form-group @if($errors->has('title')) error @endif">
                            <label for="form-text-input">@lang('admin.avatar_title') <i class="strick">*</i></label>
                            <input type="text" name="title" value="{{ old('title')?old('title'):$avatar_detail->title }}" class="form-control">
                            @if($errors->has('title'))
                                <p class="error error-msg">{{ $errors->first('title') }}</p>
                            @endif                     
                        </div> 
                        <div class="form-group">
                            <label for="form-text-input">@lang('admin.avatar_description')</label>
                            <textarea name="description">{{ old('descrption')?old('descrption'):$avatar_detail->description }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>@lang('common.gender')</label>
                            <select name="gender">
                                <option value="U" @if(old('gender') == 'U' || $avatar_detail->gender == 'U') selected="selected" @endif>@lang('common.undefined')</option>
                                <option value="M" @if(old('gender') == 'M' || $avatar_detail->gender == 'M') selected="selected" @endif>@lang('common.male')</option>
                                <option value="F" @if(old('gender') == 'F' || $avatar_detail->gender == 'F') selected="selected" @endif>@lang('common.female')</option>
                            </select>
                        </div>              
                        <div class="form-group">
                            <label>@lang('common.status')</label>
                            <select name="status">
                                <option value="1" @if(old('status') == '1' || $avatar_detail->status == '1') selected="selected" @endif>@lang('common.active')</option>
                                <option value="0" @if(old('status') == '0' || $avatar_detail->status == '0') selected="selected" @endif>@lang('common.inactive')</option>
                            </select>
                        </div>                                                             
                        <div class="form-group form-actions">
                            <div class="btns-group">
                                <a class="btn btn-back" href="{{ action('Admin\Config\AvatarController@index') }}">@lang('common.back')</a>
                                <button type="submit" name="submit" value="update" class="btn btn-primary">@lang('common.update')</button>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </form>
    </div>
      
@stop

@section('footer_scripts')   
<script src="{{ Config('constants.js_url') }}jquery-cropper.min.js" type="text/javascript"></script>
<script src="{{ Config('constants.js_url') }}avatar_cropper_setting.js" type="text/javascript"></script>    
@stop
