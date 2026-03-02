<div class="row">
  <div class="col-sm-3 attribute-wrap">
      <p>There is no attribute you have to create attribute first</p>
      <a class="btn btn-create" data-toggle="modal" data-target="#myModalCreate">@lang('attribute.create_new_attribute')</a>

     <div class="drag-wrap">
       <div class="form-group">
           <label>Drag attribute to use<i class="float-right glyphicon glyphicon-share-alt"></i></label>
           <div class="attr-search-warp">
               <input placeholder="Search Attribute" type="text" ng-model="prdData.query">
               <button type="submit" class="btn-search"><i class="fas fa-search"></i></button>
           </div>
           <div ng-class="prdData.product_usage ? 'disabled' : ''" class="attr-row-wrap attribute-wrap-table ui-sortable dragdropElement" id="attr-dragElement" dnd-list="prdData.specslist" dnd-drop="dropCallback(index, item, external, type, 'specification-leftside')" dnd-allowed-types="['container']">
              <div class="attr-row"   itemid="itm-1" ng-repeat="list in prdData.specslist|filter:prdData.query|startFrom:prdData.currentPage*prdData.pageSize| limitTo:prdData.pageSize" dnd-draggable="list">
               <dnd-nodrag class="table-row">
                   <span class="fas fa-bars pr-3 col ui-sortable-handle handle" dnd-handle></span>
                   <span class="count col pr-2"><%$index+1%>. </span>
                   <span class="col"><span class="attr-name"><%list.name%></span> | <span class="attr-name"><%list.attribute_code%></span></span>
                   <span class="col fas" ng-class="{'fa-align-justify':list.front_input=='textarea', 'fa-font' : list.front_input=='text'}" ng-if="list.front_input=='textarea' || list.front_input=='text'"></span>
                   <span class="col fas fa-check-square" ng-if="list.front_input=='multiselect'"></span>
                   <span class="col fas fa-chevron-down" ng-if="list.front_input=='select'"></span>
                   <span class="col fas fa-paperclip" ng-if="list.front_input=='browse_file'"></span>
                   <span class="col fas fa-calendar-alt" ng-if="list.front_input=='date_picker'"></span>
                </dnd-nodrag>
               </div>
          </div>
           <div class="pager form-group">
              <button type="button" class="fas fa-angle-left prev"  ng-click="prdData.currentPage=prdData.currentPage-1" ng-disabled="prdData.currentPage == 0"></button>
              <div class="col">
                  <select ng-model="prdData.pageSize" id="pageSize" class="" ng-options="opt for opt in prdData.pagearr">
                  </select>
               </div>
               <span class="count-num align-self-center"> <%(prdData.currentPage+1)%> of <%getSpecTotalPageCount()%></span>
               <button type="button" class="fas fa-angle-right next" ng-click="prdData.currentPage=prdData.currentPage+1"  ng-disabled="prdData.currentPage >= (getFilterData().length/prdData.pageSize - 1)"></button>
           </div>        
       </div>
     </div>
  </div>
  <div class="col-sm-8 content-right-space">
      <h2 class="title-prod">@lang('attribute.create_attribute_set')</h2>
      <div class="form-group row"> 
         <div class="col-sm-4 {{ $errors->has('attribute_code') ? 'error' : '' }}"">  <label>@lang('attribute.set_name')<i class="strick">*</i> </label>
            {!! Form::text('name', old('name') ,['ng-model' =>'attrset.name', 'placeholder'=>'','ng-disabled'=>'prdData.sec_disable','required'] ) !!}
            <!--p id="name-error" ng-show="showError('name')" class="error error-msg">The attribute set name is requreid</p-->
            
            <p id="name-error" ng-if="errors.name[0]" class="error error-msg"><%errors.name[0]%></p>

            @if ($errors->has('name'))
              <p id="name-error" class="error error-msg"></p>
            @endif
         </div>                       
         <div class="col-sm-4 @if(isset($attributeset->id)){{'d-none'}}@endif">
           <label>@lang('attribute.base_on')</label>
           <select ng-options="item.name for item in prdData.attributesets track by item.id" ng-model="attrset.base_on_id" ng-change='_attrSetChange()' ng-disabled="prdData.sec_disable">
            <option value="">Please select</option>
           </select>
             {{-- {!! Form::select('base_on_id',null, ['ng-model'=>'attrset.base_on_id','ng-change'=>'_attrSetChange()','ng-options'=>"item as item.name for prdData.attributesets"])  !!} --}}
         </div>
      </div>
      <div class="form-group row">
          <div class="col-sm-8">
            <label>@lang('attribute.seo_description')</label>
             {!! Form::textarea('description', old('description'),['ng-model' =>'attrset.description', 'placeholder'=>'', 'class' => 'textheight' ] ) !!}
           </div>
      </div>
      <div class="form-group row color-group">
          <div class="col-sm-2">                           
             <label>@lang('attribute.label_flag')</label>
             {!! Form::text('label_flag', old('label_flag'), ['placeholder'=>Lang::get('attribute.label_flag'),'ng-model'=>'attrset.label_flag']) !!}
          </div>
          <div class="col-sm-3">                           
                <label>@lang('attribute.font_color')</label>
                <div class="input-group-wrap">
                    <div id="font-color" class="colorpicker-element input-group">
                        {!! Form::text('font_color', old('font_color'), ['placeholder'=>'', 'value'=>'#ff004a' ,'ng-model'=>'attrset.font_color','class'=>'form-control']) !!}
                        <span class="input-group-addon coloraddon" style="background-color:#e9ecef;">
                            <i style="display: inline-block;width:20px;height: 20px;background-color:<%attrset.font_color%>"></i>
                        </span> 
                    </div>
                </div>
          </div>
          <div class="col-sm-3">
              <label>@lang('attribute.flag_bg_color')</label>
              <div class="input-group-wrap">
                <div id="bg-color" class="colorpicker-element input-group">
                    {!! Form::text('flag_bg_color', old('flag_bg_color'), ['placeholder'=>'', 'value'=>'#000','ng-model'=>'attrset.flag_bg_color','class'=>'form-control']) !!}
                    <span class="input-group-addon coloraddon" style="background-color:#e9ecef;"><i style="display: inline-block;width:20px;height: 20px; background-color:<%attrset.flag_bg_color%>"></i></span>
                </div>                
              </div>
                           
          </div>
          <div class="col-sm-2">
              <label>@lang('attribute.remind_icon')</label>
               @if(isset($attributeset->remind_icon) && !empty($attributeset->remind_icon))
                <button class="btn btn-secondary" id="iconpicker" type="button" role="iconpicker" data-icon="{{$attributeset->remind_icon}}"></button>
              @else
                <button class="btn btn-secondary" id="iconpicker" type="button" role="iconpicker" data-icon="far fa-file-image"></button>
              @endif
              <input type="hidden" name="remind_icon" id="remind_icon" value="<%prdData.browserImgSrc%>">

              <!--div class="file-wrapper">
                <span class="add-files" ng-if="prdData.browserImgSrc != ''">
                  <img ng-src="<%prdData.browserImgSrc%>" width="38" height="38">
                </span>
                <span class="add-files" ng-if="prdData.browserImgSrc == ''">
                  <i class="far fa-file-image" style="font-size: 36px"></i>
                </span>

                {{!! Form::file('remind_icon', ['class'=>'form-control','onchange'=>'angular.element(this).scope().fileNameChanged(this)','ng-model'=>'remind_icon', 'accept'=>'image/*']) !!}
                <p id="name-error" ng-show="showError('remind_icon')" class="error error-msg">Remind icon will required</p>
                @if ($errors->has('remind_icon'))
                  <p id="name-error" class="error error-msg">{{ $errors->first('remind_icon') }}</p>
                @endif
              </div-->
          </div>
          {{-- <div class="col-sm-2">
              <label for="">&nbsp;</label>
              <a class="primary-color" href="javascript:void(0)" data-toggle="modal" data-target="#help"> @lang('attribute.help')</a>
          </div> --}}
      </div>
      <!-- drag and drop in both case if attribute set have or not  -->
      <div class="form-group row">
          <div class="col-sm-12 mt-15">
              <div class="box-border dragdropElement" id="attr-dropElement" dnd-list="prdData.specs_create_list" dnd-drop="dropCallback(index, item, external, type, 'specification')"> 
                  <div class="blank-drag" data-ng-if="prdData.specs_create_list.length === 0">
                      <p class="blank-txt"><i class="fas fa-sign-out-alt"></i> Drag your Attribute drop here</p>
                  </div>
                  <div id="attr-sortable" class="blank-drag drag-attr-table" data-ng-if="prdData.specs_create_list.length">
                    <div class="form-group att-drag-row table" ng-repeat="item in prdData.specs_create_list" on-finish-render="">
                        <div class="select-color-row">
                            <span class="col fas pl-2 pr-3 fa-bars ui-sortable-handle" ></span>
                            <span class="count col pr-2"><%$index+1%>. </span>
                            <span class="col name"><span class="attr-name"><%item.name%> | <span class="attr-name"><%item.attribute_code%></span></span></span>
                           {{-- <span class="float-right glyphicon glyphicon-font skyblue" ng-if="item.front_input=='textarea' || item.front_input=='text'"></span>
                            <span class="float-right fa-sign-out-alt" ng-if="item.front_input=='multiselect'"></span>
                            <span class="float-right fa-sign-out-alt" ng-if="item.front_input=='select'"></span>
                            <span class="float-right glyphicon glyphicon-paperclip" ng-if="item.front_input=='browse_file'"></span>
                            <span class="float-right glyphicon glyphicon-calendar" ng-if="item.front_input=='date_picker'"></span> --}}
                            <span ng-if="prdData.product_usage==0" class="fas fa-times float-right"  ng-click="_removeSep($event,$index,'specification',item)"></span>
                        </div>
                    </div>
                  </div>
              </div>
          </div>
      </div>
  </div>
</div>
<div class="modal fade" id="myModalCreate" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title">What type of attribute you need to create?</h2>
        <span class="fas fa-times close" data-dismiss="modal"></span>
      </div>
      <div class="modal-body">
        <div class="attr-mgt-view row">
          <div class="col-sm-6">
              <a href="{{action('Admin\Attribute\AttributeController@createAttribute','variant')}}"><img src="{{Config('constants.image_url')}}product-variant.jpg"></a>
              <h2>@lang('attribute.product_varaint')</h2>
              <p> @lang('attribute.cut_from_tissue-weight')</p>
              <a class="btn btn-create" href="{{action('Admin\Attribute\AttributeController@createAttribute','variant')}}" class="btn btn-create"><!-- @lang('attribute.create_new_varaint_or_specification') -->
                @lang('attribute.create_new_option')
              </a>
          </div>
          <div class="col-sm-6">
              <a href="{{action('Admin\Attribute\AttributeController@createAttribute','specification')}}"><img src="{{Config('constants.image_url')}}specification.jpg" alt="" title=""></a>
              <h2>@lang('attribute.specification')</h2>
              <p>@lang('attribute.cut_from_vissue-weight') </p>
              <a href="{{action('Admin\Attribute\AttributeController@createAttribute', 'specification')}}" class="btn btn-create"><!-- @lang('attribute.create_new_varaint_or_specification') -->
                @lang('attribute.create_new_specification')
              </a>
          </div>
          <!-- <div class="col-sm-4">
              <a href="{{action('Admin\Attribute\AttributeController@createAttribute','requirement')}}"><img src="{{Config('constants.image_url')}}product-requirement.jpg" alt="" title=""></a>
              <h2>@lang('attribute.what_is_product_requirement')</h2>
               <p>@lang('attribute.cut_from_tissue-weight')</p>

              <a href="{{action('Admin\Attribute\AttributeController@createAttribute','requirement')}}" class="btn">
                  
                  @lang('attribute.create_product_requirement')
              </a>
          </div> -->
        </div>
      </div>
    </div>
  </div>
</div>