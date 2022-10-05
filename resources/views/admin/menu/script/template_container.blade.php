<script type="text/ng-template" id="nodes_renderer.html" >
    <div ui-tree-handle style="position: relative;">
        <!-- if menu_type_id == 1 mean pages -->
        <div ng-if="node.menu_type_id==1">
            @include('admin.menu.template.pages_wrapper_template')
    	</div>
        <!-- if menu_type_id == 3 mean item link -->
        <div ng-if="node.menu_type_id==3">       
            @include('admin.menu.template.item_links_template')
    	</div>
        <!-- if menu_type_id == 6 mean category -->        
        <div ng-if="node.menu_type_id==6">
            @include('admin.menu.template.category_list_template')
        </div>
    </div>
    <ol ui-tree-nodes="" ng-model="node.nodes" ng-class="{hidden: collapsed}">   
        <li ng-repeat="(nodes_key,node) in node.nodes" ui-tree-node ng-include="'nodes_renderer.html'">
        </li>
    </ol>
</script>
