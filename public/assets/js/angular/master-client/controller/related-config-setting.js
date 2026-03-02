(function(){
	"ues strict";

    angular.module("sabinaAdminApp", ['jsonseivice'], function($interpolateProvider) {
      $interpolateProvider.startSymbol("<%");
      $interpolateProvider.endSymbol("%>")
    }).config(['$httpProvider', function ($httpProvider) {
        $httpProvider.interceptors.push('httpLoaderInterceptor');
    }]).controller('relatedSettingCtrl', ['$scope', 'salesfactoryData', 'commanFun',relatedSettingCtrl]);

    function relatedSettingCtrl($scope, salesfactoryData, commanFun){
        //EDIT PAGE 
        var edit = false;
        if(typeof RELATED_SETTING!=="undefined" && Object.keys(RELATED_SETTING).length){
            edit = true;
        }

    	//model variable
    	$scope.related ={
    		enable : (edit && RELATED_SETTING.status!== undefined) ? RELATED_SETTING.status.toString() : "1",
    		added_product : (edit && RELATED_SETTING.added_product!== undefined) ? RELATED_SETTING.added_product.toString() : "1",
    		sort : (edit && RELATED_SETTING.sort_by!== undefined && parseInt(RELATED_SETTING.sort_by)>0) ? RELATED_SETTING.sort_by.toString() : null,
            cat : (edit && RELATED_SETTING.cat_cond!== undefined && parseInt(RELATED_SETTING.cat_cond)>0) ? RELATED_SETTING.cat_cond.toString() : null,
            price :{
                selection : (edit && RELATED_SETTING.price_cond!== undefined && parseInt(RELATED_SETTING.price_cond)>0) ? RELATED_SETTING.price_cond.toString() : null,
                from : (edit && RELATED_SETTING.price_from!== undefined && parseInt(RELATED_SETTING.price_from)>=1) ? RELATED_SETTING.price_from : "",
                to : (edit && RELATED_SETTING.price_to!== undefined && parseInt(RELATED_SETTING.price_to)>=1) ? RELATED_SETTING.price_to : "",
                less_more : (edit && RELATED_SETTING.less_more!== undefined && parseInt(RELATED_SETTING.less_more)>=1) ? RELATED_SETTING.less_more : "",
            },              		
    	}; 

        //data variable 
        $scope.related_data = {
            price : {
                from_to : (edit && RELATED_SETTING.price_from!== undefined && parseInt(RELATED_SETTING.price_from)>=1) ? true : false,
                single : (edit && RELATED_SETTING.less_more!== undefined && parseInt(RELATED_SETTING.less_more)>=1) ? true : false,
                selection : [
                    {"id" : "1", "label" : "similar price range from..% to ..%", "identity" : "1_for_price_range"},
                    {"id" : "2", "label" : "Lass than", "identity" : "2_for_less_price"},
                    {"id" : "3", "label" : "More than", "identity" : "3_for_more_price"},                   
                ],
            },
            sort : [
                {"id" : "1", "label" : "Created Date: high to low", "identity" : "1_for_high_to_low"},
                {"id" : "2", "label" : "Created Date: low to high", "identity" : "2_for_low_to_high"},
                {"id" : "3", "label" : "Updated Date: high to low", "identity" : "3_for_high_to_low"},
                {"id" : "4", "label" : "Updated Date: low to high", "identity" : "4_for_low_to_high"},
                {"id" : "5", "label" : "Price Date: high to low", "identity" : "5_for_high_to_low"},
                {"id" : "6", "label" : "Price Date: low to high", "identity" : "6_for_low_to_high"},
                {"id" : "7", "label" : "Name : ascending", "identity" : "7_ascending"},
                {"id" : "8", "label" : "Name: descending", "identity" : "8_descending"},
                {"id" : "9", "label" : "Random", "identity" : "9_Random"}
            ],
            categ : [
                {"id" : "1", "label" : "Same category level only", "identity" : "1_for_high_to_low"},
                {"id" : "2", "label" : "Sub category level only", "identity" : "2_for_low_to_high"},
                {"id" : "3", "label" : "Same + Sub category", "identity" : "3_for_high_to_low"},
            ], 

            loading : {
                disableBtn : false,
                btnloaderpath : tableLoaderImgUrl,
                save_and_continue : false,
            },        
        };

        //instance 
        var $rlt = $scope.related;
        var $rtd = $scope.related_data;
        var $cmd = commanFun;

        //brodcast event for loader
        $scope.$on("httpLoaderStart", function(){
            $rtd.loading.disableBtn = true;
            $rtd.loading.save_and_continue = true;
        });

        $scope.$on("httpLoaderEnd", function(){
            $rtd.loading.disableBtn = false;
            $rtd.loading.save_and_continue = false;
        });

        //Listen to price condition change
        $scope.priceCondition = function(){
            if($rlt.price.selection == "" || $rlt.price.selection == null){
                $rlt.price["from"] = "";
                $rlt.price["to"] = "";
                $rlt.price["less_more"] = "";
                $rtd.price.from_to = false;
                $rtd.price.single = false;
            }else{
                if($rlt.price.selection == "1"){
                    $rtd.price.from_to = true;
                    $rtd.price.single = false;
                    $rlt.price["less_more"] = "";
                }else if($rlt.price.selection == "2" || $rlt.price.selection == "3"){
                    $rtd.price.from_to = false;
                    $rtd.price.single = true;
                    $rlt.price["from"] = "";
                    $rlt.price["to"] = "";
                }
            }           
        };

        $scope.saveSetting = function($event, relatedConfigForm, actionBtn){            
            //check required field 
            var error = $cmd.errorMessageHandler(relatedConfigForm, formFieldName, errorMsg);
            
            if(error!= ""){
               swal({title : "Opps..",html : error,type : "error"});
               return;
            }           

            var std = angular.copy($scope.related);
            //in case of edit
            if(edit===true){
                std['action'] = "update" 
                std['id'] = RELATED_SETTING.id;
            }else
              std['action'] = actionBtn;

            salesfactoryData.getData(SAVE_SETTING_URL, "POST", std)
            .then(function(resp){
                if(typeof resp!== "undefined" && resp.data!==undefined 
                    && resp.data.status!== undefined && resp.data.status === "success"){
                    $cmd.errorHandler(resp.data.status,resp.data.mesg)
                    .then(function(done){window.location.reload(true);},
                     function(fail) {window.location.reload(true);});
                }else {
                   swal("Opps...!" , "something went wrong..!\n please try again", "error");
                }
            }, function(err){
                swal("Opps...!" , "something went wrong..!\n please try again", "error");
            });
        };
    };

})();
