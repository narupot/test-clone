<!DOCTYPE html>
<html>
<head>
    <title>กำลังเปิดสินค้า...</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var productId = '{{ $id }}';
            var deepLink = 'simummuang://product/{{ $category }}/' + productId;
            //var intentLink = 'intent://product/{{ $category }}/' + productId + '#Intent;scheme=https;package=com.smm.buyer.smm_buyer;end;';
            // var intentLink = 'intent://www.simummuangonline.com/product/{{ $category }}/' + productId + '#Intent;package=com.smm.buyer.smm_buyer;action=android.intent.action.VIEW;scheme=https;end;';
            var intentLink = 'intent://www.simummuangonline.com/product/{{ $category }}/' + productId + '#Intent;scheme=https;package=com.smm.buyer.smm_buyer;end';
            //var intentLink = window.location = 'intent://www.simummuangonline.com/product/{{ $category }}/' + productId + '#Intent;action=android.intent.action.VIEW;category=android.intent.category.DEFAULT;scheme=https;package=com.smm.buyer.smm_buyer;S.browser_fallback_url=https://www.simumuangonline.com;end;';
            var fallback = 'https://www.simummuangonline.com/product/{{ $category }}/' + productId;

            var isAndroid = /Android/i.test(navigator.userAgent);
            var isIOS = /iPhone|iPad|iPod/i.test(navigator.userAgent);

            // สร้าง iframe ซ่อนเพื่อเปิด deep link
            if (isAndroid || isIOS) {
                window.location = intentLink;

                // ถ้าไม่ได้เปิดแอปภายใน 2 วินาที ให้ fallback ไปยังเว็บ
                setTimeout(function () {
                    window.location = fallback;
                }, 2000);
            } else {
                // ถ้าไม่ใช่มือถือ เปิดหน้าเว็บโดยตรง
                window.location = fallback;
            }
        });
    </script>
</head>
<body>
    <p>กำลังเปิดสินค้าในแอป...</p>
</body>
</html>
