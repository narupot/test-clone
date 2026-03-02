<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>กำลังโหลด...</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<p>กำลังเปิดแอป...</p>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        var ua = navigator.userAgent || navigator.vendor || window.opera;

        // fallback URL เมื่อไม่มีแอป
        var fallbackUrl = "https://www.simumuangonline.com";

        // Android → ใช้ intent link
        if (/android/i.test(ua)) {
            window.location = "intent://home#Intent;scheme=https;package=com.smm.buyer.smm_buyer;end;";
            //window.location = "intent://home#Intent;action=android.intent.action.VIEW;category=android.intent.category.DEFAULT;scheme=https;package=com.smm.buyer.smm_buyer;S.browser_fallback_url=https://www.simumuangonline.com;end;";
            //window.location = "www.simummuangonline.com";
            setTimeout(function () {
                window.location = fallbackUrl;
            }, 2000);

        // iOS → ใช้ universal link
        } else if (/iPad|iPhone|iPod/.test(ua) && !window.MSStream) {
            // ลิงก์ universal link ที่กำหนดใน associated domains
            window.location = "simumuangonline.com";
            setTimeout(function () {
                window.location = fallbackUrl;
            }, 2000);
        } else {
            // Desktop หรืออุปกรณ์ไม่รู้จัก
            window.location = fallbackUrl;
        }
    });
</script>
</body>
</html>
