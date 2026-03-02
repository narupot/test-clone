(function(){
	"use strict";
	/*
	*tableDepDir Module
	*This module used to hendel all table related custom function like pagination 
	*multiselect dropdown, add row 
	* Description
	*/
	angular.module('tableDepDir', []).controller('PaginationController', ['$scope', '$attrs', '$parse',
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
		directionLinks : false,
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
			template : "<ul class=\"pagination\">\n" + "  <li ng-if=\"::boundaryLinks\" ng-class=\"{disabled: noPrevious()||ngDisabled}\" class=\"pagination-first\"><a href ng-click=\"selectPage(1, $event)\">{{::getText('first')}}</a></li>\n" + "  <li ng-if=\"directionLinksPrev\" ng-class=\"{disabled: noPrevious()||ngDisabled}\" class=\"pagination-prev prev\"><a href ng-click=\"selectPage(page - 1, $event)\">{{::getText('previous')}}</a></li>\n" + "  <li ng-repeat=\"page in pages track by $index\" ng-class=\"{active: page.active,disabled: ngDisabled&&!page.active}\" class=\"pagination-page\"><a href ng-click=\"selectPage(page.number, $event)\">{{page.text}}</a></li>\n" + "  <li ng-if=\"directionLinksNext\" ng-class=\"{disabled: noNext()||ngDisabled}\" class=\"pagination-next next\"><a href ng-click=\"selectPage(page + 1, $event)\">{{::getText('next')}}</a></li>\n" + "  <li ng-if=\"::boundaryLinks\" ng-class=\"{disabled: noNext()||ngDisabled}\" class=\"pagination-last\"><a href ng-click=\"selectPage(totalPages, $event)\">{{::getText('last')}}</a></li>\n" + "</ul>\n",
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
				
				scope.directionLinks = angular.isDefined(attrs.directionLinks) ? scope.$parent.$eval(attrs.directionLinks) : paginationConfig.directionLinks;
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
                        //In current page is one
                        if(currentPage==1 && currentPage<totalPages){
                            scope.directionLinksNext = true;
                            scope.directionLinksPrev = false;
                        }else if(currentPage!==1 && currentPage<totalPages){
                            scope.directionLinksNext = true;
                            scope.directionLinksPrev = true;
                        }else if(currentPage!==1 && currentPage === totalPages){
                           scope.directionLinksNext = false;
                           scope.directionLinksPrev = true; 
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
	}]).directive('dropdownMultiselect', function() {
		return {
			restrict : 'E',
			scope : {
				model : '=',
				options : '=',
			},
			template : "<div class='btn-group' data-ng-class='{open: open}'>" + "<button class='btn btn-small'>Select...</button>" + "<button class='btn btn-small dropdown-toggle' data-ng-click='openDropdown()'><span class='caret'></span></button>" + "<ul class='dropdown-menu' aria-labelledby='dropdownMenu'>" + "<li><a data-ng-click='selectAll()'><span class='glyphicon glyphicon-ok green' aria-hidden='true'></span> Check All</a></li>" + "<li><a data-ng-click='deselectAll();'><span class='glyphicon glyphicon-remove red' aria-hidden='true'></span> Uncheck All</a></li>" + "<li class='divider'></li>" + "<li data-ng-repeat='option in options'><a data-ng-click='toggleSelectItem(option)'><span data-ng-class='getClassName(option)' aria-hidden='true'></span> {{option.value}}</a></li>" + "</ul>" + "</div>",
			controller : function($scope, $rootScope) {
				$scope.dataArray = [];
				$scope.openDropdown = function() {
					$scope.open = !$scope.open;
				};
				$scope.selectAll = function() {
					$scope.model = [];
					angular.forEach($scope.options, function(item, index) {
						$scope.model.push(item.key);
						$scope.dataArray.push(item.value);
					});
					$rootScope.drodownValList[$scope.name] = $scope.dataArray;
				};
				$scope.deselectAll = function() {
					$scope.model = [];
					$scope.dataArray = [];
					$rootScope.drodownValList[$scope.name] = $scope.dataArray;
				};

				$scope.toggleSelectItem = function(option) {
					var intIndex = -1;
					angular.forEach($scope.model, function(item, index) {
						if (item == option.key) {
							intIndex = index;
						}
					});
					if (intIndex >= 0) {
						$scope.model.splice(intIndex, 1);
						$scope.dataArray.splice(intIndex, 1);
						$rootScope.drodownValList[$scope.name] = $scope.dataArray;
					} else {
						$scope.model.push(option.key);
						$scope.dataArray.push(option.value)
						$rootScope.drodownValList[$scope.name] = $scope.dataArray;
					}
				};

				$scope.getClassName = function(option) {
					var varClassName = 'glyphicon glyphicon-remove red';
					angular.forEach($scope.model, function(item, index) {
						if (item == option.key) {
							varClassName = 'glyphicon glyphicon-ok green';
						}
					});
					return (varClassName);
				};
				setTimeout(function() {
					$scope.selectAll();
					$scope.deselectAll();
				}, 100)
			},
			link : function($scope, element, attr) {
				$scope.name = attr.name;
			}
		}
	});
})();

