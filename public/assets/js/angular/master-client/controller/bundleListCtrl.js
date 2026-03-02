 _getInfo=(fName,fType)=>{
       let ind = fieldset.findIndex(x=>x.fieldName===fName);
       if(ind>=0){
            let r =false;
            if(fType==='sortable'){
              r= (fieldset[ind].sortable!==undefined)? fieldset[ind].sortable:false;
            }else if(fType==='width'){
              r= (fieldset[ind].width!==undefined)? fieldset[ind].width:100;
            }else if(fType==='align'){
               r= (fieldset[ind].align!==undefined)? 'text-'+fieldset[ind].align:'text-left';
            }
            return r;
       }else return false;
       return false;
    };
    /**** This code used for columns setting of table where field is field name of database filed.*****/
		var columsSetting = [
         {
          field : 'id',
    			displayName : 'Id',
    			cellTemplate : '<span><%grid.appScope.seqNumber(row)+1%></span>',
    			enableSorting : _getInfo('sno','sortable'),
          minWidth : _getInfo('sno','width'),
    		  cellClass : _getInfo('sno','align'),
        },

        { 
          field : 'sku',
          displayName : 'SKU',
          cellTemplate: '<a href="javascript:;" class="fulldiv"><span><image ng-src="<%row.entity.thumbnail_image%>"><span class="productsku"><%row.entity.sku%></span></span>',
          enableSorting : _getInfo('sku','sortable'),
          minWidth : _getInfo('sku','width'),
          cellClass : _getInfo('sku','align'),
        },

        { 
          field : 'name',
          displayName : 'Name',
          //cellTemplate:'<a href="'+variantlisturl+'<%row.entity.id%>" class="fulldiv" title="<%row.entity.sku%>"><%row.entity.sku%></a>',
          enableSorting : _getInfo('name','sortable'),
          minWidth : _getInfo('name','width'),
          cellClass : _getInfo('name','align'),
        },

        { 
          field : 'product_type',
          displayName : 'Type',
          //cellTemplate:'<a href="'+variantlisturl+'<%row.entity.id%>" class="fulldiv" title="<%row.entity.sku%>"><%row.entity.sku%></a>',
          enableSorting : _getInfo('product_type','sortable'),
          minWidth : _getInfo('product_type','width'),
          cellClass : _getInfo('product_type','align'),
        },


        {  
          field : 'initial_price',
          displayName : 'Price(Initial)',
          //cellTemplate:'<a href="'+variantlisturl+'<%row.entity.id%>" class="fulldiv" title="<%row.entity.sku%>"><%row.entity.sku%></a>',
          enableSorting : _getInfo('amount','sortable'),
          minWidth : _getInfo('amount','width'),
          cellClass : _getInfo('amount','align'),
        },

     

        { 
          field : 'status',
          displayName : 'Status',
          //cellTemplate:'<a href="'+variantlisturl+'<%row.entity.id%>" class="fulldiv" title="<%row.entity.sku%>"><%row.entity.sku%></a>',
          enableSorting : _getInfo('code','sortable'),
          minWidth : _getInfo('code','width'),
          cellClass : _getInfo('code','align'),
        },
        {  
          field : 'last_updated',
          displayName : ' Date',
          // cellTemplate: '<a href="'+variantlisturl+'<%row.entity.id%>" class="fulldiv" title="<%row.entity.initial_price%>"><%row.entity.initial_price%> '+currency+'</a>',
          enableSorting : _getInfo('date','sortable'),
          minWidth : _getInfo('date','width'),
          cellClass:_getInfo('date','align'),
        },{  
          field : 'Action',
          displayName : 'Action',
          cellTemplate: '<div><a href="<%row.entity.view%>" target="_blank" class="" >'+general_actions.view+'</a> | <a href="<%row.entity.edit%>" class="" >'+general_actions.edit+'</a> | <a href="<%row.entity.delete%>" ng-click="grid.appScope.updateStatus(\'delete\'); $event.preventDefault();" title="<%row.entity.delete_text%>">'+general_actions.delete+'</a></div>',
          minWidth: 100,
          cellClass:_getInfo('updated_at','align'),
        }];