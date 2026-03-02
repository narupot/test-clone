@extends('layouts/admin/default')

@section('title')
    @lang('admin_shipping.shipping_rate_table')
@stop

@section('header_styles')
<link rel="stylesheet" type="text/css" href="{{Config('constants.css_url') }}ui-grid-unstable.css"> 

    <!--page level css -->

    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css"/>
    <!-- end of page level css -->
    
@stop

@section('content')
    <div class="content" ng-controller="shipProfileCtrl" ng-cloak>
        @if(Session::has('succMsg'))
        <div class="alert alert-success alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong>@lang('admin_common.success'):</strong> {{ Session::get('succMsg') }}
        </div>
        @endif 
        <div class="header-title">
            <h1 class="title">@lang('admin_shipping.shipping_rate_table')</h1>
            <div class="float-right">
                <!-- <button class="">Back</button>
                <button class="btn-neg">Reset</button>-->                 
                <button class="btn btn-back">@lang('admin_common.back')</button>
                <button class="btn btn-save">@lang('admin_common.save')</button>
            </div>
        </div>
        <div class="content-wrap clearfix">
            <div class="content-left">
                <div class="tablist">                    
                    <ul class="nav nav-tabs">
                        <li class="nav-item"><a class="nav-link show active" data-toggle="tab" data-target="#general">@lang('admin_shipping.general')</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" data-target="#store_and_customer_group">@lang('admin_shipping.store_and_customer_group')</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" data-target="#import">@lang('admin_shipping.import')</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" data-target="#methods_and_rates">@lang('admin_shipping.methods_and_rates')</a></li>
                    </ul>
                </div>
            </div>
            <div class="content-right">
                <div class="tab-content">
                    <div id="general" class="tab-pane fade">
                        <div>Test</div>
                    </div>
                    <div id="store_and_customer_group" class="tab-pane fade show active">                      
                        <div class="attr-variant-view">
                            <h2 class="title-prod">Product Varaint & Specification</h2>
                        </div>
                    </div>
                    <div id="import" class="tab-pane fade">
                       <div class="">
                            <h2 class="title-prod">Product Requirment </h2>
                        </div>
                    </div>
                    <div id="methods_and_rates" class="tab-pane fade">
                        Test
                    </div>                
                </div>
            </div>
        </div>
    </div>
@stop

@section('footer_scripts')
 @include('includes.gridtablejsdeps')
 <script stype="text/javascript" src="{{Config('constants.js_url')}}angular/sabinaApp/controller/gridTableCtrl.js"></script>
    <!-- begining of page level js -->

    <script src="{{ Config('constants.admin_js_url') }}jquery.dataTables.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}dataTables.bootstrap.js"></script>
    
    <!-- end of page level js -->
    
@stop
