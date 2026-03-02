<!DOCTYPE html>
<html>
<head>
    <title>กำลังเปิดแอป...</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>
        window.onload = function () {
            var now = Date.now();
            var appScheme = "{{ $appScheme }}";
            var appStoreUrl = "{{ $appStoreUrl }}";
            var playStoreUrl = "{{ $playStoreUrl }}";
            var webUrl = "{{ $webUrl }}";
            var isAndroid = /android/i.test(navigator.userAgent);
            var isIOS = /iphone|ipad|ipod/i.test(navigator.userAgent);

            // ลองเปิดแอป
            window.location = appScheme;

            // ถ้าไม่ได้ภายใน 2 วินาที ➜ ไป Store
            setTimeout(function () {
                var elapsed = Date.now() - now;
                if (elapsed < 2500) {
                    if (isAndroid) {
                        window.location = playStoreUrl;
                    } else if (isIOS) {
                        window.location = appStoreUrl;
                    } else {
                        window.location = webUrl;
                    }
                }
            }, 1500);
        }
    </script>
</head>
<body>
    <p>กำลังพาคุณไปยังแอป Si Mum Muang Online...</p>
</body>
</html>
