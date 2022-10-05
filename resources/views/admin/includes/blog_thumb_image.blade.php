<div class="" id="crop-avatar">
   <label>@lang('common.thumb_image') <i class="strick">*</i></label> 
   {{-- 
   <div class="single-file-upload">
      <input type="file" name="thumb_image" value="{{ old('thumb_image') }}" id="thumb_image" required>
   </div>
   --}}
   <div class="avatar-view single-file-upload" title="Change the avatar">
      @if($page=="add_blog")
         <img src="{{ Config::get('constants.loader_url')}}please_upload_image.jpg" alt="" height="95" width="95" id="thumb_image" name="thumb_image">
      @else
         <img src="{{ getBlogImageUrl($page_dtls->image,'blog-570x402') }}" alt="gfdf" height="95" width="95" id="thumb_image" data-image_upload_status="true" name="thumb_image">
      @endIf

      
   </div>
   <!-- cropper model for blog thumnail image -->
   <div class="modal fade" id="avatar-modal" aria-hidden="true" aria-labelledby="avatar-modal-label" role="dialog" tabindex="-1">
      <div class="modal-dialog modal-lg">
         <div class="modal-content">
            <div name="avatar_images" class="avatar-form" >
               <input type="hidden" name="modelfor" id="modelfor">
               <input type="hidden" name="type" value="blog">
               <input type="hidden" name="blogerdetailid" value="">
               <div class="modal-header img-modal-header">
                  <h2 class="modal-title" id="avatar-modal-label">Change Avatar</h2>
                  <span class="fas fa-times" data-dismiss="modal"></span>                  
               </div>
               <div class="modal-body">
                  <div class="avatar-body">
                     <!-- Upload image and data -->
                     <div class="avatar-upload">
                        <input type="hidden" class="avatar-src" name="avatar_src">
                        <input type="hidden" class="avatar-data" name="avatar_data">
                        <div class="mb-5 clearfix"><label for="avatarInput">Local upload</label></div>
                        <input type="file" class="avatar-input" id="avatarInput" name="avatar_file" accept="image/*">
                     </div>
                     <!-- Crop and preview -->
                     <div class="row">
                        <div class="col-md-9">
                           <div class="avatar-wrapper" style="width: 350px; height : 250px"></div>
                        </div>
                        <div class="col-md-3">
                           <div class="avatar-preview preview-lg" id="imgprev"></div>
                        </div>
                     </div>
                     <div class="row avatar-btns">
                        <div class="col-md-9">
                           <div class="btn-group move-container cropper-btn">
                              <button type="button" class="btn btn-primary" data-method="setDragMode" data-option="move" title="Move">
                              <span class="docs-tooltip" data-toggle="tooltip" title="" data-original-title="cropper.setDragMode(&quot;move&quot;)">
                              <span class="fa fa-arrows"></span>
                              </span>
                              </button>
                           </div>
                           <div class="btn-group move-type cropper-btn">
                              <button type="button" class="btn btn-primary" data-method="move" data-option="-10" data-second-option="0" title="Move Left">
                              <span class="docs-tooltip" data-toggle="tooltip" title="" data-original-title="cropper.move(-10, 0)">
                              <span class="fa fa-arrow-left"></span>
                              </span>
                              </button>
                              <button type="button" class="btn btn-primary" data-method="move" data-option="10" data-second-option="0" title="Move Right">
                              <span class="docs-tooltip" data-toggle="tooltip" title="" data-original-title="cropper.move(10, 0)">
                              <span class="fa fa-arrow-right"></span>
                              </span>
                              </button>
                              <button type="button" class="btn btn-primary" data-method="move" data-option="0" data-second-option="-10" title="Move Up">
                              <span class="docs-tooltip" data-toggle="tooltip" title="" data-original-title="cropper.move(0, -10)">
                              <span class="fa fa-arrow-up"></span>
                              </span>
                              </button>
                              <button type="button" class="btn btn-primary" data-method="move" data-option="0" data-second-option="10" title="Move Down">
                              <span class="docs-tooltip" data-toggle="tooltip" title="" data-original-title="cropper.move(0, 10)">
                              <span class="fa fa-arrow-down"></span>
                              </span>
                              </button>
                           </div>
                           <div class="btn-group cropper-btn">
                              <button type="button" class="btn btn-primary" data-method="rotate" data-option="-45" title="Rotate Left">
                              <span class="docs-tooltip" data-toggle="tooltip" title="" data-original-title="cropper.rotate(-45)">
                              <span class="fa fa-rotate-left"></span>
                              </span>
                              </button>
                              <button type="button" class="btn btn-primary" data-method="rotate" data-option="45" title="Rotate Right">
                              <span class="docs-tooltip" data-toggle="tooltip" title="" data-original-title="cropper.rotate(45)">
                              <span class="fa fa-rotate-right"></span>
                              </span>
                              </button>
                           </div>
                           <div class="btn-group move-container cropper-btn float-right">
                              <button type="button" class="btn btn-primary" data-method="zoom" data-option="0.1" title="Zoom In">
                              <span class="docs-tooltip" data-toggle="tooltip" title="" data-original-title="cropper.zoom(0.1)">
                              <span class="fa fa-search-plus"></span>
                              </span>
                              </button>
                              <button type="button" class="btn btn-primary" data-method="zoom" data-option="-0.1" title="Zoom Out">
                              <span class="docs-tooltip" data-toggle="tooltip" title="" data-original-title="cropper.zoom(-0.1)">
                              <span class="fa fa-search-minus"></span>
                              </span>
                              </button>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="clearfix nopadding custome-background">
                  <div class="col-md-12 footer-btn">
                     <button type="button" class="btn-md btn-blue btn avatar-save float-right">Done</button>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>