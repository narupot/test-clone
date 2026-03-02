 _getInfo= function(fName,fType){
       var ind = fieldset.findIndex(function(x){
          return (x.fieldName!== undefined && x.fieldName===fName);
       });
       if(ind>=0){
            var r =false;
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
          field : 'label_flag',
          displayName : 'Label Flag',
          cellTemplate:'<div  class="fulldiv bg-label-color" ng-if="row.entity.flag_bg_color != \'\'" style="color: <%row.entity.font_color%>; background-color:<%row.entity.flag_bg_color%>"><%row.entity.label_flag%></div>',
          enableSorting : _getInfo('code','sortable'),
          width : _getInfo('code','width'),
          cellClass : _getInfo('code','align'),
       },
       
       /*{ 
          field : 'remind_icon',
          displayName : 'Remind',
          cellTemplate:'<img ng-src="<%row.entity.remind_icon_image%>" ng-if="row.entity.remind_icon_image!= \'\'" width="50" height="80">',
          enableSorting : _getInfo('name','sortable'),
          width : _getInfo('name','width'),
          cellClass : _getInfo('name','align'),
       },*/

       { 
          field : 'remind_icon',
          displayName : 'Remind',
          cellTemplate:'<i class="<%row.entity.remind_icon%>"></i>',
          enableSorting : _getInfo('name','sortable'),
          width : _getInfo('name','width'),
          cellClass : _getInfo('name','align'),
       },

       { 
          field : 'name',
          displayName : 'Set Name',
          cellTooltip : true,
          //cellTemplate:'<a href="'+variantlisturl+'<%row.entity.id%>" class="fulldiv" title="<%row.entity.sku%>"><%row.entity.sku%></a>',
          enableSorting : _getInfo('name','sortable'),
          width : _getInfo('name','width'),
          cellClass : _getInfo('name','align'),
      },
      {  
          field : 'description',
          displayName : 'Description',
          cellTooltip : true,
          //cellTemplate:'<a href="'+variantlisturl+'<%row.entity.id%>" class="fulldiv" title="<%row.entity.sku%>"><%row.entity.sku%></a>',
          enableSorting : _getInfo('code','sortable'),
          width : _getInfo('code','width'),
          cellClass : _getInfo('code','align'),
      },

      { 
          field : 'product_usage',
          displayName : 'Product Usage',
          cellTooltip : true,
          //cellTemplate:'<a href="'+variantlisturl+'<%row.entity.id%>" class="fulldiv" title="<%row.entity.sku%>"><%row.entity.sku%></a>',
          enableSorting : _getInfo('code','sortable'),
          width : _getInfo('code','width'),
          cellClass : _getInfo('code','align'),
      },

     
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
          cellTemplate: '<div><a href="<%row.entity.edit%>" class="" title="<%row.entity.edit_text%>"><%row.entity.edit_text%></a><span ng-if="row.entity.is_default!=\'1\'"> <a href="javascript:void(0)"  title="<%row.entity.delete_text%>" ng-click="grid.appScope.removeSelectedRow(row, row.entity.id, row.entity.delete)"><%row.entity.delete_text%></a></span></div>',
          minWidth: _getInfo('Action','width'),
          cellClass:_getInfo('updated_at','align'),
          enableSorting : false,
      }]; 


