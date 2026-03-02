 _getInfo=function(fName,fType){
       var ind = fieldset.findIndex(x=>x.fieldName===fName);
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
       }else return false;
       return false;
    };
    /**** This code used for columns setting of table where field is field name of database filed.*****/
		var columsSetting = [
         {
          field : 'id',
    			displayName : 'Id',
    			cellTemplate : '<span><%grid.appScope.seqNumber(row)+1%></span>',
    			enableSorting : _getInfo('id','sortable'),
          minWidth : _getInfo('id','width'),
    		  cellClass : _getInfo('id','align'),
        },


        { 
          field : 'name',
          displayName : 'Name',
          //cellTemplate:'<a href="'+variantlisturl+'<%row.entity.id%>" class="fulldiv" title="<%row.entity.sku%>"><%row.entity.sku%></a>',
          enableSorting : _getInfo('name','sortable'),
          minWidth : _getInfo('name','width'),
          cellClass : _getInfo('name','align'),
      },{	
          field : 'url',
    			displayName : 'URL',
    			//cellTemplate:'<a href="'+variantlisturl+'<%row.entity.id%>" class="fulldiv" title="<%row.entity.sku%>"><%row.entity.sku%></a>',
          enableSorting : _getInfo('code','sortable'),
    			minWidth : _getInfo('code','width'),
          cellClass : _getInfo('code','align'),
			},
      { 
          field : 'template',
          displayName : 'Template',
          //cellTemplate:'<a href="'+variantlisturl+'<%row.entity.id%>" class="fulldiv" title="<%row.entity.sku%>"><%row.entity.sku%></a>',
          enableSorting : _getInfo('code','sortable'),
          minWidth : _getInfo('code','width'),
          cellClass : _getInfo('code','align'),
      },
      {  
          field : 'meta_title',
          displayName : 'Meta Title',
          //cellTemplate:'<a href="'+variantlisturl+'<%row.entity.id%>" class="fulldiv" title="<%row.entity.sku%>"><%row.entity.sku%></a>',
          enableSorting : _getInfo('code','sortable'),
          minWidth : _getInfo('code','width'),
          cellClass : _getInfo('code','align'),
      },

      { 
          field : 'meta_description',
          displayName : 'Meta Description',
          //cellTemplate:'<a href="'+variantlisturl+'<%row.entity.id%>" class="fulldiv" title="<%row.entity.sku%>"><%row.entity.sku%></a>',
          enableSorting : _getInfo('code','sortable'),
          minWidth : _getInfo('code','width'),
          cellClass : _getInfo('code','align'),
      },

      { 
          field : 'meta_keywords',
          displayName : 'Meta Keywords',
          //cellTemplate:'<a href="'+variantlisturl+'<%row.entity.id%>" class="fulldiv" title="<%row.entity.sku%>"><%row.entity.sku%></a>',
          enableSorting : _getInfo('code','sortable'),
          minWidth : _getInfo('code','width'),
          cellClass : _getInfo('code','align'),
      },
    {  
          field : 'updated_at',
          displayName : 'Last date',
          // cellTemplate: '<a href="'+variantlisturl+'<%row.entity.id%>" class="fulldiv" title="<%row.entity.initial_price%>"><%row.entity.initial_price%> '+currency+'</a>',
          enableSorting : _getInfo('updated_at','sortable'),
          minWidth : _getInfo('updated_at','width'),
          cellClass:_getInfo('updated_at','align'),
      },{  
          field : 'Action',
          displayName : 'Action',
          cellTemplate: '<div><a href="<%row.entity.edit_action%>" class="skyblue"><%row.entity.edit_action_text%><a> | <a href="<%row.entity.detete_action_url%>" onclick="confirm("Are you sour to delete this data?")" class="skyblue"><%row.entity.detete_action_text%><a></div>',
          minWidth: 100,
          cellClass:_getInfo('updated_at','align'),
      }];