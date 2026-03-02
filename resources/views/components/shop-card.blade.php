@props(['shoplist'])

<div class="d-flex justify-content-between align-items-center">
    <h2>ร้านค้าที่เกี่ยวข้อง</h2>
    {{-- <a href="{{route('shop.index')}}" class="btn btn-sm px-2 "><small>ดูทั้งหมด</small></a> --}}
</div>
<div class="product-cate-slider shop">
    @foreach ($shoplist??[] as $item)
    <div class="item-box rounded p-1" >
        <a href="{{ $item->url }}?search={{request("search")}}" >
            <div class="product-item-info">
                <div class="prod-img">
                    <div class="prod-img-display shop mx-auto" style="background:url('{{ $item->logo??'' }}') center center / cover no-repeat;"></div>
                    {{-- <img class="prod-img-display" src="{{ $item->logo??"" }}" alt="" srcset=""> --}}
                </div> 
                <div class="product-info">
                    <small class="link-product-name text-center fw-100">
                        {{ $item->shop_name }}
                    </small> 
                </div>
            </div>
        </a> 
    </div>
    @endforeach
</div>