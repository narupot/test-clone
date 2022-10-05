@extends('layouts/admin/default') 

@section('title')
    @lang('admin_shipping.add_shipping_profile')
@stop

@section('header_styles')
 <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}select.css" />

<!--page level css -->

<!-- end of page level css -->
    
@stop

@section('content')
    <div class="content ng-cloak" ng-controller="shipProfileCtrl" ng-cloak>
        @if(Session::has('succMsg'))
        <div class="alert alert-success alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong>@lang('admin_common.success'):</strong> {{ Session::get('succMsg') }}
        </div>
        @endif 
        
    </div>
      
@stop

@section('footer_scripts')
 
<!-- begining of page level js -->
 @include('includes.gridtablejsdeps')
 <script src="{{ Config('constants.admin_js_url') }}chosen.jquery.min.js"></script>

 <script stype="text/javascript" src="{{Config('constants.js_url')}}angular/sabinaApp/controller/shipProfileCtrl.js"></script>
<script src="{{ Config('constants.admin_js_url') }}shipping.js"></script>
<script type="text/javascript">
    jQuery('.select').chosen();
</script>
<!-- end of page level js --> 
     
@stop
