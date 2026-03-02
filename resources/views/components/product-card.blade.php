
@props(['product','row'])
    
<div class="item-box
col-lg-{{$row == 4?'3':($row == 6?'2':'4')}}
col-md-{{$row == 4?'4':($row == 6?'3':'4')}}
col-6
px-2 mb-4"
>

{{-- @if(Auth::check())
<pre>{{$product}}</pre>
    @if (!$product->in_wishlist)
        <div class="addto-link" ng-click='addToWishlist($event, @json($product))'>
            <a href="javascript:void(0)"><i class="fas fa-heart"></i></a>
        </div>
    @else
        <div class="addto-link" ng-click='removeFromWishlist($event, @json($product), $index)'>
            <a href="javascript:void(0);" class="active"><i class="fas fa-heart"></i></a>
        </div>
    @endif
@endif --}}

    <a href="{{route('product.detail', ['cat_url'=>$product->category->url,'sku'=>$product->sku])}}" class="text-decoration-none">
        
        <div class="product-item-info">

            <div class="prod-img text-center">
                <div class="prod-img-display" 
                    style="background:url('{{ $product->product_image??'' }}') center center / cover no-repeat;">
                </div>

            </div>
            
            <div class="prod-desc">
                @if ($product->badge_img)
                <img src="{{ $product->badge_img }}" alt=""/>
                @endif
            </div>

            <div class="product-info">
                <div class="d-block link-product-name">
                    {{ $product->cate_name ?? '' }}
                </div>
                <div class="d-block shop-name">
                    <small>{{ $product->shop_name ?? '' }}</small>
                </div>
                <div class="price-wrap">
                    <div class="price-label mb-1">
                        {{ number_format($product->weight_per_unit??0,2) }}
                        {{ $product->unit_name }}
                        @if ($product->unit_name && $product->package_name)
                             /
                        @endif
                         {{ $product->package_name }}
                    </div>
                    <div class="price-label mb-1"> ราคาปัจจุบัน </div>

                    <div class="normal-price text-white h5 px-3 py-1 bg-danger rounded mb-2 d-flex align-items-center justify-content-between">
                        <strong>฿{{ number_format($product->unit_price??0,2) }}</strong>
                        <i class="fa-solid fa-cart-shopping float-right"></i>
                    </div>
                </div>
            </div>
        </div>
    </a>
</div>
