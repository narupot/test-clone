<div class="category-products" ng-if="!varModel.no_result_found" >
    <div class="toolbar border-bottom-0">
        <div class="title-bg-red"><span>@lang('product.products')</span></div>
        <!--div class="view-mode" ng-show="show_layout">
            <a href="javascript:void(0);" class="grid" ng-class="{'active': productLayoutView == 'grid'}" ng-click="prdLayoutManage('grid')">
                <i class="fas fa-th-large"></i>
            </a>
            <a href="javascript:void(0);" class="list" ng-class="{'active': productLayoutView == 'list'}" ng-click="prdLayoutManage('list')">
                <i class="fas fa-th-list"></i>
            </a>
        </div-->
    </div>
    <!-- product layout -->
    <div ng-switch on="productLayoutView">
        <div ng-switch-when="grid">
            <!-- product grid view -->
            <div class="product-grid-view grid-bdr">
                <ul class="thumb-grid-view row">
                    <li class="col-sm-4 <%list_class%>"  data-ng-repeat="item in product_Items">
                        <div class="product-container">
                            <div class="prod-img">
                                <a href="<%item.url%>">
                                    <img ng-src="<%loader.img_load%>"  data-original="<%item.image%>" jq-lazy>
                                </a>
                            </div>                                
                            <div class="product-info">
                                <a href="<%item.url%>">
                                    <h3 class="product-name ng-binding" ng-bind="item.name"><%item.name%></h3>
                                </a>    
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!-- pagination -->
    <!--div class="page-navigation">
        <div class="pagenum" ng-if="!varModel.no_result_found">
          <pagination class="pagination" total-items="pagination.totalItems" items-per-page="pagination.itemsPerPage" ng-model="pagination.currentPage" max-size="pagination.maxPageSize" rotate="false" boundary-links="true" data-my-call-back="loadNext"></pagination>
        </div>    
    </div--> 
    
</div>

<div class="category-products" ng-if="!varModel.no_result_found1" >
    <div class="toolbar border-bottom-0">
        <div class="title-bg-red"><span>@lang('product.shops')</span></div>
        <!--div class="view-mode" ng-show="show_layout">
            <a href="javascript:void(0);" class="grid" ng-class="{'active': productLayoutView == 'grid'}" ng-click="prdLayoutManage('grid')">
                <i class="fas fa-th-large"></i>
            </a>
            <a href="javascript:void(0);" class="list" ng-class="{'active': productLayoutView == 'list'}" ng-click="prdLayoutManage('list')">
                <i class="fas fa-th-list"></i>
            </a>
        </div-->
    </div>
    <!-- product layout -->
    <div ng-switch on="productLayoutView">
        <div ng-switch-when="grid">
            <!-- product grid view -->
            <div class="product-grid-view grid-bdr">
                <ul class="thumb-grid-view row">
                    <li class="col-sm-4 <%list_class%>"  data-ng-repeat="item in shop_Items">
                        <div class="product-container">
                            <div class="prod-img">
                                <a href="<%item.url%>">
                                    <img ng-src="<%loader.img_load%>"  data-original="<%item.image%>" jq-lazy>
                                </a>
                            </div>                                
                            <div class="product-info">
                                <a href="<%item.url%>">
                                    <h3 class="product-name ng-binding" ng-bind="item.name"><%item.name%></h3>
                                </a>    
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
</div>