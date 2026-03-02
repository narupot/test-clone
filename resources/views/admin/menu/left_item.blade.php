<div class="col-sm-3">
<div ui-tree id="tree1-root" data-clone-enabled="true" data-nodrop-enabled="true">
  <ol ui-tree-nodes ng-model="tree1"><%node.title%><i class="glyphicon glyphicon-chevron-down"></i>
    <li ng-repeat="node in tree1" ui-tree-node ng-include="'nodes_renderer2.html'" ng-if="node.menuTypeId==5">
    </li>
  </ol>
</div>
<br><br>
</div>