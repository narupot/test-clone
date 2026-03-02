@extends('layouts.app') 

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/myaccount','css/ui-grid-unstable'],'css') !!}
@endsection

@section('header_script')

    var fieldSetJson  = {!!$fielddata!!};
    var fieldset = fieldSetJson.fieldSets;
    var pagelimit = "{{action('JsonController@pageLimit')}}";
    var showSearchSection = false;
    var showHeadrePagination = false;
    var getAllDataFromServerOnce = true;
    var dataJsonUrl = "{{action('Seller\StockMemoController@getStockList')}}";
    var tableLoaderImgUrl = "{{Config::get('constants.loader_url')}}ajax-loader.gif";
    var pagination = {!!getPagination()!!};
    var per_page_limt = "{{getPagination('limit')}}";

    //columns setting of table where field is field name of database filed.
    var columsSetting = [{ 
            field : 'category_name',
            displayName : '@lang('stock_memo.product')',
            cellTooltip: true,
            cellTemplate: '<a href="<%row.entity.view_url%>"><span class="product-img"><img src="<%row.entity.product_img%>" width="49" height="50"></span><span class="prod-name"><%row.entity.category_name%></span></a>',
            enableSorting : false,
            width : 230,
            cellClass : "text-align:'text-center'",
        },{ 
            field : 'badge_img',
            displayName : '@lang('stock_memo.standard')',
            cellTooltip: true,
            cellTemplate: '<span><img src="<%row.entity.badge_img%>" width="34" height="34"></span>',
            enableSorting : false,
            width : 110,
            cellClass : "text-align:'text-center'",

        },{ 
            field : 'quantity',
            displayName : '@lang('product.stock')',
            cellTooltip: true,
            enableSorting : false,
            width : 200,
            cellClass : "text-align:'text-center'",
        },{ 
            field : 'package_name',
            displayName : '@lang('stock_memo.package')',
            cellTooltip: true,
            enableSorting : false,
            width : 200,
            cellClass : "text-align:'text-center'",
        },{ 
            field : 'weight_per_unit',
            displayName : '@lang('stock_memo.weight_per_unit')',
            cellTooltip: true,
            cellTemplate: '<%row.entity.weight_per_unit%>',
            enableSorting : false,
            width : 200,
            cellClass : "text-align:'text-center'",
        },{ 
            field : 'action',
            displayName : '@lang('common.action')',
            cellTooltip: true,
            cellTemplate: '<a ng-if="row.entity.manage_stock_url" href="<%row.entity.manage_stock_url%>" class="btn-dark-grey">@lang('stock_memo.manage_stock')</a>',
            enableSorting : false,
            width : 200,
            cellClass : "text-align:'text-center'",
    }];
@endsection

@section('content')

<div class="container ng-cloak">
    <div class="row">
        <div class="col-sm-12"> 

            @include('includes.seller_panel_top_menu')

            <div class="tab-content">
                <div class="product-detail" ng-controller="gridtableCtrl">                   
                    @include('includes.gridtable')                           
                </div>
            </div>  
        </div>
    </div>  
</div>
          
@endsection 

@section('footer_scripts')
    @include('includes.gridtablejsdeps')
    <script stype="text/javascript" src="{{Config('constants.js_url')}}angular/smmApp/controller/gridTableCtrl.js"></script>
@stop