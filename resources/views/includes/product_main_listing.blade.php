{{-- <div class="category-products" ng-if="!varModel.no_result_found" >
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
    
</div> --}}

{{-- 
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
    
</div> --}}






{{-- <input type="hidden" name="searchUrl" value="{{$page =='category' ? action('ProductController@category') : ($page =='categorys'?action('ProductController@categorys') : '') }}"> --}}

<div class="">
    <div class="row pt-4">
        <h1>ชนิดสินค้า  <span class="text-danger">"{{('search')}}"</span></h1>

        @if (isset($product_type) && count($product_type) > 1)
        <div class="col-12 mb-5">
            <x-product-type-card :producttype="$product_type" />
        </div>
        @endif

        @if (isset($shop_list) && count($shop_list) > 0)
        
        <div class="col-12 mb-5">
            <x-shop-card :shoplist="$shop_list" />
        </div>

        @endif

    </div>


    <div class="row">
        @if (isset($product_list) && $product_list->total()>0)
        
        <div class="col-12 mb-3">
            <h2>ผลการค้นหา <span class="text-danger">"{{request('search')}}"</span></h2>
            <div>
                <span>{{$product_list->total()??0}} รายการ</span>
                {{-- <div>
                    <label for=""></label>
                    <select name="" id="">
                        <option value="">รายการแนะนำ</option>
                        <option value=""></option>
                        <option value=""></option>
                    </select>
                </div> --}}
            </div>
        </div>

        <div class="col-lg-3 filter-field  mb-4">
                <x-product-filter :productsize="$product_size" :productgrade="$product_grade" />
        </div>
        <div class="col-lg-9">
            <div class="product_list_warpper row ml-lg-1">

                    @foreach ($product_list ?? [] as $product)
                    <x-product-card :product="$product" :row="4" />
                    @endforeach

            </div>
            <div>
                @if ($product_list??false)                        
                {!! $product_list->links('components.pagination') !!}
                @endif
                
            </div>
            
        </div>

        
        @else
            {{-- <div class="text-center p-5">
                {!!getStaticBlock('no-item')!!}
            </div> --}}
            <x-not-found />
        @endif
        
    </div>




</div>