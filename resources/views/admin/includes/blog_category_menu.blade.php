<div class="category-left">
    <div class="root-box">

    <div class="root-menu-heading">
        <ul>
            <li><a href="{{ action('Admin\BlogCategory\BlogCategoryController@create') }}" class="root-listitem">
                    <span class="foldericon-root"> <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="24px" height="22px" viewBox="0 0 14.5 11.5" enable-background="new 0 0 14.5 11.5" xml:space="preserve">
                                    <g><path fill="#F9FFD9" stroke="#707171" stroke-width="0.5" stroke-miterlimit="10" d="M4.6,2.775h8.583
                                    c0.59,0,1.068,0.479,1.068,1.066v6.34c0,0.59-0.479,1.068-1.068,1.068H1.317c-0.591,0-1.067-0.479-1.067-1.068V1.316
                                    c0-0.59,0.477-1.066,1.067-1.066h2.464L4.6,2.775z"/>
                                    <path fill="#FFFFFF" stroke="#707171" stroke-width="0.5" stroke-miterlimit="10" d="M12.983,2.775V2.027
                                    c0-0.588-0.479-1.066-1.068-1.066H4.011L4.6,2.775h8.084H12.983z"/>
                                    </g></svg>                                                  
                                    </span>
                    <span class="ficon-txt"> Add Root Category</span></a>
                <ul>
                    <li><a href="{{ action('Admin\BlogCategory\BlogCategoryController@subcreate') }}">
                        <span class="foldericon-root"><svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="24px" height="22px" viewBox="0 0 14.5 11.5" enable-background="new 0 0 14.5 11.5" xml:space="preserve"><g>   <path fill="#DDE8E8" stroke="#707171" stroke-width="0.5" stroke-miterlimit="10" d="M4.6,2.775h8.583 c0.59,0,1.068,0.479,1.068,1.066v6.34c0,0.59-0.479,1.068-1.068,1.068H1.318c-0.592,0-1.068-0.479-1.068-1.068V1.316 c0-0.588,0.477-1.066,1.068-1.066h2.463L4.6,2.775z"/>   <path fill="#FFFFFF" stroke="#707171" stroke-width="0.5" stroke-miterlimit="10" d="M12.983,2.775V2.029      c0-0.59-0.479-1.066-1.068-1.066H4.012L4.6,2.775h8.084H12.983z"/></g></svg></span>
                        <span class="ficon-txt"> Add Subcategory</span>
                    </a></li>
                </ul>
            </li>
        </ul>
    </div>

</div>
    <div class="root-box tree-menu" ui-tree="catTreeOpt" id="treeMenuContainer" data-drag-delay="200">
        <ol ui-tree-nodes data-ng-model="tree" class="tree">
            <li ng-repeat="node in tree" ui-tree-node ng-include="'nodes_renderer.html'"></li>
        </ol>
     </div> 
</div>
 <script type="text/ng-template" id="nodes_renderer.html">
  <div ui-tree-handle class="tree-node tree-node-content">
    <a class="btn-xs" data-ng-if="node.children && node.children.length > 0" data-nodrag data-ng-click="toggle(this)"><span class="expandCollapse" data-ng-class="{'glyphicon-plus plus': collapsed,'glyphicon-minus minus': !collapsed}"></span></a>
    <span class="listName" data-ng-click="catTreeOpt.cateOpen(this)" ng-class="{'category-node-disable' : node.status == '0', 'category-node-active' : node.checked}"><%node.name%> (<%node.total_products%>)</span>
  </div>
  <ol ui-tree-nodes="" ng-model="node.children" ng-class="{hidden: collapsed}">
    <li ng-repeat="node in node.children" ui-tree-node ng-include="'nodes_renderer.html'">
    </li>
  </ol>
</script>