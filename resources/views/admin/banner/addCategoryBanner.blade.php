@extends('layouts/admin/default')

@section('title')
    @lang('admin.all_banner') - {{getSiteName()}} 
@stop

@section('header_styles')

    <!--page level css -->

    <link href="{{ asset('assets/vendors/bootstrapvalidator/css/bootstrapValidator.min.css') }}" type="text/css" rel="stylesheet">
    <!-- end of page level css -->

    <link href="{{ asset('assets/css/flatpickr.min.css') }}" rel="stylesheet">
    
@stop



@section('content')

    <!-- Right side column. Contains the navbar and content of the page -->
    <div class="content">
        @if(Session::has('succMsg'))    
            <script type="text/javascript">               
                _toastrMessage('success', "{{ Session::get('succMsg') }}");    
            </script>                            
        @endif 
        <div id="tab3" class="tab-pane">
            <div class="containers">
                <div class="heading-wrapper clearfix">
                    <h2 class="title">
                        @lang('admin.add_category')
                    </h2>
                </div>
                <div class="border">

                   {!! Form::open(['url' => action('BannerController@addCategoryBanner'), 'id'=>'addTranslationBannerForm', 'class'=>'form-horizontal  form-bordered']) !!}
                    

                    <div class="tree-menu" ng-app="treeMenu" >
                      <div ng-controller="TreeController">
                        <ul class="tree">
                          <node-tree children="tree"></node-tree>
                        </ul>
                      </div>
                  </div>
                   

                   
                   {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
      
@stop

@section('footer_scripts')

  <script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.3.15/angular.min.js"></script>

  <script src="{{ asset('js/TreeController.js')}}"></script>

      
    
@stop
