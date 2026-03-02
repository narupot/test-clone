@extends('layouts.admin.default')
@section('title')
    @lang('admin_category.category')
@stop
@section('header_styles')
    <link rel="stylesheet" type="text/css" href="{{Config('constants.admin_css_url') }}cropper.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <?php  
        $cropper_setting = [
            [
                'section' => 'category_image_thumb', 'dimension' => ['width' =>263, 'height'=>195], 'file_field_selector' => '#categoryThumbImage', 'section_id'=>'category-image',
            ],
        ];
    ?>

    <style>
/* การจัดวาง: ใช้ Grid Layout เพื่อสร้างคอลัมน์แบบปรับขนาดได้ */
.unit-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    gap: 4px; 
}

.unit-option {
    display: flex;
    align-items: center;
    padding: 4px 8px; 
    border: 1px solid #d2d2d7;
    border-radius: 6px; 
    background: #fff;
    cursor: pointer;
    transition: all 0.2s ease;
    width: 100%;
    box-sizing: border-box;
}

.unit-option:hover {
    border-color: #0071e3;
    background: #f5f5f7;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05); 
}

.unit-checkbox {
    width: 14px;
    height: 14px;
    border-radius: 3px;
    accent-color: #0071e3;
    margin-right: 6px; 
}

.unit-label {
    font-size: 0.8rem;
    font-weight: 500;
    color: #1d1d1f;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis; 
}

/* Container หลัก */
.keyword-container {
    max-width: 100%;
}

/* Label */
.keyword-label {
    font-size: 1rem;
    font-weight: 600;
    color: #1d1d1f;
}


/* การจัดวาง: ใช้ Grid Layout */
.package-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); /* กว้างขึ้นนิด */
    gap: 6px; 
}

.package-option {
    display: flex;
    align-items: flex-start; /* รองรับหลายบรรทัดใน label */
    padding: 6px 10px; 
    border: 1px solid #d2d2d7;
    border-radius: 8px; 
    background: #fff;
    cursor: pointer;
    transition: all 0.2s ease;
    width: 100%;
    box-sizing: border-box;
}

.package-option:hover {
    border-color: #0071e3;
    background: #f5f5f7;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08); 
}

.package-checkbox {
    width: 14px;
    height: 14px;
    border-radius: 3px;
    accent-color: #0071e3;
    margin-right: 8px; 
    margin-top: 2px;
}

.package-label {
    font-size: 0.85rem;
    font-weight: 500;
    color: #1d1d1f;
    overflow: hidden;
}

.package-label small {
    font-size: 0.7rem;
    font-weight: 400;
    color: #666;
}


/* พื้นที่สำหรับ Input และ Button */
.keyword-input-area {
    display: flex;
    gap: 8px; /* เพิ่มระยะห่างระหว่าง input และ button */
    margin-bottom: 12px;
}

/* ส่วนแสดง Tag */
.keyword-tags-container {
    display: flex;
    flex-wrap: wrap; /* ให้แท็กลงบรรทัดใหม่เมื่อเต็ม */
    gap: 8px; /* ระยะห่างระหว่างแท็ก */
    padding: 8px;
    background-color: #f8f9fa; /* สีพื้นหลัง */
    border: 1px solid #ced4da;
    border-radius: 8px;
    min-height: 48px; /* กำหนดความสูงขั้นต่ำ */
}

/* รูปแบบของแต่ละ Tag */
.keyword-tag {
    display: flex;
    align-items: center;
    padding: 4px 8px;
    background-color: #e9ecef; /* สีพื้นหลังของแท็ก */
    border-radius: 16px; /* ทำให้ขอบโค้งมน */
    font-size: 0.875rem;
    color: #495057;
    white-space: nowrap; /* ป้องกันข้อความไม่ให้ขึ้นบรรทัดใหม่ */
}

/* ปุ่มลบใน Tag */
.keyword-tag .remove-btn {
    margin-left: 8px;
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    font-size: 1rem;
    line-height: 1;
    padding: 0;
}

