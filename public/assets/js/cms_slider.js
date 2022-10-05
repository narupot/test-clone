function conDiv(val){

	var slider_type = jQuery('input[name="slider_type"]:checked').val();
	if(slider_type == 'product'){
		
	    if(val == 'specific_level_2'){
	    	$('#custom_sku').show();
	    	$('#prd_cat_con').hide();
			$('#blog_cat_con').hide();
	        $('#custom_blog_id').hide();
	    }else if(val == 'new-arrival' || val == 'sale' || val == 'coming-soon'){
	    	$('#prd_cat_con').hide();
	        $('#custom_blog_id').hide();
	    	$('#blog_cat_con').hide();
	    	$('#custom_sku').hide();
	    	$('#custom_blog_id').hide();
	    }else{
	    	$('#prd_cat_con').show();
	        $('#custom_blog_id').hide();
	    	$('#blog_cat_con').hide();
	    	$('#custom_sku').hide();
	    	$('#custom_blog_id').hide();
	    }
	}else if(slider_type == 'blog'){
		if(val == 'custom_id'){
			$('#custom_blog_id').show();
			$('#prd_cat_con').hide();
	    	$('#blog_cat_con').hide();
	    	$('#custom_sku').hide();
		}else{
			
			$('#blog_cat_con').show();
			$('#custom_blog_id').hide();
			$('#prd_cat_con').hide();
	    	$('#custom_sku').hide();
		}
	}else if(slider_type == 'category'){
		$('#prd_cat_con').show();
		$('#brand_con').hide();
	}else{
		$('#brand_con').show();
	}
}

function sliderCon(type){
	$('#sort_by_price').hide();
	if(type == 'product'){
		$('#sort_by_price').show();
		$('#radio_prd_con').show();
		$('#radio_blog_con').hide();
		$('select[name="prd_design"]').show();
		$('select[name="blog_design"]').hide();
		$('#prd_cat_con').show();
		$('#blog_cat_con').hide();
		$('#custom_blog_id').hide();
		$('#brand_con').hide();
		var designval = $('select[name="prd_design"]').val();
		if($('.prdRd:checked').length > 0){

		}else{
			$('input[name="slider_con"]').attr('checked',false);
			$('input[value="cat_latest_prd"]').attr('checked',true);
		}
		bannerImg(designval);
	}else if(type == 'blog'){

		$('#radio_prd_con').hide();
		$('#radio_blog_con').show();	
		$('#brand_con').hide();
		$('select[name="prd_design"]').hide();
		$('select[name="blog_design"]').show();
		$('#prd_cat_con').hide();
		$('#custom_sku').hide();
		$('#blog_cat_con').show();
		var designval = $('select[name="blog_design"]').val();
		if($('.blogRd:checked').length > 0){
			
		}else{
			$('input[name="slider_con"]').attr('checked',false);
			$('input[value="cat_latest_blog"]').attr('checked',true);
		}
		
		bannerImg(designval);
	}else if(type == 'category'){
		
		$('#brand_con ,#blog_cat_con ,#banner_div ,#feature_div ,select[name="prd_design"], select[name="blog_design"], #radio_prd_con, #radio_blog_con').hide();
		$('#prd_cat_con').show();
	}else if(type == 'brand'){

		$('#prd_cat_con ,#blog_cat_con ,#banner_div ,#feature_div ,select[name="prd_design"], select[name="blog_design"], #radio_prd_con, #radio_blog_con').hide();
		$('#brand_con').show();
	}
	
}

function bannerImg(designval){
	if(designval == 1 || designval > 17){
		$('#banner_div').hide();
		if(designval > 17){
			$('#feature_div').show();
		}else{
			$('#feature_div').hide();
		}
	}else{
		$('#banner_div').show();
		$('#feature_div').hide();
	}
	if(designval == 1){
		$('#show_hide_slider').show();
	}else{
		$('#show_hide_slider').hide();
	}
}

jQuery(document).ready(function(){
    var slider_type = jQuery('input[name="slider_type"]:checked').val();
    sliderCon(slider_type);
    var val = jQuery('input[name="slider_con"]:checked').val();
    conDiv(val);
    jQuery('input[name="slider_con"]').click(function(){
        var val = jQuery(this).val();
        conDiv(val);
        
    });

    if(slider_type == 'product'){
    	var dval = jQuery('select[name="prd_design"] option:selected').val();
    	heightWidth(dval);
    	sliderOption(dval);
    }else{
    	var dval = jQuery('select[name="blog_design"] option:selected').val();
    	heightWidth(dval);
    	sliderOption(dval);
    }

    jQuery('input[name="slider_type"]').click(function(){
        var val = jQuery(this).val();
        sliderCon(val);
    });

   	jQuery('select[name="prd_design"]').click(function(){
        var val = jQuery(this).val();
        bannerImg(val);
        heightWidth(val);
        sliderOption(val);
    });

    jQuery('select[name="blog_design"]').click(function(){
        var val = jQuery(this).val();
        bannerImg(val);
        heightWidth(val);
        sliderOption(val);
    });

    function sliderOption(val){

    	if(val == '1'){
    		$('#slider_option').show();
    	}else{
    		$('#slider_option').hide();
    	}
    }

    function heightWidth(val){
    	var slider_type = jQuery('input[name="slider_type"]:checked').val();
    	var intval = parseInt(val);
	        if(val !='1'){
	        	var width = width_arr[intval];

	        	if(slider_type == 'blog'){
	        		height_one = height_blog_one;
	        		height_two = height_blog_two;
	        	}
	        	if(intval > '1' && intval < '10'){
	        		var height = height_one;
	        	}else{
	        		var height = height_two;
	        	}
	        	console.log(intval,width,height);
	        	cropper_setting.width = parseInt(width);
	        	cropper_setting.height = 0;
	        }
    }

});

$(function(){
     $('.chosen').chosen();
    if($('.banner-mobile-radio').prop('checked')){
        $('.mobile-upload-banner').show('slow');
    }
    $(".banner-mobile-radio").change(function() {
        if($(this).prop('checked')){
            $('.mobile-upload-banner').show('slow');
        }
        else {
            $('.mobile-upload-banner').hide('fast');
        }
    });
    $(".remove_thumb").on('click', function(){
        var $bannerRadio = $('.banner-mobile-radio');
        $('.mobile-upload-banner').hide('fast');
        $('#banner_mobile').prop('checked', false);
        
    });
});