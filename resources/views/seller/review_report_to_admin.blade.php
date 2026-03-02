@extends('layouts.app') 

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/dataTables.bootstrap','css/myaccount','css/bootstrap-select'],'css') !!}

@endsection

@section('header_script')
@endsection

@section('content')

@if(Session::has('verify_msg'))
<div class="alert alert-success alert-dismissible">
	{{Session::get('verify_msg')}}
    <span class="close" data-dismiss="alert" aria-hidden="true"><i class="fas fa-times"></i></span>  
</div>
@endif

@if(Session::has('not_verify_msg')) 
    <div class="alert alert-danger alert-dismissable margin5">
        {{Session::get('not_verify_msg')}}  
        <span class="close" data-dismiss="alert" aria-hidden="true"><i class="fas fa-times"></i></span> 
    </div> 
@endif
<!-- page contents start -->
    <div class="row">
        <div class="col-sm-12">
            <h1 class="page-title title-border">@lang('shop.report_for_review') </h1>
            <form action="{{action('Seller\ReviewController@sendReport')}}" class="" method="post" enctype="multipart/form-data">
                <div class="order-info-block form-group">

                    {!! csrf_field() !!} 
                    <div class="order-no">@lang('order.order_no'). <span>{{$required_data['shop_order_id']}}</span></div>
                    <div class="wrap-order-info">
                        <span class="prod-img-block"><img src="{{$required_data['product_img']}}" alt=""></span>
                        <span class="prod-info-block">
                            <span class="productName">{{$required_data['product_name']}}</span>
                           
                            <div >
                                @lang('review.user_rating')
                                <span>
                                    <div class="review-star">
                                        <div class="grey-stars"></div>
                                        <div class="filled-stars" style="width: {{$required_data['rating']}}%"></div>
                                    </div>

                                </span>
                            </div>
                            <div>
                                @lang('review.user_review')
                                <span>{{$required_data['review']}}</span>
                            </div>                           
                            
                        </span>
                    </div>
                </div>
                <div class="add-new-productForm"> 
                    <input type="hidden" name="order_id" value="{{$required_data['order_id']}}">
                    <input type="hidden" name="product_id" value="{{$required_data['product_id']}}">

                    <div class="form-group">
                        <label>@lang('review.report_mesg')<i class="red">*</i></label>
                        <textarea name="report_mesg" id=""></textarea>
                        <span class="error">{{$errors->first('report_mesg')}}</span>                       
                    </div>
                    <div class="row">
                        <div class="col-sm-6 form-group">                        
                            <label>@lang('review.report_attachment')<i class="red">*</i></label>
                            <div class="upload-img-wrap btn-grey d-block">
                                <input type="file" name="report_attachment" onchange="fileHandler(event)" accept="image/*">
                                <span class="upload-cam"><i class="fas fa-camera"></i></span>
                            </div>                  
                            <span class="error">{{$errors->first('report_mesg')}}</span>
                        </div>
                        <div class="col-sm-6">
                            <label>&nbsp;</label>
                            <div class="upload-preview upload-cam" style="display: none;">
                                <img src="" alt="upload_preview" width="119" height="119" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <button type="submit" class="btn">@lang('common.submit')</button>
                </div>
                
            </form>
           
        </div>
    </div>    
<!-- page contents end -->

@endsection 
@section('footer_scripts') 
{!! CustomHelpers::combineCssJs(['js/jquery.dataTables','js/dataTables.bootstrap', 'js/user/myaccount','js/manage_credits'],'js') !!} 

<script>

    var handle_credit_ajax_request_url = "{{action('Seller\CreditController@manageCreditAjaxRequest')}}";
    var check_credit_remove_url = "{{action('Seller\CreditController@willCreditRemove')}}";

    $(document).ready(function () {
        $('#product_review_table').DataTable({
            "processing": true,
            "serverSide": true,
            "lengthChange": false,
            "responsive":true,
            "info":false,
            ajax:{
                    "url": "{{ action('Seller\ReviewController@getProductRatings')}}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{ _token: "{{csrf_token()}}",'table':'reviewed'}
                   },
            columns: [
                { "data": "product_name"},
                { "data": "standard"},
                { "data": "rating"},
                { "data": "review"},
                { "data": "review_date"},
                { "data": "action"}
            ]    

        });

        $('#waiting_products_table').DataTable({
            "processing": true,
            "serverSide": true,
            "lengthChange": false,
            "responsive":true,
            "info":false,
            ajax:{
                    "url": "{{ action('Seller\ReviewController@getProductRatings') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{ _token: "{{csrf_token()}}",'table':'not-reviewed'}
                   },
            columns: [
                { "data": "product_name"},
                { "data": "standard"},                
                { "data": "ordered_date"}
            ]    

        });
        
    });

    //Listen to change file 
    function fileHandler(evt){
        var file = evt.currentTarget.files[0] || null;
        if(file){ 
            var reader = new FileReader();
            // Read file content on file loaded event
            reader.onload = function(event) {
                $('.upload-preview').show('slow').find('img').attr('src', event.target.result);
            };
            // Convert data to base64 
            reader.readAsDataURL(file);
        }
    };

</script>

@endsection