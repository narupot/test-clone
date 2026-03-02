@extends('layouts/admin/default')

@section('title')
 
 @lang('admin.product_seo_management')  
   
@stop

@section('header_styles')
<link rel="stylesheet" type="text/css" href="{{Config('constants.css_url') }}ui-grid-unstable.css"> 

<script type="text/javascript">
        var dataJsonUrl = "{{ action('Admin\SEO\SeoController@productlist') }}";
        var fieldSetJson = {!! $fieldsetdata !!};
        var fieldset = fieldSetJson.fieldSets;
        var showHeadrePagination = true;
        var tableLoaderImgUrl = "{{ Config::get('constants.loader_url')}}ajax-loader.gif";
        //pagination config 
        var pagination = {!! getPagination() !!};
        var per_page_limt = {{ getPagination('limit') }};
        
</script>
<script src="{{ Config('constants.angular_url') }}sabinaApp/controller/productTab.js"></script>
@stop

@section('content')
  
   <div class="content">
        <div class="header-title">
            <h1 class="title">@lang('seo.product_seo_management')
            </h1>
            
        </div>
       
     <div class="ng-cloak" data-ng-controller="gridtableCtrl" ng-cloak>
        <div class="content-wrap ">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config','seo','list')!!}
                </ul>
            </div>
           @include('includes.gridtable')
        </div>

         @if(Session::has('succMsg'))
                     {!! 
                        CustomHelpers::getSuccessMessage(Session::get('succMsg')) 
                     !!}
                 @endif

       </div>

 </div>


        
@stop





@section('footer_scripts') 
  @include('includes.gridtablejsdeps')
  <script src="{{ Config('constants.angular_url') }}sabinaApp/controller/gridTableCtrl.js"></script>
@stop


