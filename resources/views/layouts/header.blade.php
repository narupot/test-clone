<style>
    /* =======================================
       1. CORE VARIABLES & RESET
       ======================================= */
    :root {
        --mega-primary: #ED1B24;
        --mega-dark: #c4131b;
        --mega-light-bg: #fff5f5;
        --mega-text: #333;
        --mega-gray: #666;
        --mega-border: #f0f0f0;
    }

    .menu-wrap {
        background: #fff;
        border: none;
        box-shadow: none;
    }

    /* =======================================
       2. HEADER STYLES
       ======================================= */
    #header {
        margin-top: 0px;
        position: sticky;
        top: 0;
        z-index: 300;
        background-color: white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .logo img {
        height: 65px;
        width: auto;
        object-fit: contain;
    }

    /* Search Bar */
    .search-box {
        position: relative;
        width: 100%;
    }

    .search-box input {
        width: 100%;
        border-radius: 50px;
        padding: 0 50px 0 20px;
        height: 45px;
        border: 1px solid #ddd;
        background: #f9f9f9;
        transition: all 0.3s;
    }

    .search-box input:focus {
        background: #fff;
        border-color: var(--mega-primary);
        box-shadow: 0 0 0 3px rgba(237, 27, 36, 0.1);
    }

    .search-box .search-btn {
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        background: var(--mega-primary);
        color: #fff;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: 0.2s;
    }

    .search-box .search-btn:hover {
        background: var(--mega-dark);
    }

    .search-box .search-btn i {
        font-size: 14px;
    }

    /* Icons Group */
    .icon-group a {
        color: #555;
        text-decoration: none;
        transition: 0.2s;
        text-align: center;
    }

    .icon-group a:hover {
        color: var(--mega-primary);
    }

    .icon-group img {
        height: 24px;
        width: auto;
        margin-bottom: 3px;
        display: block;
        margin-left: auto;
        margin-right: auto;
    }

    .text-secondary {
        font-size: 12px;
        font-weight: 500;
        display: block;
    }

    /* Badges */
    .tot_prd_noti,
    .tot_pending_order_noti {
        background: var(--mega-primary);
        color: #fff;
        border: 2px solid #fff;
        width: 18px;
        height: 18px;
        font-size: 10px;
        line-height: 14px;
        top: -5px;
        right: 5px;
        border-radius: 50%;
        position: absolute;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
    }

    /* Hamburger (แก้ไข Z-Index และตำแหน่ง) */
    .hamburger {
        width: 28px;
        cursor: pointer;
        position: relative;
        z-index: 1002;
        /* อยู่เหนือทุกอย่าง */
        display: flex;
        flex-direction: column;
        justify-content: center;
        height: 30px;
    }

    .hamburger span {
        display: block;
        height: 3px;
        margin: 4px 0;
        background: #333;
        transition: 0.3s;
        border-radius: 3px;
        width: 100%;
    }

    .hamburger.active span:nth-child(1) {
        transform: rotate(45deg) translate(6px, 6px);
    }

    .hamburger.active span:nth-child(2) {
        opacity: 0;
    }

    .hamburger.active span:nth-child(3) {
        transform: rotate(-45deg) translate(6px, -6px);
    }

    /* =======================================
       3. MEGA MENU STYLES
       ======================================= */
    .mega-menu-bar-wrap {
        border-top: 1px solid #eee;
        border-bottom: 1px solid #eee;
        height: 50px;
    }

    .nav-bar-flex {
        display: flex;
        align-items: center;
        height: 100%;
    }

    .mega-menu-wrapper {
        position: relative;
        height: 100%;
        width: 240px;
        border-right: 1px solid #f0f0f0;
        margin-right: 20px;
    }

    .mega-menu-btn {
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: var(--mega-primary);
        font-weight: 700;
        font-size: 15px;
        cursor: pointer;
        padding: 0 15px;
        user-select: none;
        height: 100%;
        width: 100%;
        background: #fff;
        transition: background 0.2s;
    }

    .mega-menu-btn i {
        font-size: 18px;
    }

    .mega-menu-btn:hover {
        background: #fdfdfd;
    }

    .mega-panel {
        visibility: hidden;
        opacity: 0;
        transform: translateY(15px);
        transition: all 0.25s cubic-bezier(0.165, 0.84, 0.44, 1);
        position: absolute;
        left: 0;
        top: 100%;
        width: 1000px;
        height: 500px;
        background: #fff;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        display: flex;
        border-radius: 0 0 8px 8px;
        border: 1px solid #eee;
        border-top: none;
    }

    .mega-menu-wrapper:hover .mega-panel,
    .mega-menu-wrapper.show .mega-panel {
        visibility: visible;
        opacity: 1;
        transform: translateY(0);
    }

    /* Extra Menu */
    .extra-menu-list {
        display: flex;
        gap: 30px;
        align-items: center;
        flex: 1;
    }

    .extra-menu-item {
        color: #333;
        text-decoration: none;
        font-weight: 600;
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: 0.2s;
    }

    .extra-menu-item i {
        color: #888;
        font-size: 16px;
        transition: 0.2s;
    }

    .extra-menu-item:hover {
        color: var(--mega-primary);
        text-decoration: none;
    }

    .menu-highlight {
        color: var(--mega-primary);
    }

    .menu-highlight i {
        color: var(--mega-primary);
    }

    /* Left Menu */
    .left-menu {
        width: 260px;
        background: #fff;
        border-right: 1px solid #eee;
        margin: 0;
        padding: 10px 0;
        overflow-y: auto;
    }

    .left-menu li {
        padding: 12px 25px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-left: 3px solid transparent;
        transition: all 0.2s;
    }

    .left-menu li:hover,
    .left-menu li.active {
        background: var(--mega-light-bg);
        color: var(--mega-primary);
        font-weight: 600;
        border-left-color: var(--mega-primary);
    }

    .menu-badge {
        font-size: 11px;
        color: #999;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    /* Right Content */
    .right-content-wrapper {
        flex: 1;
        padding: 30px;
        overflow-y: auto;
        background: #fff;
    }

    .right-panel {
        display: none;
        width: 100%;
        animation: fadeIn 0.3s ease;
    }

    .right-panel.active {
        display: block;
    }

    .right-panel.active.layout-text-banner {
        display: flex;
        gap: 30px;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateX(10px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Text Layout */
    .text-layout-wrap {
        display: flex;
        width: 100%;
    }

    .text-area {
        flex: 1;
        padding-right: 30px;
    }

    .link-list-container {
        column-count: 3;
        column-gap: 40px;
    }

    .menu-group {
        break-inside: avoid;
        margin-bottom: 25px;
    }

    .group-header {
        font-weight: 700;
        font-size: 15px;
        color: #000;
        display: block;
        margin-bottom: 8px;
    }

    .sub-link {
        display: block;
        color: #666;
        font-size: 14px;
        margin-bottom: 6px;
        text-decoration: none;
        transition: 0.2s;
    }

    .sub-link:hover {
        color: var(--mega-primary);
        padding-left: 5px;
    }

    .see-all-btn {
        float: right;
        font-size: 13px;
        color: var(--mega-primary);
        font-weight: 600;
        text-decoration: none;
    }

    .mobile-top-bar,
    .mobile-footer {
        display: none;
    }

    /* =======================================
       4. MOBILE RESPONSIVE 
       ======================================= */
    @media (max-width: 991px) {
        .d-none.d-lg-block {
            display: none !important;
        }

        .logo img {
            height: 60px;
        }

        .extra-menu-list {
            display: none;
        }

        /* [แก้ไข] อย่าซ่อน Wrapper แต่ให้ซ่อนปุ่ม Desktop แทน */
        .mega-menu-wrapper {
            display: block;
            width: 100%;
            border: none;
            margin: 0;
            height: 0;
        }

        .mega-menu-btn {
            display: none;
        }

        .mega-menu-bar-wrap {
            height: 0 !important;
            border: none !important;
            padding: 0 !important;
            overflow: visible !important;
        }

        /* Mobile Search Bar */
        .search-box input {
            padding: 5px 55px 5px 15px;
        }

        .search-box .search-btn {
            width: 30px;
            height: 30px;
            right: 5px;
        }

        /* Mega Panel Mobile */
        .mega-panel {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw !important;
            height: 100vh;
            margin: 0;
            border: none;
            border-radius: 0;
            flex-direction: column;
            z-index: 99999;
            display: flex;
            background: #fff;
        }

        .mobile-top-bar {
            display: block;
            background: #fff;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .mobile-header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .mobile-header-row .menu-title {
            font-size: 20px;
            font-weight: 800;
            color: #333;
        }

        .mobile-header-row .close-btn {
            font-size: 28px;
            line-height: 1;
            color: #999;
            cursor: pointer;
        }

        .search-box-mobile {
            position: relative;
        }

        .search-box-mobile input {
            width: 100%;
            background: #f0f2f5;
            border: none;
            height: 45px;
            border-radius: 8px;
            padding-left: 45px;
            font-size: 14px;
        }

        .search-box-mobile i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
        }

        .left-menu {
            width: 100%;
            height: auto;
            border: none;
            padding: 0;
            background: #fff;
            margin: 0;
            flex-grow: 1;
        }

        .left-menu li {
            border-bottom: 1px solid #f5f5f5;
            padding: 18px 20px;
            font-size: 16px;
        }

        .left-menu li:last-child {
            border-bottom: none;
        }

        .left-menu li.active {
            background: #fff;
            color: #333;
        }

        .right-content-wrapper {
            display: none;
            padding: 0;
            background: #fff;
            flex-grow: 1;
        }

        .right-panel {
            padding: 20px;
            animation: none;
        }

        .link-list-container {
            column-count: 1;
        }

        .mobile-footer {
            display: block;
            margin-top: auto;
            background: #fff;
            padding: 0;
            border-top: 1px solid #eee;
            padding-bottom: 20px;
        }

        .service-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .service-menu li a {
            display: block;
            padding: 15px 20px;
            color: #555;
            text-decoration: none;
            font-size: 14px;
            border-bottom: 1px solid #f5f5f5;
        }

        /* Mobile Icon Bar */
        .mobile-icon-bar {
            display: flex !important;
            justify-content: space-around;
            align-items: center;
            padding: 8px 0;
            background: #fff;
            border-top: 1px solid #f0f0f0;
            margin-top: 10px;
        }

        .mobile-icon-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #666;
            font-size: 11px;
            position: relative;
            flex: 1;
        }

        .mobile-icon-item img {
            height: 22px;
            margin-bottom: 3px;
        }
    }

    @media (max-width: 575.98px) {
        .logo img {
            height: 60px;
            max-width: 120px;
        }

        .search-box input {
            height: 35px !important;
        }

        .search-box .search-btn {
            width: 25px;
            height: 25px;
            font-size: 12px;
        }
    }

    #closeMegaMobile {
        position: relative;
        z-index: 100000;
        cursor: pointer;
        padding: 10px !important;
        display: inline-block;
    }

    /* --- Extra Menu (Dropdown Style) --- */
    .extra-nav {
        display: flex;
        align-items: center;
        gap: 20px;
        margin: 0;
        padding: 0;
        list-style: none;
        height: 100%;
    }

    .extra-nav-item {
        position: relative;
        height: 100%;
        display: flex;
        align-items: center;
    }

    .extra-nav-link {
        color: #ed1b24;
        font-weight: 600;
        font-size: 15px;
        text-decoration: none;
        display: flex;
        align-items: center;
        padding: 10px 0;
        white-space: nowrap;
        transition: 0.2s;
    }

    .extra-nav-link:hover {
        color: var(--mega-primary);
        text-decoration: none;
    }

    .menu-highlight {
        color: var(--mega-primary) !important;
    }

    /* Dropdown Box */
    .extra-dropdown {
        visibility: hidden;
        opacity: 0;
        transform: translateY(10px);
        transition: all 0.2s ease;
        position: absolute;
        top: 100%;
        right: 0;
        /* ชิดขวา หรือเปลี่ยนเป็น left: 0 ถ้าอยากให้ชิดซ้าย */
        min-width: 200px;
        background: #fff;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        border: 1px solid #eee;
        border-top: 2px solid var(--mega-primary);
        /* เส้นสีแดงด้านบน */
        border-radius: 0 0 8px 8px;
        padding: 5px 0;
        z-index: 1100;
    }

    /* Show on Hover */
    .extra-nav-item:hover .extra-dropdown {
        visibility: visible;
        opacity: 1;
        transform: translateY(0);
    }

    /* Sub Items */
    .extra-sub-item {
        display: block;
        padding: 10px 20px;
        color: #555;
        font-size: 14px;
        text-decoration: none;
        border-bottom: 1px solid #f9f9f9;
        transition: 0.2s;
    }

    .extra-sub-item:last-child {
        border-bottom: none;
    }

    .extra-sub-item:hover {
        background: #fff5f5;
        color: var(--mega-primary);
        padding-left: 25px;
    }
