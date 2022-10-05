<script type="text/ng-template" id="nodes_renderer2.html">
  <div class="parent">
    <div ui-tree-handle class="tree-node tree-node-content list-item">
      <i class="icon fas fa-<%node.menu_icon%>"></i>
      &nbsp;&nbsp;<%node.title%>
      <i class="fa fa-bars float-right"></i>
    </div>
    </div>
    <ol  style="margin-left: 25px;"  ui-tree-nodes="" ng-model="node.nodes" ng-class="{'hidden': collapsed}">
      <li ng-repeat="node in node.nodes" ui-tree-node ng-include="'nodes_renderer2.html'">
      </li>
  </ol> 
</script>
