;(function(){
	angular.module('smm-app').directive('getInputfieldDir', function() {
		/***** This directive used for get input field value when change******/
		return {
			scope : {
				myControllerFun : '&callbackFun'
			},
			link : inputChangeHandler
		}
		function inputChangeHandler(scope, element, attr) {
			element.on('change', function(e) {
				scope.myControllerFun({
					val : e.target.value
				});
			})
		}
	}).directive('addRowTemplate', function($compile) {
		/***** This directive used for add row template******/
		var templateRow = '<label><%item.fieldName%></label><input class="addRowInput" type="text" name="<%item.fieldName%>" id=="<%item.fieldName%>"  ng-model="addrowData[item.fieldName]" ng-keyup="addRowDataGet($event)">';
		var linker = function(scope, element, attrs) {
			element.addClass('addRowTemplate');
			element.html(templateRow);
			$compile(element.contents())(scope);
		}
		return {
			restrict : "E",
			link : linker,
		};
	}).directive('selectBoxDir', function($rootScope) {
		/***** this code used for select box dir uding url data *******/
		return {
			restrict : 'E',
			link : selectBoxDirLinkFun
		}
		function selectBoxDirLinkFun(scope, element, attrs) {
			console.log($rootScope.optionJsonArr);
		}
	}).directive('dirHeaderPagination', function() {
		/*******This directive used for header pagination dir-header-pagination ********/
		return {
			restict : 'E',
			template : '<select ng-model="viewItemPerPage" ng-options="page for page in gridOptions.paginationPageSizes" ng-change="HeaderPagination.pageSizeChange(viewItemPerPage)"></select> <span class="countPage">Per page</span> <span class="fas fa-chevron-left" id="headerPrevBtn" ng-click="HeaderPagination.previousPage()"></span><input type="number" min="1" max="<%HeaderPagination.getTotalPages() %>" ng-model="gridOptions.paginationCurrentPage" ng-change="HeaderPagination.pageChange()">of <%HeaderPagination.getTotalPages()%><span class="fas fa-chevron-right next" id="headerNextBtn" data="1" ng-click="HeaderPagination.nextPage()"> </span>'
		}
	}).directive('dirHeaderPaginationNo', function() {
		 /****** 
		 * This directive used for header pagination dir-header-pagination,
		 * without input and next and prev
		 * *******/
		return {
			restict : 'E',
			template : '<select ng-model="viewItemPerPage" ng-options="page for page in gridOptions.paginationPageSizes" ng-change="HeaderPagination.pageSizeChange(viewItemPerPage)"></select>Per page of<%displayTotalNumItems%>'
		}
	}).directive('dirHeaderPaginationPrd', function() {
		/*******This directive used for header pagination dir-header-pagination ********/
		return {
			restict : 'E',
			template : '<select ng-model="vm.viewItemPerPage" ng-options="page for page in vm.gridOptions.paginationPageSizes" ng-change="vm.HeaderPagination.pageSizeChange(vm.viewItemPerPage)"></select> <span class="countPage">Per page</span> <span class="fas fa-chevron-left" id="headerPrevBtn" ng-click="vm.HeaderPagination.previousPage()"></span><input type="number" min="1" max="<% vm.HeaderPagination.getTotalPages() %>" class="header-input-page" ng-model="vm.gridOptions.paginationCurrentPage" ng-change="vm.HeaderPagination.pageChange()">of <% vm.HeaderPagination.getTotalPages()%><span class="fas fa-chevron-right next" id="headerNextBtn" data="1" ng-click="vm.HeaderPagination.nextPage()"> </span>'
		}
	})

	//This Directive used to enable date picker in filter section
	//@Name enable-date-picker
	angular.module("smm-app").directive('enableDatePicker', [function () {
		return {
			restrict: 'A',
			link: function (scope, iElement, iAttrs) {
				iElement.datepicker();
				iElement.on('change',function(){
					console.log(">>>>>>");
					console.log(iAttrs);
					console.log(iElement);
					console.log(scope);
				})				
			}
		};
	}]);

	//a directive to 'enter key press' in elements with the "ng-enter" attribute
	angular.module('smm-app').directive('ngEnter', function () {
        return function (scope, element, attrs) {
            element.bind("keydown keypress", function (event) {
                if (event.which === 13) {
                    scope.$apply(function () {
                        scope.$eval(attrs.ngEnter);
                    });
                    event.preventDefault();
                }
            });
        };
    });
        	
})();