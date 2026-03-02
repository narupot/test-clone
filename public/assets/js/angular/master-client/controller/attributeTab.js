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
    			enableSorting : _getInfo('id','sortable'),
          width : _getInfo('id','width'),
    		  cellClass : _getInfo('id','align'),
        },


        { 
          field : 'name',
          displayName : 'Name',
          cellTooltip : true,
          //cellTemplate:'<a href="'+variantlisturl+'<%row.entity.id%>" class="fulldiv" title="<%row.entity.sku%>"><%row.entity.sku%></a>',
          enableSorting : _getInfo('name','sortable'),
          width : _getInfo('name','width'),
          cellClass : _getInfo('name','align'),
      },{	
          field : 'attribute_code',
    			displayName : 'Code',
          cellTooltip : true,
    			//cellTemplate:'<a href="'+variantlisturl+'<%row.entity.id%>" class="fulldiv" title="<%row.entity.sku%>"><%row.entity.sku%></a>',
          enableSorting : _getInfo('attribute_code','sortable'),
    			width : _getInfo('attribute_code','width'),
          cellClass : _getInfo('attribute_code','align'),
			},
      {  
          field : 'input_type',
          displayName : 'Attribute Type',
          //cellTemplate:'<a href="'+variantlisturl+'<%row.entity.id%>" class="fulldiv" title="<%row.entity.sku%>"><%row.entity.sku%></a>',
          enableSorting : _getInfo('attribute_type','sortable'),
          width : _getInfo('attribute_type','width'),
          cellClass : _getInfo('attribute_type','align'),
      },

      { 
          field : 'input_value',
          displayName : 'Input Value',
          cellTooltip : true,
          //cellTemplate:'<a href="'+variantlisturl+'<%row.entity.id%>" class="fulldiv" title="<%row.entity.sku%>"><%row.entity.sku%></a>',
          enableSorting : _getInfo('attribute_type','sortable'),
          width : _getInfo('attribute_type','width'),
          cellClass : _getInfo('attribute_type','align'),
      },

      // { 
      //     field : 'is_varient',
      //     displayName : 'Variant',
      //     //cellTemplate:'<a href="'+variantlisturl+'<%row.entity.id%>" class="fulldiv" title="<%row.entity.sku%>"><%row.entity.sku%></a>',
      //     enableSorting : _getInfo('code','sortable'),
      //     width : _getInfo('code','width'),
      //     cellClass : _getInfo('code','align'),
      // },  
    {  
          field : 'updated_at',
          displayName : 'Last date',
          cellTooltip : true,
          // cellTemplate: '<a href="'+variantlisturl+'<%row.entity.id%>" class="fulldiv" title="<%row.entity.initial_price%>"><%row.entity.initial_price%> '+currency+'</a>',
          enableSorting : _getInfo('updated_at','sortable'),
          width : _getInfo('updated_at','width'),
          cellClass:_getInfo('updated_at','align'),
      },{  
          field : 'Action',
          displayName : 'Action',
          // cellTemplate: '<div><a href="<%row.entity.edit%>" class="" title="<%row.entity.edit_text%>"><%row.entity.edit_text%></a> | <a href="<%row.entity.delete%>" ng-click="grid.appScope.updateStatus(\'delete\'); $event.preventDefault();" title="<%row.entity.delete_text%>"><%row.entity.delete_text%></a></div>',
          cellTemplate: '<div><a href="<%row.entity.edit%>" class="" title="<%row.entity.edit_text%>"><%row.entity.edit_text%></a> <span ng-if="row.entity.productsCount<1">| <a href="javascript:void(0)" ng-click="grid.appScope.removeSelectedRow(row, row.entity.id, row.entity.delete)" title="<%row.entity.delete_text%>"><%row.entity.delete_text%></a><span></div>',
          minWidth: _getInfo('Action','width'),
          cellClass:_getInfo('updated_at','align'),
      }];