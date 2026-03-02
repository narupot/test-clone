/*  For product Preview */
   
$('.label-position-preview').on('click',function(){
    var shape_type = $('input[name=product_lavel_type]:checked').val();
    if(shape_type == 'fixed_shape'){
        var txtname = jQuery("input[name='product_lavel_text["+lang_id+"]']").val();
        $('.widget-name').html(txtname);
        $('.widget-name').css({ 'color': $("input[name='product_text_color']").val(),'font-size':$("input[name='product_text_size']").val()+'px'});
        
        prdShapeImg();
    }else{
        if(page_mode == 'edit'){
            var img_scr = $('.product-custom-lblimg .upload-img').attr('src');
            $(".product-badges > div").css("background-image", "url(" + img_scr + ")");
        }else{
            prdShapeImg();
        }
    }
    $('.product-main-image').show();
    prdPosition();
});
function prdShapeImg(){
    var radioImgValue = $("input[name='product_lavel']:checked").val();
    var imgUrl = "../images/badges/product/";
    var imgUrl1 = radioImgValue;
    var imgUrl2 = imgUrl + imgUrl1;
    $(".product-badges > div").css("background-image", "url(" + imgUrl2 + ")");
}

$("#badgeProduct .product-custom-uploadimg input[type='radio']").click(function(){
    prdShapeImg();               
});

function prdPosition(){
    var radioPosValue = $("input[name='product_lavel_possition']:checked").val();
    $('.product-badges > div').removeClass();
    $('.product-badges > div').addClass(radioPosValue).addClass('custom_shape');
}

$("#badgeProduct .sel-lblposition input[type='radio']").click(function(){
    prdPosition();
});

$('input[name=product_text_size]').change(function(){
    $('.widget-name').css('font-size',$(this).val()+'px');
})

$('input[name=product_text_color]').change(function(){
    $('.widget-name').css('color',$(this).val());
});

$('input[name="product_lavel_text['+lang_id+']"]').change(function(){
    $('.widget-name').html($(this).val());
});

$(document).on('change', '.prd_style_val', function(){
    var val = jQuery(this).val();
    var ddval = jQuery(this).parent().prev().find("select option:selected").val();
    $('.custom_shape').css(ddval,val+'px');
}); 

jQuery('body').on('change','input[name=product_shape]',function(){
    var file = this.files[0];
    var ext = file.name.split('.').pop().toLowerCase();
    if ($.inArray(ext, ['gif', 'png', 'jpg', 'jpeg']) == -1) {
        jQuery(this).val('');
        jQuery(this).siblings('.upload-img').attr('src', '');
        alert('invalid extension!');
        return false;
    }
    
    if (this.files && this.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            
            $(".product-badges > div").css("background-image", "url(" + e.target.result + ")");
        }
        reader.readAsDataURL(this.files[0]);
    }
});  

/// for category preview
$('.cat-position-preview').on('click',function(){
    var shape_type = $('input[name=category_lavel_type]:checked').val();
    if(shape_type == 'fixed_shape'){
        var txtname = jQuery("input[name='category_lavel_text["+lang_id+"]']").val();
        $('.cat-widget-name').html(txtname);
        $('.cat-widget-name').css({ 'color': $("input[name='category_text_color']").val(),'font-size':$("input[name='product_text_size']").val()+'px'});
        catShapeImg();
    }else{
        if(page_mode == 'edit'){
            var img_scr = $('.custom-lblimg .upload-img').attr('src');
            
            $(".cat-badges > div").css("background-image", "url(" + img_scr + ")");
        }else{
            catShapeImg();
        }
    }
    $('.cat-main-image').show();
    catPosition();
});

function catShapeImg(){
    var radioImgValue = $("input[name='category_lavel']:checked").val();
    var imgUrl = "../images/badges/category/";
    var imgUrl1 = radioImgValue;
    var imgUrl2 = imgUrl + imgUrl1;
    $(".cat-badges > div").css("background-image", "url(" + imgUrl2 + ")");
}

$("#badgeCategory .custom-uploadimg input[type='radio']").click(function(){
    catShapeImg(); 
});
function catPosition(){
    var radioPosValue = $("input[name='category_lavel_possition']:checked").val();
    $('.cat-badges > div').removeClass();
    $('.cat-badges > div').addClass(radioPosValue).addClass('custom_shape');
}

$("#badgeCategory .sel-lblposition input[type='radio']").click(function(){
    catPosition();
});

$('input[name=category_text_size]').change(function(){
    $('.cat-widget-name').css('font-size',$(this).val()+'px');
})

$('input[name=category_text_color]').change(function(){
    $('.cat-widget-name').css('color',$(this).val());
});

$('input[name="category_lavel_text['+lang_id+']"]').change(function(){
    $('.cat-widget-name').html($(this).val());
});

$(document).on('change', '.prd_style_val', function(){
    var val = jQuery(this).val();
    var ddval = jQuery(this).parent().prev().find("select option:selected").val();
    $('.custom_shape').css(ddval,val+'px');
}); 

jQuery('body').on('change','input[name=category_shape]',function(){
    var file = this.files[0];
    var ext = file.name.split('.').pop().toLowerCase();
    if ($.inArray(ext, ['gif', 'png', 'jpg', 'jpeg']) == -1) {
        jQuery(this).val('');
        jQuery(this).siblings('.upload-img').attr('src', '');
        alert('invalid extension!');
        return false;
    }
    
    if (this.files && this.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            
            $(".cat-badges > div").css("background-image", "url(" + e.target.result + ")");
        }
        reader.readAsDataURL(this.files[0]);
    }
});