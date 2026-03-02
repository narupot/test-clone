<div class="multiple-file-upload">
    <div class="upload-row">
     @lang('blog.drag_drop_file')
     Dimenstion must be higher than 405X608 and size must be less than {{GeneralFunctions::systemConfig('MAX_FILE_SIZE')}} MB in size
    </div> 
    <div class="table table-striped files">
       <droplet ng-model="blog.file_interface">
        <ul class="files">       
            <li ng-repeat="item in blog.file_interface.getFiles(blog.file_interface.FILE_TYPES.VALID)">
                <div class="col-sm-2">
                    <droplet-preview ng-model="item"></droplet-preview>
                </div>
                <div class="col-sm-6">
                   <div class="size"><%item.file.size / 1024 / 1024 | number: 1%>MB</div>
                   <p> 
                     <input name="default_image" type="radio" value="<%$index%>" ng-model="blog.defaultImage" /> 
                     @lang('blog.set_as_display_img')
                    </p>
                </div>
                <div class="col-sm-4">                    
                    <button type="button" class="btn btn-delete" ng-click="onDeleteImageFromImageUploadList(item,$index)" ng-disabled="item.customData.inprogress">
                       <span>@lang('blog.cancel')</span>
                    </button>
                    <!-- before upload-->                    
                    <button type="button" class="btn" ng-click="onImageUpload(item, 'single_upload')" ng-show="!item.customData.completed" ng-disabled="item.customData.inprogress">
                       <span>upload</span>
                    </button>                      
                    <!-- after upload -->
                    <div class="btn" style="background : #8ace8a" ng-show="item.customData.completed">
                       <span>Done</span>
                    </div>
                    
                    <!--progress bar -->                   
                    <div class="progress-row" ng-show="item.customData.percent!=0">
                        <div class="progress-container">
                            <div class="progress-border col-xs-12">
                                <div class="progress-bar" ng-style="{'width': item.customData.percent}" ng-class="{'progress-bar-warning':  item.customData.error, 'progress-bar-success': item.customData.completed}"></div>
                            </div>
                        </div>
                        <div class="progress-perc"><%item.customData.percent%></div> 
                    </div>
                </div>
            </li>
          </ul>
        </droplet>
        <div class="upload-row">
            <span class="fileinput-button file-upload-btn" ng-class="{disabled: disabled}">
                <img src="/images/upload-btn2.jpg">
                <droplet-upload-multiple ng-model="blog.file_interface"></droplet-upload-multiple>
            </span>
        </div>
    </div>
    <div class="row fileupload-buttonbar">
        <div class="col-lg-12">
            <!-- The fileinput-button span is used to style the file input field as button -->
            <span class="btn fileinput-button" ng-class="{disabled: disabled}">
                <i class="glyphicon glyphicon-plus"></i>
                <span> 
                @lang('blog.add')
                </span>
             <droplet-upload-multiple ng-model="blog.file_interface"></droplet-upload-multiple>
            </span>
            <div class="float-right" ng-show="blogData.up_cl_all">
                <button type="button" class="btn btn-delete" ng-click="_clearallfiles()">
                    <i class="icon-remove"></i>
                    <span>               
                    @lang('blog.clear_upload')
                     </span>
                </button>
                <button type="button" class="btn" ng-click="onImageUpload(undefined, 'all_upload')">Upload all</button>
            </div>
            <!-- The global file processing state -->
            <span class="fileupload-process"></span>
        </div>                 
    </div>
    <!-- </form> -->
</div>                             
