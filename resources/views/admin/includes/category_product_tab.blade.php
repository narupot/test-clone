<div class="col-sm-12 ng-cloak">
    <!-- table select all btn panel  -->
    <div class="select-tag-btn" ng-if="tableSelectBtnConfig && gridOptions.data.length > 0">
        <a href="javascript:void(0)" class="btn-grey select-all" ng-click="selectAllColumnFunAll('select')">Select All</a>
        <a href="javascript:void(0)" class="btn-grey select-all" ng-click="selectAllColumnFunAll('unselect')">Unselect All</a>
        <a href="javascript:void(0)" class="btn-grey select-all" ng-click="selectVisibleColumnsAll('visible')">Select Visible</a>
        <a href="javascript:void(0)" class="btn-grey select-all" ng-click="selectVisibleColumnsAll('unVisible')">Unselect Visible</a>
        <span><%selectItemTotalAll%> Items selected</span>
    </div>
    <div class="filter-criteria" data-ng-show="tableFilterContainer">
        <div class="form-group">
            <div ng-repeat="field in $root.filedsSet"  data-ng-if="field.filterable == true" class="col-sm-3">
                    <label><%field.showName | uppercase%></label>  
                    <!-- textBoxType == single-->              
                    <input type="text" placeholder="<%field.fieldName.split('_').join(' ')%>" data-ng-model="filedSetModel[field.fieldName]"  name="<%field.fieldName%>"  data-ng-if="field.fieldType == 'textbox' && field.textBoxType == 'single'"  />
                    
                    <!-- textBoxType == range-->
                    <div class="row">
                        <div class="col-sm-6">
                            <input type="text" placeholder="<%field.fieldName.split('_').join(' ')%>" data-ng-model="filedSetModel[field.fieldName]" name="<%field.fieldName%>"  data-ng-if="field.fieldType == 'textbox' && field.textBoxType == 'range'" />
                        </div>
                        <div class="col-sm-6">
                            <input type="text" placeholder="<%field.fieldNameTo.split('_').join(' ')%>" data-ng-model="filedSetModel[field.fieldNameTo]" name="<%field.fieldNameTo%>"  ng-if="field.fieldType == 'textbox' && field.textBoxType == 'range'" />
                        </div>
                    </div>                    
                    
                   {{-- selection type single and value type collection --}}
                   <select data-ng-model="filedSetModel[field.fieldName]" ng-options="opt.value for opt in field.optionArr track by opt.key"  data-ng-if="field.fieldType == 'selectbox' && field.optionValType == 'collection' && field.selectionType == 'single'" ></select>
                   <dropdown-multiselect model="filedSetModel[field.fieldName]"  name="<%field.fieldName%>" options="field.optionArr" ng-if="field.fieldType == 'selectbox' && field.optionValType == 'collection' && field.selectionType == 'multiple'"></dropdown-multiselect>
                   <dropdown-multiselect model="filedSetModel[field.fieldName]"  name="<%field.fieldName%>" options="$rootScope.optionJsonArr[$index]" ng-if="field.fieldType == 'selectbox' && field.optionValType == 'url' && field.selectionType == 'multiple'"></dropdown-multiselect>
                   <!-- for date -->
                   <div class="input-group date-wrap" data-ng-if="field.fieldType == 'date'">
                      <div class="input-group" enable-datepicker options="{wrap: true, dateFormat : 'd-m-Y'}" ng-model="filedSetModel[field.fieldName]">
                          <input type="text" placeholder="Select Date.." class="form-control"  ng-model="filedSetModel[field.fieldName]" data-input />
                          <span class="input-group-addon" data-toggle>
                              <span class="glyphicon glyphicon-calendar"></span>
                          </span>
                      </div>
                   </div>
                   <!-- for date range -->
                   <div class="date-wrap" data-ng-if="field.fieldType == 'date_range'">
                      <!-- date from -->
                      <div class="col-sm-6 pull-left" enable-datepicker options="{wrap: true, dateFormat : 'd-m-Y'}" ng-model="filedSetModel[field.fieldName]">
                          <input type="text" placeholder="Select Date.." class="form-control"  ng-model="filedSetModel[field.fieldName]" data-input />
                          <span class="input-group-addon" data-toggle>
                              <span class="glyphicon glyphicon-calendar"></span>
                          </span>
                      </div>
                      <!-- date to -->
                      <div class="col-sm-6 pull-left" enable-datepicker options="{wrap: true, dateFormat : 'd-m-Y'}" ng-model="filedSetModel[field.fieldNameTo]">
                          <input type="text" placeholder="Select Date.." class="form-control"  ng-model="filedSetModel[field.fieldNameTo]" data-input />
                          <span class="input-group-addon" data-toggle>
                              <span class="glyphicon glyphicon-calendar"></span>
                          </span>
                      </div>
                   </div>
            </div>
        </div>
       
       <div class="form-group">
           <div class="col-sm-3">
                <button type="button" class="btn" ng-click="searchDataFromGrid()">
                    @lang('product.search')
                </button>
            </div>
            <div class="col-sm-3">
                    <button type="button" class="btn btn-delete" ng-click="searchDataFromGrid('resetfilter')">
                    @lang('product.reset_filter')
                </button>  
            </div>  
            {{-- <div class="col-sm-3">
                <button type="button" class="btn btn-save" data-ng-click="save_category()"> 
                    @lang('product.save_category')
                </button> 
            </div>   --}}                                    
        </div>
    </div>
    <div class="table-wrapper catelog-mgt-table" >
        <div class="table-record-row">
            <span class="filter-action-icon" data-ng-show="tableFilterConfig" ng-click="tableFilterContainer = !tableFilterContainer"> <span class=""><img src="assets/images/filter.png" alt="" ></span> @lang('category.filters') </span>
            <span>@lang('category.total_records') <%displayTotalNumItems%></span>

            <div class="float-right pager pagiTablet" data-ng-if="tableHeaderPaginationConfig && gridOptions.data.length>0">
                <dir-header-pagination></dir-header-pagination>
            </div>
        </div> 
        <div>
            <div id="productTable" data-ng-if="tabActive && gridOptions.data.length>0" ui-grid="gridOptions" ui-grid-pagination="" class="tableWidth ui-grid-selectable"  ui-grid-resize-columns ui-grid-selection ui-grid-move-columns ui-grid-draggable-rows ui-grid-save-state ui-grid-exporter ui-grid-auto-resize ng-style="getTableHeight()"></div>
            <div class="error-container" data-ng-bind-html="errorInfoLog | unsafe" data-ng-if="no_result_found"></div>
            {{-- <div class="loder-wrapper" data-ng-if="showLoaderTable">
                <div class="loader loader-medium">
                  <img ng-src="<%tableLoaderImgUrl%>" alt="" />
                </div>
            </div> --}}
        </div>
    </div>
    <div class="pagination" data-ng-show="gridOptions.data.length>0">
        <pagination class="pagination-lg" total-items="gridOptions.totalItems" items-per-page="gridOptions.paginationPageSize" ng-model="gridOptions.paginationCurrentPage" max-size="10" rotate="false" boundary-links="false" data-my-call-back="clickOnNext"></pagination>
    </div>

</div>
                   