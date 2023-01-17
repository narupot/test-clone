
/*
	1) For Sidebar left Menu
	2) For Sidebar fixed bottom
	3) For Equal height in login page

===============================*/

(function($){
	var winWidht = $(window).width();
	var winHeight = $(window).height();

  // For Tree menu

  jQuery(function () {
      jQuery('.tree li:has(ul)').addClass('parent_li').find(' > a .menuIcon').attr('title', 'Expand');
      jQuery('.tree li.parent_li > a .menuIcon').on('click', function (e) {
          e.preventDefault();
          var children = jQuery(this).parent().parent('li.parent_li').find(' > ul > li');
          if (children.is(":visible")) {
              children.hide('fast');
              jQuery(this).attr('title', 'Expand').addClass('glyphicon-plus').removeClass('glyphicon-minus');
          } else {
              children.show('fast');
              jQuery(this).attr('title', 'Collapse').addClass('glyphicon-minus').removeClass('glyphicon-plus');
          }
          e.stopPropagation();
      });
  });



  jQuery('body').on('change','#change_password',function(){
    var isChecked = $(this).is(':checked');
    if(isChecked === true) {
        jQuery('#password_div').slideDown();
    }
    else {
        jQuery('#password_div').slideUp();
    }
});


	// For tooltip
	if($('[data-toggle="tooltip"]').length){
		$('[data-toggle="tooltip"]').tooltip(); 	
	}

	$(document).on("click","#navmenu .close-menu",function(){
		$('#navmenu>li').removeClass('expand active');
	});

	$(document).on("click","#navmenu>li>a",function(e) {
		$('#navmenu>li').removeClass('expand active');
		$(this).parent().addClass('expand active');
	});

	$(document).on("click", ".adm-submenu li", function(ev){
		//ev.preventDefault();
		$(this).find('i').toggleClass('fa-chevron-right fa-chevron-down');
		$(this).find('ul').slideToggle();
	});

	$(document).mouseup(function(e){
		var container = $('.admin-sidebar');
		if(!container.is(e.target)&&container.has(e.target).length==0){
			$ ('#navmenu>li').removeClass('expand active');
		}
	});

	// For Menu Scroll //

	var winHt = $(window).height(),
		menuHt =  $('.admin-sidebar').height(),
		win =  $(window),
		scrollT = $(window).scrollTop(),
		menus =  $('.admin-sidebar');

	var scrollHt = menuHt-winHt;
	var subHt = $('.submenu-wrapper').height();

	function fixMenu(){
		if(menuHt > winHt) {
			//menus.addClass('fixed');
			win.scroll(function(){
				 if($(window).scrollTop()>scrollHt){
				 		menus.addClass('fixed');
				 		$('.admin-sidebar').css('top',-scrollHt);
				 }
				 else {
				 	menus.removeClass('fixed');
				 	$('.admin-sidebar').css('top','64px');
				 }
			});
		}
	};

	fixMenu();

	win.resize(function(){
		fixMenu();
	});

	// hide message 
	// $('.alert-success').fadeOut(3000, function(){
	//      $(this).remove();
	// });
	
	/*  Menu slide in Role page  */
	$(document).on("click",".menulist .glyphicon",function(ev){
		ev.preventDefault();
		$(this).toggleClass('glyphicon-menu-up');
		$(this).parent().siblings('.submenulist').slideToggle();
	});

	
	// For Message Popup
  	$(document).on("click",".ok-msg, .close-msg",function(){
  		$(this).parents().find('.msg-container').fadeOut();
  	});

  	$(document).on("click",".ok-msg, .close-msg",function(){
  		$(this).parents().find('.error-msg-container').fadeOut();
  	});

  	// Left menu height
  	var ltmenuht = $('.content-left').height();
  	var rtmenuht = $('.content-right').height();
  	//alert(rtmenuht);
  	//alert(winHeight);
  	if(winWidht>767){

  		if(rtmenuht<winHeight){
	  		$('.content-right').css('min-height', winHeight-160);
	  	}
  	}

  	// For On off
  	$(document).on('click','.myonoffswitch',function(){
		if($(".myonoffswitch input").is(':checked')){		
			$('.onofftravelbox').toggleClass('travelon-onoff');
			$('.myonoffswitch-circle').toggleClass('circletravel');
		}
		else {
			$('.onofftravelbox').removeClass('travelon-onoff');
			$('.myonoffswitch-circle').removeClass('circletravel');
		}	
	});	

  	// Product Create
	$('.stock-from-row').next('.form-row').find('i.count').text(5);

  	
  	// For File upload image
  	$(document).on("change",".browse-btn input[type='file']",function(e){
        var fileName = e.target.files[0].name;  
        $('.browse-img-name').html(fileName);          
 	});

 	$(document).on("change",".shipment-browse-btn input[type='file']",function(e){
        var fileName = e.target.files[0].name;  
        $('.shipment-img-name').html(fileName);          
 	});

 	$(document).on('click','.mbSlide',function(){		
		$('.mbSlide').toggleClass('mbMobile');		
		// alert("Test");		
		$('.admin-sidebar').toggleClass('sellerboxMenu');
		$('.wrapper .content').toggleClass('pushright');
	});


	$('#navmenu li').click(function(){
		$('#navmenu li').each(function(){
			$('.jspPane').css('top', '0px');
		});
	});


	// added by sandeep for warehouse link
    var warehouse_lenght = $('#warehouse_management').length;
    if(warehouse_lenght > 0) {
        var data = {};
        try{
        	warehouse_status_url;
        }catch(e){
        	if(e instanceof ReferenceError){
        		console.log;
        		warehouse_status_url ="";
        	}
        }
        
        callAjax(warehouse_status_url, 'post', data, function(response){
            response = JSON.parse(response);
            if(response.status == 'success' && response.warehouse_status == 'false'){  
                $('#warehouse_management').hide();                                 
            } 
        });         
    }
    // added by sandeep for warehouse link ended 

    if(typeof order_mode_status!== "undefined" &&  order_mode_status == 'simple') {
    	$('#invoice, #shipment, #rma').hide();
    }

  //Admin Navigation scroll
  jQuery(window).on("load",function(){
    jQuery(".admin-menu").mCustomScrollbar();
     jQuery(".submenu-wrapper").mCustomScrollbar(); 
  });

})(jQuery);

