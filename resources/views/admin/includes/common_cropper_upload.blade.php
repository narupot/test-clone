<!-- 
@desc : cropper include blade used for all commman cropper upload section
/****** used place ********/
1. social share 

-->
@foreach($cropper_setting as $item)
   <!-- cropper model for blog thumnail image -->
   <div class="modal fade" id="avatar-{{$item['section_id']}}-modal" aria-hidden="true" aria-labelledby="avatar-modal-label" role="dialog" tabindex="-1">
      <div class="modal-dialog modal-lg">
         <div class="modal-content">
            <div name="avatar_images" class="avatar-form" >              
               <div class="modal-header img-modal-header">
                  <h2 class="modal-title" id="avatar-modal-label">@lang('admin_common.change_avatar')</h2>
                  <span class="fas fa-times close" data-dismiss="modal"></span>
               </div>
               <div class="modal-body mt-10">
                  <div class="avatar-body">
                     <!-- Upload image and data -->
                     <div class="avatar-contain">
                         <div class="avatar-upload">                       
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
                              <button type="button" class="btn btn-primary" data-method="move" data-option="-10" data-second-option="0" title="Move Up">
                              <span class="docs-tooltip" data-toggle="tooltip" title="" data-original-title="cropper.move(0, -10)">
                              <span class="fa fa-arrow-up"></span>
                              </span>
                              </button>
                              <button type="button" class="btn btn-primary" data-method="move" data-option="10" data-second-option="0" title="Move Down">
                              <span class="docs-tooltip" data-toggle="tooltip" title="" data-original-title="cropper.move(0, 10)">
                              <span class="fa fa-arrow-down"></span>
                              </span>
                              </button>
                           </div>
                           <div class="btn-group cropper-btn">
                              <button type="button" class="btn btn-primary" data-method="rotate" data-option="-45" title="Rotate Left">
                              <span class="docs-tooltip" data-toggle="tooltip" title="" data-original-title="cropper.rotate(-45)">
                              <!-- <span class="fa fa-rotate-left"></span> -->
                              <span class="fa">&#xf0e2;</span>
                              </span>
                              </button>
                              <button type="button" class="btn btn-primary" data-method="rotate" data-option="45" title="Rotate Right">
                              <span class="docs-tooltip" data-toggle="tooltip" title="" data-original-title="cropper.rotate(45)">
                              <!-- <span class="fa fa-rotate-right"></span> -->
                              <span class="fa">&#xf01e;</span>
                              </span>
                              </button>
                           </div>
                           <div class="btn-group move-container cropper-btn">
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
               <div class="modal-footer footer-btn">
                    <a class="btn-create avatar-save" href="javascript:void(0);">@lang('admin_common.done')</a>
               </div>
            </div>
         </div>
      </div>
   </div>
@endforeach