@extends('layouts.admin.default')
@section('title')
    @lang('admin_category.create_category')
@stop
@section('header_styles')
    <link rel="stylesheet" type="text/css" href="{{Config('constants.admin_css_url') }}cropper.min.css">
    <?php  
        $cropper_setting = [
            [
                'section' => 'category_image_thumb', 'dimension' => ['width' =>263, 'height'=>195], 'file_field_selector' => '#categoryThumbImage', 'section_id'=>'category-image',
            ],
        ];
    ?>
@stop
@section('content')
<script>
    var CROPPER_SETTING = {!! json_encode($cropper_setting) !!};   
    var action = '{{action("Admin\Category\CategoryController@store")}}';
    var cat_id = '';
    var variantlisturl ='#';
    var dataJsonUrl ='{{ action("Admin\Category\CategoryController@categorieslist")}}';
    var imageurl="#";
    var currency = "{{session('default_currency_code')}}";
    window.userFolderDefaultPath = "{{Config::get('constants.froala_img_path').md5(Auth::id()).'/'}}";

    var categoryList = "{{action('Admin\Category\CategoryController@categorieslist')}}";
    
    var cateEditurl = "{{ action('Admin\Category\CategoryController@categoryedit') }}";
    
    var showHeadrePagination = true;
    var tableLoaderImgUrl = "{{ Config::get('constants.loader_url')}}ajax-loader.gif";
    //pagination config 
    var pagination = {!! getPagination() !!};
    var per_page_limt = "{{ getPagination('limit') }}";
    //for enable external pagination (get data from server on every click)
    var ext_pagination = false;

</script>
<link rel="stylesheet" type="text/css" href="{{Config('constants.css_url') }}ui-grid-unstable.css">  
<link rel="stylesheet" type="text/css" href="{{Config('constants.css_url') }}angular-ui-tree.min.css">  
<link rel="stylesheet" type="text/css" href="{{ Config('constants.admin_css_url') }}introjs.css"/>

