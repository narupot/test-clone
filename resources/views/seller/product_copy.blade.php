@extends('layouts.app') 

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/myaccount','css/bootstrap-select'],'css') !!}
@endsection

@section('header_script')
    var lang_json = {"ok":"@lang('common.ok')", "success":"@lang('common.success')"};
    var base_unit_url = '{{action('Seller\ProductController@baseUnit')}}';

    var txt_no = "@lang('common.no')";
    var text_ok_btn = "@lang('common.ok_btn')";
    var currency = "@lang('common.baht')";
@endsection

@section('content')

<h1 class="page-title title-border">+@lang('product.edit_product')</h1>
<div class="create-new-productForm">
    <form id="product_frm" method="post" action="{{action('Seller\ProductController@copystore', $result->id)}}" enctype="multipart/form-data">
      
        {{csrf_field()}}
        <h3 class="step-title pl-3">@lang('product.basic_information')</h3>
        <div class="form-group row">
            <div class="col-sm-12">
                <label>@lang('product.select_product')<i class="red">*</i></label>
                <ul class="select-product-img ">
                @if(count($seller_prod_cat) > 0)
                    @foreach($seller_prod_cat as $prod_cat)
                        <li @if($prod_cat->id == $result->cat_id)class="active"@endif>
                            <div class="img-block"><img src="{{getCategoryImageUrl($prod_cat->img)}}" width="76" height="57" alt=""><span>{{$prod_cat->category_name}}</span></div>
                            <label class="radio-wrap">
                                <input type="radio" name="product_cat" value="{{$prod_cat->id}}"  @if($prod_cat->id == $result->cat_id)checked="checked" @endif>
                                <span class="radio-mark"></span>
                            </label>
                        </li>
                    @endforeach
                @endif
                </ul>
                <span id="error_product_cat" class="error"></span>  
            </div>                      
        </div>

        @include('includes.edit_product_include')


        <div class="form-group">
            <div class="grey-box text-center">                          
                <button class="btn" type="button" id="copy_prod_btn">@lang('common.update')</button>
            </div>                      
        </div>

    </form>
</div>

@endsection 

@section('footer_scripts')
{!! CustomHelpers::combineCssJs(['js/price_formatter', 'js/seller/product'],'js') !!}
@stop