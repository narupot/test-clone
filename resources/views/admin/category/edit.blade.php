
@extends('layouts.admin.default')
@section('title')
    @lang('admin_category.edit_category')
@stop
@section('header_styles')
    <link rel="stylesheet" type="text/css" href="{{Config('constants.admin_css_url') }}cropper.min.css">
    <?php  
        $cropper_setting = [
            [
                'section' => 'category_image_thumb', 'dimension' => ['width' => 263, 'height' => 195], 'file_field_selector' => '#categoryThumbImage', 'section_id'=>'category-image',
            ],
        ];
    ?>
@stop
@section('content')
<script>    
    var CROPPER_SETTING = {!! json_encode($cropper_setting) !!};  
    var action = '{{ action("Admin\Category\CategoryController@store")}}';
    var cat_id = '{{ $category->id}}';
    var top_cat_id = '{{ $top_parent_id}}';

    var categoryList = "{{action('Admin\Category\CategoryController@categorieslist')}}";
    var action = '{{action("Admin\Category\CategoryController@store")}}';
    
    var variantlisturl ='#';
    var dataJsonUrl ='{{ action("Admin\Category\CategoryController@categorieslist")}}';
    var imageurl="#";
    var currency = "{{session('default_currency_code')}}";
    window.userFolderDefaultPath = "{{Config::get('constants.froala_img_path').md5(Auth::id()).'/'}}";

    var categoryList = "{{action('Admin\Category\CategoryController@categorieslist')}}";
 
    var cateEditurl = "{{ action('Admin\Category\CategoryController@categoryedit') }}";
    
    var catediturl ="{{action('Admin\Category\CategoryController@categoryedit')}}";
    var showHeadrePagination = true;
    var tableLoaderImgUrl = "{{getSiteLoader('SITE_LOADER_IMAGE')}}";
    //pagination config 
    var pagination = {!! getPagination() !!};
    var per_page_limt = {{ getPagination('limit') }};
    //for enable external pagination (get data from server on every click)
    var ext_pagination = false;
    var ext_pagination = false;

