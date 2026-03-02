@extends('layouts.seller.default')
@section('content')
<section class="content sidebar-content">
    <div class="content-heading">
        <h2 class="seller-heading category-left-heading pull-left"><span>Categories</span></h2>
    </div>
    <div class="content-section">
        
        <div class="row">
            <div class="col-sm-12">
                @if (Session::exists('message'))
                <div class="alert alert-success alert-dismissable margin5">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{Session::get('message')}}
                </div>
                @endif
            </div>
        </div>
        <div class="content-mid">

            <!-- BEGIN SIDEBAR MENU -->
            @include('layouts.seller.seller_category_left')
            <!-- END SIDEBAR MENU -->

            <div class="category-right">

                <div class="category-tab">
                    <div class="float-right">
                        {!! Form::open(['style' => 'display: inline-block;', 'method' => 'DELETE', 'onsubmit' => 'return confirm("Are sure delete this data.");',  'route' => ['catalog.destroy', $category->id]]) !!}
                         {!! Form::button('Remove Category', ['class' => 'btn btn-md', 'type'=>'submit']) !!}
                        {!! Form::close() !!}
                        
                        <button class="btn-skyblue btn-md" onclick="document.getElementById('sellerCategoryForm').submit();">Save Category</button>
                    </div>

                    <ul class="tab-list">
                        <li class="active"><a href="#cetegory-general-info" data-toggle="tab">General Infomation</a></li>
                        <li><a href="#cetegory-cat-product" data-toggle="tab">Category Products</a></li>
                    </ul>
                </div>

                <div class="detail-box">
                    <div class="tab-content">
                        <div id="cetegory-general-info" class="tab-pane fade in active" >

                            {!! Form::open(['action' => ['Seller\CategoryController@update', $category->id], 'method' => 'put','id'=>'sellerCategoryForm', 'class'=>'form-horizontal']) !!}

                            {!! Form::hidden('parent_id', old('parent_id', $category->parent_id?$category->parent_id:0), ['id'=>'parent_id']) !!}
                        
                            {!! Form::hidden('category_id', old('category_id', $category->id), ['id'=>'category_id']) !!}
                            
                            <div class="category-gen-form">

                                <div class="form-row">
                                    <label>Name <i class="strick">*</i></label>
                                    {!! Form::text('url', old('url', $category->categorydesc->category_name), ['placeholder'=>'Black T-shirt'] ) !!}
                                </div>
                                <div class="form-row">
                                    <label>Active <i class="strick">*</i></label>
                                    {!! Form::select('status', ['1'=>'Yes', '2'=>'No'],  null) !!}
                                </div>
                                <div class="form-row">
                                    <label>Category Description</label>
                                    {!! Form::textarea('cat_description', old('cat_description', stripslashes($category->categorydesc->cat_description))) !!}
                                </div>
                            </div>
                            <div class="advance-setting">
                                <span class="checkbox-btn">
                                    {!! Form::checkbox('adv_setting', '1', (old('adv_setting') == '1'? '1':$category->adv_setting),['class' => 'checkbox', 'id'=>'adv-setting'] ) !!}
                                     <label for="adv-setting" class="checkbox-label">Advance Setting</label>
                                </span>								
                            </div>

                            <div class="advance-setting-option"> 
                                 {!! CustomHelpers::fieldstabWithLanuageEdit([['field'=>'textarea', 'name'=>'cat_footer_seo', 'label'=>'Description', 'cssClass'=>'texteditor']],'1', 'cat_id', $category->id, $tableCategoryDesc)!!}
                                 {!! CustomHelpers::fieldstabWithLanuageEdit([['field'=>'text', 'name'=>'meta_keyword', 'label'=>'Meta keywords', 'cssClass'=>''], ['field'=>'textarea', 'name'=>'meta_desc', 'label'=>'Meta Description', 'cssClass'=>'']], '2', 'cat_id', $category->id, $tableCategoryDesc)!!}
                            </div>

                            <button type="submit" class="btn-skyblue btn-md">Save Category</button>

                            {!! Form::close() !!}

                        </div>

                        <div id="cetegory-cat-product" class="tab-pane fade ">

                            <div class="view-number">
                                View <a href="#">30</a> 
                                <a href="#">90</a>
                                <a href="#">120</a>
                            </div>
                            <div class="table-wrapper catelog-mgt-table">
                                <div class="table">
                                    <div class="table-header">
                                        <ul>
                                            <li><input type="checkbox"><label class="checkbox-label"></label></li>
                                            <li>ID</li>
                                            <li>Name</li>
                                            <li>SKU</li>
                                            <li>Price</li>
                                        </ul>
                                    </div>
                                    <div class="table-content">
                                        <ul>
                                            <li> <select>
                                                    <option>Any</option>
                                                    <option>Any2</option>
                                                </select> </li>
                                            <li><input type="text">	</li>
                                            <li><input type="text">	</li>
                                            <li><input type="text">	</li>
                                            <li><input type="text">	</li>
                                        </ul>
                                        <ul>
                                            <li><input type="checkbox"></li>
                                            <li>1</li>
                                            <li><img src="images/ord-img2.jpg" alt=""> Too long Product Name...</li>
                                            <li>TS-001-11</li>
                                            <li>100,000.00</li>
                                        </ul>
                                        <ul>
                                            <li><input type="checkbox"></li>
                                            <li>2</li>
                                            <li><img src="images/ord-img.jpg" alt=""> Product Name </li>
                                            <li>TS-002-12</li>
                                            <li>100,000.00</li>
                                        </ul>
                                        <ul>
                                            <li><input type="checkbox"></li>
                                            <li>3</li>
                                            <li><img src="images/ord-img1.jpg" alt=""> Product Name</li>
                                            <li>TK-025-81</li>
                                            <li>100,000.00</li>
                                        </ul>
                                        <ul>
                                            <li><input type="checkbox"></li>
                                            <li>4</li>
                                            <li><img src="images/ord-img2.jpg" alt=""> Too long Product Name...</li>
                                            <li>TS-001-11</li>
                                            <li>100,000.00</li>
                                        </ul>
                                        <ul>
                                            <li><input type="checkbox"></li>
                                            <li>5</li>
                                            <li><img src="images/ord-img.jpg" alt=""> Product Name </li>
                                            <li>TS-002-12</li>
                                            <li>100,000.00</li>
                                        </ul>
                                        <ul>
                                            <li><input type="checkbox"></li>
                                            <li>6</li>
                                            <li><img src="images/ord-img1.jpg" alt=""> Product Name</li>
                                            <li>TK-025-81</li>
                                            <li>100,000.00</li>
                                        </ul>
                                        <ul>
                                            <li><input type="checkbox"></li>
                                            <li>7</li>
                                            <li><img src="images/ord-img2.jpg" alt=""> Too long Product Name...</li>
                                            <li>TS-001-11</li>
                                            <li>100,000.00</li>
                                        </ul>
                                        <ul>
                                            <li><input type="checkbox"></li>
                                            <li>8</li>
                                            <li><img src="images/ord-img.jpg" alt=""> Product Name </li>
                                            <li>TS-002-12</li>
                                            <li>100,000.00</li>
                                        </ul>
                                        <ul>
                                            <li><input type="checkbox"></li>
                                            <li>9</li>
                                            <li><img src="images/ord-img1.jpg" alt=""> Product Name</li>
                                            <li>TK-025-81</li>
                                            <li>100,000.00</li>
                                        </ul>
                                        <ul>
                                            <li><input type="checkbox"></li>
                                            <li>10</li>
                                            <li><img src="images/ord-img1.jpg" alt=""> Product Name</li>
                                            <li>TK-025-81</li>
                                            <li>100,000.00</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="pagination">
                                <ul>
                                    <li class="prev">&lt;</li>
                                    <li><a href="">1</a></li>
                                    <li><a href="">2</a></li>
                                    <li><a href="">3</a></li>
                                    <li><a href="">4</a></li>
                                    <li><a href="">5</a></li>
                                    <li><a href="">6</a></li>
                                    <li><a href="">7</a></li>
                                    <li><a href="">8</a></li>
                                    <li><a href="">9</a></li>
                                    <li><a href="">10</a></li>
                                    <li class="next">&gt;</li>
                                </ul>
                            </div>

                        </div>

                    </div>
                </div>
                
                 {!! Form::close() !!}
                

            </div>

        </div>

        <!-- help section start -->
        <div class="help-content visible-lg">
            <h3><span class="question-icon">?</span> Help</h3>
            <div class="help-row">
                <h3>Product Details</h3>
                <p>Specify what category this product  falls under in your product catalogue. </p>
                <p><span class="example"> EXAMPLE </span> Men's T-Shirts, Women's Accessories, Sports Shoes. Specify what category this product falls under in your product catalogue.</p>
            </div>
            <div class="help-row">
                <h3>Product Advance Setting</h3>
                <p>Specify what category this product falls under in your product catalogue. </p>
                <p><span class="example"> EXAMPLE </span> Men's T-Shirts, Women's Accessories, Sports Shoes. Specify what category this product falls under in your product catalogue.</p>
            </div>
        </div> 
        <!-- help section end -->


    </div>  
   <div class="push-content"></div>
</section>
@endsection 
@section('footer_scripts')
  <script src="{{ asset('js/seller.category.js') }}" type="text/javascript"></script>
@stop