</style>

<header id="header">

    @php
        $cart_count = 0;
        if (function_exists('getCartProduct')) {
            $cart_data = getCartProduct();
            $cart_count = $cart_data['cart_prd'] ?? 0;
        } elseif (class_exists('\App\Helpers\CustomHelpers')) {
            $cart_data = \App\Helpers\CustomHelpers::getCartProduct();
            $cart_count = $cart_data['cart_prd'] ?? 0;
        }

        $pending_count = 0;
        if (Auth::check()) {
            if (function_exists('getPendingOrderNoti')) {
                $pending_data = getPendingOrderNoti();
                $pending_count = $pending_data['pendingOrder'] ?? 0;
            } elseif (class_exists('\App\Helpers\CustomHelpers')) {
                $pending_data = \App\Helpers\CustomHelpers::getPendingOrderNoti();
                $pending_count = $pending_data['pendingOrder'] ?? 0;
            }
        }
    @endphp

    <div class="container">
        <div class="d-flex align-items-center header-top py-2">

            <div class="logo pr-2">
                <a href="{{ action('HomeController@index') }}" title="Smmarket">
                    <img src="{{ getSiteLogo('SITE_LOGO_HEADER') }}" alt="Logo">
                </a>
            </div>

            <div class="flex-grow-1 d-flex align-items-center">
                <form action="{{ action('ProductsController@search') }}" method="GET" id="searchForm"
                    class="w-100 position-relative">
                    <input type="hidden" name="searchtype" value="all">
                    <div class="search-box">
                        <input type="search" id="searchProduct" name="search" placeholder="ค้นหาสินค้า..."
                            autocomplete="off" value="{{request("search")}}">
                        <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                    </div>
                    <div id="search-history-dropdown" class="dropdown-menu w-100"></div>
                </form>
            </div>

            <div class="d-none d-lg-flex align-items-center ml-3 icon-group">
                <a href="{{ action('Checkout\CartController@shoppingCart') }}" class="position-relative px-2">
                    <span class="tot_prd_noti" style="display:none">{{ $cart_count ?? 0 }}</span>
                    <img src="images/basket.svg" alt="Cart"><span class="d-none d-md-block text-secondary">ตะกร้า</span>
                </a>
                @if (Auth::check())
                    <a href="{{ action('User\OrderController@pendingOrder') }}" class="position-relative px-2">
                        @if($pending_count > 0) <span class="tot_pending_order_noti">{{ $pending_count }}</span> @endif
                        <img src="images/payment.svg" alt="Payment"><span
                            class="d-none d-md-block text-secondary">รอชำระ</span>
                    </a>
                    <a href="javascript:void(0)" class="px-2 btn-buyer-chat">
                        <img src="images/chat.svg" alt="Chat"><span
                            class="d-none d-md-block text-secondary">คุยกับร้านค้า</span>
                    </a>
                    <div class="dropdown px-2 text-center">
                        <a href="#" role="button" data-toggle="dropdown"
                            class="d-flex flex-column align-items-center text-decoration-none">
                            <img src="{{ getUserImageUrl(Auth::user()->image) }}" style="height:24px; border-radius:50%;"
                                alt="User">
                            <div class="d-none d-md-block text-secondary">{{ Auth::user()->first_name }} <i
                                    class="fas fa-angle-down ml-1"></i></div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="{{ action('User\UserController@index') }}">โปรไฟล์</a>
                            @if (Auth::user()->user_type == 'seller')
                                <a href="{{ action('Seller\ProductController@index') }}/"
                                    class="dropdown-item @if (strpos($_SERVER['REQUEST_URI'], 'seller') !== false) active @endif"><i
                                        class="far fa-home"></i> @lang('customer.for_seller') </a>
                            @endif
                            <a class="dropdown-item" href="{{ action('Auth\LogoutController@logout') }}">ออกจากระบบ</a>
                        </div>
                    </div>
                @else
                    <div class="d-flex align-items-center ml-2">
                        <a href="{{ action('Auth\RegisterController@index') }}"
                            class="btn btn-sm btn-outline-danger mr-2 rounded-pill px-3">สมัครสมาชิก</a>
                        <a href="javascript:void(0);" data-toggle="modal" data-target="#loginModal"
                            class="btn btn-sm btn-danger rounded-pill px-3" style="color:#fff;">เข้าสู่ระบบ</a>
                    </div>
                @endif
            </div>

            <div class="d-lg-none ml-2">
                <div type="button" class="hamburger" id="mobileHamburger">
                    <span></span><span></span><span></span>
                </div>
            </div>

        </div>

        <div class="mobile-icon-bar d-lg-none">
            <a href="{{ action('Checkout\CartController@shoppingCart') }}" class="mobile-icon-item">
                @if($cart_count > 0) <span class="tot_prd_noti" style="top: -5px; right: 25%;">{{ $cart_count }}</span>
                @endif
                <img src="images/basket.svg" alt="Cart"><span>ตะกร้า</span>
            </a>
            @if (Auth::check())
                <a href="{{ action('User\OrderController@pendingOrder') }}" class="mobile-icon-item">
                    @if($pending_count > 0) <span class="tot_pending_order_noti"
                    style="top: -5px; right: 25%;">{{ $pending_count }}</span> @endif
                    <img src="images/payment.svg" alt="Payment"><span>รอชำระ</span>
                </a>
                <a href="javascript:void(0)" class="mobile-icon-item btn-buyer-chat">
                    <img src="images/chat.svg" alt="Chat"><span>แชท</span>
                </a>
                <div class="btn-group dropup mobile-icon-item" style="cursor: pointer;">
                    <a href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                        style="text-decoration:none; color:inherit; display:flex; flex-direction:column; align-items:center; width:100%;">
                        <img src="{{ getUserImageUrl(Auth::user()->image) }}"
                            style="height:22px; width:22px; border-radius:50%; object-fit:cover; margin-bottom: 2px;"
                            alt="User">
                        <span>บัญชี</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="{{ action('User\UserController@index') }}">โปรไฟล์</a>
                        <a class="dropdown-item"
                            href="{{ action('Checkout\TrackOrderController@trackOrderDetail') }}">ติดตามสถานะ</a>
                        @if (Auth::user()->user_type == 'seller')
                            <a href="{{ action('Seller\ProductController@index') }}/"
                                class="dropdown-item @if (strpos($_SERVER['REQUEST_URI'], 'seller') !== false) active @endif"><i
                                    class="far fa-home"></i> @lang('customer.for_seller') </a>
                        @endif
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger"
                            href="{{ action('Auth\LogoutController@logout') }}">ออกจากระบบ</a>
                    </div>
                </div>
            @else
                <a href="javascript:void(0);" data-toggle="modal" data-target="#loginModal" class="mobile-icon-item">
                    <i class="far fa-user-circle fa-lg mb-1"
                        style="color:#555; font-size:22px;"></i><span>เข้าสู่ระบบ</span>
                </a>
            @endif
        </div>

    </div>

    <div class="mega-menu-bar-wrap bg-white">
        <div class="container h-100">
            <div class="nav-bar-flex">
                <div class="mega-menu-wrapper" id="sideMenu">
                    <div class="mega-menu-btn">
                        <span><i class="fas fa-bars mr-2"></i> หมวดหมู่สินค้าทั้งหมด</span>
                        <i class="fas fa-chevron-down" style="font-size:10px; color:#ccc;"></i>
                    </div>
                    {!! \App\Helpers\CustomHelpers::getSimummuangMegaMenu(2) !!}
                </div>
                <div class="extra-menu-list pl-2">
                    {!! \App\Helpers\CustomHelpers::getExtraMenuFromDB(2) !!}
                </div>
            </div>
        </div>
    </div>