</script>
<link rel="stylesheet" type="text/css" href="{{Config('constants.css_url') }}ui-grid-unstable.css">
<div class="content" data-ng-controller="sellerCateCtrl" data-ng-cloak>
     <!--Overlay loader show on save or save and continue click -->
     <div class="loader-wrapper" ng-if="showLoaderTable">
        <span class="loader">
            <img ng-src="<%tableLoaderImgUrl%>" alt="Loader"> 
        </span>
     </div>
    <div class="row">
        <div class="col-sm-12">
        @if(Session::has('succMsg'))    
            <script type="text/javascript">               
                _toastrMessage('success', "{{ Session::get('succMsg') }}");    
            </script>                            
        @elseif(Session::has('errorMsg'))
            <script type="text/javascript"> 
                _toastrMessage('error', "{{ Session::get('errorMsg') }}");
            </script>  
        @endif            
        </div>
    </div>
     <div class="header-title">
        <h1 class="title">@lang('admin_category.product_master')</h1>  
        <div class="float-right">

            @php( $confirm = "'".Lang::get('product.are_sure_delete_this_data')."'")
            {!! Form::open(['style' => 'display: inline-block;', 'method' => Lang::get('product.delete'), 'onsubmit' => "return confirm($confirm);",  'route' => ['catalog.destroy', $category->id]]) !!}
             {!! Form::close() !!}      
            <!--  {!! Form::button(Lang::get('product.remove_category'), ['class' => 'btn btn-md', 'type'=>'submit']) !!} -->
            <span class="copy-link" ng-if="previewUrl">
                <button class="btn-back btncopylink btn-default" type="button" data-clipboard-text="<%previewUrl%>">
                    <i class="fas fa-link"></i> @lang('admin.copy_link')
                </button>
            </span>
             <a ng-if="previewUrl"  class="btn btn-secondary deleteUrlcate" ng-href="<%previewUrl%>" target="_blank">@lang('admin_product.preview')</a>
             <a ng-if="deleteUrl" onclick="return confirm({{$confirm}});" class="btn btn-delete btn-danger deleteUrlcate" ng-href="<%deleteUrl%>">@lang('admin_category.remove_fruit')</a>   
                             
            <button class="btn btn-save btn-success" onclick="document.getElementById('sellerCategoryForm').submit();">@lang('admin_category.save_fruit')</button>
        </div>     
    </div>
    <div class="content-wrap clearfix">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('category','category')!!}
                </ul>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    @if (Session::exists('message'))
                        <div class="alert alert-success alert-dismissible fade in"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            {{Session::get('message')}}
                        </div>
                    @endif

                    @if (Session::exists('cat_error'))
                        <div class="alert alert-danger alert-dismissible fade in">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            {{Session::get('cat_error')}}
                        </div>
                    @endif 
                </div>
            </div>
            <div ng-if="!cat_mesg">
                <h2 id="cat_mesg" class="title-prod">
                    @if(isset($subcat_mesg))
                        @lang('admin_category.fruit_name'): {{$subcat_mesg}}                        
                    @endif
                </h2>
            </div>
            <div class="content-left">
                <!-- BEGIN SIDEBAR MENU -->
                     @include('admin.includes.category_menu')
                <!-- END SIDEBAR MENU -->
            </div>

            <div class="category-right content-right">
                <div ng-if="cat_mesg">
                    <h2 class="title-prod">@lang('admin_product.name'): <%cat_mesg%></h2>
                </div>
                <div class="category-tab">
                    <ul class="nav nav-tabs tab-list">
                        <li class="nav-item"><a class="nav-link active show" href="#cetegory-general-info" data-toggle="tab">@lang('admin_category.general_infomation_tab')</a></li>
                    </ul>
                </div>
                {!! Form::open(['action' => ['Admin\Category\CategoryController@update', $category->id], 'method' => 'put','id'=>'sellerCategoryForm', 'class'=>'form-horizontal','enctype' => 'multipart/form-data']) !!}
                <div class="box nobg pt-0" style="clear:both;">
                    <div class="tab-content row">
                        <div id="cetegory-general-info" class="tab-pane fade show active">
                            <input type="hidden" name="catmoveerror" value="<%catmoveerror%>">

                            {!! Form::hidden('parent_id', old('parent_id', $category->parent_id?$category->parent_id:0), ['id'=>'parent_id']) !!}
                        
                            {!! Form::hidden('category_id', old('category_id', $category->id), ['id'=>'category_id']) !!}
                            
                            <div class="category-gen-form">

                                <div class="form-group" >
                                 {!! CustomHelpers::fieldstabWithLanuage([['field'=>'text', 'name'=>'category_name', 'label'=>Lang::get('admin_category.fruit_name'), 'cssClass'=>'', 'errorkey'=>'name']], '3', $errors)!!}
                                </div>
                                <div class="form-group">
                                    <label>@lang('admin_category.allow_base_unit')</label>
                                    <div class="check-group">
                                        @foreach($units as $unit)
                                        <label class="check-wrap">
                                           {{ Form::checkbox('unit',$unit->id,isset($catunit[$unit->id]), array('name'=>'unit[]')) }}
                                         <span class="chk-label">{{$unit->unitdesc->unit_name}}</span>  
                                         </label>  
                                        @endforeach
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>@lang('admin_category.url')</label>
                                    {!! Form::text('url', old('url',$category->url)) !!}
                                    @if ($errors->has('url'))
                                        <p id="name-error" class="error error-msg">{{ $errors->first('url') }}</p>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>@lang('admin_category.comment')</label>
                                    {!! Form::textarea('cat_comment', old('cat_comment',$category->comment),['ng-model'=>'comment']) !!}
                                </div>

                                @if($category->parent_id)
                                    <div class="form-group mt-15">
                                        <label>@lang('admin_common.status') <i class="strick">*</i></label>
                                        <select class="select" name="status">
                                            <option value="1" @if($category->status == '1') selected="selected" @endif>@lang('common.active')</option>
                                            <option value="0" @if($category->status == '0') selected="selected" @endif>@lang('common.inactive')</option>
                                        </select>  
                                    </div>
                                @else
                                   {!! Form::hidden('status', old('status', 1)) !!}
                                @endif
                               
                                <div class="form-group">
                                    <label>@lang('admin_common.image')</label>
                                    
                                    <img src="{{ getCategoryImageUrl($category->img) }}" width="100px" height="100px" ng-show="display_mode.image">
                                    <input type="hidden" name="category_image" id="categoryThumbImage">
                                    <div class="cropper-main" id="category-image">   
                                        <div class="avatar-view single-file-upload" title="Change the avatar" data-section="category_image_thumb">
                                            <img src="{{asset('assets/images/please_upload_image.jpg')}}" alt="" id="category_image_thumb">
                                        </div>                                
                                        @include('includes.common_cropper_upload') 
                                    </div>
                                </div>                                
                            </div>
                           

                            <div class="advance-setting-option"> 
                                {!! CustomHelpers::fieldstabWithLanuage([['field'=>'textarea', 'name'=>'cat_description', 'label'=>Lang::get('admin_common.description'), 'cssClass'=>'texteditor1','froala'=>'froala']],'1')!!}
                                
                                {!! CustomHelpers::fieldstabWithLanuage([['field'=>'text', 'name'=>'meta_title', 'label'=>Lang::get('admin_common.meta_title'), 'cssClass'=>''], ['field'=>'text', 'name'=>'meta_keyword', 'label'=>Lang::get('admin_common.meta_keyword'), 'cssClass'=>''], ['field'=>'textarea', 'name'=>'meta_description', 'label'=>Lang::get('admin_common.meta_description'), 'cssClass'=>'']], '2')!!}
                                
                            </div>

                            <!-- <button type="submit" class="btn">@lang('admin_product.save_category')</button> -->
                            <input type="hidden" name="productids" id="assigned_product_ids">
                            

                        </div>
                        
                    </div>
                </div>
                
                 {!! Form::close() !!}
             </div>

             <div class="loading-more-indicator loader-container hide" ng-show="loadingMore">
               <div class="loader"></div>
             </div>    


    </div>  
   <div class="push-content"></div>
