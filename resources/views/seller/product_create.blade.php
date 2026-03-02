@extends('layouts.app') 

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/myaccount','css/bootstrap-select'],'css') !!}
    <style type="text/css">
        .w-25custom {
            display: inline-block;
            width: 50px;
        }
    </style>
    <style>
        /* ... CSS เดิมสำหรับ .product-grid และ .product-item ... */
        .product-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .product-item {
            border: 1px solid #ddd;
            padding: 10px 15px;
            border-radius: 10px;
            cursor: pointer;
            transition: 0.2s;
            background: #fafafa;
            min-width: 180px;
            text-align: center;
            position: relative;
        }

        .product-item:hover {
            background: #ffe6e6;
            border-color: #ff4d4d;
            transform: translateY(-2px);
        }

        .product-item.active {
            background: #ff4d4d;
            color: white;
            border-color: #ff1a1a;
        }

        .product-item input[type="radio"] {
            display: none;
        }
        #productSearch {
            border-radius: 30px;
            padding-left: 15px;
        }
        
        /* --- CSS สำหรับ Category Selection  --- */
        #searchCategory {
            border-radius: 8px; 
            padding: 10px 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: border-color 0.3s;
        }

        #searchCategory:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .category-container {
            display: flex;
            flex-wrap: wrap;
            gap: 12px; 
            padding-top: 5px;
        }

        .category-item {
            border: 1px solid #e0e0e0;
            padding: 0; /
            border-radius: 8px; 
            cursor: pointer;
            transition: all 0.25s ease;
            background: #ffffff;
            display: inline-block; 
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .category-item:hover {
            background: #f0f4ff;
            border-color: #007bff;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .category-item input[type="radio"] {
            display: none;
        }
        .category-item span {
            display: block; 
            padding: 10px 18px;
            border-radius: 7px; 
            font-weight: 500;
            color: #495057;
            transition: background 0.25s ease, color 0.25s ease;
        }

        /* Active State */
        .category-item input[type="radio"]:checked + span {
            background: #007bff; 
            color: white;
            font-weight: 600;
            padding: 10px 18px; 
            border-radius: 7px;
        }


/* ============ RADIO PILL/TAG STYLE ============ */

.radio-wrap input[type="radio"] {
    display: none;
}

.radio-wrap {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 15px;
    padding: 8px 16px; 
    border-radius: 25px; 
    background: #f8f8f8;
    color: #495057;
    border: 1px solid #e0e0e0;
    transition: all 0.2s ease;
    margin-right: 10px;
    text-align: center;
    user-select: none;
}

.radio-wrap:hover {
    background: #fff3f3;
    border-color: #dc3545; 
}

.radio-wrap input[type="radio"]:checked {
    display: none;
}

.radio-wrap:has(input[type="radio"]:checked) {
    background: #dc3545;
    color: white;
    border-color: #dc3545;
    font-weight: 600;
}

.radio-mark {
    display: none !important; 
}
    </style>
@endsection

@section('header_script')
    var lang_json = {"ok":"@lang('common.ok')", "success":"@lang('common.success')", "select":"@lang('common.select')"};
    var base_unit_url = '{{action('Seller\ProductController@baseUnit')}}';
    var parent_cat_data_url = '/admin/product/parent-cat-data';
    var txt_no = "@lang('common.no')";
    var text_ok_btn = "@lang('common.ok_btn')";
    var currency = "@lang('common.baht')";
    var base_unit_id = "";
    var package_id = "";
@endsection

@section('content')

<h1 class="page-title title-border">+@lang('product.create_new_product')</h1>
<div class="create-new-productForm">
    <form id="product_frm" method="post" action="{{action('Seller\ProductController@store')}}" enctype="multipart/form-data">

        {{csrf_field()}}
        <h3 class="step-title pl-3">@lang('product.basic_information')</h3>
        <!-- <div class="form-group row">
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
        </div> -->

        <div class="form-group">
    <label class="d-block mb-2" style="font-size: 1.1em; font-weight: bold;">
        @lang('product.select_product')<i class="red">*</i>
    </label>

    <input type="text" id="searchCategory" class="form-control mb-3" placeholder="ค้นหาหมวดหมู่สินค้า">

    <div class="category-container">
        @if(count($seller_prod_cat) > 0)
            @foreach($seller_prod_cat as $prod_cat)
                <label class="category-item">
                    <input type="radio" name="product_cat" value="{{ $prod_cat->id }}">
                    <span>{{ $prod_cat->category_name }}</span>
                </label>
            @endforeach
        @endif
    </div>

    <span id="error_product_cat" class="error"></span>
</div>

        @include('includes.add_product_include')

        <div class="form-group">
            <div class="grey-box text-center">                          
                <button class="btn" type="button" id="create_prod_btn">@lang('common.create')</button>
                <button class="btn" type="button" id="cancel_prod_btn" onclick="history.back()" >  ยกเลิก </button>
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
<script>
document.getElementById("searchCategory").addEventListener("keyup", function() {
    let search = this.value.toLowerCase();
    document.querySelectorAll(".category-item span").forEach(function(el) {
        let text = el.innerText.toLowerCase();
        el.parentElement.style.display = text.includes(search) ? "" : "none";
    });
});
</script>
@stop