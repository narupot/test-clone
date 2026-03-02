<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>กำลังเปิดแอป...</title>
</head>
<body>
  <p>กำลังเปิดแอป สี่มุมเมือง กรุณารอสักครู่...</p>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      var params = new URLSearchParams(window.location.search);
      var path = params.get("path") || "";
      var deeplink = "simummuang://" + path;

      var userAgent = navigator.userAgent.toLowerCase();
      var isAndroid = /android/.test(userAgent);
      var isIOS = /iphone|ipad|ipod/.test(userAgent);

      if (isAndroid) {
        window.location = "intent://" + path + "#Intent;scheme=simummuang;package=com.smm.buyer.smm_buyer;end";
      } else if (isIOS) {
        setTimeout(function () {
          window.location = "https://apps.apple.com/th/app/id1607337228";
        }, 1500);
        window.location = deeplink;
      } else {
        // fallback เผื่อเปิดบน desktop หรือเบราว์เซอร์ที่ไม่รองรับ
        document.body.innerHTML += "<p>กรุณาเปิดหน้านี้ในโทรศัพท์มือถือเพื่อเข้าใช้งานแอป</p>";
      }
    });
  </script>
</body>
</html>
