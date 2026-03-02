@props(['producttype'])

<div class="d-flex justify-content-between align-items-center">
    <h2>ชนิดสินค้า</h2>
</div>
<div class="product-cate-slider">
    @foreach ($producttype??[] as $item)
    <div class="item-box rounded p-md-2 p-1">
        <a href="{{ $item->url }}">
            <div class="product-item-info">
                <div class="prod-img">
                    <img class="prod-img-display lazyload"
                         data-src="{{ $item->category_image }}"
                         src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3C/svg%3E"
                         alt="{{ $item->category_name }}"
                         width="100"
                         height="100">
                </div> 
                <div class="product-info">
                    <small class="link-product-name text-center fw-100">
                        {{ $item->category_name }}
                    </small> 
                </div>
            </div>
        </a> 
    </div>
    @endforeach
</div>

<style>
.prod-img-display {
    width: 100%;
    height: 100px;
    object-fit: cover;
    background: #f5f5f5;
}
</style>

<script>
// ใช้ IntersectionObserver สำหรับ lazy loading
document.addEventListener("DOMContentLoaded", function() {
    const lazyImages = [].slice.call(document.querySelectorAll("img.lazyload"));
    
    if ("IntersectionObserver" in window) {
        let lazyImageObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    let lazyImage = entry.target;
                    lazyImage.src = lazyImage.dataset.src;
                    lazyImage.classList.remove("lazyload");
                    lazyImageObserver.unobserve(lazyImage);
                }
            });
        });

        lazyImages.forEach(function(lazyImage) {
            lazyImageObserver.observe(lazyImage);
        });
    }
});
</script>