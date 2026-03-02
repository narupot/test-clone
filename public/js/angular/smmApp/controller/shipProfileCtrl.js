(function(){
	/*****
	*This controller used for shipping profile 
	****/
	"used strict";
	function shipProfileCtrl($scope,salesfactoryData){
		$scope.countryListData = countryListData;
		$scope.selectedCountry = ( typeof selectedCountry !== 'undefined' && selectedCountry.length > 0) ? selectedCountry : [];
		$scope.provinceData = ( typeof provinceData !== 'undefined' && provinceData.length > 0) ? provinceData : [];
		$scope.countrySelected = [];

		var myArr = [];
		if ($scope.selectedCountry.length >= 1) {
			selectedCountry.map(function(val, key) {
				myArr.push(val.id);
			})
		}
		$scope.countryArr = myArr;
		$scope.selectedProvince=[];
		if ($scope.provinceData.length >= 1) {
			provinceData.map(function(val, key) {
				if (selProvinceInd.indexOf(val.id) != -1) {
					$scope.selectedProvince.push(val);
				}
			})
		}

		/****** This function used for add and remove one by one ******/
		$scope.moveItem = function(item, from, to, tempStr) {
			var countryId = [];
			var itemLen = item.length;

			if (itemLen > 1) {
				item.map(function(val, key) {
					var idx = from.indexOf(val);
					if (idx != -1) {
						from.splice(idx, 1);
						to.push(val);
						countryId.push(val.id);
					}
				})
				if (tempStr == 'addItems') {
					handlerItemRemoveAdd(to);
				} else if (tempStr == 'removeItems') {
					handlerItemRemoveAdd(from);
				}
			} else {
				var idx = from.indexOf(item[0]);
				if (idx != -1) {
					from.splice(idx, 1);
					to.push(item[0]);
					countryId.push(item[0].id);
				}

				if (tempStr == 'addItems') {
					handlerItemRemoveAdd(to);
				} else if (tempStr == 'removeItems') {
					handlerItemRemoveAdd(from);
				}
			}
		};
		/**** This function used to add all and remove all country on click arrow****/
		$scope.moveAll = function(from, to, strAction) {
			var countryId = [];
			angular.forEach(from, function(item) {
				to.push(item);
				countryId.push(item.id);
			});
			from.length = 0;
			switch(strAction) {
			case 'addAlList' :
				var tempCtn = {
					"countryId" : JSON.stringify(countryId),
				}
				$scope.countryArr = countryId;
				salesfactoryData.getData(getShippingState, 'GET', tempCtn).then(function(resp) {
					$scope.provinceData = resp.data;
				},(error)=>{
					//
				})
				break;
			case 'removeAllList' :
				var tempCtn = {
					"countryId" : ''
				};
				$scope.countryArr = '';
				salesfactoryData.getData(getShippingState, 'GET', tempCtn).then(function(resp) {
					$scope.provinceData = resp.data;
				},(error)=>{
					//sdf
				})
				break;
			}
		};
		/*****Custom function to used to handel movelist*****/
		function handlerItemRemoveAdd(ctnId) {
			var tempArr = [];
			ctnId.map(function(val, key) {
				tempArr.push(val.id);
			})
			var ctnName = {
				"countryId" : JSON.stringify(tempArr)
			}
			$scope.countryArr = tempArr;
			salesfactoryData.getData(getShippingState, 'GET', ctnName).then(function(resp) {
				$scope.provinceData = resp.data;
			},(error)=>{
				//dfsdf
			});
		}
	};
	angular.module("smm-app").controller('shipProfileCtrl', shipProfileCtrl);
}).call(this);