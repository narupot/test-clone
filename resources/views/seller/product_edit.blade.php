@extends('layouts.app') 

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/myaccount','css/bootstrap-select'],'css') !!}
@endsection

@section('header_script')
    var lang_json = {"ok":"@lang('common.ok')", "success":"@lang('common.success')", "select":"@lang('common.select')"};
    var base_unit_url = '{{action('Seller\ProductController@baseUnit')}}';
  
    var txt_no = "@lang('common.no')";
    var text_ok_btn = "@lang('common.ok_btn')";
    var currency = "@lang('common.baht')";
@endsection

@section('content')
@if(Session::has('verify_msg'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    {{Session::get('verify_msg')}}
</div>
@endif

@if(Session::has('not_verify_msg')) 
    <div class="alert alert-danger alert-dismissable margin5"> 
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button> 
        {{Session::get('not_verify_msg')}} 
    </div> 
@endif
<h1 class="page-title title-border">+@lang('product.edit_product')</h1>
<div class="create-new-productForm">
    <form id="product_frm" method="post" action="{{action('Seller\ProductController@update', $result->id)}}" enctype="multipart/form-data"><input name="_method" type="hidden" value="PUT">
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
                <button class="btn" type="button" id="update_prod_btn">@lang('common.update')</button>
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

@section('footer_scripts')
<script type="text/javascript">
    // Set custom buttons with separator between them.
    const TOOLBAR_BATTONS = ['undo', 'redo' , '|', 'bold', 'italic', 'underline', 'html'];
    const TOOLBAR_BATTONS_XS = ['undo', 'redo' , '-', 'bold', 'italic', 'underline'];
    
</script>
{!! CustomHelpers::combineCssJs(['js/price_formatter', 'js/seller/product'],'js') !!}
@include('includes.froalaeditor_dependencies')
<script src="{{asset('js/normal_froala_editor_setting.js')}}"></script>
<script type="text/javascript">
    $(function(){
       $('.active input[name="product_cat"]').trigger('click');
    });
</script>  
@stop
