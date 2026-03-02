<div>
  <h2 class="title-prod">
      <i class="count icon-search"></i> @lang('product.requirement')   
  </h2>
  <!--if requirement is already there -->
  <div class="row" ng-if="prdData.reqhas">
      <div class="col-sm-4 attribute-wrap">        
          <div class="drag-wrap">
             <div class="form-group">
                <label>Drag attribute to use <i class="float-right glyphicon glyphicon-share-alt"></i></label>
                <form action="">
                   <div class="attr-search-warp">
                   <input placeholder="Search Attribute" type="text" ng-model="prdData.reqQuery">
                   <button type="button" class="btn-search"><span class="icon-search"></span></button>
                   </div>
                </form>
                <div class="pager">
                <button type="button" class="fas fa-chevron-left prev"  ng-click="prdData.reqCurPage=prdData.reqCurPage-1" ng-disabled="prdData.reqCurPage == 0"></button>
                  <div class="col-sm-6">
                    <select ng-model="prdData.reqPageSize" id="reqPageSize" class="" ng-options="opt for opt in prdData.pagearr">
                    </select>
                  </div>
                   <span class="count-num"> <%(prdData.reqCurPage+1)%> of <%getSpecTotalPageCount()%></span>
                   <button type="button" class="fas fa-chevron-right next" ng-click="prdData.reqCurPage=prdData.reqCurPage+1" ng-disabled="prdData.currentPage >= (getFilterData().length/prdData.pageSize-1)"></button>
                   <!-- <span class="count-num"> of <%prdData.reqlist.length%></span>
                   <button type="button" class="fas fa-chevron-right next" ng-click="prdData.reqCurPage=prdData.reqCurPage+1"  ng-disabled="prdData.reqCurPage >= prdData.reqlist.length/prdData.reqPageSize - 1"></button> -->
                </div>
                <div class="attr-row-wrap ui-sortable dragdropReqElement" id="attr-dragElement" dnd-list="prdData.reqlist" dnd-drop="dropCallback(index, item, external, type, 'requirement-leftside')">
                  <div class="attr-row"  itemid="itm-1" ng-repeat="list in prdData.reqlist | filter: {attributedesc: {name: prdData.reqQuery}} | startFrom:prdData.reqCurPage*prdData.reqPageSize | limitTo:prdData.reqPageSize" dnd-draggable="list"><!-- prdData.specslist.splice($index, 1); -->
                 <!-- <div class="attr-row"  itemid="itm-1" ng-repeat="list in prdData.reqlist | filter:prdData.reqQuery | startFrom:prdData.reqCurPage*prdData.reqPageSize | limitTo:prdData.reqPageSize" dnd-draggable="list" dnd-moved="prdData.reqlist.splice($index, 1);moveHandler(list,'requirement')"> -->
                    <dnd-nodrag>
                     <span class="count"><%$index+1%> </span>
                     <span class="fa fa-bars ui-sortable-handle handle" dnd-handle></span>
                     <span class="attr-name"><%list.attributedesc.name%></span>
                     <span class="float-right  glyphicon glyphicon-font skyblue" ng-if="list.front_input=='textarea' || list.front_input=='text'"></span>
                     <span class="float-right glyphicon glyphicon-menu-down" ng-if="list.front_input=='multiselect'"></span>
                     <span class="float-right glyphicon glyphicon-menu-down" ng-if="list.front_input=='select'"></span>
                     <span class="float-right glyphicon glyphicon-paperclip" ng-if="list.front_input=='browse_file'"></span>
                     <span class="float-right glyphicon glyphicon-calendar" ng-if="list.front_input=='date_picker'"></span>
                    <dnd-nodrag>
                 </div>
                </div>
            </div>
          </div>
      </div>
      <div class="col-sm-8">
          <h3>Requirment fill for your customer</h3>
          <p>
              <a href="javascript:void(0)" class="float-right">See Example</a>
              It will show on Product detail page
          </p>
          <div class="box" dnd-list="prdData.req_create_list" dnd-drop="dropCallback(index, item, external, type, 'requirement')">
              <div class="blank-drag" data-ng-if="prdData.req_create_list.length === 0">
                  <p class="blank-txt"><i class="glyphicon glyphicon-share-alt"></i> Drag your Attribute drop here</p>
              </div>
              <div class="form-group select-color-row" ng-repeat="item in prdData.req_create_list track by $index"> 
                <label class="mb-5">
                    <!-- <span class="fa fa-bars"></span> -->
                    <span class="attr-name"><%item.attributedesc.name%></span>
                </label>   
                <div class="row">
                  <div class="col-sm-11 req-field-group" ng-if="item.front_input=='select' || item.front_input=='multiselect'">
                      <div class="row">
                        <input class="float-right" placeholder="Order" type="text" ng-model="product.reqmodel[$index].oderval"> 
                      </div>
                      <div ng-repeat="opt in item.get_all_attribute_value_detail">
                          <div class="checkbox-section row" >

                            <div class="col-sm-12">
                              <label class="check-wrap"><input type="checkbox" ng-model="product.reqmodel[$parent.$index].multiopt[$index].check"> <span class="chk-label"></span></label>
                              <label class="check-wrap"><%opt.values%></label>
                            </div>
                            <div class="col-sm-12">
                              <input placeholder="Price" type="text" ng-model="product.reqmodel[$parent.$index].multiopt[$index].priceval"> 

                             <select data-ng-model="product.reqmodel[$parent.$index].multiopt[$index].pricetype">
                              <option value="Fixed"> 
                              @lang('product.fixed')
                              </option>
                              <option value="Percent" >
                              @lang('product.percent')
                              </option>

                          </select>
                          <input placeholder="Order" type="text" ng-model="product.reqmodel[$parent.$index].multiopt[$index].orderval">
                          </div>
                          </div>
                      </div>
                  </div>
                  <div class="col-sm-11 req-field-group" ng-if="item.front_input=='text' || item.front_input=='textarea' || item.front_input=='browse_file' || item.front_input=='date_picker'">
                      <input placeholder="Price" type="text" ng-model="product.reqmodel[$index].priceval"> 
                      <select data-ng-model="product.reqmodel[$index].pricetype">
                        <option value="Fixed">
                          @lang('product.fixed')
                        </option>
                        <option value="Percent">
                          @lang('product.percent')
                        </option>
                      </select>
                      <input placeholder="Order" type="text" ng-model="product.reqmodel[$index].oderval"> 
                  </div>
                  <div class="col-sm-1 float-right check-group label-option-use">
                   <span class="icon-close float-right" ng-click="_removeSep($event,$index,'requirement',item)"></span>
                  </div>
                </div>
              </div>
          </div>        
      </div>
  </div>
  <!-- if requirement is not available -->
  <div class="row" ng-if="!prdData.reqhas">
      <div class="form-group mt-2"> 
          <div class="col-sm-4">
              <a href="#" class="btn col-sm-12">Create Product Requirement</a>
          </div>                                      
      </div>
      <div class="form-group">
          <div class="col-sm-4">
              <a href="javascript:void(0)"><img src="images/product-variant.jpg" alt="" title=""></a>
              <h2>What is Product Requirement</h2>
              <p>Cut from tissue-weight silk crepe de  chine, this airy style features a  ruched neckline with tie and an  unfinished hem for a contrastinly </p>
          </div>
      </div>                           
  </div>
</div> 