function callAjaxSavedata(submiturl,formData,callback){
  $.ajax({
        type: "POST",
        url : submiturl,
        enctype: 'multipart/form-data',
        processData: false,  // Important!
        contentType: false,
        cache: false,
        data : formData,
        success : function(response){
              callback(response);
        },
    });
}

jQuery(document).ready(function() {
    // Configure/customize these variables.
    var showChar = 98;  // How many characters are shown by default
    var ellipsestext = "...";
    var moretext = "show more";
    var lesstext = "show less";
    

    jQuery('.more-view').each(function() {
        var content = $(this).html();
 
        if(content.length > showChar) {
 
            var c = content.substr(0, showChar);
            var h = content.substr(showChar, content.length - showChar);
 
            var html = c + '<span class="moreellipses">' + ellipsestext+ '&nbsp;</span><span class="morecontent"><span>' + h + '</span><a href="" class="morelink">' + moretext + '</a></span>';
 
            $(this).html(html);
        }
 
    });
 
    jQuery(".morelink").click(function(){
        if($(this).hasClass("less")) {
            $(this).removeClass("less");
            $(this).html(moretext);
        } else {
            $(this).addClass("less");
            $(this).html(lesstext);
        }
        $(this).parent().prev().toggle();
        $(this).prev().toggle();
        return false;
    });

    /* Dropdown Search Filter*/
    jQuery(".filter-search > a").click(function(){
        jQuery('.show-searchFltr').slideToggle();
        jQuery(this).find('.glyphicon-menu-down').toggleClass('glyphicon-menu-up');
        
    });

    /* Report Admin Toggle */
    jQuery(".chartSeries-wrapper").on('click',function(e) {
      e.preventDefault();
      jQuery(this).toggleClass('active');
      if(jQuery(this).hasClass('active')){
      jQuery(this).next().slideDown(350);
      }
      else{
      jQuery(this).next().slideUp(350);
      }
    });
    jQuery(".chartSeries-accordion").on('click',function(e) {
      e.preventDefault();
      jQuery(this).toggleClass('active');
      if(jQuery(this).hasClass('active')){
      jQuery(this).parents().parents(".accordionrow").next().next(".chartSeries-contents").slideDown(350);
      }
      else{
      jQuery(this).parents().parents(".accordionrow").next().next(".chartSeries-contents").slideUp(350);
      }
    }); 

    //Header search dropdown
    $('.search-selectitem').on('change', function(){                  
        var optionSelected = $(this).find("option:selected").text();
        $('.nav-search-selected .nav-search-text').text(optionSelected);
    });

    // autocomplete header search
    /*$('#autosearch').typeahead({            
        source: [                
            {id: 1, name: 'Product Name'},
            {id: 2, name: 'Product Name'},
            {id: 3, name: 'Product Name'},            
            {id: 4, name: 'Product Name'},
        ],
        item:'<li class="product-list"><div class="product-img"><span href="javascript:void(0)"><img src="images/product/thumb/cartier-leather-bag-02.jpg" alt=""></span></div><div class="prodocut-detail"><span class="price">฿1,300</span><h3><a href="javascript:void(0);">Product Name</a></h3><div class="prod-mobile">SKU</div></div></li></span>',
        onSelect: displayResult
    }); */
    // language click
    $('.dd-language a').on('click',function(e){
        e.stopPropagation();
        $('.lang-flag').toggle();
        $(this).find('.fa-chevron-down').toggleClass('fa-chevron-up')
    });


    // Header on off switch
    $(document).on('click', '#switchonoff', function(evt){
        var $that = $(this);
        if($that.is(':checked')){
          websiteMaintenanceMode("Are you sure want to open website ?", 'checked', '0', $that);
        }else{
          websiteMaintenanceMode("Are you sure want to close website ?", 'unchecked', '1', $that);
        }
    });

    function websiteMaintenanceMode(titleText, action, statusVal, $elem){

      swal({
        title: titleText,
        type: "warning",
        showCancelButton: true,
      }).then(function(isDone){
        callAjax(website_maintenance_url, 'post', {'SITE_MAINTENANCE':statusVal}, function(response){
            if(response.status == 'success'){  
                swal("Success", response.msg, "success");
                $elem.val(statusVal);
                if(action == 'checked'){
                  $elem.parents('.switch-vertical').find('.toggle-outside').removeClass('switch-close');
                  $('.cng-text').text("Open").css("color", "#1DDB5D");        
                  $('.toggle-inside').removeClass('trv-bottom');
                }else if(action == 'unchecked'){
                  $elem.prop("checked", false);
                  $elem.parents('.switch-vertical').find('.toggle-outside').addClass('switch-close');
                  $('.cng-text').text("Close").css("color", "#ff0000");
                  $('.toggle-inside').addClass('trv-bottom');
                }                              
            }else{
              swal("Error",response.msg, "error");
            } 
        });
      }, function(notDone){
        return false;
      });
    };   


    $(document).on('click', '#mobileswitchonoff', function(evt){
        var $that = $(this);
        if($that.is(':checked')){
          mobileMaintenanceMode("Are you sure want to open website ?", 'checked', '0', $that);
        }else{
          mobileMaintenanceMode("Are you sure want to close website ?", 'unchecked', '1', $that);
        }
    });

    function mobileMaintenanceMode(titleText, action, statusVal, $elem){

      swal({
        title: titleText,
        type: "warning",
        showCancelButton: true,
      }).then(function(isDone){
        callAjax(mobile_maintenance_url, 'post', {'MOBILE_MAINTENANCE':statusVal}, function(response){
            if(response.status == 'success'){  
                swal("Success", response.msg, "success");
                $elem.val(statusVal);
                if(action == 'checked'){
                  $elem.parents('.switch-vertical').find('.toggle-outside').removeClass('switch-close');
                  $('.cng-text-1').text("Open").css("color", "#1DDB5D");        
                  $('.toggle-inside').removeClass('trv-bottom');
                }else if(action == 'unchecked'){
                  $elem.prop("checked", false);
                  $elem.parents('.switch-vertical').find('.toggle-outside').addClass('switch-close');
                  $('.cng-text-1').text("Close").css("color", "#ff0000");
                  $('.toggle-inside').addClass('trv-bottom');
                }                              
            }else{
              swal("Error",response.msg, "error");
            } 
        });
      }, function(notDone){
        return false;
      });
    };  
});