<div class="content"  ng-controller="sellerCateCtrl" ng-cloak> 
    <!--Overlay loader show on save or save and continue click -->
     <div class="loader-wrapper" ng-if="showLoaderTable">
        <span class="loader">
            <img ng-src="<%tableLoaderImgUrl%>" alt="Loader"> 
        </span>
     </div>
    {!! Form::open(['action' => 'Admin\Category\CategoryController@store', 'id'=>'sellerCategoryForm', 'class'=>'form-horizontal','enctype' => 'multipart/form-data']) !!}
    {!! Form::hidden('_method', old('_method', 'POST')) !!}
    {!! Form::hidden('category_id', old('category_id'), ['id'=>'category_id']) !!}
    <div class="header-title">
        <h1 class="title">@lang('admin_category.product_master')</h1>  

         @php( $confirm = "'".Lang::get('product.are_sure_delete_this_data')."'")
        <div class="float-right">        
            <a ng-if="previewUrl"  class="btn btn-secondary deleteUrlcate" ng-href="<%previewUrl%>" target="_blank">@lang('admin_product.preview')</a>
            <a ng-if="deleteUrl" onclick="return confirm({{$confirm}});" class="btn btn-delete deleteUrlcate " ng-href="<%deleteUrl%>">@lang('admin_product.remove_category')</a>
             <input type="submit" class="btn btn-save btn-success" value="@lang('admin_category.save_fruit')" ng-disabled="catmoveerror" />
             
             <input type="hidden" name="productids" id="assigned_product_ids"> 
        </div>
    </div>
    <div class="content-wrap clearfix">
        <div class="breadcrumb">
            <ul class="bredcrumb-menu">
                {!!getBreadcrumbAdmin('category','category','list')!!}
            </ul>
        </div>
        <div class="row">
            <div class="col-sm-12">
                @if (Session::exists('message'))
                <div class="alert alert-success alert-dismissable margin5 mb-10">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{Session::get('message')}}
                </div>
                @endif

                <!-- to show the package limitation error -->
                @if (Session::exists('errMsg'))
                <div class="alert alert-danger alert-dismissible margin5 mb-10">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">x</a>
                    {{Session::get('errMsg')}}
                </div>
                @endif
            </div>
        </div>
        <div  ng-if="!cat_mesg">
            <h2 id="cat_mesg" class="title-prod">
                @if(isset($subcat_mesg))
                    {{$subcat_mesg}}
                @else
                   @lang('admin_category.create_main_fruit')
                @endif
            </h2>
        </div>
        <div class="content-left">

            @include('admin.includes.category_menu')

        </div>
        <div class="content-right">
            <!-- BEGIN SIDEBAR MENU -->
            
           
            <!-- END SIDEBAR MENU -->
            <div class="category-right">
                <div class="category-tab saveCat clearfix">
                    <div class="tab-list">
                        
                    </div>
                    <ul class="nav nav-tabs listing-nav-tabs mb-0">
                        <li class="nav-item">
                            <a class="nav-link show active" data-ng-click="enableTab('deactive')" href="#cetegory-general-info" data-toggle="tab">@lang('admin_category.general_infomation_tab')</a>
                        </li>
                        
                    </ul>
                </div>
                <div class="box nobg">
                    <div class="tab-content row">
                        <div id="cetegory-general-info" class="tab-pane fade show active col-sm-7" >
                            <div class="category-gen-form" data-hint="@lang('admin_hint.category_4')" data-position="bottom" data-hintPosition="top-left">

                                <input type="hidden" name="catmoveerror" value="<%catmoveerror%>">

                                @if(isset($category->id))

                                 {!! Form::hidden('parent_id', old('parent_id', $category->id))!!}
                                
                                @elseif(isset($categoriesids) && !empty($categoriesids))
                                    <div class="form-group" ng-if="parent_cat">
                                        <label>@lang('admin_category.main_fruit')<span class="star-top">*</span></label>


                                        <select name="parent_id" id="parent_id" class="form-control" >
                                          <option value="">@lang('admin_category.please_select')</option>
                                          {!!$categorydropdown!!}}  
                                       </select> 
                                        
                                    </div>

                                {!! Form::hidden('parent_id', old('parent_id', null), ['ng-if'=> '!parent_cat', 'id'=>'parent_id']) !!}
                                   
                                @else
                                {!! Form::hidden('parent_id', old('parent_id', 0)) !!}
                                @endif
                          

                                <div class="form-group">
                                 {!! CustomHelpers::fieldstabWithLanuage([['field'=>'text', 'name'=>'category_name', 'label'=>Lang::get('admin_category.fruit_name'), 'cssClass'=>'', 'errorkey'=>'name']], '2', $errors)!!}
                                </div>
                                
                                <div class="form-group" id="unit-div">
                                    <label>@lang('admin_category.allow_base_unit')</label>
                                    @foreach($units as $unit)
                                      <label class="check-wrap">
                                        {{ Form::checkbox('unit',$unit->id,null, array('name'=>'unit[]')) }}
                                        <span class="chk-label">{{$unit->unitdesc->unit_name}}</span></label>  
                                    @endforeach
                                </div>

                                <div class="form-group">
                                    <label>@lang('admin_category.url')</label>
                                    {!! Form::text('url', old('url'), ['ng-model'=>'url']) !!} 
                                    @if ($errors->has('url'))
                                        <p id="name-error" class="error error-msg">{{ $errors->first('url') }}</p>
                                    @endif
                                </div>

                                <div class="form-group" data-hint="@lang('admin_hint.category_6')" data-position="bottom" data-hintPosition="top-left">
                                    <label>@lang('admin_category.comment')</label>
                                    {!! Form::textarea('cat_comment', old('cat_comment')) !!}
                                </div>
                                
                                @if($status)
                                    <div class="form-group" data-hint="@lang('admin_hint.category_7')" data-position="bottom" data-hintPosition="top-left">
                                        <label>@lang('admin_common.status')<i class="strick">*</i></label>
                                        {!! Form::select('status', [],  null, ['ng-model'=>'status', 'ng-options'=> 'template.name for template in statusdropdown.configs track by template.value']) !!}
                                    </div>
                                @else
                                    {!! Form::hidden('status', old('status', 1)) !!}

                                @endif

                                <div class="form-group" data-hint="@lang('admin_hint.category_9')" data-position="bottom" data-hintPosition="top-left">
                                    <label>@lang('admin_common.image')</label>
                                    <img ng-src="<%display_mode.image%>" width="100px" height="100px" ng-show="display_mode.image">                                    
                                    <input type="hidden" name="category_image" id="categoryThumbImage">
                                    <div class="cropper-main" id="category-image">   
                                        <div class="avatar-view single-file-upload" title="Change the avatar" data-section="category_image_thumb">
                                            <img src="{{asset('assets/images/please_upload_image.jpg')}}" alt="" id="category_image_thumb">
                                        </div>                                
                                        @include('includes.common_cropper_upload') 
                                    </div> 
                                </div> 
                                <div class="form-group">
                                 <div class="advance-setting-option" data-hint="@lang('admin_hint.category_10')" data-position="bottom" data-hintPosition="top-left"> 

                                    {!! CustomHelpers::fieldstabWithLanuage([['field'=>'textarea', 'name'=>'cat_description', 'label'=>Lang::get('admin_common.description'), 'cssClass'=>'texteditor1','froala'=>'froala']],'1')!!}
                                    
                              
                                    {!! CustomHelpers::fieldstabWithLanuage([['field'=>'text', 'name'=>'meta_title', 'label'=>Lang::get('admin_common.meta_title'), 'cssClass'=>''], ['field'=>'text', 'name'=>'meta_keyword', 'label'=>Lang::get('admin_common.meta_keyword'), 'cssClass'=>''], ['field'=>'textarea', 'name'=>'meta_description', 'label'=>Lang::get('admin_common.meta_description'), 'cssClass'=>'']], '4')!!}
                                   
                                </div>
                                </div>                               
                                
                            </div>
                            
                            
                               

                        </div>

                    </div>
                </div>
            </div>
            <div class="loading-more-indicator loader-container" ng-show="loadingMore">
               <div class="loader"></div>
            </div>
        </div>
    </div>  
    <div class="push-content"></div>
    {!! Form::close() !!}
