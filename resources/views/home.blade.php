@php($section_data = GeneralFunctions::sectionData(basename(request()->path())))  
@php(extract($section_data))
@extends('layouts.app') 

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/myaccount','css/bootstrap-select'],'css') !!}    
    <link rel="stylesheet" href="{{ Config('constants.css_url') }}chosen.min.css"/>
    <link rel="stylesheet" href="{{ Config('constants.css_url') }}chosenImage.css"/>
@endsection

@section('header_script')


@endsection

@section('content')




<div class="dropdown-list d-none">
    <select data-placeholder="Choose Bank List..." class="my-select" style="width:350px;" tabindex="2">
        <option data-img-src="https://via.placeholder.com/150/000000/FFFFFF">List One</option> 
        <option data-img-src="https://via.placeholder.com/300.png/09f/fff">List Two</option> 
    </select>
</div>


{{--<div class="content-wrap">
    <div class="container">
        
        <!-- Banner Start -->
        <div class="banner-home">
            <div class="banner-item">
                <a href="#"><img src="/images/banner/banner.jpg" alt=""></a>
            </div>
            <div class="banner-item">
                <a href="#"><img src="/images/banner/banner2.jpg" alt=""></a>
            </div>
            <div class="banner-item">
                <a href="#"><img src="/images/banner/banner3.jpg" alt=""></a>
            </div>
        </div>
        <!-- Recommended Start -->

        <h2 class="title-bg"><span>Recommended</span></h2>

        <!--  Recommned Slider  -->
        <div class="recomend-product">
            <ul class="recomend-slider main-carousel">
                <li>
                    <div class="product-item">
                        <div class="prod-img">
                            <a href="#"><img src="images/banner/prod-item.jpg" alt=""></a>
                        </div>
                        <div class="prod-info">
                            <h3 class="prod-name"><a href="#">ส้มโชกุน</a></h3>
                            <div class="grade">
                                <span class="grade-label">เกรด</span>
                                <span class="la">LA</span>
                            </div>
                            <div class="price-box">
                                <span class="price-label"> ราคาเริ่มต้นเฉลี่ย    </span>
                                <span class="price">55.00 บาท / กิโลกรัม</span>
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="product-item">
                        <div class="prod-img">
                            <a href="#"><img src="images/banner/prod-item2.jpg" alt=""></a>
                        </div>
                        <div class="prod-info">
                            <h3 class="prod-name"><a href="#">แอปเปิ้ล</a></h3>
                            <div class="grade">
                                <span class="grade-label">เกรด</span>
                                <span class="lb">LB</span>
                            </div>
                            <div class="price-box">
                                <span class="price-label"> ราคาเริ่มต้นเฉลี่ย    </span>
                                <span class="price">55.00 บาท / กิโลกรัม</span>
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="product-item">
                        <div class="prod-img">
                            <a href="#"><img src="images/banner/prod-item3.jpg" alt=""></a>
                        </div>
                        <div class="prod-info">
                            <h3 class="prod-name"><a href="#">สัปปะรด</a></h3>
                            <div class="grade">
                                <span class="grade-label">เกรด</span>
                                <span class="lb">LB</span>
                            </div>
                            <div class="price-box">
                                <span class="price-label"> ราคาเริ่มต้นเฉลี่ย    </span>
                                <span class="price">55.00 บาท / กิโลกรัม</span>
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="product-item">
                        <div class="prod-img">
                            <a href="#"><img src="images/banner/prod-item4.jpg" alt=""></a>
                        </div>
                        <div class="prod-info">
                            <h3 class="prod-name"><a href="#">แตงโม</a></h3>
                            <div class="grade">
                                <span class="grade-label">เกรด</span>
                                <span class="lc">LC</span>
                            </div>
                            <div class="price-box">
                                <span class="price-label"> ราคาเริ่มต้นเฉลี่ย    </span>
                                <span class="price">55.00 บาท / กิโลกรัม</span>
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="product-item">
                        <div class="prod-img">
                            <a href="#"><img src="images/banner/prod-item5.jpg" alt=""></a>
                        </div>
                        <div class="prod-info">
                            <h3 class="prod-name"><a href="#">ฟักทอง</a></h3>
                            <div class="grade">
                                <span class="grade-label">เกรด</span>
                                <span class="la">LA</span>
                            </div>
                            <div class="price-box">
                                <span class="price-label"> ราคาเริ่มต้นเฉลี่ย    </span>
                                <span class="price">55.00 บาท / กิโลกรัม</span>
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="product-item">
                        <div class="prod-img">
                            <a href="#"><img src="images/banner/prod-item6.jpg" alt=""></a>
                        </div>
                        <div class="prod-info">
                            <h3 class="prod-name"><a href="#">มะม่วง</a></h3>
                            <div class="grade">
                                <span class="grade-label">เกรด</span>
                                <span class="la">LA</span>
                            </div>
                            <div class="price-box">
                                <span class="price-label"> ราคาเริ่มต้นเฉลี่ย    </span>
                                <span class="price">55.00 บาท / กิโลกรัม</span>
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="product-item">
                        <div class="prod-img">
                            <a href="#"><img src="images/banner/prod-item7.jpg" alt=""></a>
                        </div>
                        <div class="prod-info">
                            <h3 class="prod-name"><a href="#">ส้มโชกุน</a></h3>
                            <div class="grade">
                                <span class="grade-label">เกรด</span>
                                <span class="lb">LB</span>
                            </div>
                            <div class="price-box">
                                <span class="price-label"> ราคาเริ่มต้นเฉลี่ย    </span>
                                <span class="price">55.00 บาท / กิโลกรัม</span>
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="product-item">
                        <div class="prod-img">
                            <a href="#"><img src="images/banner/prod-item8.jpg" alt=""></a>
                        </div>
                        <div class="prod-info">
                            <h3 class="prod-name"><a href="#">ส้มโชกุน</a></h3>
                            <div class="grade">
                                <span class="grade-label">เกรด</span>
                                <span class="lc">LC</span>
                            </div>
                            <div class="price-box">
                                <span class="price-label"> ราคาเริ่มต้นเฉลี่ย    </span>
                                <span class="price">55.00 บาท / กิโลกรัม</span>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
            
            
                <div class="banner-home-sm container">                    
                        <a href="#"><img src="images/banner/banner-small.jpg" title="" alt=""></a>                    
                </div>
            

            <ul class="product-slider main-carousel">
                <li>
                    <div class="product-item">
                        <div class="prod-img">
                            <a href="#"><img src="images/banner/prod-item.jpg" alt=""></a>
                        </div>
                        <div class="prod-info">
                            <h3 class="prod-name"><a href="#">ส้มโชกุน</a></h3>
                            <div class="grade">
                                <span class="grade-label">เกรด</span>
                                <span class="la">LA</span>
                            </div>
                            <div class="price-box">
                                <span class="price-label"> ราคาเริ่มต้นเฉลี่ย    </span>
                                <span class="price">55.00 บาท / กิโลกรัม</span>
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="product-item">
                        <div class="prod-img">
                            <a href="#"><img src="images/banner/prod-item8.jpg" alt=""></a>
                        </div>
                        <div class="prod-info">
                            <h3 class="prod-name"><a href="#">แอปเปิ้ล</a></h3>
                            <div class="grade">
                                <span class="grade-label">เกรด</span>
                                <span class="lb">LB</span>
                            </div>
                            <div class="price-box">
                                <span class="price-label"> ราคาเริ่มต้นเฉลี่ย    </span>
                                <span class="price">55.00 บาท / กิโลกรัม</span>
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="product-item">
                        <div class="prod-img">
                            <a href="#"><img src="images/banner/prod-item9.jpg" alt=""></a>
                        </div>
                        <div class="prod-info">
                            <h3 class="prod-name"><a href="#">สัปปะรด</a></h3>
                            <div class="grade">
                                <span class="grade-label">เกรด</span>
                                <span class="lb">LB</span>
                            </div>
                            <div class="price-box">
                                <span class="price-label"> ราคาเริ่มต้นเฉลี่ย    </span>
                                <span class="price">55.00 บาท / กิโลกรัม</span>
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="product-item">
                        <div class="prod-img">
                            <a href="#"><img src="images/banner/prod-item12.jpg" alt=""></a>
                        </div>
                        <div class="prod-info">
                            <h3 class="prod-name"><a href="#">แตงโม</a></h3>
                            <div class="grade">
                                <span class="grade-label">เกรด</span>
                                <span class="lc">LC</span>
                            </div>
                            <div class="price-box">
                                <span class="price-label"> ราคาเริ่มต้นเฉลี่ย    </span>
                                <span class="price">55.00 บาท / กิโลกรัม</span>
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="product-item">
                        <div class="prod-img">
                            <a href="#"><img src="images/banner/prod-item11.jpg" alt=""></a>
                        </div>
                        <div class="prod-info">
                            <h3 class="prod-name"><a href="#">ฟักทอง</a></h3>
                            <div class="grade">
                                <span class="grade-label">เกรด</span>
                                <span class="la">LA</span>
                            </div>
                            <div class="price-box">
                                <span class="price-label"> ราคาเริ่มต้นเฉลี่ย    </span>
                                <span class="price">55.00 บาท / กิโลกรัม</span>
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="product-item">
                        <div class="prod-img">
                            <a href="#"><img src="images/banner/prod-item.jpg" alt=""></a>
                        </div>
                        <div class="prod-info">
                            <h3 class="prod-name"><a href="#">มะม่วง</a></h3>
                            <div class="grade">
                                <span class="grade-label">เกรด</span>
                                <span class="la">LA</span>
                            </div>
                            <div class="price-box">
                                <span class="price-label"> ราคาเริ่มต้นเฉลี่ย    </span>
                                <span class="price">55.00 บาท / กิโลกรัม</span>
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="product-item">
                        <div class="prod-img">
                            <a href="#"><img src="images/banner/prod-item9.jpg" alt=""></a>
                        </div>
                        <div class="prod-info">
                            <h3 class="prod-name"><a href="#">ส้มโชกุน</a></h3>
                            <div class="grade">
                                <span class="grade-label">เกรด</span>
                                <span class="lb">LB</span>
                            </div>
                            <div class="price-box">
                                <span class="price-label"> ราคาเริ่มต้นเฉลี่ย    </span>
                                <span class="price">55.00 บาท / กิโลกรัม</span>
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="product-item">
                        <div class="prod-img">
                            <a href="#"><img src="images/banner/prod-item10.jpg" alt=""></a>
                        </div>
                        <div class="prod-info">
                            <h3 class="prod-name"><a href="#">ส้มโชกุน</a></h3>
                            <div class="grade">
                                <span class="grade-label">เกรด</span>
                                <span class="lc">LC</span>
                            </div>
                            <div class="price-box">
                                <span class="price-label"> ราคาเริ่มต้นเฉลี่ย    </span>
                                <span class="price">55.00 บาท / กิโลกรัม</span>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>

            <div class="view-all-wrap">
                <a href="#" class="view-all btn-grey">ดูทั้งหมด</a>
            </div>

        </div>

                
        
    </div>  

    <!--  About banner wrap -->

    <div class="about-banner-wrap">

        <div class="home-about-img">
            <div class="about-img">
                <img src="images/banner/z.jpg" alt="" class="about-banner-img">
                <div class="container">
                    <div class="about-banner-content">
                        <a href="#" class="about-logo"><img src="images/logo.png" alt="" width="100"></a>
                        <ul class="about-ship-list">
                            <li>
                                <a href="#">
                                    <i class="fas fa-stop"></i>
                                    <h3>สินค้าครบจบที่เดียว</h3>
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <i class="fas fa-thumbs-up"></i>
                                    <h3>รับรองคุณภาพสินค้า</h3>
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <i class="fas fa-truck"></i>
                                    <h3>บริการรับส่งสินค้า</h3>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
        </div>
        <div class="about-slider">
            <div class="carousel_wrap">
               <div id="carousel">
                    <div class="shadow">
                        <img src="images/shop/about3.jpg" alt="" id="item-3">
                        <div class="caption carous_item carous_item3">
                            <span class="carous_desc">Slider 1</span>
                        </div>
                    </div>
                    <div  class="shadow">
                        <img src="images/shop/about2.jpg" id="item-2" />
                        <div class="caption carous_item carous_item2">
                            <span class="carous_desc">Slider 2</span>
                        </div>
                    </div>
                    <div  class="shadow">                       
                        <img src="images/shop/about1.jpg" alt="" id="item-3">
                        <div class="caption carous_item carous_item7">
                            <span class="carous_desc">Slider 3</span>
                        </div>
                    </div>
                    <div>
                        <img src="images/shop/about4.jpg" id="item-4" />
                        <div class="caption carous_item carous_item1">
                           <span class="carous_desc">Slider 4</span>  
                        </div>
                    </div>
                    <div  class="shadow">
                        <img src="images/shop/about5.jpg" id="item-5" />
                        <div class="caption carous_item carous_item2">
                            <span class="carous_desc">Slider 4</span>
                        </div>
                    </div>          
               </div>
                <!-- <a id="prev" class="nav_button prev_button link pull-left" title="prev"><i class="fas fa-chevron-left"></i></a>
                <a id="next" class="nav_button next_button link pull-right" title="next"><i class="fas fa-chevron-right"></i></a> -->
            </div>
        </div>

        <div class="about container">
            <h2 class="about-title">About Us</h2>
            <p>เป็นตลาดกลางผักผลไม้ชั้นนำที่มีการบริหารจัดการที่ทันสมัย มุ่งมั่นในการบริการที่เป็นเลิศ ด้วยสินค้าที่ครบถ้วนปลอดภัย มีมาตรฐาน ด้วยกฎระเบียบการค้าที่ชัดเจน มีรูปแบบการซื้อขายที่รวดเร็วในราคาที่ยุติธรรม มีระบบข้อมูลข่าวสารที่เอื้อประโยชน์ต่อองค์กรและผู้ค้า มีความสะอาด ความสะดวกและความสงบเรียบเรียบร้อยเป็นโครงสร้างพื้นฐาน พร้อมทั้งมีส่วนร่วมในการบริหารจัดการห่วงโซ่คุณภาพสินค้าให้มีประสิทธิภาพและมีความรับผิดชอบต่อสังคม</p>
        </div>
    </div>

    <!-- Map Start -->

    <div class="map-wrap">
        <div class="mapouter">
            <div class="gmap_canvas">
                <!-- <iframe width="100%" height="450" id="gmap_canvas" src="https://maps.google.com/maps?q=smoothgraph%20Sathorn%2C%20Bangkok%2010120&t=&z=11&ie=UTF8&iwloc=&output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0">                        
                </iframe> -->   
                <iframe width="100%" height="450" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3871.944374168778!2d100.61631131483279!3d13.961916690211709!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTPCsDU3JzQyLjkiTiAxMDDCsDM3JzA2LjYiRQ!5e0!3m2!1sen!2sin!4v1549864143916" width="600" height="450" marginwidth="0" frameborder="0" allowfullscreen></iframe>             
            </div>
        </div>
    </div>


</div>--}}


@endsection 
@section('footer_scripts')
<script src="{{ Config('constants.js_url') }}chosen.jquery.min.js"></script>
<script src="{{ Config('constants.js_url') }}chosenImage.jquery.js"></script>

{!! CustomHelpers::combineCssJs(['js/flickity.pkgd.min', 'js/jquery.touchSwipe.min', 'js/TweenMax.min', 'js/slider3d'],'js') !!}

<!--  <script>
    jQuery('.banner').slick({
        arrows:false,
        dots:true,
        autoplay:true,
    });
</script> -->
<script type="text/javascript">


    $(".my-select").chosenImage({
      disable_search_threshold: 10 
    });

    $('.main-carousel').flickity({
      // options
      freeScroll: true,
      wrapAround: true,
      cellAlign: 'left',      
      pageDots: false
    });
</script>

@stop


