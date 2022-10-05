@extends('layouts/admin/default')
@section('title')
    @lang('admin_menu.edit_menu')
@stop
@section('header_styles')
    <link rel="stylesheet"  type="text/css" href="{{ Config('constants.css_url') }}angular-ui-tree.min.css"/>       
    <link rel="stylesheet"  type="text/css" href="{{ Config('constants.admin_css_url') }}mega-menu.css"/>
    <link rel="stylesheet"  type="text/css" href="{{ Config('constants.admin_css_url') }}preview.css"/>    
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}fontawesome-iconpicker.min.css" />
 
    <script>        
        var default_lang_id = {!! Session::get('admin_default_lang') !!}; 
        var LANG_CODE = "{{$lang_code}}";
        var globalTree = {!! $globalTree !!};
        var CATEGORY_PAGE_DATA_URL="{{ action('Admin\Menu\MenuController@getTypeList')}}";
        var SAVE_URL = "{{ action('Admin\Menu\MenuController@update', $id)}}"; 

        var formFieldName = [
            {"name" : "page-title", "mesg" : "@lang('admin_menu.please_enter_page_title')"},
            {"name" : "category-title", "mesg" : "@lang('admin_menu.please_enter_category_title')"},
            {"name" : "page-selection", "mesg" : "@lang('admin_menu.please_select_page')"},
            {"name" : "category-selection", "mesg" : "@lang('admin_menu.please_select_category')"},
            {"name" : "menu-name", "mesg" : "@lang('admin_menu.please_enter_menu_name')"},
            {"name" : "menu-design", "mesg" : "@lang('admin_menu.please_select_menu_design')"},
            {"name" : "link-title", "mesg" : "@lang('admin_menu.please_enter_link_title')"},
            {"name" : "link-url", "mesg" : "@lang('admin_menu.please_enter_link_url')"},
        ];

        var menu_json = {!! $menu_json!!};
        var pagetype = "edit_menu";

        //update menu json if nodes value is null
        (function updateNodes($){
            try{
                function updateLeafNodes(node){
                    if(node.nodes!="undefined" && (node.nodes == null || node.nodes == "")) node.nodes = [];
                    if(node.nodes && node.nodes.length){
                        $.map(node.nodes, (childNode)=>{
                            updateLeafNodes(childNode);
                        });
                    }
                };

                //update nodes
                $.map(menu_json['menu_json'], (item)=>{                    
                    if(item.nodes!="undefined" &&  (item.nodes == null || item.nodes == "")) item.nodes = [];
                    else if(item.nodes.length){
                        $.map(item.nodes, (cnd)=>{
                            updateLeafNodes(cnd);
                        });                        
                    } 
                });
            }catch(er){
                console.log;
            };
        })(jQuery);
        
    </script>
@stop

@section('content')
<!-- Content start here -->
<div class="content" data-ng-controller="megamenuCtrl" ng-cloak>
    <form name="megamenuForm" method ="post" id="megamenuForm" enctype='multipart/form-data'  novalidate>
        <div class="header-title"> 
            <h1 class="title">@lang('admin_menu.edit_menu')</h1>       
            <div class="float-right">         
                <a href="{{ action('Admin\Menu\MenuController@index')}}" class="btn btn-back" ng-click="back()">&lt;@lang('admin_common.back')</a>
                <button class="btn-save btn-primary" type="button" ng-click="saveMenu($event, megamenuForm)">@lang('admin_common.update')</button>
            </div>
        </div>   
        <div class="content-wrap mega-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('menu','menu')!!}
                </ul>
            </div>
            <div class="form-group row">
                <div class="col-sm-4">
                    <label class="">@lang('admin_menu.menu_name')<i class="red">*</i></label>
                    <input type="text" class="col-sm-8 form-control" name="menu-name" value="" ng-model="menu.name" required>
                </div>
            </div>      
            <div class="box megamenu-box">
                <div class="row">
                    <div class="col-sm-4">
                      <div class="left-side-content">
                            <div class="title-wrap clearfix">                                
                                <h2>@lang('admin_menu.megamenu_material')</h2>
                            </div>
                            <div class="mega-item-wrap">
                                <div class="meag-item">
                                    <div ui-tree="megaMenuTreeOption" data-clone-enabled="true" data-nodrop-enabled="true">
                                      <ol ui-tree-nodes ng-model="menuData['left_menu']">
                                          <li ng-repeat="node in menuData['left_menu']" ui-tree-node ng-include="'nodes_renderer2.html'" id="menu_<%node.id%>">
                                          </li>
                                        </ol>
                                    </div>          
                                </div>
                            </div> 
                        </div>                      
                    </div>
                    <div class="col-sm-8">
                        <div class="title-wrap clearfix">
                            <div class="float-right">
                              <button type="button" ng-click="expandAll()" class="btn secondary btn-info"><i class="far fa-expand-alt"></i>&nbsp;@lang('admin_menu.expand_all')</button>
                                <button type="button" ng-click="collapseAll()" class="btn secondary btn-warning"><i class="far fa-compress-alt"></i>&nbsp;@lang('admin_menu.collapse_all')</button>
                            </div>
                            <h2>@lang('admin_menu.megamenu_structure')</h2>
                        </div>
                        <div class="drop-menu-box" ui-tree="megaMenuTreeOption">
                            <ol ui-tree-nodes ng-model="menuData['right_menu']">
                                <li ng-repeat="(key,node) in menuData['right_menu']" ui-tree-node ng-include="'nodes_renderer.html'"> 
                                </li>
                            </ol>
                        </div>
                        <div>&nbsp;</div>                   
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="content-wrap clearfix" >
        @include('admin.menu.script.template_menu')
        @include('admin.menu.script.template_container')
    </div>  

    <!-- category modal -->
    <div class="category-modal-container">        
        @include('admin.menu.category_modal')
    </div>    
</div>
              
@stop
@section('footer_scripts')
    <script src="{{ Config('constants.js_url') }}fontawesome-iconpicker.js"></script>
    <script src="{{ Config('constants.angular_admin_url') }}libs/angular.min.js"></script>   
    <script src="{{ Config('constants.angular_admin_url') }}libs/angular-ui-tree.min.js"></script>
    <script src="{{ Config('constants.angular_app_url') }}services/service.js"></script>
    <script src="{{ Config('constants.angular_app_url') }}model/megaMenuApp.js"></script>
    <script src="{{ Config('constants.angular_app_url') }}controller/megamenuCtrl.js"></script>
    <script type="text/javascript">
        (function($) {
            $('#menu_5').addClass('second-last');
            var offset = $(".left-side-content").offset();
            var topPadding = 130;
            $(window).scroll(function() {
                if ($(window).scrollTop() > offset.top) {
                    $(".left-side-content").stop().animate({
                        marginTop: $(window).scrollTop() - offset.top - 50
                    });
                } else {
                    $(".left-side-content").stop().animate({
                        marginTop: 0
                    });
                };
            });                      
        })(jQuery);
    </script>
@stop
