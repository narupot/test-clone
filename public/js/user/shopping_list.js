jQuery(document).ready(function($){

	var default_shopping = $("#shoppinglist_from").val();
	loadItemsFromShoppingList(default_shopping);

	$(document).on("show.bs.modal",'#edit_shopping_list', function(e){
        $("#name").val($(e.relatedTarget).data('name'));
        $("#hidden_id").val($(e.relatedTarget).data('id'));
    });
    $(document).on("show.bs.modal",'#add_product', function(e){
        $("#shopping_list_id").val($(e.relatedTarget).data('id'));
    });
    $(document).on("show.bs.modal",'#edit_note', function(e){
        $("#shopping_id").val($(e.relatedTarget).data('id'));
        $("#shopping_list_item_id").val($(e.relatedTarget).data('item_id'));
        $("#note").val($(e.relatedTarget).data('note'));
    });

	$('body').on('change', "#shoppinglist_from", function(){
		var slist = $(this).val(); 
        if(slist!='create_new_list'){
            checkCurrentItemStatus().then(res=>{
                if(res.loading_flag){
                    loadItemsFromShoppingList(slist);
                }else{
                    swal({
                        type: "warning", 
                        title: text_warning, 
                        text: res.message,
                        confirmButtonText : text_ok_btn,
                        allowOutsideClick: false
                    }).then(function(){
                        location.reload();
                    });
                }
            }, err=>{
                console.log;
            });
        }else{
            loadItemsFromShoppingList(slist);
        } 
	});

	$('body').on('click', "#create_shopping_list", function(){
		var shopping_list_name = $("#shopping_list_name").val();
        if(shopping_list_name == ''){
            swal({
                type : 'error', 
                text : error_msg.shopping_name_empty_msg, 
                allowOutsideClick: false
            }).then(function(){
                $("#shopping_list_name").css({'border-color':'red'});
            });
        }else{
            var data = {shopping_list_name:shopping_list_name};
            callAjaxRequest(create_shopping_list_url, 'post', data, function(response){
                swal({
                    type : response.status, 
                    text : response.message, 
                    confirmButtonText : text_ok_btn,
                    allowOutsideClick: false
                }).then(function(){
                    location.reload();
                });
            }); 
        }
		
	});

	$('body').on('click', "#delete_shopping_list", function(){
		var shopping_list_id = $(this).attr('data-id');
		var data = {'shopping_list_id':shopping_list_id};
		swal({
            title: error_msg.are_you_sure,
            text: error_msg.are_you_sure_to_delete_this,
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: error_msg.yes_delete_it,
            cancelButtonText: txt_no,
            allowOutsideClick: false
        }).then((response) => {
            if (response) {
                callAjaxRequest(delete_shopping_list_url, 'post', data, function(response){
		            swal({
                        type : response.status, 
                        text : response.message, 
                        confirmButtonText : text_ok_btn,
                        allowOutsideClick: false
                    }).then(function(){
		                location.reload();
		            });
		        });
            }
        })
	});

	$('body').on('click', ".add_product_in_shopping_list", function(){
		var shopping_list_id = $("#shoppinglist_from").val();
		var formAction = $("#addProduct").attr('action');
        var formMethod = $("#addProduct").attr('method');
        var form_data = new FormData($("#addProduct")[0]);
            
        callAjaxFormRequest(formAction,formMethod,form_data,function(result){
            if(result.status=='fail'){
                showSweetAlertError(result.message);

            }else if(result.status=='success'){
                swal({
                    type : result.status, 
                    text : result.message,
                    confirmButtonText : text_ok_btn,
                    allowOutsideClick: false
                }).then(function(){
                    //location.reload();
                    loadItemsFromShoppingList(shopping_list_id);
                    $('#add_product').modal('toggle');
                });       
            }
        });
	});

	$('body').on("click",".update_shopping_list_name",function(){
            var shopping_list_name = $("#name").val();
            if(shopping_list_name == ''){
                $("#name").css({'border-color':'red'});
                $("#error-info").html(error_msg.shopping_name_empty_msg).css({'color':'red'});
            }else{
                var formAction = $("#editShoppingList").attr('action');
                var formMethod = $("#editShoppingList").attr('method');
                var form_data = new FormData($("#editShoppingList")[0]);
                
                callAjaxFormRequest(formAction,formMethod,form_data,function(result){
                    if(result.status=='fail'){
                        showSweetAlertError(result.message);

                    }else if(result.status=='success'){
                        swal({
                            type : result.status, 
                            text : result.message, 
                            confirmButtonText : text_ok_btn,
                            allowOutsideClick: false
                        }).then(function(){
                            location.reload();
                        });       
                    }
                });  
            }
    });

    $('body').on("click",".edit_note",function(){
            var formAction = $("#editNote").attr('action');
            var formMethod = $("#editNote").attr('method');
            var form_data = new FormData($("#editNote")[0]);
            
            callAjaxFormRequest(formAction,formMethod,form_data,function(result){
                if(result.status=='fail'){
                    showSweetAlertError(result.message);

                }else if(result.status=='success'){
                    swal({
                        type: result.status, 
                        text:result.message,
                        confirmButtonText : text_ok_btn,
                        allowOutsideClick: false
                    }).then(function(){
                        location.reload();
                    });       
                }
            });
    });

    $('body').on("click",".edit_standered",function(){
        var thisObj = $(this);
        var item_id = thisObj.attr('data-item_id');
        var shopping_id = thisObj.attr('data-shopping_id');
        var cat_id = thisObj.attr('data-cat_id');

        var data = {'item_id':item_id,'shopping_id':shopping_id,'cat_id':cat_id};
        callAjaxRequest(save_shopping_list_badge_edit_url, 'post', data, function(response){
            thisObj.parent('.prod-sizetype').html(response.data);
        }); 
    });

    $('body').on("click",".edit_price",function(){
        var thisObj = $(this);
        var item_id = thisObj.attr('data-item_id');
        var data = {'item_id':item_id};
        callAjaxRequest(save_shopping_list_price_edit_url, 'post', data, function(response){
            thisObj.parent('#price_sec').html(response.data);
        });   
    });

    $('body').on("click",".edit_qty",function(){
        var thisObj = $(this);
        var item_id = thisObj.attr('data-item_id');
        var data = {'item_id':item_id};
        callAjaxRequest(save_shopping_list_qty_edit_url, 'post', data, function(response){
            thisObj.parent('#qty_sec').html(response.data);
        });   
    });

    $('body').on("click","#save_standered",function(){
        //alert($(this).attr('data-item_id'));
        var item_id = $(this).attr('data-item_id');
        
        var size = $(this).parents('.prod-sizetype').find('#item_size').val();
        var grade = $(this).parents('.prod-sizetype').find('#item_grade').val();
        var shopping_id = $(this).attr('data-shopping_id');
        var cat_id = $(this).attr('data-cat_id');
        var data = {'item_id':item_id,'size':size,'grade':grade,'shopping_id':shopping_id,'cat_id':cat_id};
        callAjaxRequest(save_shopping_list_size_grade_url, 'post', data, function(response){
            swal({
                type : response.status, 
                text : response.message, 
                confirmButtonText : text_ok_btn,
                allowOutsideClick: false
            }).then(function(){
                location.reload();
            }); 
        });
    });

    $('body').on("click","#save_price",function(){
        var item_id = $(this).attr('data-item_id');
        var price = $(this).parents('#price_sec').find('#item_price').val();
        var data = {'item_id':item_id,'price':price};
        callAjaxRequest(save_shopping_list_price_url, 'post', data, function(response){
            swal({
                type: response.status, 
                text: response.message,
                confirmButtonText : text_ok_btn,
                allowOutsideClick: false
            }).then(function(){
                location.reload();
            }); 
        });
    });

    $('body').on("click","#save_qty",function(){
        var item_id = $(this).attr('data-item_id');
        var qty = $(this).parents('#qty_sec').find('#item_qty').val();
        var data = {'item_id':item_id,'qty':qty};
        callAjaxRequest(save_shopping_list_qty_url, 'post', data, function(response){
            swal({
                type: response.status, 
                text: response.message,
                confirmButtonText : text_ok_btn,
                allowOutsideClick: false
            }).then(function(){
                location.reload();
            }); 
        });
    });

    $('body').on("click",".item_complete",function(){
        var item_id = $(this).attr("data-item_id");
        var data = {'item_id':item_id};
        if($(this).is(":checked")){
            swal({
                title: are_you_sure,
                text: would_you_like_to_complete_this_item,
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: yes_complete_it,
                cancelButtonText: txt_no,
            }).then((response) => {
                if (response) {
                    callAjaxRequest(complete_shopping_list_item_url, 'post', data, function(response){

                        swal({
                            type: response.status, 
                            text: response.message,
                            confirmButtonText : text_ok_btn
                        }).then(function(){
                            location.reload();
                        }); 
                        
                    });
                }
            })  
        }
    });

    $('body').on("click",".delete_shopp_item",function(){
        var item_id = $(this).attr("data-item_id");
        var data = {'item_id':item_id};
        swal({
            title: are_you_sure,
            text: error_msg.txt_delete_confirm,
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: error_msg.yes_delete_it,
            cancelButtonText: txt_no,
            allowOutsideClick: false
        }).then((response) => {
            if (response) {
                callAjaxRequest(delete_shopping_list_item_url, 'post', data, function(response){
                    swal({
                        type: response.status, 
                        text: response.message, 
                        confirmButtonText : text_ok_btn,
                        allowOutsideClick: false
                    }).then(function(){
                        location.reload();
                    });
                });
            }
        })
    });

    
	function loadItemsFromShoppingList(slist){
		switch(slist){
			case 'create_new_list':
				var create_html = "<label>"+error_msg.create_shopping_list_name+"</label><div class=\"createlist-block\"><input type=\"text\" name=\"shopping_list_name\" id=\"shopping_list_name\"><button class=\"btn\" id=\"create_shopping_list\">"+error_msg.create_shopping_list+"</button></div>";
				$('#create_new_shopping_list').html(create_html);
			break;
			default:
				var data = {shopping_list_id:slist};
				callAjaxRequest(get_shopping_list_items_url, 'post', data, function(response){
                    $('#show_shopping_list').html(response.shopping_list_html);
		        });
		        $('#create_new_shopping_list').html('');
		}
	}

    function checkCurrentItemStatus(){
        var data = {};
        return new Promise((resolv, reject)=>{
            callAjaxRequest(check_shopping_list_items_url, 'post', data, function(response){
                resolv(response);
            });
        }).catch(err=>{
            console.log;
        });
    };

    $('body').on("click","#save_all",function(){
        var reqObj = {};
        $(this).parents("#show_shopping_list").children('.table-shoplist').children('.table-responsive').children('.table').children('.table-content').find('ul').each(function(index){
            var item_id = $(this).prop('id');
            var shopping_id = $(this).attr('data-shopping_id');
            var cat_id = $(this).attr('data-cat_id');
            var lastObj = $(this).find('li > .product > .product-info');
            var item_size = lastObj.find('.form-group > .prod-sizetype > span > .item_size').val();
            var item_grade = lastObj.find('.form-group > .prod-sizetype > span > .item_grade').val();
            var item_price = lastObj.find('.price-box > .inputgroup-icon > #item_price').val();
            var item_qty = lastObj.find('.price-box > .inputgroup-icon > #item_qty').val();
            //if(item_grade!==undefined && item_size!==undefined || item_price!==undefined){
                var itm = {};
                
                itm['item_size'] = item_size;
                itm['item_grade'] = item_grade;
                itm['item_price'] = item_price;
                itm['item_qty'] = item_qty;
                itm['item_id'] = item_id;
                itm['shopping_id'] = shopping_id;
                itm['cat_id'] = cat_id;
                reqObj[index] = itm;
            //}
        });

        callAjaxRequest(save_shopping_list_all_url, 'post', {'req':reqObj}, function(response){
            swal({
                type: response.status, 
                text: response.message, 
                confirmButtonText : text_ok_btn,
                allowOutsideClick: false
            }).then(function(){
                location.reload();
            });
        });

    });

});