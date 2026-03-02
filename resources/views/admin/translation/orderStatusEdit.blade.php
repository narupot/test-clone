@extends('layouts/admin/default')

@section('title')
    @lang('admin.menu_edit')
@stop

@section('header_styles')
    
@stop

@section('content')

    <div class="content">      
        <div class="header-title">
            <h1 class="title">@lang('admin.edit_order_status_name') </h1>         
        </div>
        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config','translation-menu')!!}
                </ul>
            </div>
            <form action="{{ action('Admin\Translation\OrderStatusController@update', $id) }}" method="post" class="row">
                {{ csrf_field() }}
                {{ method_field('PUT') }}
                <div class="col-sm-8">
                    <div class="form-group">
                        {!! CustomHelpers::fieldstabWithLanuageEdit([['field'=>'text', 'name'=>'status', 'label'=>Lang::get('admin.order_status_name').' <i class="strick">*</i>', 'errorkey'=>'order_status_name']], '1', 'order_status_id', $id, $tblOrderStatusDesc, $errors) !!}
                    </div>
                    <div class="form-group btns-group">
                        <a class="btn-secondary mr-2" href="{{ action('Admin\Translation\OrderStatusController@index') }}">&lt;@lang('admin_common.back')</a>
                        <button type="submit" name="submit_type" value="submit" class="btn-secondary">@lang('admin_common.update')</button>                        
                    </div>
                </div>
            </form>
        </div>
    </div>
      
@stop

@section('footer_scripts')
    
@stop
