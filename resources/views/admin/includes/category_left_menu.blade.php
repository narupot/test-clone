<div class="content-left">
    <div class="category-left">
        <div class="root-box">
            <div class="root-menu-heading">
                <ul>
                    <li>
                        <a href="{{ action('Admin\BlogCategory\BlogCategoryController@create') }}" class="root-listitem">
                            <span class="foldericon-root"> 
                                <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="24px" height="22px" viewBox="0 0 14.5 11.5" enable-background="new 0 0 14.5 11.5" xml:space="preserve">
                                    <g>
                                        <path fill="#F9FFD9" stroke="#707171" stroke-width="0.5" stroke-miterlimit="10" d="M4.6,2.775h8.583
                                            c0.59,0,1.068,0.479,1.068,1.066v6.34c0,0.59-0.479,1.068-1.068,1.068H1.317c-0.591,0-1.067-0.479-1.067-1.068V1.316
                                            c0-0.59,0.477-1.066,1.067-1.066h2.464L4.6,2.775z"></path>
                                            <path fill="#FFFFFF" stroke="#707171" stroke-width="0.5" stroke-miterlimit="10" d="M12.983,2.775V2.027
                                            c0-0.588-0.479-1.066-1.068-1.066H4.011L4.6,2.775h8.084H12.983z"></path>
                                    </g>
                                </svg>                                                  
                            </span>
                            <span class="ficon-txt active">@lang('admin_blog.add_root_blog_category')</span>
                        </a>
                        <ul>
                            <li>
                                <a href="{{ action('Admin\BlogCategory\BlogCategoryController@subcreate') }}">
                                    <span class="foldericon-root">
                                        <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="24px" height="22px" viewBox="0 0 14.5 11.5" enable-background="new 0 0 14.5 11.5" xml:space="preserve">
                                            <g>   
                                                <path fill="#DDE8E8" stroke="#707171" stroke-width="0.5" stroke-miterlimit="10" d="M4.6,2.775h8.583 c0.59,0,1.068,0.479,1.068,1.066v6.34c0,0.59-0.479,1.068-1.068,1.068H1.318c-0.592,0-1.068-0.479-1.068-1.068V1.316 c0-0.588,0.477-1.066,1.068-1.066h2.463L4.6,2.775z"></path>   <path fill="#FFFFFF" stroke="#707171" stroke-width="0.5" stroke-miterlimit="10" d="M12.983,2.775V2.029      c0-0.59-0.479-1.066-1.068-1.066H4.012L4.6,2.775h8.084H12.983z"></path>
                                            </g>
                                        </svg>
                                    </span>
                                    <span class="ficon-txt"> @lang('admin_blog.add_sub_blog_category')</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>

        <!-- menu tree-->
        @if(count($categories) > 0)  
            <ul class="tree">
            @foreach($categories as $key=>$mainCategory)
                <li>
                    <a href="{{ action('Admin\BlogCategory\BlogCategoryController@edit', $mainCategory->id) }}">
                    @if(count($mainCategory->category) > 0)
                        <i class="menuIcon glyphicon glyphicon-plus"></i>
                    @endif
                    <i><img src="assets/images/folder.svg" alt=""></i> {{$mainCategory->getCatDesc->name}}</a>
                    @if(count($mainCategory->category) > 0) 
                        <ul>
                        @foreach($mainCategory->category as $subcategory)
                            <li>
                                <a href="{{ action('Admin\BlogCategory\BlogCategoryController@edit', $subcategory->id) }}">
                                @if(count($subcategory->category) > 0)
                                    <i class="menuIcon glyphicon glyphicon-plus"></i>
                                @endif
                                <i><img src="assets/images/folder.svg" alt=""></i> {{$subcategory->getCatDesc->name}}</a> 
                                @if(count($subcategory->category) > 0)
                                    <ul>
                                    @foreach($subcategory->category as $subsubcategory)
                                        <li>
                                            <a href="{{ action('Admin\BlogCategory\BlogCategoryController@edit', $subsubcategory->id) }}">
                                            @if(count($subsubcategory->category) > 0)
                                                <i class="menuIcon glyphicon glyphicon-plus"></i>
                                            @endif
                                            <i><img src="assets/images/subfolder.svg" alt=""></i> {{$subsubcategory->getCatDesc->name}}</a>
                                            @if(count($subsubcategory->category) > 0) 
                                                <ul>
                                                @foreach($subsubcategory->category as $finalcategory)
                                                    <li>
                                                        <a href="{{ action('Admin\BlogCategory\BlogCategoryController@edit', $finalcategory->id) }}">
                                                        <i><img src="assets/images/subfolder.svg" alt=""></i> {{$finalcategory->getCatDesc->name}}</a> 
                                                    </li>
                                                @endforeach
                                                </ul>
                                            @endif
                                        </li>
                                    @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
            </ul>
        @endif         
        <!-- menu tree end -->

    </div>  
</div>