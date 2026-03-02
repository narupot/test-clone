@extends('layouts/admin/default')

@section('title')
    {{-- @lang('admin_campaign.edit_campaign') --}}
    Edit {{$campaign->megaCampaign->name??''}} Campaign
@stop

@section('header_styles')

@stop

@section('content')
    <div class="content">
        <form id="campaignForm" action="{{ route('admin.campaign.subCampaign.update',['campaign'=>$campaign->id]) }}" 
            method="post" class="form-horizontal form-bordered" novalidate="novalidate" enctype="multipart/form-data">
            {{ csrf_field() }}
            {{ method_field('PUT') }}        
            <div class="header-title">
                @if(Session::has('succMsg'))
                    <div class="alert alert-success alert-dismissable margin5">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <strong>@lang('common.success'):</strong> {{ Session::get('succMsg') }}
                    </div>
                @endif 
                <h1 class="title">Edit {{$campaign->megaCampaign->name??''}} Campaign {{$badge_dtls->badgedesc->badge_name ?? ''}}</h1> 
                <div class="float-right">                
                    <a class="btn btn-back" href="{{ action('Admin\Campaign\CampaignController@indexSubCampaign') }}">@lang('common.back')</a>
                    <button type="submit" name="submit_type" value="submit_continue" class="btn static-block-save btn-secondary" data-action="submit_continue">@lang('common.save_and_continue')</button>
                    <button type="submit" class="btn btn-save btn-success">@lang('common.save')</button>
                    
                </div>               
            </div>
                  
            <div class="content-wrap">
                <div class="breadcrumb">
                    <ul class="bredcrumb-menu">
                        {{-- {!!getBreadcrumbAdmin('product','badge')!!} --}}
                    </ul>
                </div>
                <div class="container mt-4 bg-white shadow-sm p-4 p-lg-5">
                    {{-- <div class="form-group row pt-3">
                        <div class="col-md-5">
                            <label>@lang('admin_campaign.mega_campaign') </label>
                            <select name="mega_campaign">
                                <option value=""> -- ไม่ระบุ --</option>
                                @foreach($megaCampaigns??[] as $key => $value)
                                <option value="{{ $value['id'] }}"
                                {{$value['id'] === $campaign->parent_id?'selected':''}}
                                >{{ $value['name'] }}</option>
                                @endforeach
                            </select>
                            <p class="error" id="mega_campaign"></p>
                        </div>
                    </div> --}}
                    
                    
                    <div class="form-group row">
                        <div class="col-md-5">
                            <label>@lang('admin_common.name') <i class="strick">*</i></label>
                            <input type="text" name="name" value="{{$campaign->name}}" >
                            <p class="error" id="name"></p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-md-5">
                            <label>@lang('admin_common.description')</label>
                            <textarea name="desc" id="desc" cols="30" rows="10">{{$campaign->desc}}</textarea>
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
                                <img id="uploadPreview" class="upload-img" src="{{'/files/campaign/'.$campaign->image }}" style="height: 50px; display: none;">
                            </div>
                            <p class="error" id="file_image"></p>
                            <p class="strick small">* รอบรับการอัพโหลดไฟล์ไม่เกิน 10MB นามสกุล jpeg,png,jpg,gif,svg,webp เท่านั้น</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>@lang('admin_common.status')</label>
                        <label class="button-switch mt-2">
                            <input type="checkbox" name="status" value="1" class="switch switch-orange ng-valid ng-dirty ng-valid-parse ng-touched ng-not-empty" 
                            id="autoRelated" {{($campaign->status??false) == 1 ?'checked':'' }}>                        
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