.keyword-tag .remove-btn:hover {
    color: #dc3545;
}
</style>
@stop
@section('content')
<script>
    var CROPPER_SETTING = {!! json_encode($cropper_setting) !!};   
    var action = '{{action("Admin\CategoryManagement\CategoryController@store")}}';
    var cat_id = '';
    var variantlisturl ='#';
    var dataJsonUrl ='{{ action("Admin\CategoryManagement\CategoryController@categorieslist")}}';
    var imageurl="#";
    var currency = "{{session('default_currency_code')}}";
    window.userFolderDefaultPath = "{{Config::get('constants.froala_img_path').md5(Auth::id()).'/'}}";

    var categoryList = "{{action('Admin\CategoryManagement\CategoryController@categorieslist')}}";
    
    var cateEditurl = "{{ action('Admin\CategoryManagement\CategoryController@categoryedit') }}";
    
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

        @if(isset($category))
            {!! Form::open([
                'url' => route('admin.category.update', $category->id),
                'method' => 'POST', 
                'id'=>'sellerCategoryForm',
                'class'=>'form-horizontal',
                'enctype' => 'multipart/form-data'
            ]) !!}
                @method('PUT') 
        @else
            {!! Form::open([
                'action' => 'Admin\CategoryManagement\CategoryController@storeParentCategory',
                'method' => 'POST',
                'id'=>'sellerCategoryForm',
                'class'=>'form-horizontal',
                'enctype' => 'multipart/form-data'
            ]) !!}
        @endif

    <div class="header-title">
        <h1 class="title">@lang('admin_category.category')</h1>

         @php( $confirm = "'".Lang::get('product.are_sure_delete_this_data')."'")
        <div class="float-right">     
            <a class="btn btn-back" href="{{ action('Admin\CategoryManagement\CategoryController@index') }}">@lang('common.back')</a>   
            <a ng-if="previewUrl"  class="btn btn-secondary deleteUrlcate" ng-href="<%previewUrl%>" target="_blank">@lang('admin_product.preview')</a>
            <a ng-if="deleteUrl" onclick="return confirm({{$confirm}});" class="btn btn-delete deleteUrlcate " ng-href="<%deleteUrl%>">@lang('admin_product.remove_category')</a>
             <input type="submit" class="btn btn-save btn-success" value="@lang('admin_category.save_category')" ng-disabled="catmoveerror" />
             
             <input type="hidden" name="productids" id="assigned_product_ids"> 
        </div>
    </div>
    <div class="content-wrap clearfix">
        <div class="breadcrumb">
            <ul class="bredcrumb-menu">
                {!!getBreadcrumbAdmin('category','category','create')!!}
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
                   @lang('admin_category.create_category')
                @endif
            </h2>
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
                                          {!!$categorydropdown!!}
                                       </select>
                                        
                                    </div>

                                {!! Form::hidden('parent_id', old('parent_id', null), ['ng-if'=> '!parent_cat', 'id'=>'parent_id']) !!}
                                   
                                @else
                                {!! Form::hidden('parent_id', old('parent_id', 0)) !!}
                                @endif

                                <div class="row mb-4">
                                    {{-- Product Group Dropdown --}}
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label font-weight-bold">
                                                @lang('admin_category.product_group') <i class="strick">*</i>
                                            </label>
                                            <select id="product_group" name="product_group" class="form-control" required>
                                                <option value="">-- เลือกกลุ่มสินค้า --</option>
                                                @foreach($productGroups as $group)
                                                    <option value="{{ $group->id }}" 
                                                        {{ (isset($category) && $category->group_id == $group->id) ? 'selected' : '' }}>
                                                        {{ $group->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Product Subgroup Dropdown --}}
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label font-weight-bold">
                                                @lang('admin_category.product_subgroup') <i class="strick">*</i>
                                            </label>
                                            <select id="product_subgroup" name="product_subgroup" class="form-control" required>
                                                <option value="">-- เลือกหมวด --</option>
                                                @if(isset($subgroups))
                                                    @foreach($subgroups as $sub)
                                                        <option
                                                            value="{{ $sub->id }}"
                                                            {{ (isset($category) && $category->subgroup_id == $sub->id) ? 'selected' : '' }}>
                                                            {{ $sub->name }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>@lang('admin_category.product_category_name') <i class="strick">*</i></label> 
                                    {!! Form::text('category_name', old('category_name', $category->category_name ?? ''), ['class'=>'form-control']) !!}
                                    @if ($errors->has('category_name'))
                                        <p class="error">{{ $errors->first('category_name') }}</p>
                                    @endif
                                </div>

                                <div class="form-group mb-3" id="package-div" style="max-width: 100%;">
                                    <label class="form-label" style="font-size: 1rem; font-weight: 600; color: #1d1d1f;">
                                        @lang('admin_category.allow_base_package') <i class="strick">*</i>
                                    </label>

                                    <div class="input-group input-group-sm mb-3" style="max-width: 300px;">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text" id="packageSearchInput" class="form-control" placeholder="@lang('admin_category.search_package_placeholder')">
                                        <button class="btn btn-outline-secondary" type="button" id="clearPackageSearchBtn" style="display: none;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>

                                    <div class="package-grid">
                                        @foreach($packages->sortBy(fn($p) => mb_strtolower($p->title)) as $package)
                                            <label class="package-option">
                                                {{ Form::checkbox(
                                                    'package[]',
                                                    $package->id,
                                                    in_array($package->id, $selectedPackages ?? []),
                                                    ['class' => 'package-checkbox']
                                                ) }}
                                                <span class="package-label">
                                                    {{ $package->title }}<br>
                                                    
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="form-group mb-3" id="unit-div" style="max-width: 100%;">
                                    <label class="form-label" style="font-size: 1rem; font-weight: 600; color: #1d1d1f;">
                                        @lang('admin_category.allow_base_unit') <i class="strick">*</i>
                                    </label>

                                    <div class="input-group input-group-sm mb-3" style="max-width: 300px;">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text" id="unitSearchInput" class="form-control" placeholder="@lang('admin_category.search_unit_placeholder')">
                                        <button class="btn btn-outline-secondary" type="button" id="clearSearchBtn" style="display: none;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div class="unit-grid">
                                        @foreach($units->sortBy(function($u) {
                                            return mb_strtolower($u->unitdesc->unit_name ?? '');
                                        }) as $unit)
                                            @if($unit->unitdesc)
                                                <label class="unit-option">
                                                    {{ Form::checkbox(
                                                        'unit[]',
                                                        $unit->id,
                                                        in_array($unit->id, $selectedUnits ?? []),
                                                        ['class' => 'unit-checkbox']
                                                    ) }}
                                                    <span class="unit-label">{{ $unit->unitdesc->unit_name }}</span>
                                                </label>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>@lang('admin_category.sorting_no') <i class="strick">(เงื่อนไข "0-9")</i> <i class="strick">*</i></label>
                                    {!! Form::number('sorting_no', old('sorting_no', $category->sorting_no ?? ''), ['class' => 'form-control']) !!}
                                    @if ($errors->has('sorting_no'))
                                        <p class="error">{{ $errors->first('sorting_no') }}</p>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>@lang('admin_category.name_en') <i class="strick">(เงื่อนไข "A-Z, _-")</i> <i class="strick">*</i></label>
                                    {!! Form::text('url', old('url', $category->url ?? ''), ['class' => 'form-control']) !!}
                                    @if ($errors->has('url'))
                                        <p class="error">{{ $errors->first('url') }}</p>
                                    @endif
                                </div>

                                 <div class="form-group">
                                    <label>@lang('admin_common.comment')</label>
                                    {!! Form::textarea('comment', old('comment', $category->comment ?? ''), ['class' => 'form-control']) !!}
                                    @if ($errors->has('comment'))
                                        <p class="error">{{ $errors->first('comment') }}</p>
                                    @endif
                                </div>
                                <div class="form-group" data-hint="@lang('admin_hint.category_7')" data-position="bottom" data-hintPosition="top-left">
                                    <label>@lang('admin_common.status')<i class="strick">*</i></label>

                                   {!! Form::select('is_deleted', [
                                        0 => 'Active',
                                        1 => 'Inactive',
                                    ], old('is_deleted', $category->is_deleted ?? 0), ['class' => 'form-control']) !!}
                                </div>
                               <div class="form-group">
                                    <label>@lang('admin_common.image')</label>

                                    {{-- แสดงรูปเก่า หรือ placeholder --}}
                                    <div style="width:100px; height:100px;  display:flex; align-items:center; justify-content:center;">
                                        @if(isset($category) && !empty($category->img))
                                            <img src="{{ getCategoryImageUrl($category->img) }}" width="100" height="100" alt="Category Image">
                                        @endif
                                    </div>


                                    {{-- เก็บค่า path ของรูป --}}
                                    <input type="hidden" name="category_image" id="categoryThumbImage" value="{{ $category->img ?? '' }}">

                                    <div class="cropper-main" id="category-image">
                                        <div class="avatar-view single-file-upload" title="Change the avatar" data-section="category_image_thumb">
                                            <img src="{{ asset('assets/images/please_upload_image.jpg') }}" alt="Upload Image" id="category_image_thumb">
                                        </div>                                
                                        @include('includes.common_cropper_upload') 
                                    </div>
                                </div>

                                <!-- <div class="form-group">
                                 <div class="advance-setting-option" data-hint="@lang('admin_hint.category_10')" data-position="bottom" data-hintPosition="top-left"> 

                                    {!! CustomHelpers::fieldstabWithLanuage([['field'=>'textarea', 'name'=>'cat_description', 'label'=>Lang::get('admin_common.description'), 'cssClass'=>'texteditor1','froala'=>'froala']],'1')!!}
                                    
                              
                                    {!! CustomHelpers::fieldstabWithLanuage([['field'=>'text', 'name'=>'meta_title', 'label'=>Lang::get('admin_common.meta_title'), 'cssClass'=>''], ['field'=>'text', 'name'=>'meta_keyword', 'label'=>Lang::get('admin_common.meta_keyword'), 'cssClass'=>''], ['field'=>'textarea', 'name'=>'meta_description', 'label'=>Lang::get('admin_common.meta_description'), 'cssClass'=>'']], '4')!!}
                                   
                                </div>
                                </div>   
                                
                                -->

                                <div class="form-group">
                                    <label>@lang('admin_common.description')</label>
                                    {!! Form::textarea('cat_description', old('cat_description', $category->cat_description ?? ''), ['class' => 'form-control texteditor1']) !!}
                                    @if ($errors->has('cat_description'))
                                        <p class="error">{{ $errors->first('cat_description') }}</p>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>@lang('admin_common.meta_title')</label>
                                    {!! Form::text('meta_title', old('meta_title', $category->meta_title ?? ''), ['class' => 'form-control']) !!}
                                    @if ($errors->has('meta_title'))
                                        <p class="error">{{ $errors->first('meta_title') }}</p>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>@lang('admin_common.meta_keyword')</label>
                                    {!! Form::text('meta_keyword', old('meta_keyword', $category->meta_keyword ?? ''), ['class' => 'form-control']) !!}
                                    @if ($errors->has('meta_keyword'))
                                        <p class="error">{{ $errors->first('meta_keyword') }}</p>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>@lang('admin_common.meta_description')</label>
                                    {!! Form::textarea('meta_description', old('meta_description', $category->meta_description ?? ''), ['class' => 'form-control']) !!}
                                    @if ($errors->has('meta_description'))
                                        <p class="error">{{ $errors->first('meta_description') }}</p>
                                    @endif
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
    <script src="{{ Config('constants.angular_app_url') }}controller/angular-froala.js"></script>
    <script src="{{ Config('constants.js_url') }}common_cropper_upload_setting.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
            var ajax_url = "{{ action('Admin\CategoryManagement\CategoryController@assignUnit') }}";

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

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const groupSelect = document.getElementById('product_group');
            const subgroupSelect = document.getElementById('product_subgroup');
            const currentGroupId = "{{ $category->group_id ?? '' }}";
            const currentSubgroupId = "{{ $category->subgroup_id ?? '' }}";

            async function loadSubgroups(groupId, selectedId = null) {
                const showSubgroupStatus = (message, isError = false) => {
                    subgroupSelect.innerHTML = `<option value="">${message}</option>`;
                    subgroupSelect.disabled = isError;
                };

                if (!groupId) {
                    showSubgroupStatus('-- เลือกกลุ่มสินค้าก่อน --');
                    return;
                }

                showSubgroupStatus('กำลังโหลดหมวด...');
                subgroupSelect.disabled = true;

                try {
                    const url = `/admin/category-management/get-subgroups/${groupId}`;
                    const response = await fetch(url);
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);

                    const data = await response.json();
                    subgroupSelect.innerHTML = '';
                    subgroupSelect.disabled = false;

                    const defaultOpt = document.createElement('option');
                    defaultOpt.value = '';
                    defaultOpt.textContent = '-- เลือกหมวด --';
                    subgroupSelect.appendChild(defaultOpt);

                    if (data.length > 0) {
                        data.forEach(sg => {
                            const opt = document.createElement('option');
                            opt.value = sg.id;
                            opt.textContent = sg.subgroup_name;
                            if (selectedId && String(selectedId) === String(sg.id)) {
                                opt.selected = true;
                            }
                            subgroupSelect.appendChild(opt);
                        });
                    } else {
                        showSubgroupStatus('ไม่พบหมวดสำหรับกลุ่มสินค้านี้');
                    }
                } catch (err) {
                    console.error('Error fetching subgroups:', err);
                    showSubgroupStatus('เกิดข้อผิดพลาดในการดึงข้อมูล', true);
                }
            }

            if (currentGroupId) {
                await loadSubgroups(currentGroupId, currentSubgroupId);
            }

            groupSelect.addEventListener('change', function() {
                const groupId = this.value;
                loadSubgroups(groupId, null);
            });
        });
    </script>


     <script>
        document.addEventListener('DOMContentLoaded', () => {
            
            const unitSearchInput = document.getElementById('unitSearchInput');
            const unitOptions = document.querySelectorAll('.unit-option');
            const clearSearchBtn = document.getElementById('clearSearchBtn'); 

            const filterUnits = (searchTerm) => {
                unitOptions.forEach(option => {
                    const unitName = option.querySelector('.unit-label').textContent.toLowerCase();
                    if (unitName.includes(searchTerm)) {
                        option.style.display = 'flex';
                    } else {
                        option.style.display = 'none';
                    }
                });
            };

            unitSearchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                filterUnits(searchTerm);
                
                
                if (searchTerm.length > 0) {
                    clearSearchBtn.style.display = 'block';
                } else {
                    clearSearchBtn.style.display = 'none';
                }
            });

            clearSearchBtn.addEventListener('click', function() {
                unitSearchInput.value = ''; 
                filterUnits(''); 
                this.style.display = 'none'; 
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // PACKAGE SEARCH
            const packageSearchInput = document.getElementById('packageSearchInput');
            const packageOptions = document.querySelectorAll('.package-option');
            const clearPackageSearchBtn = document.getElementById('clearPackageSearchBtn');

            const filterPackages = (searchTerm) => {
                packageOptions.forEach(option => {
                    const packageName = option.querySelector('.package-label').textContent.toLowerCase();
                    if (packageName.includes(searchTerm)) {
                        option.style.display = 'flex';
                    } else {
                        option.style.display = 'none';
                    }
                });
            };

            packageSearchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                filterPackages(searchTerm);

                if (searchTerm.length > 0) {
                    clearPackageSearchBtn.style.display = 'block';
                } else {
                    clearPackageSearchBtn.style.display = 'none';
                }
            });

            clearPackageSearchBtn.addEventListener('click', function() {
                packageSearchInput.value = '';
                filterPackages('');
                this.style.display = 'none';
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                let alerts = document.querySelectorAll('.alert');
                alerts.forEach(function (alert) {
                    alert.classList.add('fade');
                    setTimeout(() => alert.remove(), 500); 
                });
            }, 4000); // 4 วิ
        });
    </script>


    

   
@stop