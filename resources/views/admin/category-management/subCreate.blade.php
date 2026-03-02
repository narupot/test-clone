@extends('layouts.admin.default')
@section('title')
    @lang('admin_category.create_type_master')
@stop
@section('header_styles')
<link rel="stylesheet" type="text/css" href="{{Config('constants.admin_css_url') }}cropper.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
/* การจัดวาง: ใช้ Grid Layout เพื่อสร้างคอลัมน์แบบปรับขนาดได้ */
.unit-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    gap: 4px; 
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


    <link rel="stylesheet" type="text/css" href="{{Config('constants.create_type_master') }}cropper.min.css">
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

    // New: Pass initial data to JavaScript
    var initialData = {
        category: {!! isset($category) ? json_encode($category) : 'null' !!},
        productTypeTags: {!! isset($productTypeTags) ? json_encode($productTypeTags) : '[]' !!},
        productSubGroups: {!! isset($productSubGroups) ? json_encode($productSubGroups) : '[]' !!},
        catunit: {!! isset($catunit) ? json_encode($catunit) : '[]' !!},
    };

</script>
<link rel="stylesheet" type="text/css" href="{{Config('constants.css_url') }}ui-grid-unstable.css">   
<link rel="stylesheet" type="text/css" href="{{Config('constants.css_url') }}angular-ui-tree.min.css">   
<link rel="stylesheet" type="text/css" href="{{ Config('constants.admin_css_url') }}introjs.css"/>


