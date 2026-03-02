
/*  For Back  */

function goBack() {
  window.history.back();
}

var winWidth = jQuery(window).width();

jQuery(document).ready(function(){
	
	if(winWidth < 768) {
		jQuery('.menu-icon').click(function(){
			jQuery(this).find('i').toggleClass('far fa-window-close');
			jQuery('.header-menu .main-menu').slideToggle();
		});
	}

	/*   File Image upload  */

	jQuery('.file-wrapper .custom-img-button').click(function(){
		$(".custom-img-file input").trigger('click');
	});

	/*  For tooltip */
	
	// jQuery('[data-toggle="tooltip"]').tooltip(); 
	

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
	jQuery(".showFilter").click(function(){					
		jQuery(".filer-box").slideToggle();			
	});

	// Forget Form email phone
		jQuery("#phone-chk").click(function() {
	        if(jQuery(this).is(':checked')) {         	
	            jQuery('#find-by-phone').show();
	            jQuery('#find-by-email').hide();
	        } else {
	            jQuery('#find-by-phone').hide();
	            jQuery('#find-by-email').show();
	        }
	    });

	    jQuery("#email-chk").click(function() {
	        if(jQuery(this).is(':checked')) {         	
	            jQuery('#find-by-email').show();
	            jQuery('#find-by-phone').hide();
	        } else {
	            jQuery('#find-by-email').hide();
	            jQuery('#find-by-phone').show();
	        }
	    });

   


});
