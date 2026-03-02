@extends('layouts/admin/default') 

@section('title')
     @lang('admin_shipping.shipping_rate_table') 
@stop

@section('header_styles')
  <link rel="stylesheet" type="text/css" href="{{Config('constants.css_url') }}ui-grid-unstable.css"> 
  <link type="text/css" rel="stylesheet"  href="{{ Config('constants.css_url') }}sweetalert.min.css" >

  <script>  
      var fieldSetJson  = {!! $fielddata !!};
      var removeRowUrl = "{{action('Admin\ShippingProfile\ShippingRateTableController@deleteProfile')}}";
      var fieldset = fieldSetJson.fieldSets;
      var pagelimit = "{{action('JsonController@pageLimit')}}";
      var showSearchSection = true;
      var showHeadrePagination = true;
      var getAllDataFromServerOnce = true;
      var dataJsonUrl = "{{ action('Admin\ShippingProfile\ShippingRateTableController@listShippingProfileData') }}";
      var lang = ["@lang('admin_shipping.name')","@lang('admin_shipping.logo')","@lang('admin_shipping.status')","@lang('admin_shipping.created_at')","@lang('admin_shipping.action')","@lang('admin_shipping.edit')","@lang('admin_shipping.remove')","@lang('admin_shipping.sno')","@lang('admin_shipping.active')","@lang('admin_shipping.deactive')"];
      var tableLoaderImgUrl = "{{ Config::get('constants.loader_url')}}ajax-loader.gif";
      //pagination config 
      var pagination = {!! getPagination() !!};
      var per_page_limt = {{ getPagination('limit') }};

      //for table tab active
      var currentActiveTab = "all";

      //find index cahnge using method
      function findIndexMethod(list, matchEle){
        var index = -1;
        for (var i = 0; i < list.length; ++i) {
          if (list[i].fieldName!== undefined && list[i].fieldName===matchEle) {
              index = i;
              break;
          }
        }

        return index;  
      };
      //Listen on table columns setting
     _getInfo= function(fName,fType){
       var ind = findIndexMethod(fieldset, fName);
       if(ind>=0){
            var r =false;
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
          displayName : '@lang('admin_shipping.sno')',
          cellTemplate : '<span><%grid.appScope.seqNumber(row)+1%></span>',
          enableSorting : _getInfo('sno','sortable'),
          width : _getInfo('sno','width'),
          cellClass : _getInfo('sno','align'),
        },        
        { 
          field : 'name',
          displayName : '@lang('admin_shipping.name')',
          cellTooltip: true,
          enableSorting : _getInfo('name','sortable'),
          width : _getInfo('name','width'),
          cellClass : _getInfo('name','align'),
        },
        { 
          field : 'logo',
          displayName : '@lang('admin_shipping.logo')',
          cellTemplate : '<img src="<%row.entity.logo%>" />',
          enableSorting : _getInfo('logo','sortable'),
          width : _getInfo('name','width'),
          cellClass : _getInfo('name','align'),
        },        
        { 
          field : 'status',
          displayName : '@lang('admin_shipping.status')',
          enableSorting : _getInfo('status','sortable'),
          width : _getInfo('status','width'),
          cellClass : _getInfo('status','align'),
        },        
        {  
          field : 'created_at',
          displayName : 'date',
          cellTooltip: true,
          enableSorting : _getInfo('date','sortable'),
          width : _getInfo('date','width'),
          cellClass:_getInfo('date','align'),
        },
        {  
          field : 'Action',
          displayName : 'Action',
          cellTemplate: '<a href="<%row.entity.edit_url%>" class="primary-color">Edit</a> | <a href="javascript://" class="delete_profile" ng-click="grid.appScope.removeSelectedRow(row, row.entity.delete_id)"> Delete </a>',
          minWidth: _getInfo('Action','width'),
          cellClass:_getInfo('action','align'),
          enableSorting : false,
        }
      ];
  </script>

  <!--page level css -->
  <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css"/>
  <!-- end of page level css -->
    
@stop

@section('content')
    <div class="content ng-cloak" ng-controller="gridtableCtrl">
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
        <div class="header-title">
            <h1 class="title">@lang('admin_shipping.shipping_rate_table')</h1>
            <div class="float-right">
                <a href="{{action('Admin\ShippingProfile\ShippingRateTableController@addNewShippingRateTableProfile')}}" class="btn btn-create" >@lang('admin_shipping.add_shipping_table_rate_method')</a> 
            </div>
        </div>
        <!-- Main content -->         
        <div class="content-wrap clearfix">
          <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config','shipping-table-rates','list')!!}
                </ul>
            </div>
            @include('includes.gridtable')
            <div class="table table-content">
                        @if(!empty($session_data))
                            <div>
                                <h3 class="title">Import CSV Response</h3>
                                @foreach($session_data as $res_key => $row)

                                <div>
                                    <h4>{{$res_key}}</h4>
                                    <div>
                                        @if(!empty($row))
                                            @foreach($row as $k => $data)
                                                <div style="border-bottom: 1px solid gray;">{{$k+1}}.<?php echo implode(',', $data);

                                                ?></div>
                                            @endforeach
                                        @else
                                        <div>No Record(s) found.</div>
                                        @endif
                                    </div>

                                </div>
                                @endforeach
                            </div>
                        @endif
            </div>
        </div>
        
            

    </div>
@stop

@section('footer_scripts')
  <script src="{{ Config('constants.js_url') }}sweetalert.min.js" type="text/javascript"></script>
  @include('includes.gridtablejsdeps')
  <script stype="text/javascript" src="{{Config('constants.js_url')}}angular/sabinaApp/controller/gridTableCtrl.js"></script>

  <script>
        $(function(){

            $('.delete_profile').on('click', function(){
                
                var attribute = $(this).attr('id');
                
                var related_url = "{{action('Admin\ShippingProfile\ShippingRateTableController@deleteProfile')}}";
                var token = window.Laravel.csrfToken;

               $.ajax({
                url : related_url,
                DataType : 'JSON',
                data : {'_token':token,'attribute':attribute},
                method : 'POST',
                success : function(response){
                    var obj = $.parseJSON(response);

                    if(obj.success===true){
                       window.location.reload();
                    }else{
                        alert("Opps! Somthing went wrong.");
                    }   
                }
            });

            });
        });
    </script>
    
@stop
