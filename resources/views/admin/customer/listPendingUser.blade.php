@extends('layouts/admin/default')

@section('title')
    @lang('admin_customer.pending_user_list')
@stop

@section('header_styles')
  <link rel="stylesheet" type="text/css" href="{{Config('constants.css_url') }}ui-grid-unstable.css"> 

  <script>

      var fieldSetJson  = {!! $fielddata !!};
      var fieldset = fieldSetJson.fieldSets;
      var pagelimit = "{{action('JsonController@pageLimit')}}";
      var showSearchSection = true;
      var showHeadrePagination = true;
      var getAllDataFromServerOnce = true;
      var dataJsonUrl = "{{ action('Admin\Customer\UserController@pendingUserData') }}";
      var statusUrl = "{{ action('Admin\Customer\UserController@approve') }}";
      var tableLoaderImgUrl = "{{ Config::get('constants.loader_url')}}ajax-loader.gif";
      //pagination config 
      var pagination = {!! getPagination() !!};
      var per_page_limt = {{ getPagination('limit') }};

      //Listen on column setting 
     _getInfo=(fName,fType)=>{
       let ind = fieldset.findIndex(x=>x.fieldName===fName);
       if(ind>=0){
            let r =false;
            if(fType==='sortable'){
              r= (typeof fieldset[ind].sortable!=='undefined')? fieldset[ind].sortable:false;
            }else if(fType==='width'){
              r= (typeof fieldset[ind].width!=='undefined')? fieldset[ind].width:100;
            }else if(fType==='align'){
               r= (typeof fieldset[ind].align!=='undefined')? 'text-'+fieldset[ind].align:'text-left';
            }
            return r;
       }else {
          if(fType==='width'){
            return 100;
          }else if(fType==='align'){
            return 'text-left';
          }else if(fType==='sortable'){
            return false;
          }
       }
       return false;
      };
      /**** This code used for columns setting of table where field is field name of database filed.*****/
      var columsSetting = [
       {
         field : 'id',
          displayName : '@lang('admin_common.sno')',
          cellTemplate : '<span><%grid.appScope.seqNumber(row)+1%></span>',
          enableSorting : _getInfo('id','sortable'),
          width : _getInfo('id','width'),
          cellClass : _getInfo('id','align'),
        },

        { 
          field : 'first_name',
          displayName : '@lang('admin_common.first_name')',
          enableSorting : _getInfo('name','sortable'),
          width : _getInfo('name','width'),
          cellClass : _getInfo('name','align'),
        },
        { 
          field : 'last_name',
          displayName : '@lang('admin_common.last_name')',
          enableSorting : _getInfo('name','sortable'),
          width : _getInfo('name','width'),
          cellClass : _getInfo('name','align'),
        },
        
        { 
          field : 'email',
          displayName : '@lang('admin_common.email')',
          cellTooltip: true,
          enableSorting : _getInfo('paid','sortable'),
          width : _getInfo('paid','width'),
          cellClass : _getInfo('paid','align'),
        },
        { 
          field : 'status',
          displayName : '@lang('admin_common.status')',
          enableSorting : _getInfo('status','sortable'),
          width : _getInfo('status','width'),
          cellClass : _getInfo('status','align'),
        },
        
        {  
          field : 'created_date',
          displayName : 'date',
          cellTooltip: true,
          enableSorting : _getInfo('date','sortable'),
          width : _getInfo('date','width'),
          cellClass:_getInfo('date','align'),
        },
        {  
          field : 'Action',
          displayName : 'Action',
          cellTemplate: '<a href="<%row.entity.detail_url%>" class="primary-color">Detail</a> | <a href="javascript:;" ng-click="grid.appScope.updateStatus(row,col,$event,row.entity.statusurl,row.entity.id,\'change_status\')" ng-if="row.entity.register_step==\'1\'"  data-val="2">@lang('admin_common.pending')</a><a href="javascript:;" ng-click="grid.appScope.updateStatus(row,col,$event,row.entity.statusurl)" ng-if="row.entity.register_step==\'2\'" data-val="1">@lang('admin_common.approve')</a>',
          minWidth: 100,
          cellClass:_getInfo('action','align'),
        }
      ];
      
  </script>

  <!--page level css -->
  <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css"/>
  <!-- end of page level css -->
    
@stop

@section('content')
    <div class="content ng-cloak" ng-controller="gridtableCtrl" ng-cloak>
        <div class="header-title">
            <h1 class="title">@lang('admin_customer.pending_user_list')</h1>

        </div>
               
        <!-- Main content -->         
           
        <div class="content-wrap ">
            @if(Session::has('sucBlockg'))
            <div class="alert alert-success alert-dismissable margin5">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <strong>@lang('admin_common.success'):</strong> {{ Session::get('succMsg') }}
            </div>
            @elseif(Session::has('errorMsg'))
            <div class="alert alert-danger alert-dismissable margin5">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>@lang('admin_common.error'):</strong> {{ Session::get('errorMsg') }}
            </div>    
            @endif 

            @include('includes.gridtable')
            
        </div>
            

    </div>
@stop

@section('footer_scripts')

  @include('includes.gridtablejsdeps')
  <script stype="text/javascript" src="{{Config('constants.angular_app_url')}}controller/gridTableCtrl.js"></script>
    
@stop
