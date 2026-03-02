jQuery(document).ready(function(){

	/* 1) For slide popup */
				
     jQuery(".nav-pills-custom .nav-item .nav-link").click(function(){
       jQuery(".nav-pills-custom .nav-item .nav-link").removeClass('active');
    });

    jQuery('.eyehvr-icon').click(function() {
       jQuery(this).find("i").toggleClass('icon-eye-hide icon-eye-show');
    });

    // For tooltip
    jQuery('[data-toggle="tooltip"]').tooltip(); 
	/* 2) slide popup */
	/*  For Currency */
	jQuery(".currency .dropdown-menu li a").click(function(){
        var selText = jQuery(this).html();
        jQuery(this).parents('.currency').find('.curr-name').html(selText);
    });


	/*  For Language */
    jQuery(".language .dropdown-menu li a").click(function(){
        var selText = jQuery(this).html();
        jQuery(this).parents('.language').find('.lang-name').html(selText);
    });


	/*  For Currency2 */
    jQuery(".currency2 .dropdown-menu li a").click(function(){
        var selText = jQuery(this).html();
        jQuery(this).parents('.currency2').find('.curr2-name').html(selText);
    });

    	/*  For Currency2 */
    jQuery(".otherlang a").click(function(){
    	jQuery('.otherlang a').removeClass('active');
        jQuery(this).addClass('active');
    });


    /*  For increment and decrement */
    

    jQuery('.increase').on('click',function(){
        var $qty=$(this).prev('.spinNum');  
        var currentVal = parseInt($qty.val());
        if (!isNaN(currentVal)) {
            $qty.val(currentVal + 1);
        }
    });
    jQuery('.decrease').on('click',function(){
        var $qty=$(this).next('.spinNum'); 
        var currentVal = parseInt($qty.val());
        if (!isNaN(currentVal) && currentVal > 0) {
            $qty.val(currentVal - 1);
        }
    });
	

    //thumb arrow move
     jQuery('.listing-drop h2 a').on('click',function(){     	
     	jQuery(this).find('.arrowtgl').toggleClass('open');          
        jQuery(this).parent('h2').next().slideToggle(); 
    });

     //thumb image replace image src
	 jQuery('.sthmb img').click(function(){
		var thumbSrc = jQuery('.sthmb').attr('src'); 
		var largeSrc = jQuery('.main-img img').attr('src');  
		jQuery('.main-img img').attr('src',jQuery(this).attr('src').replace(thumbSrc,largeSrc));
	});

    //Enable Border checkbox
    $('.hidden').hide();
    $('.trigger').change(function() {  
    var hiddenId = $(this).attr("data-trigger");
        if ($(this).is(':checked')) {
          $("#" + hiddenId).show();
        } else {
          $("#" + hiddenId).hide();
        }
    });
    // dropdown animation
    $('.dropdown').on('show.bs.dropdown', function(e) {
        $(this).find('.dropdown-menu').first().stop(true, true).slideDown(350);
    });
    $('.dropdown').on('hide.bs.dropdown', function(e) {
        $(this).find('.dropdown-menu').first().stop(true, true).slideUp(0);
    });

   

    /****************** click handler section ***************/
    $(document).on("click", ".header-top-slide", function(){
        if($(this).hasClass('fa-chevron-up')){
            $(this).removeClass('fa-chevron-up').addClass('fa-chevron-down');
        }else{
            $(this).removeClass('fa-chevron-down').addClass('fa-chevron-up');
        }
        $("#header").toggleClass("active");
    });


    $(".tab-wrapper, .hcscroll").mCustomScrollbar({
        axis:"x" // horizontal scrollbar
    });

    // Product Listing left aside toggle
    jQuery('.filter-block ul li a').click(function(){            
        jQuery(this).next('ul').slideToggle();
    });

    jQuery('.filter-block .filter-arrow-title').click(function(){            
        jQuery(this).next('.filter-over').slideToggle();
        jQuery(this).find('.fa-chevron-down').toggleClass('fa-chevron-up');
    });

    jQuery( "#slider-range" ).slider({
      range: true,
      min: 0,
      max: 20800.00,
      values: [ 790, 20800 ],
      slide: function( event, ui ) {
        jQuery( "#amount" ).val( "THB" + ui.values[ 0 ] + " - THB" + ui.values[ 1 ] );
      }
    });
    jQuery( "#amount" ).val( "THB" + jQuery( "#slider-range" ).slider( "values", 0 ) +
      " - THB" + jQuery( "#slider-range" ).slider( "values", 1 ) );


});



// jQuery(window).on("load", function() {
//    $(".ralated-slider").slick({
//        slidesToShow: 4,
//        slidesToScroll: 1
//    });
// });