</div>
@endsection 
@section('footer_scripts')
    <script src="{{ Config('constants.js_url') }}jquery-cropper.min.js" type="text/javascript"></script>
    @include('includes.froalaeditor_dependencies') 
    @include('includes.cate_blog_js_desp')

    <script src="{{ Config('constants.angular_app_url') }}controller/sellerCateCtrl.js"></script>
    <script src="{{ Config('constants.js_url') }}common_cropper_upload_setting.js"></script>
    <script>    
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

             $('.tablist a').on('click',function(){
                introJs('.content-wrap').removeHints();
                $('.hint-btn a').removeClass('active');                
                $('.hint-txt').text("@lang('admin_common.hint_off')");
            }); 


            // Clipboard
            //var clipboard = new ClipboardJS('.btncopylink');

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

/*            clipboard.on('success', function(e) {
              var btn = $(e.trigger);
              setTooltip(btn, 'Copied');
              hideTooltip(btn);
            });*/
            var ajax_url = "{{ action('Admin\Category\CategoryController@assignUnit') }}";

            jQuery('#parent_id').change(function(e){
                var parent_id = $(this).val();
                if(parent_id ==''){
                   return false;
                }
                var data = 'id='+parent_id;
                $.ajax({
                    url: ajax_url,
                    type:"POST",
                    data:data,
                    headers : {
                        
                        'X-CSRF-TOKEN' : window.Laravel.csrfToken,
                    },
                    beforeSend: function(){
                       jQuery('#unit-div input').attr('checked',false); 
                    },
                    success:function(result){  
                        jQuery.each( result, function( key, value ) {
                            jQuery('#unit-div input[value="'+key+'"]').attr('checked',true);
                        });
                    }
                });


            });

        });



    </script>
   
@stop