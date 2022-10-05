 _getInfo= function(fName,fType){
       var ind = fieldset.findIndex(function(x){
        return(x.fieldName!== undefined && x.fieldName===fName);
       });
       if(ind>=0){
            var r =false;
            if(fType==='sortable'){
              r= (fieldset[ind].sortable!==undefined)? fieldset[ind].sortable:false;
            }else if(fType==='width'){
              r= (fieldset[ind].width!==undefined)? parseInt(fieldset[ind].width):100;
            }else if(fType==='align'){
               r= (fieldset[ind].align!==undefined)? 'text-'+fieldset[ind].align:'text-left';
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
    			displayName : 'Id',
    			cellTemplate : '<span><%grid.appScope.seqNumber(row)+1%></span>',
    			enableSorting : _getInfo('sno','sortable'),
          width : _getInfo('sno','width'),
    		  cellClass : _getInfo('sno','align'),
        },
        {
          field : 'product_thumb',
          displayName: 'Image',
          cellTemplate : '<span><img src="<%row.entity.product_thumb%>"></span>',
          width : _getInfo('product_thumb','width'),
          cellClass : _getInfo('product_thumb','align'),
          enableSorting : _getInfo('product_thumb','sortable')
        },
        { 
          field : 'name',
          displayName : 'Name',
          cellTooltip : true,
          cellTemplate:'<span ng-if="row.entity.product_type!=\'Simple\'"><a href="<%row.entity.varient_url%>" class="fulldiv" title="<%row.entity.name%>"><%row.entity.name%></a></span><span ng-if="row.entity.product_type==\'Simple\'"><a href="<%row.entity.edit%>" class="fulldiv" title="<%row.entity.name%>"><%row.entity.name%></a></span>',
          enableSorting : _getInfo('name','sortable'),
          width : _getInfo('name','width'),
          cellClass : _getInfo('name','align'),
      },
      { 
          field : 'sku',
          displayName : 'SKU',             
          cellTooltip: true,
          //cellTemplate:'<a href="'+variantlisturl+'<%row.entity.id%>" class="fulldiv" title="<%row.entity.sku%>"><%row.entity.sku%></a>',
          enableSorting : _getInfo('sku','sortable'),
          width : _getInfo('sku','width'),
          cellClass : _getInfo('sku','align'),
      },
      {  
          field : 'initial_price',
          displayName : 'Base Price',
          //cellTemplate:'<a href="'+variantlisturl+'<%row.entity.id%>" class="fulldiv" title="<%row.entity.sku%>"><%row.entity.sku%></a>',
          enableSorting : _getInfo('amount','sortable'),
          width : _getInfo('amount','width'),
          cellClass : _getInfo('amount','align'),
      },
      {   
          field : 'special_price',
          displayName : 'Special Price',
          enableSorting : _getInfo('amount','sortable'),
          width : _getInfo('amount','width'),
          cellClass : _getInfo('amount','align')
      },
      { 
          field : 'quantity',
          displayName : 'Quantity',
          //cellTemplate:'<a href="'+variantlisturl+'<%row.entity.id%>" class="fulldiv" title="<%row.entity.sku%>"><%row.entity.sku%></a>',
          enableSorting : _getInfo('quantity','sortable'),
          width : _getInfo('quantity','width'),
          cellClass : _getInfo('quantity','align'),
      },
      { 
          field : 'product_type',
          displayName : 'Type',
          //cellTemplate:'<a href="'+variantlisturl+'<%row.entity.id%>" class="fulldiv" title="<%row.entity.sku%>"><%row.entity.sku%></a>',
          enableSorting : _getInfo('type','sortable'),
          width : _getInfo('type','width'),
          cellClass : _getInfo('type','align'),
      },

      { 
          field : 'status',
          displayName : 'Status',
          cellTemplate:'<span class="inactive-btn btn-status" ng-click="grid.appScope.userActionHandler.status($event,row.entity.status, row.entity.id, rowRenderIndex)" id="<%row.entity.id%>" ng-if="row.entity.status == \'Inactive\'" data-val="1">Inactive</span><span ng-click="grid.appScope.userActionHandler.status($event,row.entity.status,row.entity.id, rowRenderIndex)" class="active-btn btn-status" id="<%row.entity.id%>" ng-if="row.entity.status == \'Active\'" data-val="0">Active</span>',
          enableSorting : _getInfo('status','sortable'),
          width : _getInfo('status','width'),
          cellClass : _getInfo('status','align'),
      },
      {  
          field : 'updated_at',
          displayName : 'Updated Date',
          cellTooltip: true,
          // cellTemplate: '<a href="'+variantlisturl+'<%row.entity.id%>" class="fulldiv" title="<%row.entity.initial_price%>"><%row.entity.initial_price%> '+currency+'</a>',
          enableSorting : _getInfo('date','sortable'),
          width : _getInfo('date','width'),
          cellClass:_getInfo('date','align'),
      },
      {  
          field : 'created_at',
          displayName : 'Created Date',
          cellTooltip: true,
          // cellTemplate: '<a href="'+variantlisturl+'<%row.entity.id%>" class="fulldiv" title="<%row.entity.initial_price%>"><%row.entity.initial_price%> '+currency+'</a>',
          enableSorting : _getInfo('date','sortable'),
          width : _getInfo('date','width'),
          cellClass:_getInfo('date','align'),
      },
      {  
          field : 'Action',
          displayName : 'Action',
          cellTemplate: '<div><a href="<%row.entity.front_url%>" target="_self" class="" >'+general_actions.view+'</a> | <a href="<%row.entity.edit%>">'+general_actions.edit+'</a> | <a href="javascript:void(0)" ng-click="grid.appScope.removeSelectedRow(row, row.entity.id, row.entity.delete)" title="<%row.entity.delete_text%>">'+general_actions.delete+'</a></div>',
          minWidth: _getInfo('Action','width'),
          cellClass:_getInfo('updated_at','align'),
          enableSorting : false
      }];      