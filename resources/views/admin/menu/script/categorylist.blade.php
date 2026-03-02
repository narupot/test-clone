<!-- recursive template -->
<script type="text/ng-template" id="categoryTree">
   <a class="menu-link" href="#">
   <span><%category.name%></span>   
    <i class="fas fa-chevron-right"></i></a>
    <ul ng-if="category.children" class="level1 groupmenu-drop">
        <li ng-repeat="category in category.children" ng-include="'categoryTree'" class="item level2 nav-2-1">      
        <a class="menu-link" href="#">
           <span><%category.name%></span>
            <i class="fas fa-chevron-right"></i></a>     
        </li>
    </ul>
</script>
