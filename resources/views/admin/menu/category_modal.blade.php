<!-- for category list -->
<div class="modal fade iconModal" id="categoryList" role="dialog">
    <div class="modal-dialog modal-lg">
        <!-- Modal content Start Here-->
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="m-0 border-0 pb-0">@lang('admin_menu.category_list')</h3>
                <span class="fas fa-times close" data-dismiss="modal"></span>          
            </div>
            <div class="modal-body">                            
                <div class="form-group">
                    <label>@lang('admin_menu.choose_category_id')</label>                   
                    <div class="" id="treeMenuContainer">
                        <div class="megatree-menu" ng-if="menuData.category_tree.length">
                            <ul class="tree">
                                <node-tree children="menuData.category_tree"></node-tree>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>       
        </div>   
    </div>
</div>

<!-- for page list -->
<div class="modal fade page-modal" id="pagesList" role="dialog">
    <div class="modal-dialog modal-lg">
        <!-- Modal content Start Here-->
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="m-0">@lang('admin_menu.page_list')</h3>
                <span class="fas fa-times close" data-dismiss="modal"></span>          
            </div>
            <div class="modal-body">                            
                <div class="form-group">
                    <label>@lang('admin_menu.choose_page_id')</label>                   
                    <div class="page-list">
                        <div class="megatree-menu" ng-if="menuData.pages.length">
                            <ul class="tree">
                                <li class="page-list" ng-repeat="page in menuData.pages" ng-click="getParentId($event, page)">
                                    <span ng-bind="page.page_title"></span>
                                </li>                                
                            </ul>
                        </div>
                    </div>
                </div>
            </div>       
        </div>   
    </div>
</div>
