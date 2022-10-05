<div>
    <!-- table select all btn panel  -->
    <div class="select-tag-btn" ng-if="tableSelectBtnConfig">
        <a href="javascript:void(0)" class="btn-default select-all" ng-click="rowSelectionFun('select')">Select All</a>
        <a href="javascript:void(0)" class="btn-default select-all" ng-click="rowSelectionFun('unselect')">Unselect All</a>
        <a href="javascript:void(0)" class="btn-default select-all" ng-click="rowVisibleSelectionFun('visible')">Select Visible</a>
        <a href="javascript:void(0)" class="btn-default select-all" ng-click="rowVisibleSelectionFun('unVisible')">Unselect Visible</a>
        <span><%selectItemTotal%> Items selected</span>
    </div>
    <div class="filter-criteria" data-ng-show="tableFilterContainer && gridOptions.data.length>0">
        <div class="form-row">
         <div ng-repeat="field in $root.filedsSet"  data-ng-if="field.filterable == true" class="col-sm-3">
                <label><%field.displayName%></label>
                <input type="text" placeholder="<%field.displayName.split('_').join(' ')%>" data-ng-model="filedSetModel[$index]"  name="<%field.fieldName%>"  data-ng-if="field.fieldType == 'textbox' && field.textBoxType == 'single'"  data-ng-keyup="textChangeFunction(field.fieldName,$event)"/>
                <input type="text" placeholder="from" data-ng-model="filedSetModel[$index]" name="<%field.fieldName%>"  data-ng-if="field.fieldType == 'textbox' && field.textBoxType == 'range'" ng-keyup="textChangeFunction(field.fieldName+'_From',$event)">
                <input type="text" placeholder="to" data-ng-model="filedSetModel[$index]" name="<%field.fieldName%>"  ng-if="field.fieldType == 'textbox' && field.textBoxType == 'range'" ng-keyup="textChangeFunction(field.fieldName+'_To',$event)">
               {{-- selection type single and value type collection --}}
               <select data-ng-model="filedSetModel[$index]" ng-options="opt.value for opt in field.optionArr"  data-ng-if="field.fieldType == 'selectbox' && field.optionValType == 'collection' && field.selectionType == 'single'" data-ng-change="filterSelectBoxChange(filedSetModel[$index],field.fieldName)"></select>
               <dropdown-multiselect model="filedSetModel[$index]"  name="<%field.fieldName%>" options="field.optionArr" ng-if="field.fieldType == 'selectbox' && field.optionValType == 'collection' && field.selectionType == 'multiple'"></dropdown-multiselect>
               <dropdown-multiselect model="filedSetModel[$index]"  name="<%field.fieldName%>" options="$rootScope.optionJsonArr[$index]" ng-if="field.fieldType == 'selectbox' && field.optionValType == 'url' && field.selectionType == 'multiple'"></dropdown-multiselect>
         </div>
        </div>
       {{--  <div class="btn-row updbtn"> --}}
       <div class="form-row">
           <div class="col-sm-6">
                <button type="button" class="btn" ng-click="searchDataFromGrid()">
                    @lang('product.search')
                </button>            
                <button type="button" class="btn btn-delete" ng-click="searchDataFromGrid('resetfilter')">
                @lang('product.reset_filter')
            </button>  
            </div>
       </div>
    </div>
    <div class="table-wrapper catelog-mgt-table" >
        <div class="table-record-row" data-ng-show="gridOptions.data.length>0">                 
            <select ng-model="actionSelectBox" ng-change="actionOnDataGrid()" ng-options="option.name for option in actioOptions">
            </select>
            <button type="button" class="btn" ng-show="selBoxActBtn" ng-click="actionBtnClick()">Submit</button>
            <span class="filter-action-icon" data-ng-show="tableFilterConfig" ng-click="tableFilterContainer = !tableFilterContainer"> <span class=""><img src="assets/images/filter.png" alt="" ></span> @lang('category.filters') </span>
            <span>@lang('category.total_records') <%displayTotalNumItems%></span>
            <div class="float-right pager pagiTablet" data-ng-if="tableHeaderPaginationConfig">
                <dir-header-pagination></dir-header-pagination>
            </div>
        </div> 
        <div>
            <div id="productTable" data-ng-if="tabActive || gridOptions.data.length>0" ui-grid="gridOptions" ui-grid-pagination="" class="tableWidth ui-grid-selectable"  ui-grid-resize-columns ui-grid-selection ui-grid-move-columns ui-grid-draggable-rows ui-grid-save-state ui-grid-exporter ui-grid-auto-resize ng-style="getTableHeight()"></div>
            <div class="error-container" data-ng-bind-html="errorInfoLog | unsafe" data-ng-if="no_result_found"></div>
            <div class="loder-wrapper" data-ng-if="showLoaderTable">
                <div class="loader loader-medium"></div>
            </div>
        </div>
    </div>
    <div class="pagination" data-ng-show="gridOptions.data.length>0">
        <pagination class="pagination-lg" total-items="gridOptions.totalItems" items-per-page="gridOptions.paginationPageSize" ng-model="gridOptions.paginationCurrentPage" max-size="10" rotate="false" boundary-links="true"></pagination>
    </div>
</div>