</header>

<script src="{{ Config::get('constants.js_url') . 'jquery-ui.min.js' }}"></script>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', () => {

        // =========================================
        // 1. MEGA MENU LOGIC (Robust Mobile Drill-down)
        // =========================================

        // ตัวแปรหลัก
        const sideMenu = document.getElementById('sideMenu');
        const mobileTrigger = document.getElementById('mobileHamburger');
        let searchTimer;

        // ฟังก์ชัน Reset หน้าจอกลับเป็นค่าเริ่มต้น (แสดงเมนูหลัก ซ่อนเมนูย่อย)
        function resetMobileView() {
            if (window.innerWidth > 991) return; // ไม่ทำใน Desktop

            const leftMenu = document.getElementById('leftMenu');
            const rightWrapper = document.getElementById('rightContentWrapper');
            const backBtn = document.getElementById('megaMenuBackBtn');
            const menuTitle = document.getElementById('megaMenuTitle');
            const mobileFooter = document.getElementById('mobileMegaFooter');

            if (leftMenu) leftMenu.style.display = 'block';
            if (rightWrapper) rightWrapper.style.display = 'none';
            if (backBtn) backBtn.style.display = 'none';
            if (menuTitle) menuTitle.innerText = "หมวดหมู่สินค้า";
            if (mobileFooter) mobileFooter.style.display = 'block';
        }

        // ฟังก์ชันเปิด/ปิดเมนู
        function toggleMenu(action) {
            if (!sideMenu) return;
            if (action === 'open') {
                sideMenu.classList.add('show');
                if (mobileTrigger) mobileTrigger.classList.add('active');
            } else {
                sideMenu.classList.remove('show');
                if (mobileTrigger) mobileTrigger.classList.remove('active');
                setTimeout(resetMobileView, 300); // รอเมนูปิดแล้วค่อยรีเซ็ต
            }
        }

        // --- Event Listeners (ดักจับการคลิกทั่วทั้งหน้า) ---
        document.addEventListener('click', function (e) {

            // 1. กดปุ่ม Hamburger -> เปิดเมนู
            if (e.target.closest('#mobileHamburger')) {
                toggleMenu('open');
                e.stopPropagation();
                return;
            }

            // 2. กดปุ่มปิด (X) -> ปิดเมนู
            if (e.target.closest('#closeMegaMobile')) {
                toggleMenu('close');
                setTimeout(() => location.reload(), 100);
                e.stopPropagation();
                return;
            }

            // 3. กดปุ่มย้อนกลับ (<) -> กลับหน้าเมนูหลัก
            if (e.target.closest('#megaMenuBackBtn')) {
                resetMobileView();
                e.stopPropagation();
                return;
            }

            // 4. กดรายการเมนูหลัก (Drill Down Logic) *** จุดสำคัญ ***
            const menuItem = e.target.closest('.mobile-drill-item');
            if (menuItem && window.innerWidth <= 991) {
                // ดึง ID และชื่อของหมวดหมู่ที่กด
                const targetId = menuItem.getAttribute('data-target');
                const targetTitle = menuItem.getAttribute('data-title');
                const targetPanel = document.getElementById(targetId);

                if (targetPanel) {
                    // ซ่อนเมนูซ้าย
                    const leftMenu = document.getElementById('leftMenu');
                    if (leftMenu) leftMenu.style.display = 'none';

                    // ซ่อน Footer
                    const mobileFooter = document.getElementById('mobileMegaFooter');
                    if (mobileFooter) mobileFooter.style.display = 'none';

                    // แสดง Wrapper ขวา
                    const rightWrapper = document.getElementById('rightContentWrapper');
                    if (rightWrapper) rightWrapper.style.display = 'block';

                    // ซ่อน Panel อื่นๆ ทั้งหมด แล้วโชว์ตัวที่เลือก
                    document.querySelectorAll('.right-panel').forEach(el => el.style.display = 'none');
                    targetPanel.style.display = 'block';

                    // เปลี่ยนชื่อหัวข้อ
                    const menuTitle = document.getElementById('megaMenuTitle');
                    if (menuTitle && targetTitle) menuTitle.innerText = targetTitle;

                    // โชว์ปุ่ม Back
                    const backBtn = document.getElementById('megaMenuBackBtn');
                    if (backBtn) backBtn.style.display = 'inline-block';
                }
                e.preventDefault();
                e.stopPropagation();
                return;
            }
        });

        // Desktop Hover Effect (คงเดิม)
        const leftListItems = document.querySelectorAll('.left-menu li');
        leftListItems.forEach(item => {
            item.addEventListener('mouseenter', () => {
                if (window.innerWidth > 991) {
                    document.querySelectorAll('.left-menu li.active')?.forEach(el => el.classList.remove('active'));
                    document.querySelectorAll('.right-panel.active')?.forEach(el => el.classList.remove('active'));
                    item.classList.add('active');
                    const targetId = item.getAttribute('data-target');
                    const targetPanel = document.getElementById(targetId);
                    if (targetPanel) targetPanel.classList.add('active');
                }
            });
        });


        // =========================================
        // 2. SEARCH LOGIC
        // =========================================
        const searchInput = document.getElementById("searchProduct");
        const clearBtn = document.getElementById("clearSearch");
        const dropdown = document.getElementById("search-history-dropdown");
        const searchForm = document.getElementById('searchForm');

        // Fix jQuery Autocomplete Conflict
        if (window.jQuery && jQuery.fn.autocomplete) {
            var $searchProduct = jQuery("#searchProduct");
            if ($searchProduct.data('ui-autocomplete')) {
                $searchProduct.autocomplete("destroy");
            }
        }

        function getHistory() { return JSON.parse(localStorage.getItem("searchHistory") || "[]"); }
        function saveHistory(keyword) {
            let history = getHistory();
            history = history.filter(item => item !== keyword);
            history.unshift(keyword);
            if (history.length > 10) history = history.slice(0, 10);
            localStorage.setItem("searchHistory", JSON.stringify(history));
        }

        function renderCombinedDropdown() {
            if (!dropdown) return;
            const history = getHistory();
            let html = '';
            if (history.length > 0) {
                html = `<div class="dropdown-section history-section">
                            <div class="dropdown-header text-muted px-3 py-2">ประวัติการค้นหา</div>
                            <div class="dropdown-body">
                                ${history.map(item => `
                                    <div class="dropdown-item d-flex justify-content-between align-items-center history-item px-3 py-2">
                                        <span class="search-item text-dark" style="cursor:pointer; flex-grow:1;">
                                            <i class="fas fa-history text-muted me-2"></i> ${item}
                                        </span>
                                        <span class="text-muted remove-item ms-3" data-keyword="${item}" style="cursor:pointer;">
                                            <i class="fas fa-times"></i>
                                        </span>
                                    </div>`).join("")}
                            </div>
                        </div>`;
            } else {
                html = `<div class="dropdown-item text-center text-muted py-2">ไม่มีประวัติการค้นหา</div>`;
            }
            dropdown.innerHTML = html;
            dropdown.classList.add('show');
        }

        async function fetchTags(keyword) {
            if (!window.Laravel || !dropdown) return;
            try {
                const res = await fetch(`/search-tags?keyword=${encodeURIComponent(keyword)}`, {
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.Laravel.csrfToken }
                });
                const data = await res.json();
                if (data && data.length > 0) {
                    dropdown.innerHTML = `<div class="dropdown-header text-muted px-3 py-2 border-bottom">คำแนะนำ</div>` +
                        data.map(tag => `<div class="dropdown-item search-item px-3 py-2" style="cursor:pointer;">${tag}</div>`).join("");
                    dropdown.classList.add('show');
                }
            } catch (e) { console.error(e); }
        }

        // Search Events
        if (searchInput) {
            searchInput.addEventListener("input", () => {
                const keyword = searchInput.value.trim();
                
                if (clearBtn) clearBtn.style.display = keyword ? "block" : "none";
                clearTimeout(searchTimer);

                if (keyword.length >= 3) {
                    
                    searchTimer = setTimeout(() => {
                        fetchTags(keyword);
                    }, 500);
                } else {
                    renderCombinedDropdown();
                }
            });

            searchInput.addEventListener("focus", () => {
                const keyword = searchInput.value.trim();
                keyword.length >= 3 ? fetchTags(keyword) : renderCombinedDropdown();
            });

            searchInput.addEventListener("keydown", e => {
                if (e.key === "Enter" && searchInput.value.trim()) {
                    e.preventDefault();
                    clearTimeout(searchTimer); // ยกเลิกการค้นหาที่กำลังหน่วงอยู่
                    saveHistory(searchInput.value.trim());
                    dropdown.classList.remove('show');
                    if (searchForm) searchForm.submit();
                }
            });
        }

        if (clearBtn) {
            clearBtn.addEventListener("click", () => {
                searchInput.value = "";
                clearBtn.style.display = "none";
                renderCombinedDropdown();
                searchInput.focus();
            });
        }

        // Dropdown Click (Delegation included in document click above, but specific for dropdown logic)
        if (dropdown) {
            dropdown.addEventListener("click", e => {
                const target = e.target.closest(".search-item") || e.target.closest(".remove-item");
                if (!target) return;

                if (target.classList.contains("search-item")) {
                    const val = target.innerText.trim();
                    searchInput.value = val;
                    saveHistory(val);
                    dropdown.classList.remove('show');
                    if (searchForm) searchForm.submit();
                } else if (target.classList.contains("remove-item")) {
                    e.stopPropagation();
                    let history = getHistory().filter(i => i !== target.dataset.keyword);
                    localStorage.setItem("searchHistory", JSON.stringify(history));
                    renderCombinedDropdown();
                }
            });
        }

        // Close dropdown when clicking outside
        document.addEventListener("click", e => {
            if (searchInput && dropdown) {
                if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.remove('show');
                }
            }
        });

        function toggleCartNoti() {
            let count = parseInt($('.tot_prd_noti').text()) || 0;

            if (count > 0) {
                $('.tot_prd_noti').show();
            } else {
                $('.tot_prd_noti').hide();
            }
        }
        $(document).ready(toggleCartNoti);

    });
</script>

@if (Auth::guest() && (!isset($page) || ($page != 'login' && $page != 'register')))
    @include('includes.login_register_popup')
@endif