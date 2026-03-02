@extends('layouts.admin.default')

@section('title')
    @if(isset($group))
        แก้ไขข้อมูลกลุ่ม
    @else
        เพิ่มข้อมูลกลุ่ม
    @endif
@stop

@section('header_styles')
{{-- 1. INCLUDE CROPPER.JS CSS --}}
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" rel="stylesheet">
<style>
    /* ... Your existing styles ... */
    form .form-label {
        font-weight: 600 !important;
        color: #374151 !important;
    }
    .card form .form-control, 
    .card form select {
        width: 100% !important;
        padding: 10px 14px !important;
        border-radius: 6px !important;
        border: 1px solid #d1d5db !important;
        font-size: 14px !important;
        transition: 0.2s !important;
    }
    .card form .form-control:focus {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.2) !important;
        outline: none !important;
    }

    .card .btn {
        font-size: 14px !important;
        border-radius: 6px !important;
        transition: 0.2s ease-in-out !important;
    }

    .card .btn:hover {
        transform: translateY(-1px) !important;
        opacity: 0.95 !important;
    }

    .btn-outline-secondary {
        border: 1px solid #ccc !important;
        background-color: #fff !important;
        color: #333 !important;
    }

    .btn-success {
        background-color: #28a745 !important;
        color: white !important;
    }

    .btn-secondary {
        background-color: #6c757d !important;
        color: white !important;
    }

    .card {
        max-width: 720px;
        margin-top: 20px;
        background: #fff;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .form-error {
        font-size: 13px !important;
        color: #e11d48 !important;
        margin-top: 4px !important;
    }

    .upload-box {
        width: 240px;
        height: 140px;
        border: 2px dashed #ced4da !important;
        border-radius: 10px !important;
        cursor: pointer;
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
        background-color: #f8f9fa !important;
        transition: all 0.3s ease !important;
    }

    .upload-box:hover {
        background-color: #e9ecef !important;
        border-color: #86b7fe !important;
    }

    .preview-img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        border-radius: 10px !important;
    }

    #upload-placeholder i {
        color: #486e8fff !important;
        border-radius: 10px !important;
    }

    #upload-placeholder p {
        color: #6c757d !important;
        margin-top: 8px !important;
    }

    /* Styles specific to Cropper Modal */
    .modal-dialog {
        max-width: 800px;
    }
    .img-container {
        max-height: 800px;
        overflow: hidden;
        display: flex;
        justify-content: center;
    }
    .img-container img {
        max-width: 100%;
    }
    .modal-body .img-container {
        height: 400px; 
        min-height: 400px;
        max-height: 70vh;
        overflow: hidden;
        display: block;
    }


    .modal-body .img-container img {
        display: block;
        max-width: none !important; 
    }

    .cropper-main {
    width: 100%;
    max-width: 600px;
    height: 400px;
    margin: 0 auto;
    }

    .cropper-main img {
    display: block;
    max-width: 100%;
    width: 100%;
    height: auto;
    }
    
</style>

@stop

