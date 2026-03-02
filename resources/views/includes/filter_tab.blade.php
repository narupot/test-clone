<div id="tab3_<%$index%>" class="tab-pane fade clrearfix" ng-repeat="ftab in filterTab track by $index">
   <div class="filter-container-section"  data-ng-form name="ftForm">
         <div class="form-row">
            <div class="col-sm-4"> 
              <label>@lang('admin_product.filter_name') <i class="strick">*</i></label>
               <input type="text" class="form-control" name="filter_name" data-ng-model="filterObj.filterAtrModel[ftab.id].filter_name" ng-init="filterObj.filterAtrModel[ftab.id].filter_name = ftab.filter_model.filter_name" ng-class="{'error':ftForm.filter_name.$error.required && !ftForm.filter_name.$pristine}" required>
               <span class="error" ng-show="ftForm.filter_name.$error.required && !ftForm.filter_name.$pristine">@lang('admin_product.please_enter_filter_name')</span>
            </div>
         </div>
         <!-- create conditional html on basics of json field -->
         <div class="form-row" data-ng-repeat="ft in filterObj.jsonForField[$index].value track by $index">
            <!--for simple text box like user input (email) -->
            <div data-ng-if="ft.type==='text'" class="filter-box">
               <div class="col-sm-4">
                 <label><%ft.label%> :</label>
                  <input type="text" ng-model="filterObj.filterAtrModel[ftab.id][ft.input_name]" class="">
               </div>
               <span class="fa fa-times col-sm-2 " data-ng-click="filterActionHendler.removeField($event,ftab.id, $index, ft.input_name)"></span>
            </div>
            <!-- form coose box (mutiselect for choosen / auto-complete ) -->
            <div data-ng-if="ft.type==='multiselect'" class="filter-box">
                <div class="col-sm-5">
                  <label><%ft.label%> :</label>
                     <tags-input data-ng-model="filterObj.filterAtrModel[ftab.id][ft.input_name]" display-property="name" key-property="id" add-from-autocomplete-only="true" replace-spaces-with-dashes="false" min-length="1">
                     <auto-complete min-length="1" source="filterActionHendler.autoSugest($query, ft.attr_value)" ></auto-complete>
                  </tags-input>
                </div>
                  <span class="fa fa-times col-sm-2 " data-ng-click="filterActionHendler.removeField($event, ftab.id, $index, ft.input_name)"></span>
            </div>
            <!-- for from and to  -->
            <div data-ng-if="ft.type==='range'" class="date-wrapper exp-date-wrapper filter-box">
                <div class="col-sm-4">
                  <label><%ft.label%> <%ft.from_label%></label>
                  <input type="text" name="<%ft.from_label%>" class="form-control" data-ng-model="filterObj.filterAtrModel[ftab.id][ft.from_range]">                  
                </div>
                <div class="col-sm-4">
                  <label><%ft.label%> <%ft.to_label%></label> 
                  <input type="text" name="<%ft.to_label%>" class="form-control"  data-ng-model="filterObj.filterAtrModel[ftab.id][ft.to_range]">                  
                </div>
 
               <div class="col-sm-2">
                  <!-- <label>&nbsp;</label> -->
                  <span class="fa fa-times" data-ng-click="filterActionHendler.removeField($event, ftab.id, $index, ft.from_range, ft.to_range)"></span>
               </div>
            </div>
            <!-- for date picker from and to -->
            <div data-ng-if="ft.type ==='date_range'" class="date-wrapper exp-date-wrapper filter-box">
              <!-- date from -->
              <div class="col-sm-3">
                <label><%ft.label%> <%ft.from_label%></label>
                <div class='date' enable-datepicker options="{wrap: true, dateFormat : 'd-m-Y'}" ng-model="filterObj.filterAtrModel[ftab.id][ft.from_modelName]">
                    <input type='text' class="form-control" name="<%ft.from_label%>"  ng-model="filterObj.filterAtrModel[ftab.id][ft.from_modelName]" placeholder="Select date.." data-input />
                    <span class="input-group-addon" data-toggle>
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                  </div>
              </div>
              <!-- date to -->
              <div class="col-sm-3">
                <label><%ft.label%> <%ft.to_label%></label>
                <div class='date' enable-datepicker options="{wrap: true, dateFormat : 'd-m-Y'}" ng-model="filterObj.filterAtrModel[ftab.id][ft.to_modelName]">
                    <input type='text' class="form-control " name="<%ft.to_label%>"  ng-model="filterObj.filterAtrModel[ftab.id][ft.to_modelName]" placeholder="Select date.." data-input/>
                    <span class="input-group-addon" data-toggle>
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                  </div>
              </div>      
               <div class="col-sm-2">
                  <!-- <label>&nbsp;</label> -->
                  <span class="fa fa-times " data-ng-click="filterActionHendler.removeField($event, ftab.id, $index, ft.from_modelName, ft.to_modelName)"></span>
               </div>
            </div>
            <!-- for only date(date picker)  -->
            <div data-ng-if="ft.type ==='date'" class="date-wrapper exp-date-wrapper filter-box">
              <div class="col-sm-4">
                <label><%ft.label%></label>
                <div class='date date-wrap' enable-datepicker options="{wrap: true, dateFormat : 'd-m-Y'}" ng-model="filterObj.filterAtrModel[ftab.id][ft.modelName]">
                    <input type='text' class="form-control date-select-new" name="<%ft.input_name%>"  ng-model="filterObj.filterAtrModel[ftab.id][ft.modelName]" placeholder="Select date.."  data-input/>
                   <!--  <span class="input-group-addon" data-toggle>
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span> -->
                  </div>
              </div>
              <div class="col-sm-2">
                  <!-- <label>&nbsp;</label> -->
                  <span class="fa fa-times " data-ng-click="filterActionHendler.removeField($event, ftab.id, $index, ft.modelName)"></span>
               </div>
            </div>
            <!-- for input type check box-->
            <div data-ng-if="ft.type == 'checkbox'" class="filter-box" >
               <div class="col-sm-4">
                 <label><%ft.label | uppercase %> :</label>
                 <div class="inline-block check-group" ng-repeat="item in ft.attr_value">
                   <label class="check-wrap">
                       <input type="checkbox" name="fancy-checkbox-success" id="fancy-checkbox-success" autocomplete="off" ng-model="filterObj.filterAtrModel[ftab.id][ft.input_name][item.id]" class=""> 
                       <span class="chk-label"><%item.name%></span>
                    </label>
                 </div>
               </div>
               <div class="col-sm-2 mt-5">
                  <span class="fa fa-times " data-ng-click="filterActionHendler.removeField($event, ftab.id, $index, ft.input_name)"></span>
               </div>
            </div>
            <!-- for input type radio buttion -->
            <div data-ng-if="ft.type === 'radio'" class="filter-box">
                <div class="radio-group col-sm-4">
                <label><%ft.label | uppercase %> :</label>
                  <label class="radio-wrap" ng-repeat="item in ft.attr_value"> 
                    <input type="radio" name="<%ft.label%>" ng-model="filterObj.filterAtrModel[ftab.id][ft.input_name]"  value="<%item.id%>" /> 
                    <span class="radio-label "><%item.name%></span>
                  </label>
                </div>
                <div class="col-sm-2">
                  <span class="fa fa-times " data-ng-click="filterActionHendler.removeField($event, ftab.id, $index, ft.input_name)"></span>
                </div>
            </div>
            <!-- for input type select  -->
            <div data-ng-if="ft.type === 'select'" class="filter-box">
               <div class="col-sm-4">
                 <label><%ft.label | uppercase %> :</label>
                 <select  ng-model="filterObj.filterAtrModel[ftab.id][ft.input_name]" ng-options="opt.name for opt in ft.attr_value track by opt.id">
                  <option value="">Please select.. </option>>
                 </select>
               </div>
               <div class="col-sm-2 mt-5">
                  <span class="fa fa-times " data-ng-click="filterActionHendler.removeField($event, ftab.id, $index, ft.input_name)"></span>
               </div> 
            </div>
         </div>
      <div class="row">
        <div class="col-sm-12">
          <hr>
        </div>
      </div>
      <div class="form-row row">
        <div class="col-sm-12">
          <label class="filter-label">@lang('admin_product.choose_your_filter_from_list') <i class="strick">*</i></label>
        </div>
      </div>      
      <div class="form-row row">
         <div class="col-sm-4">
           <label>@lang('admin_product.filter_type') :</label>
            <select data-ng-model="filterObj.filterModel[ftab.id]" data-ng-change="filterActionHendler.optChange(ftab.id)"
               data-ng-options="fOpt.label for fOpt in filterObj.filterOption">
               <option value="">Please select</option>
            </select>
         </div>
      </div>
      <div class="form-row mt-10">
         <button type="button" class="btn mr-1" data-ng-click="filterActionHendler.filter(ftab)">@lang('admin_product.filter')</button>
         <button type="button" class="btn mr-1 btn-save" data-ng-click="filterActionHendler.save(enableDeleteBtn, 'save', ftab)">Save</button>
         <button type="button" class="btn btn-delete" data-ng-if="enableDeleteBtn" data-ng-click="filterActionHendler.deleteFilter(ftab,'delete')">@lang('admin_product.delete')</button>
      </div>
   </div>
   <!-- for filter section table -->
   <div class="table-wrapper catelog-mgt-table" >
        <div class="table-record-row">
            <span class="total-records">@lang('category.total_records') <%displayTotalNumItems%></span>
            <div class="float-right pager pagiTablet" data-ng-if="tableHeaderPaginationConfig && gridOptions.data.length>0">
                <dir-header-pagination></dir-header-pagination>
            </div>
        </div> 
        <div>
            <div id="productTable" data-ng-if="tabFilter && gridOptions.data.length>0" ui-grid="gridOptions" ui-grid-pagination="" class="tableWidth ui-grid-selectable"  ui-grid-resize-columns ui-grid-selection ui-grid-move-columns ui-grid-draggable-rows ui-grid-save-state ui-grid-exporter ui-grid-auto-resize ng-style="getTableHeight()"></div>
            <div class="error-container" data-ng-bind-html="errorInfoLog | unsafe" data-ng-if="no_result_found"></div>
            <div class="loder-wrapper" data-ng-if="showLoaderTable">
                <div class="loader loader-medium"><img ng-src="<%tableLoaderImgUrl%>" alt="" /></div>
            </div>
        </div>        
    </div>
    <div class="pagination" data-ng-show="gridOptions.data.length>0">
        <pagination class="pagination-lg" total-items="gridOptions.totalItems" items-per-page="gridOptions.paginationPageSize" ng-model="gridOptions.paginationCurrentPage" max-size="10" rotate="false" boundary-links="false" data-my-call-back="clickOnNext"></pagination>
    </div>   
</div>
