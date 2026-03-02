@php 
$rating = 0;
@endphp



<form action="" method="post" id="user_product_review_form" class="review-contents formone-size clearfix">
	<span id="review_error"></span>

	<div class="form-group">
		<label>@lang('product_review.rating')</label>
		<fieldset class="user-review-star">
			<input type="radio" id="star5_1" name="product_rating" value="5" />
			<label class="full fas fa-star" for="star5_1" title="Meh"></label>


			<input type="radio" id="star4_1" name="product_rating" value="4"  @if($rating == 4) checked @endif/>
			<label class="full fas fa-star" for="star4_1" title="Kinda bad"></label>

			<input type="radio" id="star3_1" name="product_rating" value="3" @if($rating == 3) checked @endif />
			<label class="full fas fa-star" for="star3_1" title="Kinda bad"></label>


			<input type="radio" id="star2_1" name="product_rating" value="2" @if($rating == 2) checked @endif />
			<label class="full fas fa-star" for="star2_1" title="Sucks big tim"></label>

			<input type="radio" id="star1_1" name="product_rating" value="1"  @if($rating == 1) checked @endif/>
			<label class="full fas fa-star" for="star1_1" title="Sucks big time"></label>
		</fieldset>	
		<p class="review-required-error error-msg"></p>
	</div>
	<!-- <div class="form-group">
			<label>@lang('user.short_name')<span class="red">*</span></label>
			<input id="cust_name" name="name" type="text" placeholder="Name" value="{{ Auth::check() ? Auth::user()->first_name : '' }}">
			<span class="error-msg float-right" id="name-error"></span>
	</div> -->
	<div class="form-group">
			<label>@lang('product.review')</label>
			<textarea name="review" id="product_review_text"></textarea>
			<input id="review_url" type="hidden" name="review_url" value= "{{ action('User\ReviewController@store')}}" >
			<input id="product_id" type="hidden" name ="productid"  value = "" >
			<input type="hidden" name="order_id" value = "" > 
	</div>

	<div class="btn-group">
		<button type="button" class="float-right btn btn-primary" data-review="user_product_review" data-product="" id="user_product_review">@lang('product_review.submit_review')</button>
	</div>


</form>


<script>
		function getFormData(object) {
		    const formData = new FormData();
		    Object.keys(object).forEach(key => formData.append(key, object[key]));
		    return formData;
		}

		



		$('body').on('click',"#user_product_review",function(evt){ 
		    evt.preventDefault();
		    var formAction = "{{action('User\ReviewController@store')}}";
		    var formMethod = "POST";
		    var star = $("input[name='product_rating']:checked").val();
		    if(typeof star != 'undefined')
		        review_data.rating = star;
		    else
		    	review_data.rating = '';

		    review_data.review = $('#product_review_text').val();

		    var form_data = getFormData(review_data);
		    let opt_index;
		    let $ctrScope;
		    //In case of product deatil page first need to select order id for review 
		    if(typeof review_data!== 'undefined' && review_data.page && review_data.page === 'product_detail'){
		    	let opt_val = $('#product_order_review option:selected').val(),
		    		$opt =  $('#product_order_review option:selected');		    		
		    	if(!opt_val){
		    		swal('Opps...!', "@lang('product.please_select_order_for_review')", 'warning');
		    		return;
		    	} 
		    	opt_index =  $opt.index();
		    	form_data.append('order_id', opt_val);
		    	form_data.append('shop_id', $opt.data('spid'));
		    	form_data.append('product_id', $opt.data('pid'));
		    	try{
		    		$ctrScope = angular.element(document.getElementById('product_order_review')).scope();
		    	}catch(err){
		    		console.log;
		    	}
		    }

		    $.ajaxSetup({
				headers : { "X-CSRF-TOKEN" :jQuery('meta[name="csrf-token"]'). attr("content")}
			});
    		callAjaxFormRequest(formAction,formMethod,form_data,function(response){
	            if(response.status=='success'){
	            	$('#reviewmodel').modal('hide'); 	            	
	            	if(review_data.page == 'user_order_review'){
	            		$('#rating-'+review_data.product_id+'-'+review_data.order_id).html('');
	            		$('#rating-'+review_data.product_id+'-'+review_data.order_id).html('<div class="review-star" ><div class="grey-stars"></div><div class="filled-stars" style="width: '+response.rating +'%"></div></div>');
	            		$('#review-'+review_data.product_id+'-'+review_data.order_id).html('<span>'+response.review+'</span>')
	            	}else{
	            	}
	            	//In case of product deatil page first need to select order id for review 
	            	if(review_data.page && review_data.page === 'product_detail' && $('#product_order_review option').length === 2){
	            		$('#product_order_review').remove();
	            		$('#user_product_review_form').remove();
	            		$('.order_label').remove();
	            		$ctrScope && $ctrScope.rv.loadMore(evt);
	            	}else if(review_data.page && review_data.page === 'product_detail' && $('#product_order_review option').length>2){
	            		$('#product_order_review option:eq('+opt_index+')').remove();
	            		$ctrScope && $ctrScope.rv.loadMore(evt);
	            	}
	            }else{
	                if(response.status == 'error'){
	                	var errormesg = '';

	                	for(error in response.message){	                		
	                		for(key in response.message[error]){
	                			errormesg += '<div class="alert alert-danger ">'+response.message[error][key]+'</div>';
	                		}
	                	} 

	                	$('#review_error').html(errormesg);
						window.setTimeout(function(){
							$('#review_error').html('');
						}, 5000);
	                }
	            }
    });

});

		
</script>

