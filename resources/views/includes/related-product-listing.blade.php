<div class="product-grid-view grid-bdr"> <!-- slick-slider-dir -->
    <ul class="row" slick-slider-dir>
        <li class="<%list_class%>"  data-ng-repeat="item in product_Items">
            <div class="product-container">
                <div class="prod-img">
                    <a href="<%item.url%>">
                        <!-- <img ng-src="<%loader.img_load%>"  data-original="<%item.thumbnail_image%>" jq-lazy> -->
                        <img ng-src="<%item.thumbnail_image%>"  data-original="<%item.thumbnail_image%>" jq-lazy>
                    </a>
                    @if(Auth::check())
                        <div class="addto-link" ng-if="!item.in_wishlist"  ng-click="addToWishlist($event, item)">
                            <a href="javascript:void(0)"><i class="fas fa-heart"></i></a>
                        </div>
                        <div class="addto-link" ng-if="item.in_wishlist"  ng-click="removeFromWishlist($event, item, $index)">
                            <a href="javascript:void(0)" class="active"><i class="fas fa-heart"></i></a>
                        </div>
                    @endif
                </div>                                
                <div class="product-info">
                    @if(Auth::check())
                        <span class="chat-wrap" ng-if="item.show_price.toString()=='1'">
                            <a href="javascript:void(0)"><i class="fas fa-comments"></i></a>
                        </span>                                   
                    @else 
                        <span class="chat-wrap" ng-if="item.show_price.toString()=='1'">
                            <a href="javascript:void(0);" data-toggle="modal" data-target="#loginModal" class="btn-default chat"><i class="fas fa-comments"></i></a>
                        </span>                                    
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
                        <div class="price"><%item.unit_price%> @lang('common.baht')/<%item.package_name%></div>
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
                       <!-- <div class="add-shippinglist" ng-if="((item.show_price.toString()=='1' && item.stock == 1) || (item.show_price.toString()=='1' && item.stock == 0 && item.quantity>=1))">
                            <a href="javascript:void(0)" data-url="<%item.shopping_url%>" ng-click="addToShoppinglistHandler($event, item)" ><i class="fas fa-pencil-alt"></i>  + @lang('product.add_to_shopping_list')</a>
                       </div> -->
                       <div class="action-btn" ng-if="((item.show_price.toString()=='1' && item.stock == 1) || (item.show_price.toString()=='1' && item.stock == 0 && item.quantity>=1))">
                            <a class="btn-default bargain" ng-if="((item.show_price.toString()=='1' && item.stock == 1) || (item.show_price.toString()=='1' && item.stock == 0 && item.quantity>=1))"  href="<%item.bargain_url%>" rel="<%item._id%>">@lang('product.bargain')</a>
                            <a href="javascript:void(0)" data-url="<%item.url%>" class="btn-blue" ng-click="addToCartHandler($event,'addtocart', item)" ng-disabled="loader.disableBtn">@lang('product.add_to_cart')</a>
                            {{-- <a href="javascript:void(0)" data-url="<%item.url%>" class="btn" ng-click="addToCartHandler($event,'buynow', item)" ng-disabled="loader.disableBtn">@lang('product.buy_now')</a> --}}
                       </div>
                        <span class="action-btn" ng-if="item.show_price.toString()=='0'">
                            <a href="javascript:void(0)" class="btn-grey">@lang('product.quote')</a>
                        </span> 
                    @else
                       <!--  <div class="add-shippinglist" ng-if="((item.show_price.toString()=='1' && item.stock == 1) || (item.show_price.toString()=='1' && item.stock == 0 && item.quantity>=1))">
                            <a href="#" data-toggle="modal" data-target="#loginModal" class="addshop-link">
                                <i class="fas fa-pencil-alt"></i> 
                                <span>+ @lang('product.add_to_shopping_list')</span>
                            </a>                            
                        </div> -->
                        <div class="action-btn" ng-if="((item.show_price.toString()=='1' && item.stock == 1) || (item.show_price.toString()=='1' && item.stock == 0 && item.quantity>=1))">
                            <a href="javascript:void(0);" data-toggle="modal" data-target="#loginModal" class="btn-default bargain">@lang('product.bargain')</a>
                            <a href="javascript:void(0);" data-toggle="modal" data-target="#loginModal" class="btn-blue">@lang('product.add_to_cart')</a>
                            {{-- <a href="javascript:void(0);" data-toggle="modal" data-target="#loginModal" class="btn">@lang('product.buy_now')</a> --}}
                        </div>
                        <span class="action-btn" ng-if="item.show_price.toString()=='0'">
                            <a href="javascript:void(0)" data-toggle="modal" data-target="#loginModal" class="btn-default chat">@lang('product.quote')</a>
                        </span>
                    @endif
                    <!-- product sold out -->
                </div>
            </div>
        </li>
    </ul>
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
                        <span class="qty-label">@lang('product.box')</span>
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