// Auto Search

function displayResult(item) {
  $('.alert').show().html('You selected <strong>' + item.value + '</strong>: <strong>' + item.text + '</strong>');
}

/*
*@Description : Listen on toastr message display 
*@param : status (string) like - seccuss/error
*@param : message (string)
*/

function _toastrMessage(status, message){
  Command: toastr[status](message);
}  

//Toaster option setting for message display
try{
    toastr.options = {
    "closeButton": false,
    "debug": false,
    "newestOnTop": false,
    "progressBar": false,
    "positionClass": "toast-top-right",
    "preventDuplicates": false,
    "onclick": null,
    "showDuration": "9000",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut",
  };
}catch(e){
  if(e instanceof ReferenceError)
    console.log;
}

let optGrpVal ='order';
$('#autoSearchItem').change(function() {
    optGrpVal = $(this).val();
});



//header search
/*$("#header-search" ).autocomplete({
  minLength: 4,
  source: function(rq,rs){
        jQuery.get(autoUrl, {
            'search_type': optGrpVal,
            'term': rq.term
        }, function (data) {
            
            if(parseInt(data.tot) > 0){
                
                var tot = data.tot;
                
                data.data.push({totrec: tot, listurl : data.url});
                rs(data.data);
            }else{
                // $('.ui-autocomplete').html('');
                rs([{ label: 'No results found.', val: -1}]);
            }
        })

  } ,

 classes: {
      "ui-autocomplete": "header-autocomplete"
   },

}).autocomplete( "instance" )._renderItem = function( ul, item ) { 
    if(item.val==-1){
          return $( "<li class='product-list'>" )
          .append( no_record_found )
          .appendTo( ul );            
    }          
    if(item.totrec){
        return $( "<li class='product-list'>" )
        .append( "<div class='search-result'><a href='"+item.listurl+"'>"+all+" "+item.totrec+" "+search_result+" <i>→</i></a></div>" )
        .appendTo( ul );
    }
    switch(optGrpVal){
        case 'order' :
            
            return $( "<li class='product-list'>" )
            .append( "<a href='"+ item.url+"'><div class='prodocut-detail'><span class='price'>"+item.cur+""+item.amount+"</span><h3> "+txt_order+" </h3> <div class='prod-mobile'>" + item.order_id +"</div></div></a>" )
            .appendTo( ul );
            
            
        break;

        case 'invoice' :
            return $( "<li class='product-list'>" )
            .append( "<a href='"+ item.url+"'><div class='prodocut-detail'><span class='price'>"+item.cur+""+item.amount+"</span><h3> "+txt_invoice+" </h3> <div class='prod-mobile'>" + item.invoice_id +"</div></div></a>" )
            .appendTo( ul );
        break;

        case 'shipment' :
            return $( "<li class='product-list'>" )
            .append( "<a href='"+ item.url+"'><div class='prodocut-detail'><span class='price'>"+item.cur+""+item.amount+"</span><h3> "+txt_shipment+" </h3> <div class='prod-mobile'>" + item.shipment_id +"</div></div></a>" )
            .appendTo( ul );
        break;

        case 'user' :
            return $( "<li class='product-list'>" )
            .append( "<a href='"+ item.url+"'><div class='prodocut-detail'><span class='price'>"+item.group_name+"</span><h3> "+item.name+" </h3> <div class='prod-mobile'>" + item.email +"</div></div></a>" )
            .appendTo( ul );
        break;     

        case 'product' :
            return $( "<li class='product-list block-img'>" )
            .append( "<a href='"+ item.url+"'><div class='product-img'><img src='"+ item.imgurl +"'></div><div class='prodocut-detail'><span class='price'>"+item.amount+"</span><h3 > " + item.name +" </h3> <div class='prod-mobile'>" + item.sku +"</div></div></a>" )
            .appendTo( ul );
        break;

       
    }
};*/

