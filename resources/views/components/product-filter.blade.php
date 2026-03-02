@props(['productsize' => [], 'productgrade' => []])


<div class="bg-white p-3">

    <form action="{{url()->current()}}" method="GET" id="filterForm">
        @csrf
        <input type="hidden" name="search" value="{{request('search')}}">
        <input type="hidden" name="productCate" value="{{request('productCate')}}">
        <input type="hidden" name="productType" value="{{request('productType')}}">
        <input type="hidden" name="sortBy" value="{{request('sortBy')}}">
        <div class="d-flex justify-content-between align-items-center mb-3 pt-2">
            <strong>ตัวกรอง</strong>
        </div>
        <div>
            <!-- <div>
                <input type="checkbox" name="badgesAll" id="badgesAll" class="filter-check-all"
                @if (request('badgesAll') && request('badgesAll') == 'on')
                    checked
                @endif>
                <label for="badgesAll">สินค้าทั้งหมด</label>
            </div> -->
            <hr />
            

            <div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>ขนาด</strong>
                    <a class="toggle-btn" type="button" data-toggle="collapse" data-target="#productSize">
                        <i class="fa fa-angle-{{$agent->isDesktop()?'up':'down'}}" aria-hidden="true"></i>
                    </a>
                </div>
                <div class="row collapse {{$agent->isDesktop()?'show':''}}" id="productSize">
                    @foreach ($productsize??[] as $i => $item)
                        @if ($item)
                            <div class="col-lg-12 col-md-3 col-6">
                                <input type="checkbox" name="productSize[]" id="productSize_{{ $i }}" value="{{ $item->slug??'' }}" class="filter-check-one"
                                 @if (request('productSize') && in_array($item->slug??'', request('productSize')))
                                    checked
                                 @endif>
                                <label for="productSize_{{ $i }}">{{ $item->name??'' }}</label>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
            <hr>
            <div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>เกรด</strong>
                    <a class="toggle-btn" type="button" data-toggle="collapse" data-target="#productGrade">
                        <i class="fa fa-angle-{{$agent->isDesktop()?'up':'down'}}" aria-hidden="true"></i>
                    </a>
                </div>
                <div class="row collapse {{$agent->isDesktop()?'show':''}}" id="productGrade">
                    @foreach ($productgrade??[] as $i => $item)
                        @if ($item)
                            <div class="col-lg-12 col-md-3 col-6">
                                <input type="checkbox" name="productGrade[]" id="productGrade_{{ $i }}" value="{{ $item->slug??'' }}" class="filter-check-one"
                                @if (request('productGrade') && in_array($item->slug??'', request('productGrade')))
                                    checked
                                @endif>
                                <label for="productGrade_{{ $i }}">{{ $item->name }}</label>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
            <hr>
            <div class="d-flex justify-content-center align-items-center mb-3 pt-2">

                <button type="button" class="btn btn-outline-danger btn-sm px-2 btn_clean_filter">
                    <i class="fa fa-repeat"></i> ล้างตัวกรอง
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    $(document).ready(function() {

        $('.btn_clean_filter').click(function() {
            $('.filter-field input[type="checkbox"]').prop('checked', false);
            $('#filterForm').submit();
        });

        $('.filter-check-one').on('change', function () {
            $('.filter-check-all').prop('checked', false);
            $('#filterForm').submit();
        });
        
        $('.filter-check-all').on('change', function () {
            if ($(this).is(':checked')) {
                $('.filter-check-one').prop('checked', true);
            } else {
                $('.filter-check-one').prop('checked', false);
            }
            $('#filterForm').submit();
        });

    });
</script>
