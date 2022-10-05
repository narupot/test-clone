@extends('layouts/admin/default')
@section('title')
    @lang('admin_menu.create_menu')
@stop
@section('header_styles')
 
@stop

@section('content')
<!-- Content start here -->
<div class="content">
    @if(Session::has('errorMsg'))
        <div class="alert alert-danger alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong>@lang('admin_common.error'):</strong> {{ Session::get('errorMsg') }}
        </div>
    @endif
    <form name="megamenuForm" method ="post" id="megamenuForm" enctype='multipart/form-data' action="{{action('Admin\Menu\MenuController@store')}}">
        {{csrf_field()}}
        <div class="header-title"> 
            <h1 class="title">@lang('admin_menu.create_menu')</h1>       
            <div class="float-right">         
                <a href="{{ action('Admin\Menu\MenuController@index')}}" class="btn-back" ng-click="back()">&lt;@lang('admin_common.back')</a>
                <button class="btn-save" type="submit">@lang('admin_common.save')</button>
            </div>
        </div>   
        <div class="content-wrap mega-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('menu','menu')!!}
                </ul>
            </div>
            <div class="container-fluid mt-2">  
                <div class="row">
                    <div class="col-sm-4 form-group">
                        <label class="">@lang('admin_menu.menu_name')<i class="red">*</i></label>
                        <input type="text" class="col-sm-8 form-control" name="menu_name" value="" required>
                    </div>
                </div>      
                
            </div>
        </div>
    </form>
  
</div>
              
@stop
@section('footer_scripts')
    
@stop
