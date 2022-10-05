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
    var dataJsonUrl = "{{action('ShopController@shopListData',$cat_url)}}";
    var tableLoaderImgUrl = "{{Config::get('constants.loader_url')}}ajax-loader.gif";
    var pagination = {!!getPagination()!!};
    var per_page_limt = "{{getPagination('limit')}}";
    var txt_delete_confirm = "@lang('common.are_you_sure_to_delete_this_record')";
    var yes_delete_it = "@lang('common.yes_delete_it')";
    var txt_no = "@lang('common.no')";
    var are_you_sure = "@lang('common.are_you_sure')";
    var yes_send_it = "@lang('common.yes_send_it')";
    var txt_cancel = "@lang('common.txt_cancel')";
    var text_ok_btn = "@lang('common.ok_btn')";
    var text_success = "@lang('common.text_success')";
    var text_error = "@lang('common.text_error')";
    var text_yes_remove_it = "@lang('common.yes_remove_it')";
    var search_text = "{{ $search }}";

    //columns setting of table where field is field name of database filed.
    var columsSetting = [{ 
            field : 'logo',
            displayName : '@lang('shop.favorit_shop_store')',
            cellTooltip: true,
            cellTemplate: '<a href="<%row.entity.shop_url%>"><div class="product-wrap"><span class="prod-img"><img src="<%row.entity.logo%>" width="49" height="50"></span><div class="product-info"><div class="shop-name"><%row.entity.shop_name%></div><div class="review-star"><div class="grey-stars"></div><div class="filled-stars" style="width: <%row.entity.avg_rating*20%>%"></div></div></div></div></a>',
            enableSorting : false,
            width : 330,
            cellClass : "text-align:'text-center'",
        },{ 
            field : 'market',
            displayName : '@lang('shop.favorit_shop_market')',
            cellTooltip: true,
            enableSorting : false,
            width : 140,
            cellClass : "text-align:'text-center'",
        },{ 
            field : 'shop_category',
            displayName : '@lang('shop.favorit_shop_product_type')',
            cellTooltip: true,
            enableSorting : false,
            width : 400,
            cellClass : "text-align:'text-center'",
        },{ 
            field : 'updated_at',
            displayName : '@lang('shop.favorit_shop_update_price')',
            cellTooltip: true,
            cellTemplate: '<div class="last-updatebox"><div class="wrap-box text-left"><%row.entity.updated_at%></div><div class="wrap-box"><a href="javascript:void(0);" class="wishlist" ng-click="grid.appScope.userActionHandler.custom($event, row.entity)"><i class="fas fa-heart" ng-class="{active:row.entity.favorite}"></i></a></div></div>',
            enableSorting : false,
            width : 300,
            cellClass : "text-align:'text-center'",
        }];
@endsection

@section('content')

<div class="container ng-cloak favorite-shop">
    <div class="filter-wrap">
        <div class="row">
            <div class="col-md-8 col-lg-9">
               <a href="{{Config::get('constants.public_url')}}">Home ></a> 
               @lang('shop.shop_search'): {{ $search }}
            </div>
            @if(!empty($cat_detail))
                <div class="col-md-4 col-lg-3">
                    <div class="view-product-shop">
                        <span>@lang('common.view') :</span>
                        <a href="{{ action('ProductsController@category',$cat_url) }}">@lang('product.product')</a>
                        <a href="{{action('ShopController@shopList')}}" class="active">@lang('shop.shop')</a>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @if(!empty($cat_detail))
        <div class="toolbar border-bottom-0">
            <div class="title-bg-red"><span>{{ $cat_detail->category_name }}</span></div>
            <div class="view-mode">
            </div>
        </div>
    @endif
    <div class="row">
        <div class="col-sm-12"> 
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
    <script type="text/javascript">
        //Listen to add in favorite shop
        function changeNickName(evt,row,rowIndex,$scope){  
            $scope.showLoaderTable = true;
            $.ajax({
                url : "{{ action('ShopController@manageFavoriteShop') }}",
                method: 'post',
                dataType: "json",
                headers: {
                    'Content-Type': 'application/json',
                    // 'Content-Type': 'application/x-www-form-urlencoded',
                    _token:"{{ csrf_token() }}",
                    'X-CSRF-TOKEN':"{{ csrf_token() }}",
                },
                data: JSON.stringify({
                    'shop_url': row.shop_slug                    
                }), 
            }).done(resp=>{ 
                if(resp && resp.status === "success"){ 
                    $scope.$evalAsync(()=>{                       
                        row.favorite = resp.favorite;
                    });
                    swal({
                        type: resp.status, 
                        title: text_success, 
                        text: resp.msg,
                        confirmButtonText : text_ok_btn,
                    });
                    return;
                }
                swal('Opps..!', lang_oops, 'error');
                
            }).always(()=>{
                $scope.$evalAsync(()=>{
                    $scope.showLoaderTable = false;
                });                   
            });
        };
    </script>
@stop