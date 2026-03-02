@extends('layouts.admin.default')

@section('title')
    @if(isset($subgroup))
        แก้ไขข้อมูลหมวด
    @else
        เพิ่มข้อมูลหมวด
    @endif
@stop

@section('header_styles')
{{-- 1. INCLUDE CROPPER.JS CSS --}}
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" rel="stylesheet">
<style>
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
        max-height: 400px;
        overflow: hidden;
        display: flex;
        justify-content: center;
    }
    .img-container img {
        max-width: 100%;
    }
</style>
@stop


@section('content')
<div class="content">

    {{-- START CROPPER MODAL --}}
    <div class="modal fade" id="cropImageModal" tabindex="-1" aria-labelledby="cropImageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cropImageModalLabel">ปรับขนาดและตำแหน่งรูปภาพ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> 
                </div>
                <div class="modal-body">
                    <div class="img-container">
                        <img id="imageToCrop" src="">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-success" id="cropButton">ยืนยัน</button>
                </div>
            </div>
        </div>
    </div>
    {{-- END MODAL --}}

    <div class="card">
        {{-- Conditional form action for Add/Edit --}}
        @if(isset($subgroup))
            <form id="groupForm" method="POST" action="{{ action('Admin\Groups\SubGroupsController@update', $subgroup->id) }}" enctype="multipart/form-data">
            @method('PUT') {{-- Use PUT method for update --}}
        @else
            <form id="groupForm" method="POST" action="{{ action('Admin\Groups\SubGroupsController@store') }}" enctype="multipart/form-data">
        @endif
            {{ csrf_field() }}

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">
                    @if(isset($subgroup))
                        แก้ไขหมวด: {{ $subgroup->subgroup_name }}
                    @else
                        เพิ่มหมวดใหม่
                    @endif
                </h2>
                <div class="d-flex gap-2">
                   
                    <a class="btn btn-back me-2"
                    href="{{ action('Admin\Groups\SubGroupsController@index', ['group_id' => $subgroup->pro_group_id ?? request('group_id')]) }}">
                    ← ยกเลิก
                    </a>

                    <button type="submit" form="groupForm" name="submit_type" value="submit" class="btn btn-success" style="margin-left: 10px;">
                        บันทึก
                    </button>
                </div>
            </div>

            <div class="mb-4">
                <label for="pro_group_id" class="form-label">กลุ่มหลัก <span class="red">*</span></label>
                <select id="pro_group_id" name="pro_group_id" class="form-control" required>
                    <option value="">-- เลือกกลุ่มหลัก --</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}"
                            {{ old('pro_group_id', $subgroup->pro_group_id ?? '') == $group->id ? 'selected' : '' }}>
                            {{ $group->name }}
                        </option>
                    @endforeach
                </select>
                @error('pro_group_id')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="subgroup_name" class="form-label">ชื่อหมวด <span class="red">*</span></label>
                <input type="text" name="subgroup_name" class="form-control"
                    value="{{ old('subgroup_name', $subgroup->subgroup_name ?? '') }}">
                @error('subgroup_name')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>


            {{-- CROPPER IMAGE UPLOAD FIELD --}}
            <div class="mb-4">
                <label for="group_image_file" class="form-label fw-bold">รูปภาพกลุ่ม</label>

                <div class="image-upload-wrapper position-relative">
                    {{-- File Input: Trigger Cropper Modal --}}
                    <input type="file" id="group_image_file" class="d-none" accept="image/*" onchange="showCropModal(event)">
                    
                    {{-- Hidden Input: Send cropped Base64 data to server (Controller receives this as 'group_image') --}}
                    <input type="hidden" id="group_image_cropped" name="group_image" value="">
                    
                    <button type="button"
                                class="upload-box"
                                onclick="document.getElementById('group_image_file').click()"
                                aria-label="คลิกเพื่ออัปโหลดรูป"
                                style="border: none; background: none; padding: 0;"
                    >
                        {{-- Image preview and placeholder logic --}}
                        <img id="preview" 
                            {{-- *** แก้ไข: ใช้ $subgroup->images แทน $subgroup->image *** --}}
                            class="preview-img @unless(isset($subgroup->images) && $subgroup->images) d-none @endunless" 
                            alt="Group preview" 
                            {{-- *** แก้ไข: ใช้ $subgroup->images แทน $subgroup->image *** --}}
                            @if(isset($subgroup->images) && $subgroup->images) src="{{ asset($subgroup->images) }}" @endif />
                            
                        
                        <div id="upload-placeholder" class="upload-box text-center p-4 border border-dashed rounded shadow-sm"
                             {{-- *** แก้ไข: ใช้ $subgroup->images แทน $subgroup->image *** --}}
                             @if(isset($subgroup->images) && $subgroup->images) style="display: none;" @endif>
                            <i class="fa fa-cloud-upload fa-3x text-primary mb-2"></i>
                            <p class="text-muted mb-0">คลิกเพื่ออัปโหลดรูป</p>
                        </div>
                    </button>
                </div>

                @error('group_image')
                    <p class="form-error text-danger">{{ $message }}</p>
                @enderror
                {{-- *** แก้ไข: ใช้ $subgroup->images แทน $subgroup->image *** --}}
                @if(isset($subgroup->images) && $subgroup->images)
                    <small class="text-muted mt-2 d-block">อัปโหลดรูปใหม่เพื่อแทนที่รูปเดิม</small>
                @endif
            </div>


            <div class="mb-4">
                <label for="sort_order" class="form-label">ลำดับ</label>
                {{-- ใช้ 'sorting_no' ตามชื่อคอลัมน์ใน DB --}}
                <input type="number" id="sort_order" name="sort_order" class="form-control w-25" value="{{ old('sort_order', $subgroup->sorting_no ?? 0) }}">
                @error('sorting_no')
                    <p class="form-error text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-3">
                <label for="groupStatus" class="form-label d-block">Status</label>
                <label class="button-switch mt-2">
                    {{-- Checkbox status based on existing group data or default to checked for new --}}
                    <input type="checkbox" name="status" value="1" class="switch switch-orange" id="groupStatus" 
                            @if(isset($subgroup)) {{ ($subgroup->status == 1) ? 'checked' : '' }} @else checked @endif>
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
            'pro_group_id': 'required',
            'subgroup_name': 'required'
        };
        const messages = {
            'pro_group_id': 'กรุณาเลือกกลุ่มหลัก',
            'subgroup_name': 'กรุณากรอกชื่อหมวด'
        };
        validateForm('groupForm', rules, messages);
    })(jQuery);

</script>

---

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
