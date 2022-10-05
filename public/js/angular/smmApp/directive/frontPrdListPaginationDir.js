(function(){
    "use strict";
    /*
    *paginationDir Module
    *This module used to hendel all table related custom function like pagination 
    *multiselect dropdown, add row 
    * Description
    */
    angular.module('paginationDir', []).controller('PaginationController', ['$scope', '$attrs', '$parse',
    function($scope, $attrs, $parse) {
        var self = this,
            ngModelCtrl = {
            $setViewValue : angular.noop
        }, // nullModelCtrl
        setNumPages = $attrs.numPages ? $parse($attrs.numPages).assign : angular.noop;
        this.init = function(ngModelCtrl_, config) {
            ngModelCtrl = ngModelCtrl_;
            this.config = config;
            ngModelCtrl.$render = function() {
                self.render();
            };
            if ($attrs.itemsPerPage) {
                $scope.$parent.$watch($parse($attrs.itemsPerPage), function(value) {
                    self.itemsPerPage = parseInt(value, 10);
                    $scope.totalPages = self.calculateTotalPages();
                });
            } else {
                this.itemsPerPage = config.itemsPerPage;
            }
            $scope.$watch('totalItems', function() {
                $scope.totalPages = self.calculateTotalPages();
            });
            $scope.$watch('totalPages', function(value) {
                setNumPages($scope.$parent, value);
                // Readonly variable
                if ($scope.page > value) {
                    $scope.selectPage(value);
                } else {
                    ngModelCtrl.$render();
                }
            });
        };
        this.calculateTotalPages = function() {
            var totalPages = this.itemsPerPage < 1 ? 1 : Math.ceil($scope.totalItems / this.itemsPerPage);
            return Math.max(totalPages || 0, 1);
        };
        this.render = function() {
            $scope.page = parseInt(ngModelCtrl.$viewValue, 10) || 1;
        };
        $scope.selectPage = function(page, evt) {           
            if (evt) {evt.preventDefault();}
            var clickAllowed = !$scope.ngDisabled || !evt;
            if (clickAllowed && $scope.page !== page && page > 0 && page <= $scope.totalPages) {
                if (evt && evt.target) {
                    evt.target.blur();
                }
                ngModelCtrl.$setViewValue(page);
                ngModelCtrl.$render();
                $scope.myCallBack()(page);
                //enable next and prev directionLinks 
                //if(page)
            }
        };
        $scope.getText = function(key) {
            return $scope[key + 'Text'] || self.config[key + 'Text'];
        };
        $scope.noPrevious = function() {
            return $scope.page === 1;
        };
        $scope.noNext = function() {
            return $scope.page === $scope.totalPages;            
        };
    }]).constant('paginationConfig', {
        itemsPerPage : 10,
        boundaryLinks : false,
        directionLinksPrev : false,
        directionLinksNext : false,
        firstText : 'First',
        previousText : '<',
        nextText : '>',
        lastText : 'Last',
        rotate : true
    }).directive('pagination', ['$parse', 'paginationConfig',
    function($parse, paginationConfig) {
        return {
            restrict : 'EA',
            scope : {
                totalItems : '=',
                firstText : '@',
                previousText : '@',
                nextText : '@',
                lastText : '@',
                ngDisabled : '=',
                myCallBack:'&myCallBack',
            },
            require : ['pagination', '?ngModel'],
            controller : 'PaginationController',
            controllerAs : 'pagination',
            template : "<ul class=\"pagination type-one\">\n" + "  <li ng-if=\"directionLinksPrev\" ng-class=\"{disabled: noPrevious()||ngDisabled}\" class=\"pagination-first page-item\"><a class=\"page-link\" href ng-click=\"selectPage(1, $event)\"><i class=\"fas fa-angle-double-left\"></i></a></li>\n" + "  <li ng-if=\"directionLinksPrev\" ng-class=\"{disabled: noPrevious()||ngDisabled}\" class=\"pagination-prev prev page-item\"><a class=\"page-link\" href ng-click=\"selectPage(page - 1, $event)\"><i class=\"fas fa-angle-left\"></i></a></li>\n" + "  <li ng-repeat=\"page in pages track by $index\" ng-class=\"{active: page.active,disabled: ngDisabled&&!page.active}\" class=\"pagination-page page-item\"><a class=\"page-link\" href ng-click=\"selectPage(page.number, $event)\">{{page.text}}</a></li>\n" + "  <li ng-if=\"directionLinksNext\" ng-class=\"{disabled: noNext()||ngDisabled}\" class=\"pagination-next next page-item\"><a class=\"page-link\" href ng-click=\"selectPage(page + 1, $event)\"><i class=\"fas fa-chevron-right\"></i></a></li>\n" + "  <li ng-if=\"directionLinksNext\" ng-class=\"{disabled: noNext()||ngDisabled}\" class=\"pagination-last page-item\"><a class=\"page-link\" href ng-click=\"selectPage(totalPages, $event)\"><i class=\"fas fa-angle-double-right\"></i></a></li>\n" + "</ul>\n",/*"<ul class=\"pagination\">\n" + "  <li ng-if=\"directionLinksPrev\" ng-class=\"{disabled: noPrevious()||ngDisabled}\" class=\"pagination-first page-item\"><a class=\"page-link\" href ng-click=\"selectPage(1, $event)\">{{::getText('first')}}</a></li>\n" + "  <li ng-if=\"directionLinksPrev\" ng-class=\"{disabled: noPrevious()||ngDisabled}\" class=\"pagination-prev prev page-item\"><a class=\"page-link\" href ng-click=\"selectPage(page - 1, $event)\">{{::getText('previous')}}</a></li>\n" + "  <li ng-repeat=\"page in pages track by $index\" ng-class=\"{active: page.active,disabled: ngDisabled&&!page.active}\" class=\"pagination-page page-item\"><a class=\"page-link\" href ng-click=\"selectPage(page.number, $event)\">{{page.text}}</a></li>\n" + "  <li ng-if=\"directionLinksNext\" ng-class=\"{disabled: noNext()||ngDisabled}\" class=\"pagination-next next page-item\"><a class=\"page-link\" href ng-click=\"selectPage(page + 1, $event)\">{{::getText('next')}}</a></li>\n" + "  <li ng-if=\"directionLinksNext\" ng-class=\"{disabled: noNext()||ngDisabled}\" class=\"pagination-last page-item\"><a class=\"page-link\" href ng-click=\"selectPage(totalPages, $event)\">{{::getText('last')}}</a></li>\n" + "</ul>\n",*/
            replace : true,
            link : function(scope, element, attrs, ctrls) {
                var paginationCtrl = ctrls[0],
                    ngModelCtrl = ctrls[1];

                if (!ngModelCtrl) {
                    return;
                    // do nothing if no ng-model
                }
                
                // Setup configuration parameters
                var maxSize = angular.isDefined(attrs.maxSize) ? scope.$parent.$eval(attrs.maxSize) : paginationConfig.maxSize,
                    rotate = angular.isDefined(attrs.rotate) ? scope.$parent.$eval(attrs.rotate) : paginationConfig.rotate;
                scope.boundaryLinks = angular.isDefined(attrs.boundaryLinks) ? scope.$parent.$eval(attrs.boundaryLinks) : paginationConfig.boundaryLinks;
                scope.directionLinksPrev = angular.isDefined(attrs.directionLinksPrev) ? scope.$parent.$eval(attrs.directionLinksPrev) : paginationConfig.directionLinksPrev;
                scope.directionLinksNext = angular.isDefined(attrs.directionLinksNext) ? scope.$parent.$eval(attrs.directionLinksNext) : paginationConfig.directionLinksNext;
                
                paginationCtrl.init(ngModelCtrl, paginationConfig);

                if (attrs.maxSize) {
                    scope.$parent.$watch($parse(attrs.maxSize), function(value) {
                        maxSize = parseInt(value, 10);
                        paginationCtrl.render();
                    });
                }

                // Create page object used in template
                function makePage(number, text, isActive) {
                    return {
                        number : number,
                        text : text,
                        active : isActive
                    };
                }

                function getPages(currentPage, totalPages) {
                    var pages = [];
                   
                    // Default page limits
                    var startPage = 1,
                        endPage =
                        totalPages;
                    var isMaxSized = (angular.isDefined(maxSize) && maxSize < totalPages );
                    
                    // recompute if maxSize
                    if (isMaxSized) { 
                        if (rotate) {
                            // Current page is displayed in the middle of the visible ones
                            startPage = Math.max(currentPage - Math.floor(maxSize / 2), 1);
                            endPage = startPage + maxSize - 1;

                            // Adjust if limit is exceeded
                            if (endPage > totalPages) {
                                endPage = totalPages;
                                startPage = endPage - maxSize + 1;
                            }
                        } else {
                            // Visible pages are paginated with maxSize
                            startPage = ((Math.ceil(currentPage / maxSize) - 1) * maxSize) + 1;
                           
                            // Adjust last page if limit is exceeded
                            endPage = Math.min(startPage + maxSize - 1, totalPages);
                            
                        }
                    }

                    // Add page number links
                    for (var number = startPage; number <= endPage; number++) {
                        var page = makePage(number, number, number === currentPage);
                        pages.push(page);
                    }

                    // Add links to move between page sets
                    if (isMaxSized && !rotate) { 
                        if (startPage > 1) {
                            var previousPageSet = makePage(startPage - 1, '...', false);
                            pages.unshift(previousPageSet);
                        }

                        if (endPage < totalPages) {
                            var nextPageSet = makePage(endPage + 1, '...', false);
                            pages.push(nextPageSet);
                        }
                    }

                    //enable disable directionLinksNext/directionLinksPrev
                    if(totalPages ){
                        // console.log("currentPage"); console.log("currentPage \t" +  currentPage + " \t totalPages \t" + totalPages);
                        //In current page is one
                        if(currentPage==1 && currentPage<totalPages){
                            // console.log('in first')
                            scope.directionLinksNext = true;
                            scope.directionLinksPrev = false;
                        }else if(currentPage!==1 && currentPage<totalPages){
                            scope.directionLinksNext = true;
                            scope.directionLinksPrev = true;
                            // console.log('in second');
                        }else if(currentPage!==1 && currentPage === totalPages){
                           scope.directionLinksNext = false;
                           scope.directionLinksPrev = true; 
                           // console.log('in last')
                        }else if(currentPage===1 && totalPages === 1){
                            scope.directionLinksNext = false;
                            scope.directionLinksPrev = false;
                        }
                        
                    }
                   
                    return pages;
                }

                var originalRender = paginationCtrl.render;
                paginationCtrl.render = function() {
                    originalRender();
                    if (scope.page > 0 && scope.page <= scope.totalPages) {
                        scope.pages = getPages(scope.page, scope.totalPages);
                    }
                };
            }
        };
    }]);
})(window.angular);