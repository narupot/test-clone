@extends('layouts.app') 

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/magicscroll','css/magiczoomplus'],'css') !!}
@endsection

@section('header_script')
    var sub_cats = {!! $child_cat_data !!}; 
@endsection

@section('content')
    <div class="category-products" ng-controller="CategoryList" ng-cloak>
        <div class="toolbar border-bottom-0">                   
            <div class="title-bg-red"><span>{{$parent_cat_detail->category_name ?? ''}}</span></div>
            <div class="view-mode" ng-if="product_Items.length">
                <a href="javascript:void(0)" class="grid" ng-class="{'active': productLayoutView == 'grid-view'}" ng-click="prdLayoutManage('grid-view')"><i class="fas fa-th-large"></i></a>
                <a href="javascript:void(0)" class="list" ng-class="{'active': productLayoutView == 'list-view'}" ng-click="prdLayoutManage('list-view')"><i class="fas fa-th-list"></i></a>
            </div>
        </div>
        <div class="title-product"> @lang('category.product') </div>
        <ul class="thumb-<%productLayoutView%>" ng-if="product_Items.length"> 
            <li ng-repeat="item in product_Items track by item._id" ng-if="item.tot_prd >0">
                <div class="prod-img">
                    <a ng-href="category/<%item.url%>">
                        <img ng-src="{{Config::get('constants.category_img_url')}}<%item.img%>" alt="<%item.img%>" onerror="this.onerror=null;this.src='{{getCategoryImageUrl('')}}'"></a>
                </div>
                <div class="product-info">
                    <h3 class="product-name"><a ng-href="category/<%item.url%>" ng-bind="item.category_name"></a></h3>
                </div>
                <div class="view-product" ng-if="productLayoutView == 'list-view'">
                    <a ng-href="category/<%item.url%>">@lang('category.view_products')</a>
                </div>
            </li>
        </ul>  
        
          <div class="thumb-<%productLayoutView%> row" ng-if="!product_Items.length">
            <div class="col-sm-12 text-center py-3">No subcategories</div>
          </div>
        

        <!-- pagination -->
       <!--  <ul class="pagination">
            <li class="page-item">
                <a class="page-link" href="javascript:void()">
                    <i class="fas fa-angle-double-left"></i>                          
                </a>
           </li>
           <li class="page-item">
                <a class="page-link" href="javascript:void()">
                    <i class="fas fa-angle-left"></i>
                </a>
           </li>
           <li class="page-item"><a class="page-link" href="javascript:void()">1</a></li>
           <li class="page-item"><a class="page-link" href="javascript:void()">2</a></li>
           <li class="page-item"><a class="page-link" href="javascript:void()">3</a></li>
           <li class="page-item"><a class="page-link" href="javascript:void()">4</a></li>
           <li class="page-item"><a class="page-link" href="javascript:void()"><i class="fas fa-ellipsis-h"></i></a></li>
           <li class="page-item"><a class="page-link" href="javascript:void()">13</a></li>
           <li class="page-item">
              <a class="page-link" href="javascript:void()" aria-label="Next">
              <i class="fas fa-chevron-right"></i>
              </a>
           </li>
           <li class="page-item">
              <a class="page-link" href="javascript:void()">
              <i class="fas fa-angle-double-right"></i>                                                      
              </a>
           </li>
        </ul> -->
        <div class="page-navigation">          
            <div class="pagenum" ng-if="!varModel.no_result_found">
              <pagination class="pagination" total-items="pagination.totalItems" items-per-page="pagination.itemsPerPage" ng-model="pagination.currentPage" max-size="pagination.maxPageSize" rotate="false" boundary-links="true" data-my-call-back="loadNext"></pagination>
            </div>    
        </div>  
    </div>
@endsection 

@section('footer_scripts') 

{!! CustomHelpers::combineCssJs(['js/magicscroll','js/magiczoomplus'],'js') !!}
<script type="text/javascript" src="{{ Config::get('constants.angular_libs_url').'angular.min.js' }}"></script>
<script src="{{ Config::get('constants.angular_front_url').'controller/frontend/CategoryListCtrl.js' }}"></script> 

@stop