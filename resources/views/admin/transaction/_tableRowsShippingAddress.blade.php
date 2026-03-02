@forelse($data as $address)
    @php
        $chk_sub_district = ($address->sub_district != '' && $address->sub_district != '-') ? $address->sub_district_val : '';
    @endphp
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $address->id }}</td>
        <td>{{ $address->full_name }}</td>
        <td>{{ $address->title ?? '' }} {{ $address->address ?? '' }} {{ $address->road ?? '' }} {{ $address->sub_district ?? '' }} {{ $address->city_district ?? '' }} {{ $address->province_state ?? '' }} {{ $address->zip_code ?? '' }}</td>
        <td>{{ $chk_sub_district }} {{ $address->city_district_val ?? '' }} {{ $address->province_state_val ?? '' }} {{ $address->zip_code ?? '' }}</td>
        <td>{{ $address->ph_number ?? '' }}</td>
        <td>{{ $address->email ?? '' }}</td>
        <td style="text-align: center;"><span style="color: red">{{ $address->check_update ?? '' }}</span></td>
        <td style="text-align: center; width: 8%;">{{ $address->updated_at ?? '' }}</td>
    </tr>
@empty
<tr>
    <td colspan="9" class="text-center">ไม่มีข้อมูล</td>
</tr>
@endforelse
