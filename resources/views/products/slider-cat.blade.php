@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1>{{ $slider->sliderdesc->title }}</h1>
        </div>
    </div>

    <!-- <div class="row">
        <div class="col-12">
            <div class="toolbar">
                <div class="row">
                    <div class="col-md-6">
                        <div class="items-per-page">
                            <label>Show:</label>
                            <select ng-model="itemsPerPage" ng-change="changeItemsPerPage()">
                                <option ng-repeat="item in showPerPage" value="@{{item}}">@{{item}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="sort-by">
                            <label>Sort by:</label>
                            <select ng-model="orderBy" ng-change="changeOrderBy()">
                                <option ng-repeat="item in orderByItem" value="@{{item.value}}">@{{item.name}}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> -->

    <div class="row product_list_warpper ml-lg-1">
        @foreach($products as $product)
            <x-cat-card :product="$product" :row="4" />
        @endforeach
    </div>

    <div class="row">
        <div class="col-12">
            @if (method_exists($products, 'links'))
                {!! $products->links('components.pagination') !!}
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    angular.module('app', [])
    .controller('ProductListController', function($scope) {
        $scope.showPerPage = {!! $show_per_page !!};
        $scope.orderByItem = {!! $order_by_item !!};
        $scope.ratingStarItem = {!! $rating_star_item !!};
        
        //$scope.itemsPerPage = {{ request('perPage', 12) }};
       // $scope.orderBy = '{{ request('orderBy', 'created_at') }}';
        
        //$scope.changeItemsPerPage = function() {
            //var url = new URL(window.location.href);
            //url.searchParams.set('perPage', $scope.itemsPerPage);
            //window.location.href = url.toString();
        //};
        
        $scope.changeOrderBy = function() {
            var url = new URL(window.location.href);
            url.searchParams.set('orderBy', $scope.orderBy);
            window.location.href = url.toString();
        };
    });
</script>
@endpush
@endsection 