@section('content')
<div class="content"> 
    {{-- MODAL FOR CROPPING IMAGE --}}
    <div class="modal fade" id="cropImageModal" tabindex="-1" role="dialog" aria-labelledby="cropImageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document"> 
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cropImageModalLabel">ปรับขนาดและตำแหน่งรูปภาพ</h5>
                </div>
                <div class="modal-body">
                    <!-- <div class="img-container mb-3"> 
                        <img id="imageToCrop" src="" style="max-width: 100%;"> 
                    </div> -->
                    <div class="cropper-main" id="category-image" >
                        <div class="" title="" data-section="category_image_thumb" style="width: 600px; height: 400px;">
                             <img id="imageToCrop" src="" style="max-width: 100%;"> 
                        </div>                                
                    </div>
                </div>
                <div class="modal-footer">
                
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-success" id="cropButton">ยืนยัน</button>
                </div>
            </div>
        </div>
    </div>
    {{-- END MODAL --}}

    <div class="card">
        {{-- Conditional form action for Add/Edit --}}
        @if(isset($group))
            <form id="groupForm" method="POST" action="{{ action('Admin\Groups\GroupsController@update', $group->id) }}" enctype="multipart/form-data">
            @method('PUT') {{-- Use PUT method for update --}}
        @else
            <form id="groupForm" method="POST" action="{{ action('Admin\Groups\GroupsController@store') }}" enctype="multipart/form-data">
        @endif
            {{ csrf_field() }}

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">
                    @if(isset($group))
                        แก้ไขกลุ่ม: {{ $group->name }}
                    @else
                        เพิ่มกลุ่มใหม่
                    @endif
                </h2>
                <div class="d-flex">
                    <a class="btn btn-back" href="{{ action('Admin\Groups\GroupsController@index') }}">
                        ← ยกเลิก
                    </a>
                    <button type="submit" form="groupForm" name="submit_type" value="submit" class="btn btn-success" style="margin-left: 10px;">
                        บันทึก
                    </button>
                </div>
            </div>

            <div class="mb-4">
                <label for="group_name" class="form-label">ชื่อกลุ่ม <span class="red">*</span></label>
                <input type="text" id="group_name" name="group_name" class="form-control" value="{{ old('name', $group->name ?? '') }}">
                @error('group_name')
                    <div class="red">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="group_image_file" class="form-label fw-bold">รูปภาพกลุ่ม</label>

                <div class="image-upload-wrapper position-relative">
                    {{-- 2. ORIGINAL FILE INPUT IS NOW HIDDEN AND HAS onchange='showCropModal(event)' --}}
                    <input type="file" id="group_image_file" class="d-none" accept="image/*" onchange="showCropModal(event)">
                    
                    {{-- 3. HIDDEN INPUT FIELD TO SEND CROPPED DATA TO SERVER --}}
                    <input type="hidden" id="group_image_cropped" name="group_image" value="">
                    
                    <button type="button"
                                 class="upload-box"
                                 onclick="document.getElementById('group_image_file').click()" {{-- Trigger the new input --}}
                                 aria-label="คลิกเพื่ออัปโหลดรูป"
                                 style="border: none; background: none; padding: 0;"
                    >
                        {{-- Image preview and placeholder logic --}}
                        <img id="preview" 
                            class="preview-img @unless(isset($group->image) && $group->image) d-none @endunless" 
                            alt="Group preview" 
                            {{-- Check if $group->image exists AND the input field for cropped image is empty (for new uploads) --}}
                            @if(isset($group->image) && $group->image) src="{{ asset($group->image) }}" @endif />
                        
                        <div id="upload-placeholder" class="upload-box text-center p-4 border border-dashed rounded shadow-sm"
                             @if(isset($group->image) && $group->image) style="display: none;" @endif>
                            <i class="fa fa-cloud-upload fa-3x text-primary mb-2"></i>
                            <p class="text-muted mb-0">คลิกเพื่ออัปโหลดรูป</p>
                        </div>
                    </button>
                </div>

                @error('group_image')
                    <p class="form-error text-danger">{{ $message }}</p>
                @enderror
                @if(isset($group->group_image) && $group->group_image)
                    <small class="text-muted mt-2 d-block">อัปโหลดรูปใหม่เพื่อแทนที่รูปเดิม</small>
                @endif
            </div>


            <div class="mb-4">
                <label for="sort_order" class="form-label">ลำดับ</label>
                <input type="number" id="sort_order" name="sort_order" class="form-control w-25" value="{{ old('sorting_no', $group->sorting_no ?? 0) }}">
                @error('sorting_no')
                    <p class="form-error text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-3">
                <label for="groupStatus" class="form-label d-block">Status</label>
                <label class="button-switch mt-2">
                    {{-- Checkbox status based on existing group data or default to checked for new --}}
                    <input type="checkbox" name="status" value="1" class="switch switch-orange" id="groupStatus" 
                            @if(isset($group)) {{ ($group->status == 1) ? 'checked' : '' }} @else checked @endif>
                    <span class="lbl-off">off</span>
                    <span class="lbl-on">on</span>
                </label>
            </div>
        </form>
    </div>
</div>
@stop

@section('footer_scripts')
<script src="{{ Config('constants.js_url') }}jquery.validate.min.js"></script>
{{-- 4. INCLUDE CROPPER.JS LIBRARY --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>

<script>
    function validateForm(formId, rules, messages) {
        $("#" + formId).validate({
            rules: rules,
            messages: messages,
            errorClass: "text-danger",
            submitHandler: function(form) {
                form.submit(); 
            }
        });
    }

    (function($){
        const rules = {
            'group_name': 'required'
        };
        const messages = {
            'group_name': 'กรุณากรอกชื่อกลุ่ม'
        };
        // Ensure form validation targets the correct form ID
        validateForm('groupForm', rules, messages);
    })(jQuery);
</script>

<script>
    let cropper; 
    const cropModalElement = document.getElementById('cropImageModal');
    let cropModal;

    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        cropModal = new bootstrap.Modal(cropModalElement, {});
    } else {
        console.warn("Bootstrap JS not detected. Cropping modal may not function correctly.");
    }

    function showCropModal(event) {
        const input = event.target;
        const reader = new FileReader();

        reader.onload = function() {
            const image = document.getElementById('imageToCrop');
            image.src = reader.result;

            if (cropModal) {
                cropModal.show();
            } else {
                alert("Bootstrap Modal is required for image cropping.");
                return;
            }
        };

        if (input.files && input.files[0]) {
            reader.readAsDataURL(input.files[0]);
        }
    }

    const imageToCrop = document.getElementById('imageToCrop');

    imageToCrop.addEventListener('load', function() {

        if (cropper) cropper.destroy();

        cropper = new Cropper(imageToCrop, {
            viewMode: 1,
            responsive: true,
            autoCropArea: 1,  
            background: false,
            movable: true,
            zoomable: true,
            scalable: true,
            minContainerWidth: 600,  
            minContainerHeight: 400,
        });

        // บังคับให้ cropper render ใหม่ (ภาพจะเต็ม)
        setTimeout(() => {
            if (cropper) {
                cropper.reset(); 
                cropper.render();
            }
        }, 100);
    });

    document.getElementById('cropButton').addEventListener('click', function() {
        if (!cropper) return;

        const croppedCanvas = cropper.getCroppedCanvas({});
        const croppedDataURL = croppedCanvas.toDataURL('image/jpeg', 0.9);

        document.getElementById('group_image_cropped').value = croppedDataURL;

        const preview = document.getElementById('preview');
        const placeholder = document.getElementById('upload-placeholder');

        preview.src = croppedDataURL;
        preview.classList.remove('d-none');
        if (placeholder) placeholder.style.display = 'none';

        if (cropModal) cropModal.hide();
        document.getElementById('group_image_file').value = '';
    });
</script>

@stop