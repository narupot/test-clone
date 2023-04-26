<div class="category-products" ng-if="!varModel.no_result_found" >
    <div class="toolbar border-bottom-0">
        <div class="title-bg-red" ng-if="cate_data.category_name"><span ng-bind="cate_data.category_name"></span></div>
        <div class="view-mode" ng-show="show_layout">
            <a href="javascript:void(0);" class="grid" ng-class="{'active': productLayoutView == 'grid'}" ng-click="prdLayoutManage('grid')">
                <i class="fas fa-th-large"></i>
            </a>
            <a href="javascript:void(0);" class="list" ng-class="{'active': productLayoutView == 'list'}" ng-click="prdLayoutManage('list')">
                <i class="fas fa-th-list"></i>
            </a>
        </div>
    </div>
    <!-- product layout -->
    <div ng-switch on="productLayoutView">
        <div ng-switch-when="grid">
            <!-- product grid view -->
            <div class="product-grid-view grid-bdr">
                <ul class="thumb-grid-view row">
                    <li class="col-sm-4 <%list_class%>"  data-ng-repeat="item in product_Items | limitTo:pagination.itemsPerPage">
                        <div class="product-container">
                            <div class="prod-img">
                                <a href="<%item.url%>">
                                    <img ng-src="<%loader.img_load%>"  data-original="<%item.thumbnail_image%>" jq-lazy>
                                </a>
                                @if(Auth::check())
                                    <div class="addto-link" ng-if="!item.in_wishlist"  ng-click="addToWishlist($event, item)">
                                        <a href="javascript:void(0)"><i class="fas fa-heart"></i></a>
                                    </div>
                                    <div class="addto-link" ng-if="item.in_wishlist"  ng-click="removeFromWishlist($event, item, $index)">
                                        <a href="javascript:void(0);" class="active"><i class="fas fa-heart"></i></a>
                                    </div>
                                @endif
                            </div>                                
                            <div class="product-info">
                                @if(Auth::check())
                                    <!-- <span class="chat-wrap btn-buyer-chat" data-val="<%item._id%>" ng-if="item.show_price.toString()=='1'">
                                        <a href="javascript:void(0);"><i class="fas fa-comments"></i></a>
                                    </span>    -->                                
                                @else 
                                    <!-- <span class="chat-wrap" ng-if="item.show_price.toString()=='1'">
                                        <a href="javascript:void(0);" data-toggle="modal" data-target="#loginModal" class="btn-default chat"><i class="fas fa-comments"></i></a>
                                    </span>   -->                                  
                                @endif 
                                <h3 class="product-name" ng-bind="item.category.category_name"></h3>
                                <div class="review-star">
                                  <div class="grey-stars"></div>
                                  <div class="filled-stars" style="width: <% item.avg_star*20%>%"></div>
                               </div>
                               <div class="shop-name">
                                    <a ng-href="<%item.shop.shop_url%>" ng-bind="item.shop.shop_name"></a>
                               </div>
                               <div class="price-wrap" ng-if="item.show_price == 1">
                                    <div class="price"><%item.weight_per_unit%> <%item.unit_name%>/<%item.package_name%> <br><%item.unit_price%> @lang('common.baht')</div>
                               </div>
                               <div class="price-wrap" ng-if="item.show_price == 0">
                                    @lang('product.ask_product_price_to_seller')
                               </div>
                               <div class="prod-standard">
                                    <!-- <span>@lang('product.product_standard')</span> -->
                                     <span class="la">
                                        <img ng-src="{{Config::get('constants.standard_badge_url')}}<%item.badge.icon%>" />
                                    </span>
                                    <!-- <span class="size">@lang('product.badge_size') : <%item.badge.size%></span> -->
                                    <!-- <span class="quality">@lang('product.badge_quality') : <%item.badge.grade%></span> -->
                               </div>
                               @if(Auth::check())
                                   <div class="add-shippinglist d-none" ng-if="((item.show_price.toString()=='1' && item.stock == 1) || (item.show_price.toString()=='1' && item.stock == 0 && item.quantity>=1))">
                                        <a href="javascript:void(0);" data-url="<%item.shopping_url%>" ng-click="addToShoppinglistHandler($event, item)" ><i class="fas fa-pencil-alt"></i>  + @lang('product.add_to_shopping_list')</a>
                                   </div>
                                   <div class="action-btn" ng-if="((item.show_price.toString()=='1' && item.stock == 1) || (item.show_price.toString()=='1' && item.stock == 0 && item.quantity>=1))">
                                        <a class="btn-default btn-buyer-chat" data-val="<%item._id%>" ng-if="((item.show_price.toString()=='1' && item.stock == 1) || (item.show_price.toString()=='1' && item.stock == 0 && item.quantity>=1))"  href="javascript:;" qty="<%item.order_qty_limit>0?'1':item.min_order_qty%>" rel="<%item._id%>"><i class="fas fa-comments"></i> @lang('product.talk_to_shop')</a>
                                        <a href="javascript:void(0);" data-url="<%item.url%>" class="btn-blue" ng-click="addToCartHandler($event,'addtocart', item)" ng-disabled="loader.disableBtn">@lang('product.add_to_cart')</a>
                                        <a href="javascript:void(0);" data-url="<%item.url%>" class="btn d-none" ng-click="addToCartHandler($event,'buynow', item)" ng-disabled="loader.disableBtn">@lang('product.buy_now')</a>
                                   </div>
                                   <span ng-if="item.show_price.toString()=='1' && item.stock == 0 && item.quantity < 1" class="red"> @lang('product.out_of_stock')</span>
                                    <span class="action-btn" ng-if="item.show_price.toString()=='0'">
                                        <a href="javascript:void(0);" class="btn-grey"><i class="fas fa-comments"></i> @lang('product.quote')</a>
                                    </span> 
                                @else
                                    <div class="add-shippinglist d-none" ng-if="((item.show_price.toString()=='1' && item.stock == 1) || (item.show_price.toString()=='1' && item.stock == 0 && item.quantity>=1))">
                                        <a href="#" data-toggle="modal" data-target="#loginModal" class="addshop-link">
                                            <i class="fas fa-pencil-alt"></i> 
                                            <span>+ @lang('product.add_to_shopping_list')</span>
                                        </a>                            
                                    </div>
                                    <div class="action-btn" ng-if="((item.show_price.toString()=='1' && item.stock == 1) || (item.show_price.toString()=='1' && item.stock == 0 && item.quantity>=1))">
                                        <a href="javascript:void(0);" data-toggle="modal" data-target="#loginModal" class="btn-default"><i class="fas fa-comments"></i> @lang('product.talk_to_shop')</a>
                                        <a href="javascript:void(0);" data-toggle="modal" data-target="#loginModal" class="btn-blue">@lang('product.add_to_cart')</a>
                                        <a href="javascript:void(0);" data-toggle="modal" data-target="#loginModal" class="btn d-none">@lang('product.buy_now')</a>
                                    </div>
                                    <span ng-if="item.show_price.toString()=='1' && item.stock == 0 && item.quantity < 1" class="red"> @lang('product.out_of_stock')</span>
                                    <span class="action-btn" ng-if="item.show_price.toString()=='0'">
                                        <a href="javascript:void(0);" data-toggle="modal" data-target="#loginModal" class="btn-grey"><i class="fas fa-comments"></i> @lang('product.quote')</a>
                                    </span>
                                @endif
                                <!-- product sold out -->
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <div ng-switch-when="list">
            <!-- product list view -->
            <div class="table-responsive">
                <div class="table-productlist">
                    <div class="table">
                        <div class="table-header">
                            <ul>
                                <li>@lang('product.product')</li>                                
                                <li>
                                    <span class="text-standard">@lang('product.product_standard')</span>
                                </li>
                                <li>@lang('product.price')</li>                              
                            </ul>
                        </div>
                        <div class="table-content">
                            <ul data-ng-repeat="item in product_Items | limitTo:pagination.itemsPerPage">
                                <li>
                                    <div class="product">
                                        <a href="<%item.url%>">
                                        <div class="prod-img">
                                            <img ng-src="<%loader.img_load%>"  data-original="<%item.thumbnail_image%>" jq-lazy>
                                            @if(Auth::check())
                                                <div class="addto-link" ng-if="!item.in_wishlist"  ng-click="addToWishlist($event, item)">
                                                    <a href="javascript:void(0);"><i class="fas fa-heart"></i></a>
                                                </div>
                                                <div class="addto-link" ng-if="item.in_wishlist"  ng-click="removeFromWishlist($event, item, $index)">
                                                <a href="javascript:void(0);" class="active"><i class="fas fa-heart"></i></a>
                                                </div>
                                            @endif                                                                      
                                        </div>
                                        </a>
                                        <div class="product-info">
                                            <h3 class="product-name" ng-bind="item.category.category_name"></h3>
                                            <div class="product-review">
                                                <div class="review-star">
                                                  <div class="grey-stars"></div>
                                                  <div class="filled-stars" style="width: <% item.avg_star*20%>%"></div>
                                                </div>
                                            </div>
                                            <div class="shop-name">
                                                <a ng-href="<%item.shop.shop_url%>" ng-bind="item.shop.shop_name"></a>
                                            </div>
                                            {{--
                                            <div class="desc">
                                                <span class="d-block">Respon : 67%</span> 
                                                <span class="d-block"> @lang('product.last_update') : <%item.updated_at%></span>
                                                <span>Chat Respon in : 15 Min</span>
                                            </div> --}}
                                        </div>
                                    </div>                                      
                                </li>
                                <li class="prod-standard"> 
                                    <span class="la">
                                        <img ng-src="{{Config::get('constants.standard_badge_url')}}<%item.badge.icon%>" />
                                    </span>
                                    <span class="size" ng-if="item.badge.size">@lang('product.badge_size') : <%item.badge.size%></span>
                                    <span class="quality" ng-if="item.badge.grade">@lang('product.badge_quality') : <%item.badge.grade%></span>
                                </li>
                                <li>
                                    <div class="action-wrap">
                                        <div class="price-wrap" ng-if="item.show_price == 1">
                                            <span class="price-label"> ราคาปัจจุบัน </span>
                                            <div class="price"><%item.weight_per_unit%> <%item.unit_name%>/<%item.package_name%> <br><%item.unit_price%> @lang('common.baht')</div>
                                        </div>
                                        <div class="price-wrap" ng-if="item.show_price == 0">
                                            @lang('product.ask_product_price_to_seller')
                                       </div>
                                        @if(Auth::check())
                                           <!--  <div class="add-shippinglist" ng-if="((item.show_price.toString()=='1' && item.stock == 1) || (item.show_price.toString()=='1' && item.stock == 0 && item.quantity>=1))">
                                                <a href="javascript:void(0)">
                                                    <i class="fas fa-pencil-alt"></i> <span> + @lang('product.add_to_shopping_list')</span>
                                                </a>
                                            </div> -->
                                            <div class="add-shippinglist d-none" ng-if="((item.show_price.toString()=='1' && item.stock == 1) || (item.show_price.toString()=='1' && item.stock == 0 && item.quantity>=1))">
                                                <a href="javascript:void(0);" data-url="<%item.shopping_url%>" ng-click="addToShoppinglistHandler($event, item)" ><i class="fas fa-pencil-alt"></i>  + @lang('product.add_to_shopping_list')</a>
                                            </div>
                                            <div class="action-btn">                                 
                                                <!-- <span class="chat-wrap btn-buyer-chat" data-val="<%item._id%>" ng-if="item.show_price.toString()=='1' ">
                                                    <a href="javascript:void(0);" class="btn-default">
                                                        <i class="fas fa-comments"></i>
                                                    </a>  
                                                </span>  -->
                                                <span class="" ng-if="item.show_price.toString()=='0'">
                                                    <a class="btn-grey" href="javascript:void(0)"><i class="fas fa-comments"></i> @lang('product.quote')</a>
                                                </span>                                
                                                <a class="btn-default btn-buyer-chat" data-val="<%item._id%>" ng-if="((item.show_price.toString()=='1' && item.stock == 1) || (item.show_price.toString()=='1' && item.stock == 0 && item.quantity>=1))"  href="javascript:;" qty="<%item.order_qty_limit>0?'1':item.min_order_qty%>" rel="<%item._id%>"><i class="fas fa-comments"></i> @lang('product.talk_to_shop')</a>
                                                <a href="javascript:void(0);" data-url="<%item.url%>" class="btn-blue" ng-click="addToCartHandler($event,'addtocart', item)" ng-if="((item.show_price.toString()=='1' && item.stock == 1) || (item.show_price.toString()=='1' && item.stock == 0 && item.quantity>=1))" ng-disabled="loader.disableBtn">@lang('product.add_to_cart')</a>
                                                <a href="javascript:void(0);" data-url="<%item.url%>" ng-if="((item.show_price.toString()=='1' && item.stock == 1) || (item.show_price.toString()=='1' && item.stock == 0 && item.quantity>=1))" class="btn d-none" ng-click="addToCartHandler($event,'buynow', item)" ng-disabled="loader.disableBtn">@lang('product.buy_now')</a>
                                            </div> 
                                            <span ng-if="item.show_price.toString()=='1' && item.stock == 0 && item.quantity < 1" class="red"> @lang('product.out_of_stock')</span>
                                        @else
                                            <a href="#" data-toggle="modal" data-target="#loginModal" class="addshop-link" ng-if="((item.show_price.toString()=='1' && item.stock == 1) || (item.show_price.toString()=='1' && item.stock == 0 && item.quantity>=1))"><i class="fas fa-pencil-alt"></i> <span>+ @lang('product.add_to_shopping_list')</span></a>
                                            <div class="action-btn">
                                                <a href="javascript:void(0);" data-toggle="modal" data-target="#loginModal" class="btn-default chat" ng-if="item.show_price.toString()=='1'"><i class="fas fa-comments"></i></a>
                                                <a href="javascript:void(0)" data-toggle="modal" data-target="#loginModal" class="btn-default chat" ng-if="item.show_price.toString()=='0'">@lang('product.quote')</a>
                                                <a href="javascript:void(0);" data-toggle="modal" data-target="#loginModal" class="btn-default bargain" ng-if="((item.show_price.toString()=='1' && item.stock == 1) || (item.show_price.toString()=='1' && item.stock == 0 && item.quantity>=1))"><i class="fas fa-comments"></i> @lang('product.talk_to_shop')</a>
                                                <a href="javascript:void(0);" data-toggle="modal" data-target="#loginModal" class="btn-blue" ng-if="((item.show_price.toString()=='1' && item.stock == 1) || (item.show_price.toString()=='1' && item.stock == 0 && item.quantity>=1))">@lang('product.add_to_cart')</a>
                                                <a href="javascript:void(0);" data-toggle="modal" data-target="#loginModal" class="btn d-none" ng-if="((item.show_price.toString()=='1' && item.stock == 1) || (item.show_price.toString()=='1' && item.stock == 0 && item.quantity>=1))">@lang('product.buy_now')</a>
                                            </div>
                                            <span ng-if="item.show_price.toString()=='1' && item.stock == 0 && item.quantity < 1" class="red"> @lang('product.out_of_stock')</span>
                                        @endif                                         
                                   </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- pagination -->
    <div class="page-navigation">
        <div class="pagenum" ng-if="!varModel.no_result_found">
          <pagination class="pagination" total-items="pagination.totalItems" items-per-page="pagination.itemsPerPage" ng-model="pagination.currentPage" max-size="pagination.maxPageSize" rotate="false" boundary-links="true" data-my-call-back="loadNext"></pagination>
        </div>    
    </div> 
    <!-- add to cart popup for listing -->
    <div class="modal fade" tabindex="-1" id="add_to_cart_modal" role="dialog" aria-labelledby="addToCartModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document" add-modal-dir>
            <div class="modal-content">
                <div class="modal-header">
                    <span class="close fa fa-times" data-dismiss="modal"></span>
                </div>
                <div class="modal-body">
                    <div class="product-information text-left">
                        <div class="row">
                            <div class="col-sm-4">
                                <img src="" alt="" class="prd-image">
                            </div>
                            <div class="col-sm-8">
                                <h1 class="product-name"></h1>
                                <div class="product-review">
                                    <div class="review-star">
                                        <div class="grey-stars"></div>
                                        <div class="filled-stars"></div>
                                    </div>
                                </div>
                                <div class="prod-standard"> 
                                    <span class="la">
                                        <img src="" data-basepath="{{Config::get('constants.standard_badge_url')}}">
                                    </span>
                                    <!-- ngIf: item.badge.size -->
                                    <span class="size ng-binding ng-scope">
                                         @lang('product.badge_size')
                                         <label></label>
                                    </span>
                                    <span class="quality ng-binding ng-scope">
                                        @lang('product.badge_quality')
                                        <label></label>
                                    </span>
                                </div>
                                <div class="price-box">
                                    <span class="price"></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group prod-orderDetail-box text-left">
                            <label>@lang('product.qty') :</label>
                            <span class="spiner">
                                <span class="decrease fas fa-minus"></span>
                                <input type="number" class="spinNum">
                                <span class="increase fas fa-plus"></span>
                            </span>
                            <span class="qty-label show-unit"></span>
                        </div>
                        <div class="text-center">
                            <a href="javascript:void(0)" class="btn addtocart modalcartbuy">@lang('product.buy_now')</a>
                            <a href="javascript:void(0)" class="btn addtocart modalcartadd">@lang('product.add_to_cart')</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>