<div class="content"  > 
    <div class="loader-wrapper" ng-if="showLoaderTable">
       <span class="loader">
           <img ng-src="<%tableLoaderImgUrl%>" alt="Loader"> 
       </span>
     </div>
    @if(isset($category->id))
        {!! Form::model($category, [
            'route' => ['category-management.updateCategory', $category->id],
            'method' => 'POST',
            'id' => 'sellerCategoryForm',
            'class' => 'form-horizontal',
            'enctype' => 'multipart/form-data'
        ]) !!}
    @else
        {!! Form::open([
            'route' => 'category-management.store',
            'id' => 'sellerCategoryForm',
            'class' => 'form-horizontal',
            'enctype' => 'multipart/form-data'
        ]) !!}
    @endif

    {!! Form::hidden('category_id', old('category_id'), ['id'=>'category_id']) !!}

    <div class="header-title">
        <h1 class="title">
            @if(isset($category->id))
                @lang('admin_category.edit_main_product_type')
            @else
                @lang('admin_category.create_main_product_type')
            @endif
        </h1>
          @php( $confirm = "'".Lang::get('product.are_sure_delete_this_data')."'")
        <div class="float-right">     
            <a class="btn btn-back" href="{{ action('Admin\CategoryManagement\CategoryController@subcategorylist') }}">@lang('common.back')</a>   
            <a ng-if="previewUrl"  class="btn btn-secondary deleteUrlcate" ng-href="<%previewUrl%>" target="_blank">@lang('admin_product.preview')</a>
            <a ng-if="deleteUrl" onclick="return confirm({{$confirm}});" class="btn btn-delete deleteUrlcate " ng-href="<%deleteUrl%>">@lang('admin_product.remove_category')</a>

             <input type="submit" 
                 class="btn btn-success" 
                 value="{{ isset($category->id) ? __('common.update') : __('common.save') }}">

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
              
                   @lang('admin_category.create_type_master')
        
            </h2>
        </div>
       
        <div class="content-right">
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
                            @if(isset($category->id))
                                {!! Form::hidden('parent_id', old('parent_id', $category->id))!!}
                            @elseif(isset($categoriesids) && !empty($categoriesids))

                                
                                {!! Form::hidden('parent_id', old('parent_id', null), ['ng-if'=> '!parent_cat', 'id'=>'parent_id']) !!}
                            @else
                               
                               
                            @endif
                                <div class="row g-3 mb-4" style="max-width: 800px;">
                                    {{-- Product Group Dropdown --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">@lang('admin_category.product_group') <i class="strick">*</i></label>
                                        <select id="product_group" class="form-select" name="product_group_id">
                                            <option value="">-- เลือกกลุ่มสินค้า --</option>
                                            @foreach($productGroups as $group)
												<option value="{{ $group->id }}" {{ $group->id == ($currentGroupId ?? '') ? 'selected' : '' }}>
													{{ $group->name }}
												</option>
											@endforeach
                                        </select>
                                    </div>

                                    {{-- Product Subgroup Dropdown --}}
                                     <div class="col-md-6">
                                        <label class="form-label fw-bold">@lang('admin_category.product_subgroup') <i class="strick">*</i></label>
                                        <select id="product_subgroup" class="form-select" name="product_subgroup_id">
                                            <option value="">-- เลือกหมวด --</option>
                                        </select>
                                    </div>
                                </div>
                                {!! Form::hidden('parent_id', old('parent_id', 0), ['id'=>'parent_id_hidden']) !!} 
                                <input type="hidden" name="catmoveerror" value="<%catmoveerror%>">

                            

                                {{-- Select Dropdown ตัวนี้ต้องใช้ id="parent_id" เพื่อให้ JS อ้างถึง --}}
                                <label class="form-label fw-bold">@lang('admin_category.category') <i class="strick">*</i></label>
                                <div class="form-group mb-4" id="parent_cat_div" style="max-width: 800px;">
                                    <select name="parent_id" id="parent_select_id" class="form-control"></select>
                                </div>

                                <div class="form-group">
                                    <label for="category_name">
                                        @lang('admin_category.name_type') <i class="strick">*</i>
                                    </label>
                                    {!! Form::text('category_name', old('category_name', $category->categorydesc->category_name ?? ''), ['class' => 'form-control', 'id' => 'category_name']) !!}
                                </div>


                                <div class="form-group">
                                    <label>@lang('admin_category.name_en') <i class="strick">(เงื่อนไข "A-Z, _-")</i> <i class="strick">*</i></label>
                                   {!! Form::text('url', old('url', $category->url ?? ''), ['class' => 'form-control', 'id' => 'category-url']) !!}
                                    @if ($errors->has('url'))
                                        <p id="name-error" class="error error-msg">{{ $errors->first('url') }}</p>
                                    @endif
                                </div>

                                <div class="form-group" data-hint="@lang('admin_hint.category_6')" data-position="bottom" data-hintPosition="top-left">
                                    <label>@lang('admin_category.comment')</label>
                                    {!! Form::textarea('cat_comment', $category->comment ?? '', ['class' => 'form-control']) !!}
                                </div>

                                <div class="form-group" data-hint="@lang('admin_hint.category_6')" data-position="bottom" data-hintPosition="top-left">
                                    <label>@lang('admin_category.description')</label>
                                    {!! Form::textarea('cat_description', $category->categorydesc->cat_description ?? '', ['class' => 'form-control']) !!}
                                </div>
                                
                                 <div class="form-group" data-hint="@lang('admin_hint.category_7')" data-position="bottom" data-hintPosition="top-left">
                                    <label>@lang('admin_common.status')<i class="strick">*</i></label>

                                   {!! Form::select('status', [
                                        1 => 'Active',
                                        0 => 'Inactive',
                                    ], old('status', $category->status ?? 0), ['class' => 'form-control']) !!}
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
                                <div class="form-group">
                                    <div class="advance-setting-option" data-hint="@lang('admin_hint.category_10')" data-position="bottom" data-hintPosition="top-left">
                                    
                                    <hr>

                                    <div class="form-group">
                                        <label for="meta_title">@lang('admin_common.meta_title')</label>
                                        {!! Form::text('meta_title', $category->categorydesc->meta_title ?? '', ['class' => 'form-control']) !!}
                                    </div>

                                    <div class="form-group">
                                        <label for="meta_keyword">@lang('admin_common.meta_keyword')</label>
                                        {!! Form::text('meta_keyword', $category->categorydesc->meta_keyword ?? '', ['class' => 'form-control']) !!}
                                    </div>

                                    <div class="form-group">
                                        <label for="meta_description">@lang('admin_common.meta_description')</label>
                                        {!! Form::textarea('meta_description', $category->categorydesc->meta_description ?? '', ['class' => 'form-control']) !!}
                                    </div>

                                </div>
                                </div>

                               
                               <div class="form-group keyword-container" id="keyword-div">
                                    <label class="form-label keyword-label">@lang('admin_category.tag')</label>
                                    <div class="keyword-input-area d-flex gap-2">
                                        <input type="text" id="keyword-input" class="form-control" placeholder="พิมพ์คีย์เวิร์ดแล้วกดเพิ่ม">
                                        <button type="button" id="add-keyword" class="btn btn-primary">เพิ่ม</button>
                                    </div>
                                    <div id="keyword-tags" class="keyword-tags-container mt-2"></div>

                                    <input type="hidden" 
                                    name="keywords" 
                                    id="keywords-hidden" 
                                    value="{{ old('keywords', isset($productTypeTags) ? json_encode($productTypeTags) : '[]') }}">

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

 	<!-- <script src="{{ Config('constants.angular_app_url') }}controller/sellerCateCtrl.js"></script> -->
 	<script src="{{ Config('constants.js_url') }}common_cropper_upload_setting.js"></script>
 	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js"></script>
 	<script> 	 	
 		$(document).ready(function(){ 	 	 	
            
            // ====================================================
            // Select2 Initialization and Display Handler
            // ====================================================

            // Function to update the display text below the dropdown
            const updateCategoryDisplay = () => {
                var selectedText = $('#parent_select_id').find('option:selected').text();
                
                // ตรวจสอบว่ามีค่าถูกเลือกหรือไม่ (value ไม่ว่างเปล่า)
                if ($('#parent_select_id').val()) {
                    $('#selected_category_display').html(`<strong>ประเภทสินค้าที่เลือก:</strong> ${selectedText}`);
                } else {
                    $('#selected_category_display').html(`<strong>ประเภทสินค้าที่เลือก:</strong> ยังไม่มีการเลือก`);
                }
            };
            
            // เปิดใช้งาน Select2 สำหรับช่องค้นหาประเภทสินค้า
            $('#parent_select_id').select2({
                // กำหนด placeholder
                placeholder: "-- ค้นหาหรือเลือกประเภทสินค้าหลัก --", 
                // อนุญาตให้ล้างค่าที่เลือกออกได้
                allowClear: true,
            });

            // Bind change event to the display handler
            $('#parent_select_id').on('change', updateCategoryDisplay);
            
            // Trigger display update once on initial load
            updateCategoryDisplay(); 

            // ====================================================
            // Original jQuery Logic
            // ====================================================

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

 /* clipboard.on('success', function(e) {
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


	<select name="parent_id" id="parent_select_id" class="form-control"></select>

	<script>
	document.addEventListener('DOMContentLoaded', () => {
		const groupSelect = document.getElementById('product_group');
		const subgroupSelect = document.getElementById('product_subgroup');
		const parentCategorySelect = document.getElementById('parent_select_id');
		const parentCategoryDiv = document.getElementById('parent_cat_div');

		const initialData = {
			parent_id: "{{ $parent_id ?? '' }}",
			product_group_id: "{{ $currentGroupId ?? '' }}",
			product_subgroup_id: "{{ $currentSubGroupId ?? '' }}"
		};

		const fetchSubgroups = async (groupId) => {
			try {
				const res = await fetch(`/admin/category-management/get-subgroups/${groupId}`);
				if (!res.ok) throw new Error(`Subgroup fetch failed (${res.status})`);
				return await res.json();
			} catch (e) {
				console.error('Error fetching subgroups:', e);
				return [];
			}
		};

		const fetchParentCategories = async (subgroupId) => {
			try {
				const res = await fetch(`/admin/category-management/get-parent-category-name/${subgroupId}`);
				if (!res.ok) throw new Error(`Parent fetch failed (${res.status})`);
				return await res.json();
			} catch (e) {
				console.error('Error fetching parent categories:', e);
				return [];
			}
		};

		const delay = (ms) => new Promise(r => setTimeout(r, ms));

		const loadParentCategories = async (subgroupId) => {
			parentCategorySelect.innerHTML = '<option value="">กำลังโหลด...</option>';
			parentCategoryDiv.style.display = 'block';

			// destroy Select2 ชั่วคราวระหว่างโหลด
			if ($(parentCategorySelect).data('select2')) {
				$(parentCategorySelect).select2('destroy');
			}

			if (!subgroupId) {
				parentCategorySelect.innerHTML = '<option value="">-- กรุณาเลือก ประเภทสินค้า --</option>';
				$(parentCategorySelect).select2({ placeholder: "-- ค้นหาหรือเลือกประเภทสินค้าหลัก --", allowClear: true });
				return;
			}

			const parentCats = await fetchParentCategories(subgroupId);

			parentCategorySelect.innerHTML = '<option value="">-- กรุณาเลือก ประเภทสินค้า --</option>';

			if (Array.isArray(parentCats) && parentCats.length > 0) {
				parentCats.forEach(cat => {
					const opt = document.createElement('option');
					opt.value = cat.id;
					opt.textContent = cat.category_name;
					parentCategorySelect.appendChild(opt);
				});
			} else {
				parentCategorySelect.innerHTML = '<option value="">ไม่พบประเภทสินค้าที่เกี่ยวข้อง</option>';
			}

			// re-init Select2 หลัง options เติมเสร็จ
			$(parentCategorySelect).select2({
				placeholder: "-- ค้นหาหรือเลือกประเภทสินค้าหลัก --",
				allowClear: true
			});

			await delay(350); // รอให้ select2 สร้าง DOM เสร็จก่อน set ค่า

			// ตั้งค่า preselect ถ้ามี
			if (initialData.parent_id) {
				parentCategorySelect.value = initialData.parent_id;
				$(parentCategorySelect).trigger('change');
				console.log("Preselected parent:", initialData.parent_id);
				initialData.parent_id = ''; // เคลียร์หลังใช้งาน
			}
		};

		subgroupSelect.addEventListener('change', async function () {
			const subgroupId = this.value;
			await loadParentCategories(subgroupId);
		});

		groupSelect.addEventListener('change', async function () {
			const groupId = this.value;
			subgroupSelect.innerHTML = '<option value="">กำลังโหลด...</option>';
			subgroupSelect.disabled = true;

			const subgroups = groupId ? await fetchSubgroups(groupId) : [];

			subgroupSelect.innerHTML = '<option value="">-- เลือกหมวด --</option>';
			subgroups.forEach(sg => {
				const opt = document.createElement('option');
				opt.value = sg.id;
				opt.textContent = sg.subgroup_name;
				subgroupSelect.appendChild(opt);
			});
			subgroupSelect.disabled = false;

			// Preselect subgroup ถ้ามี
			if (initialData.product_subgroup_id) {
				subgroupSelect.value = initialData.product_subgroup_id;
				console.log("Preselected subgroup:", initialData.product_subgroup_id);
				const tempSubgroupId = initialData.product_subgroup_id;
				initialData.product_subgroup_id = '';
				await delay(100);
				await loadParentCategories(tempSubgroupId);
			} else {
				subgroupSelect.dispatchEvent(new Event('change'));
			}
		});

		// เริ่มต้นโหลด group/subgroup ที่เลือกไว้ก่อนหน้า
		if (initialData.product_group_id) {
			groupSelect.value = initialData.product_group_id;
			groupSelect.dispatchEvent(new Event('change'));
		} else {
			$(parentCategorySelect).select2({
				placeholder: "-- ค้นหาหรือเลือกประเภทสินค้าหลัก --",
				allowClear: true
			});
		}
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

 			// Set initial checked state for units
 			const initialCatunits = initialData.catunit;
 			if (initialCatunits && Object.keys(initialCatunits).length > 0) {
 				Object.keys(initialCatunits).forEach(unitId => {
 					const unitCheckbox = document.querySelector(`input[value="${unitId}"]`);
 					if (unitCheckbox) {
 						unitCheckbox.checked = true;
 					}
 				});
 			}

 		});
 	</script>

 	<!-- keywordInput -->
 	<script>

 		document.addEventListener('DOMContentLoaded', function() {
 			const keywordInput = document.getElementById('keyword-input');
 			const addKeywordBtn = document.getElementById('add-keyword');
 			const keywordTagsContainer = document.getElementById('keyword-tags');
 			const hiddenInput = document.getElementById('keywords-hidden');
 			const categoryNameInput = document.getElementById('category_name');
 			const alertMessage = document.createElement('div');

 			alertMessage.className = 'text-danger mt-2';
 			alertMessage.style.display = 'none';
 			keywordInput.parentNode.appendChild(alertMessage);

 			let keywords = [];
 			let originalCategoryName = categoryNameInput.value.trim();

 			try {
 				keywords = JSON.parse(hiddenInput.value || '[]');
 				if (!Array.isArray(keywords)) {
 					keywords = [];
 				}
 			} catch (e) {
 				keywords = [];
 			}
 			
 			if (originalCategoryName && !keywords.includes(originalCategoryName)) {
 				keywords.unshift(originalCategoryName);
 			}

 			function renderKeywords() {
 				keywordTagsContainer.innerHTML = '';
 				const fragment = document.createDocumentFragment();

 				keywords.forEach(keyword => {
 					const tag = document.createElement('div');
 					tag.className = 'keyword-tag';
 					
 					const isCategoryKeyword = (keyword === originalCategoryName);
 					
 					let tagHtml = `<span>${keyword}</span>`;
 					if (!isCategoryKeyword) {
 						tagHtml += `<button type="button" class="remove-btn">&times;</button>`;
 					}

 					tag.innerHTML = tagHtml;
 					fragment.appendChild(tag);
 				});

 				keywordTagsContainer.appendChild(fragment);
 				hiddenInput.value = JSON.stringify(keywords);
 			}

 			function addKeywords() {
 				const inputValue = keywordInput.value.trim();
 				if (inputValue === '') {
 					return;
 				}
 				
 				const inputKeywords = inputValue
 					.split(',')
 					.map(k => k.trim())
 					.filter(k => k.length > 0);

 				let duplicateFound = false;
 				
 				inputKeywords.forEach(keyword => {
 					if (!keywords.includes(keyword)) {
 						keywords.push(keyword);
 					} else {
 						duplicateFound = true;
 					}
 				});
 				
 				renderKeywords();
 				keywordInput.value = '';

 				if (duplicateFound) {
 					alertMessage.textContent = 'มีบางคำซ้ำกับคีย์เวิร์ดที่มีอยู่แล้ว';
 					alertMessage.style.display = 'block';
 				} else {
 					alertMessage.style.display = 'none';
 				}
 			}

 			function updateCategoryKeyword() {
 				const newCategoryName = categoryNameInput.value.trim();
 				
 				const oldKeywordIndex = keywords.indexOf(originalCategoryName);
 				const newKeywordIndex = keywords.indexOf(newCategoryName);

 				if (newCategoryName.length > 0) {
 					if (oldKeywordIndex > -1) {
 						if (newKeywordIndex === -1 || newKeywordIndex === oldKeywordIndex) {
 							keywords[oldKeywordIndex] = newCategoryName;
 						}
 					} else if (!keywords.includes(newCategoryName)) {
 						keywords.unshift(newCategoryName);
 					}
 				} else {
 					if (oldKeywordIndex > -1) {
 						keywords.splice(oldKeywordIndex, 1);
 					}
 				}

 				originalCategoryName = newCategoryName;

 				renderKeywords();
 			}

 			addKeywordBtn.addEventListener('click', addKeywords);
 			
 			keywordInput.addEventListener('keypress', function(e) {
 				if (e.key === 'Enter') {
 					e.preventDefault();
 					addKeywords();
 				}
 			});

 			keywordTagsContainer.addEventListener('click', function(e) {
 				if (e.target.classList.contains('remove-btn')) {
 					const tagToRemove = e.target.closest('.keyword-tag');
 					const keywordText = tagToRemove.querySelector('span').textContent;

 					if (keywordText === originalCategoryName) {
 						return;
 					}
 					
 					keywords = keywords.filter(k => k !== keywordText);
 					renderKeywords();
 					alertMessage.style.display = 'none';
 				}
 			});
 			
 			keywordInput.addEventListener('input', function() {
 				alertMessage.style.display = 'none';
 			});

 			categoryNameInput.addEventListener('blur', updateCategoryKeyword);
 			
 			renderKeywords();
 		});
 	</script>


 	<script>
 		document.addEventListener('DOMContentLoaded', function () {
 			setTimeout(function () {
 				let alerts = document.querySelectorAll('.alert');
 				alerts.forEach(function (alert) {
 					alert.classList.add('fade');
 					setTimeout(() => alert.remove(), 500); // ลบออกจาก DOM หลัง fade
 				});
 			}, 4000); // 4 วิ
 		});
 	</script>
    
@stop