<div class="drop-menu-box">
    <div class="menu-edit-box">
        <div class="list-item">
            <i class="icon fas fa-<%node.menu_icon%>"></i>
            <span><%menuFunction.getLangTitle(node)%></span>                
            <div class="float-right irem-wrap-icon">
                @lang('admin_menu.category')        
                <a href="javascript:void(0)" data-nodrag  ng-click="toggle(collapsed, node)">
                    <i class="fas fa-pencil-alt"></i>
                </a>               
                <i data-nodrag  ng-click="toggle(collapsed, node)" ng-class="collapsed? 'fal fa-chevron-down' : 'fal fa-chevron-right' "></i>
                <i class="fas fa-times" data-nodrag ng-click="remove(this)"></i>
            </div>
        </div>
        <div class="menu-edit-inner" data-nodrag  ng-show="collapsed">
            <ul class="nav nav-tabs lang-nav-tabs">               
                <li ng-repeat="lng in node.lang" class="nav-item">
                    <a data-toggle="tab" href="#lang_<%lng.id%>_<%node.menu_type_id%>_<%node.temp_id%>" class="nav-link" ng-class="(menuData.default_lang_id == lng.id) ? 'active':''">
                        <img ng-src="<%lng.flag%>" alt="">
                    </a>
                </li>
            </ul>
            <div class="tab-content language-tab">
                <div id="lang_<%lng.id%>_<%node.menu_type_id%>_<%node.temp_id%>" class="tab-pane fade" ng-repeat="lng in node.lang" ng-class="(menuData.default_lang_id == lng.id) ? 'show active':''">
                    <div class="form-group row">
                        <div class="col-sm-7">
                            <label><%lng.title%><i class="red" ng-show="menuData.default_lang_id == lng.id">*</i></label>
                            <input type="text" ng-model="lng.input" name="category-title-<%lng.code%>" class="form-control" value="" required>
                        </div>                        
                    </div>                                       
                </div>
                <!--Page Link Start-->
                <div class="form-group row">
                    <div class="col-sm-7">
                        <label>@lang('admin_menu.custom_css_class') </label>
                        <input type="text" ng-model="node.atrcustomcss" class="form-control">
                    </div>
                </div>
                <div class="category-container">    
                    <div class="form-group">
                        <label>@lang('admin_menu.category_id')<i class="red">*</i></label>
                        <div class="row">
                            <div class="col-sm-7">
                                <input type="text" class="form-control" ng-model="node.category_id" name="category-selection-<%node.temp_id%>" required>
                            </div>
                            <div class="col-sm-4">
                                <button class="btn btn-danger" data-toggle="modal" data-target="#categoryList" ng-click="selectNodeModal(node, 'category')">Select</button>
                            </div>                            
                            
                        </div>
                    </div>
                </div>
            </div>
            <div class="menu-edit-icon-wrap">
                <h3>@lang('admin_menu.menu_icon')</h3>                      
                <div class="lib-icon-wrap row">  
                    <div class="col-sm-4">                   
                        <div class="fontpicker fontpicker-component input-group">
                           <input type="text" class="form-control icp icp-auto action-swesome d-table-cell" value="fas fa-archive" ng-model="node.atr_menu_icon" icon-picker>
                           <div class="input-group-append">
                              <span class="input-group-text input-group-addon"><i class="fab fa-fonticons"></i></span>
                           </div>
                        </div>
                    </div>
                    <div class="list-icon col-sm-8">
                        <label class="w-100">@lang('admin_menu.choose_icon_position')</label>
                        <label class="radio-wrap-admin mr-2">
                            <input type="radio" class="radiomark" value="none" ng-model="node.icon_show" />
                            <span class="radiomark">None</span>                         
                        </label>
                        <label class="radio-wrap-admin mr-2">
                            <input type="radio" class="radiomark" value="after_text" ng-model="node.icon_show" />
                            <span class="radiomark">After text</span>                         
                        </label>
                        <label class="radio-wrap-admin mr-2">
                            <input type="radio" class="radiomark" value="before_text" ng-model="node.icon_show" />
                            <span class="radiomark">Before text</span>                         
                        </label>                                             
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>