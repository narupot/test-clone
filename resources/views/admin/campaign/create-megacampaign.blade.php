@extends('layouts/admin/default')

@section('title')
    {{-- @lang('admin_campaign.create_new_campaign') --}}
    Create MEGA Campaign
@stop

@section('header_styles')
<!--page level css -->
<!-- end of page level css --> 
@stop

@section('content')
    <div class="content">
        @if(Session::has('succMsg'))
            <div class="alert alert-success alert-dismissable margin5">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <strong>@lang('admin_common.success'):</strong> {{ Session::get('succMsg') }}
            </div>
        @endif 
        <form id="campaignForm"  method="post" action="{{ route('admin.campaign.megaCampaign.store') }}" class=" form-horizontal form-bordered" novalidate="novalidate" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="header-title">
                <h1 class="title">Create MEGA Campaign</h1>
                <div class="float-right">
                    <a class="btn btn-back" href="{{ route('admin.campaign.megaCampaign.list') }}"><span>< </span>@lang('admin_common.back')</a>
                    <button type="submit" name="submit_type" value="submit_continue" class="btn static-block-save btn-secondary" data-action="submit_continue">@lang('admin_common.save_and_continue')</button>
                    
                    <!-- <button type="button" name="submit_type" value="preview" class="btn static-block-save" style="background: #38c1ff;" data-action="preview">@lang('admin_common.priview')</button> -->                    
                    <button type="submit" name="submit_type" value="submit" class="btn btn-save static-block-save btn-success" data-action="submit">@lang('common.save')</button>
                </div>
            </div>       
            <div class="content-wrap ">
                <div class="breadcrumb">
                    <ul class="bredcrumb-menu">
                        {{-- {!!getBreadcrumbAdmin('product','badge')!!} --}}
                    </ul>
                </div>

                <div class="container mt-4 bg-white shadow-sm p-4 p-lg-5">
                    
                    <div class="form-group row">
                        <div class="col-md-5">
                            <label>ชื่อ Campaign <i class="strick">**</i></label>
                            <input type="text" name="name" value="{{old('name')}}" placeholder="กรอกชื่อ campaign">
                            <p class="error" id="name"></p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-md-5">
                            <label>รายละเอียด</label>
                            <textarea name="desc" id="desc" cols="30" rows="10"  placeholder="รายละเอียด">{{old('desc')}}</textarea>
                            <p class="error" id="desc"></p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>@lang('admin_common.image')</label>
                        <div class="file-wrapper">
                            <div class="custom-img-file" style="position: relative; width: auto; display: inline-block;">
                                <input type="file" name="file_image" class="file-upload" accept="image/*" onchange="previewImage(event)">
                                <span class="file-img btn-outline-primary d-flex">
                                    {{-- <img src="{{ asset('images/file-upload.png') }}" class="">  --}}
                                    <i class="fa fa-camera my-auto mr-3"></i>
                                    <span>Upload New Image</span>
                                </span>
                                <img id="uploadPreview" class="upload-img" src="" style="height: 50px; display: none;">
                            </div>
                            <p class="error" id="file_image"></p>
                            <p class="strick small">* รอบรับการอัพโหลดไฟล์ไม่เกิน 10MB นามสกุล jpeg,png,jpg,gif,svg,webp เท่านั้น</p>
                        </div>
                    </div>
                    

                    <div class="form-group">
                        <label>@lang('admin_common.status')</label>
                        <label class="button-switch mt-2">
                            <input type="checkbox" name="status" value="1" class="switch switch-orange ng-valid ng-dirty ng-valid-parse ng-touched ng-not-empty" id="autoRelated" checked>                        
                            <span for="autoRelated" class="lbl-off">@lang('admin_common.off')</span>
                            <span for="autoRelated" class="lbl-on">@lang('admin_common.on')</span>
                        </label>
                    </div>
                    
                </div>
                                                          
            </div>
        </form>
    </div>
      
@stop

@section('footer_scripts')
 
<!-- begining of page level js -->
<!-- end of page level js --> 

<script src="{{ Config('constants.js_url') }}jquery.validate.min.js" type="text/javascript"></script> 
<script type="text/javascript">
    
    (function($){

        let rules= {
            name : { 
                required: true,
                maxlength: 255 
            }
        };
                      
        let messages = {
            name : { 
                required: "กรุณากรอกชื่อ",
                maxlength: "ชื่อยาวเกินไป"
            }
        };
        validateForm('campaignForm',rules,messages);

    })(jQuery);
</script>  


@stop
