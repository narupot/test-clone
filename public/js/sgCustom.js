
/*  For Back  */

function goBack() {
  window.history.back();
}

var winWidth = jQuery(window).width();

	

jQuery(document).ready(function($){

	if(winWidth < 768) {
		jQuery('.menu-icon').on('click',function(){
			jQuery(this).find('i').toggleClass('far fa-window-close');
			jQuery('.header-menu .static-menu').slideToggle();
		});
		jQuery('.static-menu .arrow ricon').on('click',function(){
			jQuery(this).toggleClass('fa-angle-down');
			jQuery(this).parent().siblings('ul').slideToggle();
		})
	}

	/*   File Image upload  */

	jQuery('.file-wrapper .custom-img-button').click(function(){
		$(".custom-img-file input").trigger('click');
	});

	/*  For tooltip */
	
	jQuery('[data-toggle="tooltip"]').tooltip();  


	// Checkbox on and off
	jQuery(document).on('click','.myonoffswitch',function(){
	    if(jQuery(".myonoffswitch input").is(':checked')){      
	        jQuery(this).find('.onofftravelbox').toggleClass('travelon-onoff');
	        jQuery(this).find('.myonoffswitch-circle').toggleClass('circletravel');
	        jQuery(this).find('.pwd-wrap').show();
	        
	    }
	    else {
	        jQuery('.onofftravelbox').removeClass('travelon-onoff');
	        jQuery('.myonoffswitch-circle').removeClass('circletravel');
	        jQuery('.pwd-wrap').hide();
	    }   
	});

	// Checkbox on and off2
	jQuery(document).on('click','.myonoffswitch2',function(){
	    if(jQuery(".myonoffswitch2 input").is(':checked')){      
	        jQuery(this).find('.onofftravelbox2').toggleClass('travelon-onoff2');
	        jQuery(this).find('.myonoffswitch-circle2').toggleClass('circletravel2');
	        jQuery(this).find('.pwd-wrap2').show();
	        
	    }
	    else {
	        jQuery('.onofftravelbox2').removeClass('travelon-onoff2');
	        jQuery('.myonoffswitch-circle2').removeClass('circletravel2');
	        jQuery('.pwd-wrap2').hide();
	    }   
	});

	// Seller Diliviry list item Show/Hide	
	jQuery(".delivery-list .show-hide a").click(function(){					
		jQuery(this).parents("li").find(".deliv-content").slideToggle();			
	});

	// Seller Order list item Show/Hide	
	jQuery(".block-list-order .list-header .show-hide a").click(function(){					
		jQuery(this).parents("li").find(".order-table-group").slideToggle();		
		 if (jQuery(this).text() == "Expand")
	       jQuery(this).text("Hide");
	    	else
	       jQuery(this).text("Expand");
			
	});

	// Order Table View Detail Show/Hide	
	jQuery(".tbl-order .inner-details").click(function(){					
		jQuery(this).parents("tr").next(".tbl-viewdetail").slideToggle();			
	});

	// Order Table buyer View Detail Show/Hide	
	jQuery(".order-history-buyer .inner-details").click(function(){					
		jQuery(this).parents("tr").next(".tbl-viewdetail-buyer").slideToggle();			
	});

	//Filter slider 
	jQuery(document).on('click', '.showFilter', function(){
		jQuery(".filer-box").slideToggle();	
	});

	//Create New Product selected
	jQuery(document).on('click',".select-product-img li",function(){	
		if (jQuery(this).find('input[type="radio"]').is(':checked')){
			jQuery('.select-product-img li').removeClass('active');			
			jQuery(this).toggleClass('active');
		}
	});


	//Date select 
	if($('.date-select').length>0){
       $(".date-select").flatpickr({
			 "locale": "th",       		
       });
	}
	

	/*   in checkout page number */
	/*   in checkout page number */
	function textnum(){
		$( document ).ajaxComplete(function() {
		  if($("#checkout_form #select-address").hasClass("active")) {
				$('#payment_method_div .step-title .step-num').text(3);
			}
			else {
				$('#payment_method_div .step-title .step-num').text(2);
			}
		});		
	}	
	textnum();
	$('#shipTab a').on('click',function(){
		textnum();  
		console.log(1);
	});
    


	//Wishlist active
  	jQuery('.addto-link a').on('click',function(){
  		jQuery(this).toggleClass('active');
  	});

    jQuery(".m-menu li .arrow").on('click', function(){
        var element = jQuery(this).parent().parent("li");
        jQuery(this).toggleClass("active");
        if (element.hasClass('open')) {
            element.removeClass('open');
            element.find('li').removeClass('open');
            element.find('ul').slideUp();
        }
        else {
            element.addClass('open');
            element.children('ul').slideDown();
            element.siblings('li').children('ul').slideUp();
            element.siblings('li').removeClass('open');
            element.siblings('li').find('li').removeClass('open');
            element.siblings('li').find('ul').slideUp();
        }
    });
}(jQuery));

try{
// Magic Slider Height JS
 var MagicScrollOptions = {};
    MagicScrollOptions = {
        onReady: function () {
        var sliders = document.querySelectorAll('.MagicScroll');
        for(k = 0; k < sliders.length; ++k) {
            // console.log(sliders[k] );
            if(!sliders[k].querySelector('.mcs-items-container')) break;
            var sizes = [];
            var divs = sliders[k].querySelector('.mcs-items-container').querySelectorAll(".item-box"), i;

            for (i = 0; i < divs.length; ++i) {
                sizes.push(divs[i].offsetHeight);
            }
            sliders[k].style.height = Math.max.apply(null, sizes) +'px';
        }
        console.log('onReady', arguments[0]);
    }
};
}
catch(er){
//
}