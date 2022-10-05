@extends('layouts.app') 

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/myaccount','css/bootstrap-select'],'css') !!}
    <style type="text/css">
        .w-25custom {
            display: inline-block;
            width: 50px;
        }
    </style>
@endsection

@section('header_script')
    var lang_json = {"ok":"@lang('common.ok')", "success":"@lang('common.success')", "select":"@lang('common.select')"};
    var base_unit_url = '{{action('Seller\ProductController@baseUnit')}}';

    var txt_no = "@lang('common.no')";
    var text_ok_btn = "@lang('common.ok_btn')";
    var currency = "@lang('common.baht')";
@endsection

@section('content')

<h1 class="page-title title-border">+@lang('product.create_new_product')</h1>
<div class="create-new-productForm">
    <form id="product_frm" method="post" action="{{action('Seller\ProductController@store')}}" enctype="multipart/form-data">

        {{csrf_field()}}
        <h3 class="step-title pl-3">@lang('product.basic_information')</h3>
        <div class="form-group row">
            <div class="col-sm-12">
                <label>@lang('product.select_product')<i class="red">*</i></label>
                <ul class="select-product-img">
                @if(count($seller_prod_cat) > 0)
                    @foreach($seller_prod_cat as $prod_cat)
                        <li>
                            <div class="img-block"><img src="{{getCategoryImageUrl($prod_cat->img)}}" width="76" height="57" alt=""><span>{{$prod_cat->category_name}}</span></div>
                            <label class="radio-wrap">
                                <input type="radio" name="product_cat" value="{{$prod_cat->id}}">
                                <span class="radio-mark"></span>
                            </label>

                        </li>
                    @endforeach
                @endif
                </ul>
                <span id="error_product_cat" class="error"></span>  
            </div>                      
        </div>

        @include('includes.add_product_include')

        <div class="form-group">
            <div class="grey-box text-center">                          
                <button class="btn" type="button" id="create_prod_btn">@lang('common.create')</button>
            </div>                      
        </div>

    </form>
</div>


<div class="modal fade" id="standard_size_popup">
  <div class="modal-dialog  modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">Standard Size</h3>
        <span class="close fas fa-times" data-dismiss="modal"></span>
      </div>
      <div class="modal-body">
        {!!getStaticBlock('standard-size-popup')!!}
      </div>
    </div>
  </div>
</div>

@endsection 

<script>
	// $(document).ready(function(){
	// 	$('.select-product-img li').on('click',function(){
	// 		$(this).find('.radio-wrap').css({
	// 			"opacity": 1, 
	// 			"visibility": "visible";
	// 		});
	// 		$(this).find('input[type="radio').prop("checked", true);
	// 	});
	// });
</script>
@section('footer_scripts')
{!! CustomHelpers::combineCssJs(['js/price_formatter', 'js/seller/product'],'js') !!}

@stop