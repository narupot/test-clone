@extends('layouts/admin/default')

@section('title')
 
 @lang('admin.global_seo_management')
  
   
@stop

@section('header_styles')
<link rel="stylesheet" type="text/css" href="{{Config('constants.css_url') }}ui-grid-unstable.css"> 
<script type="text/javascript">
        var dataJsonUrl = "{{ action('Admin\SEO\SeoController@allpageslist') }}";
        var fieldSetJson = {!! $fieldsetdata !!};
        var fieldset = fieldSetJson.fieldSets;
        var showHeadrePagination = true;
        var tableLoaderImgUrl = "{{ Config::get('constants.loader_url')}}ajax-loader.gif";
        //pagination config 
        var pagination = {!! getPagination() !!};
        var per_page_limt = {{ getPagination('limit') }};
        
</script>
<script src="{{ Config('constants.angular_url') }}masterApp/controller/SeoPageCtrl.js"></script>
    
@stop

@section('content')
  
   <div class="content">
        <div class="header-title">
            <h1 class="title">@lang('seo.global_seo_management') </h1>
             <div class="float-right">
              <a class="float-right btn btn-primary" href="{{ action('Admin\SEO\SeoController@createpageseo') }}"> @lang('seo.add_global_seo')
              </a>
             </div>            
      </div>
       
    <div class="ng-cloak" data-ng-controller="gridtableCtrl" ng-cloak>
        <div class="content-wrap">
           @include('includes.gridtable')
        </div>
    </div>
  
      @if(Session::has('succMsg'))
             {!! 
                CustomHelpers::getSuccessMessage(Session::get('succMsg')) 
             !!}
      @endif

 </div>


        
@stop


@section('footer_scripts') 
  @include('includes.gridtablejsdeps')
  <script src="{{ Config('constants.angular_url') }}masterApp/controller/gridTableCtrl.js"></script>
@stop