</div>
@endsection 
@section('footer_scripts')
    <script src="{{ Config('constants.js_url') }}jquery-cropper.min.js" type="text/javascript"></script>
    @include('includes.froalaeditor_dependencies') 
    @include('includes.cate_blog_js_desp')
    <script src="{{ Config('constants.angular_app_url') }}controller/sellerCateCtrl.js"></script>
    <script src="{{ Config('constants.js_url') }}common_cropper_upload_setting.js"></script>
    <script>    
        var assign_seller_url = "{{ action('Admin\Category\CategoryController@assignSeller') }}";
        $(document).ready(function(){       
            $('.hint-btn a').on('click',function(){
                if($(this).hasClass('active')){
                    $(this).removeClass('active');
                    introJs('.content-wrap').removeHints();
                    $(this).find('.hint-txt').text("@lang('admin_common.hint_off')");
                }else{
                    introJs('.content-wrap').addHints();
                    $(this).addClass('active');
                    $(this).find('.hint-txt').text("@lang('admin_common.hint_on')");
                }          
            });

             //tooltip for copy link
            jQuery('.btncopylink').tooltip({
              trigger: 'click',
              placement: 'bottom'
            });

            function setTooltip(btn, message) {
              btn.tooltip('hide')
                .attr('data-original-title', message)
                .tooltip('show');
            }

            function hideTooltip(btn) {
              setTimeout(function() {
                btn.tooltip('hide');
              }, 1000);
            }    

        });
    </script>

@stop