( function() {
	'use strict';

	angular.module('ui.grid.draggable-rows', ['ui.grid']).constant('uiGridDraggableRowsConstants', {
		featureName : 'draggableRows',
		ROW_TARGET_CLASS : 'ui-grid-draggable-row-target',
		ROW_OVER_CLASS : 'ui-grid-draggable-row-over',
		ROW_OVER_ABOVE_CLASS : 'ui-grid-draggable-row-over--above',
		ROW_OVER_BELOW_CLASS : 'ui-grid-draggable-row-over--below',
		ROW_HANDLE_CLASS : 'ui-grid-draggable-row-handle',
		POSITION_ABOVE : 'above',
		POSITION_BELOW : 'below',
		publicEvents : {
			draggableRows : {
				rowDragged : function(scope, info, rowElement) {
				},
				rowDropped : function(scope, info, targetElement) {
				},
				rowOverRow : function(scope, info, rowElement) {
				},
				rowEnterRow : function(scope, info, rowElement) {
				},
				rowLeavesRow : function(scope, info, rowElement) {
				},
				rowFinishDrag : function(scope) {
				},
				beforeRowMove : function(scope, from, to, data) {
				}
			}
		}
	}).factory('uiGridDraggableRowsCommon', [
	function() {
		return {
			draggedRow : null,
			draggedRowEntity : null,
			targetRow : null,
			targetRowEntity : null,
			position : null,
			fromIndex : null,
			toIndex : null,
			dragDisabled : false
		};
	}]).factory('uiGridDraggableRowsSettings', [
	function() {
		return {
			dragDisabled : false
		};
	}]).service('uiGridDraggableRowsService', ['uiGridDraggableRowsConstants', 'uiGridDraggableRowsCommon', 'uiGridDraggableRowsSettings',
	function(uiGridDraggableRowsConstants, uiGridDraggableRowsCommon, uiGridDraggableRowsSettings) {
		var publicMethods = {
			dragndrop : {
				setDragDisabled : function setDragDisabled(status) {
					uiGridDraggableRowsSettings.dragDisabled = ~~status;
				}
			}
		};

		this.initializeGrid = function(grid, $scope, $element) {
			grid.api.registerEventsFromObject(uiGridDraggableRowsConstants.publicEvents);
			grid.api.registerMethodsFromObject(publicMethods);

			grid.api.draggableRows.on.rowFinishDrag($scope, function() {
				angular.forEach($element[0].querySelectorAll('.' + uiGridDraggableRowsConstants.ROW_OVER_CLASS), function(row) {
					row.classList.remove(uiGridDraggableRowsConstants.ROW_OVER_CLASS);
					row.classList.remove(uiGridDraggableRowsConstants.ROW_OVER_ABOVE_CLASS);
					row.classList.remove(uiGridDraggableRowsConstants.ROW_OVER_BELOW_CLASS);
				});
				angular.forEach($element[0].querySelectorAll('.' + uiGridDraggableRowsConstants.ROW_TARGET_CLASS), function(row) {
					row.classList.remove(uiGridDraggableRowsConstants.ROW_TARGET_CLASS);
				});
			});
		};
	}]).service('uiGridDraggableRowService', ['uiGridDraggableRowsConstants', 'uiGridDraggableRowsCommon', 'uiGridDraggableRowsSettings', '$parse',
	function(uiGridDraggableRowsConstants, uiGridDraggableRowsCommon, uiGridDraggableRowsSettings, $parse) {
		var move = function(from, to, grid) {
			grid.api.draggableRows.raise.beforeRowMove(from, to, this);

			/*jshint validthis: true */
			this.splice(to, 0, this.splice(from, 1)[0]);
		};

		this.prepareDraggableRow = function($scope, $element) {
			var grid = $scope.grid;
			var row = $element[0];
			var hasHandle = $scope.grid.options.useUiGridDraggableRowsHandle;
			var currentTarget = null;
			var handle = null;

			var data = function() {
				if (angular.isString(grid.options.data)) {
					return $parse(grid.options.data)(grid.appScope);
				}

				return grid.options.data;
			};

			// issue #16
			if (grid.api.hasOwnProperty('edit')) {
				grid.api.edit.on.beginCellEdit(null, function() {
					row.setAttribute('draggable', false);
				});

				grid.api.edit.on.afterCellEdit(null, function() {
					row.setAttribute('draggable', true);
				});
			}

			var listeners = {
				onMouseDownEventListener : function(e) {
					currentTarget = angular.element(e.target);
					handle = currentTarget.closest('.' + uiGridDraggableRowsConstants.ROW_HANDLE_CLASS, $element)[0];
				},

				onMouseUpEventListener : function(e) {
					currentTarget = null;
					handle = null;
				},

				onDragOverEventListener : function(e) {
					if (e.preventDefault) {
						e.preventDefault();
					}

					var dataTransfer = e.dataTransfer || e.originalEvent.dataTransfer;
					dataTransfer.effectAllowed = 'copyMove';
					dataTransfer.dropEffect = 'move';

					var offset = e.offsetY || e.layerY || (e.originalEvent ? e.originalEvent.offsetY : 0);

					$element.addClass(uiGridDraggableRowsConstants.ROW_OVER_CLASS);

					if (offset < this.offsetHeight / 2) {
						uiGridDraggableRowsCommon.position = uiGridDraggableRowsConstants.POSITION_ABOVE;

						$element.removeClass(uiGridDraggableRowsConstants.ROW_OVER_BELOW_CLASS);
						$element.addClass(uiGridDraggableRowsConstants.ROW_OVER_ABOVE_CLASS);

					} else {
						uiGridDraggableRowsCommon.position = uiGridDraggableRowsConstants.POSITION_BELOW;

						$element.removeClass(uiGridDraggableRowsConstants.ROW_OVER_ABOVE_CLASS);
						$element.addClass(uiGridDraggableRowsConstants.ROW_OVER_BELOW_CLASS);
					}

					grid.api.draggableRows.raise.rowOverRow(uiGridDraggableRowsCommon, this);
				},

				onDragStartEventListener : function(e) {
					if (uiGridDraggableRowsSettings.dragDisabled || (hasHandle && !handle)) {
						e.preventDefault();
						e.stopPropagation();

						return false;
					}

					this.classList.add(uiGridDraggableRowsConstants.ROW_TARGET_CLASS);
					e.dataTransfer.setData('Text', 'move');
					// Need to set some data for FF to work
					uiGridDraggableRowsCommon.draggedRow = this;
					uiGridDraggableRowsCommon.draggedRowEntity = $scope.$parent.$parent.row.entity;

					uiGridDraggableRowsCommon.position = null;

					uiGridDraggableRowsCommon.fromIndex = data().indexOf(uiGridDraggableRowsCommon.draggedRowEntity);
					uiGridDraggableRowsCommon.toIndex = null;

					grid.api.draggableRows.raise.rowDragged(uiGridDraggableRowsCommon, this);
				},

				onDragLeaveEventListener : function() {
					this.classList.remove(uiGridDraggableRowsConstants.ROW_TARGET_CLASS);

					this.classList.remove(uiGridDraggableRowsConstants.ROW_OVER_CLASS);
					this.classList.remove(uiGridDraggableRowsConstants.ROW_OVER_ABOVE_CLASS);
					this.classList.remove(uiGridDraggableRowsConstants.ROW_OVER_BELOW_CLASS);

					grid.api.draggableRows.raise.rowLeavesRow(uiGridDraggableRowsCommon, this);
				},

				onDragEnterEventListener : function() {
					grid.api.draggableRows.raise.rowEnterRow(uiGridDraggableRowsCommon, this);
				},

				onDragEndEventListener : function() {
					grid.api.draggableRows.raise.rowFinishDrag();
				},

				onDropEventListener : function(e) {
					var draggedRow = uiGridDraggableRowsCommon.draggedRow;

					if (e.stopPropagation) {
						e.stopPropagation();
					}

					if (e.preventDefault) {
						e.preventDefault();
					}

					if (draggedRow === this) {
						return false;
					}

					uiGridDraggableRowsCommon.toIndex = data().indexOf($scope.$parent.$parent.row.entity);

					uiGridDraggableRowsCommon.targetRow = this;

					uiGridDraggableRowsCommon.targetRowEntity = $scope.$parent.$parent.row.entity;

					if (uiGridDraggableRowsCommon.position === uiGridDraggableRowsConstants.POSITION_ABOVE) {
						if (uiGridDraggableRowsCommon.fromIndex < uiGridDraggableRowsCommon.toIndex) {
							uiGridDraggableRowsCommon.toIndex -= 1;
						}

					} else if (uiGridDraggableRowsCommon.fromIndex >= uiGridDraggableRowsCommon.toIndex) {
						uiGridDraggableRowsCommon.toIndex += 1;
					}

					$scope.$apply(function() {
						move.apply(data(), [uiGridDraggableRowsCommon.fromIndex, uiGridDraggableRowsCommon.toIndex, grid]);
					});

					grid.api.draggableRows.raise.rowDropped(uiGridDraggableRowsCommon, this);

					e.preventDefault();
				}
			};

			row.addEventListener('dragover', listeners.onDragOverEventListener, false);
			row.addEventListener('dragstart', listeners.onDragStartEventListener, false);
			row.addEventListener('dragleave', listeners.onDragLeaveEventListener, false);
			row.addEventListener('dragenter', listeners.onDragEnterEventListener, false);
			row.addEventListener('dragend', listeners.onDragEndEventListener, false);
			row.addEventListener('drop', listeners.onDropEventListener);

			if (hasHandle) {
				row.addEventListener('mousedown', listeners.onMouseDownEventListener, false);
				row.addEventListener('mouseup', listeners.onMouseUpEventListener, false);
			}
		};
	}]).directive('uiGridDraggableRow', ['uiGridDraggableRowService',
	function(uiGridDraggableRowService) {
		return {
			restrict : 'ACE',
			scope : {
				grid : '='
			},
			compile : function() {
				return {
					pre : function($scope, $element) {
						uiGridDraggableRowService.prepareDraggableRow($scope, $element);
					}
				};
			}
		};
	}]).directive('uiGridDraggableRows', ['uiGridDraggableRowsService',
	function(uiGridDraggableRowsService) {
		return {
			restrict : 'A',
			replace : true,
			priority : 0,
			require : 'uiGrid',
			scope : false,
			compile : function() {
				return {
					pre : function($scope, $element, $attrs, uiGridCtrl) {
						uiGridDraggableRowsService.initializeGrid(uiGridCtrl.grid, $scope, $element);
					}
				};
			}
		};
	}]);	
}()); 