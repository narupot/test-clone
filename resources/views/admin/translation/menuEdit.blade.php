@extends('layouts/admin/default')

@section('title')
    @lang('admin.menu_edit')
@stop

@section('header_styles')
    
@stop

@section('content')

    <div class="content">      
        <div class="header-title">
            <h3 class="title">@lang('admin.edit_menu_name') :  @if(isset($menuData->menu_name)) {{$menuData->menu_name}} @else {{'N/A'}} @endif </h3>            
        </div>
        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config','translation-menu')!!}
                </ul>
            </div>
            <form action="{{ action('Admin\Translation\MenuController@update', $id) }}" method="post" class="row">
                {{ csrf_field() }}
                {{ method_field('PUT') }}
                <div class="col-sm-5">
                    <div class="form-group">
                        {!! CustomHelpers::fieldstabWithLanuageEdit([['field'=>'text', 'name'=>'menu_name', 'label'=>Lang::get('admin.menu_name').' <i class="strick">*</i>', 'errorkey'=>'menu']], '1', 'menu_id', $id, $tblMenuDesc, $errors) !!}
                    </div>
                    <div class="form-group btns-group">
                        <a class="btn btn-back" href="{{ action('Admin\Translation\MenuController@index') }}">@lang('common.back')</a>
                        <button type="submit" name="submit_type" value="submit" class="btn btn-primary">@lang('common.update')</button>                        
                    </div>
                </div>
            </form>
        </div>
    </div>
      
@stop

@section('footer_scripts')
    
@stop
