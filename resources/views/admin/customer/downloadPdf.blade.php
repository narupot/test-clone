<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details</title>

    <style>
        @font-face {
        font-family: "THSarabunNew";
        font-style: normal;
        font-weight: normal;
        src: url("{{ asset('pdf_fonts/THSarabunNew.ttf')}}") format("truetype");

        }  
        table {
            width: 95%;
            border-collapse: collapse;
            margin: 50px auto;
        }

        /* Zebra striping */
        tr:nth-of-type(odd) {
            background: #eee;
        }

        th {
            background: #3498db;
            color: white;
            font-weight: bold;
        }

        td,
        th {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
            font-size: 18px;
        }


    </style>

</head>

<body>

    <div style="width: 95%; margin: 0 auto;">
        <!-- <div style="width: 10%; float:left; margin-right: 20px;">
            <img src="{{ public_path('assets/images/logo.png') }}" width="100%"  alt="">
        </div> -->
        <div style="width: 50%; float: left;">
            <h1>All User Details</h1>
        </div>
    </div>

    <table style="position: relative; top: 50px;">
        <thead>
            <tr>
                <th>@lang('admin_customer.name')</th>
                <th>@lang('admin_customer.email')</th>
                <th>@lang('admin_customer.dob')</th>
                <th>@lang('admin_customer.phone_no')</th>
                <th>@lang('admin_customer.user_type')</th>
                <th>@lang('admin_customer.register_from')</th>
                <th>@lang('admin_customer.status')</th>
                <th>@lang('admin_customer.verified')</th>
                <th>@lang('admin_common.created_at')</th>
                <th>@lang('admin_common.last_updated')</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td data-column="name">{{ $user->display_name }}</td>
                    <td data-column="email">{{ $user->email }}</td>
                    <td data-column="dob">{{ $user->dob }}</td>
                    <td data-column="phn">{{ $user->ph_number }}</td>
                    <td data-column="user_type">{{ $user->user_type }}</td>
                    <td data-column="register_from">{{ $user->register_from }}</td>
                    <td data-column="status">
                        @if($user->status==0)
                            @lang('common.inactive')
                        @elseif($user->status==1)
                            @lang('common.active')
                        @else
                            @lang('common.delete')
                        @endif
                    </td>
                    <td data-column="verified">
                        @if($user->verified==0)
                            @lang('admin_customer.not_verified')
                        @else
                            @lang('admin_customer.verified')
                        @endif
                    </td>
                    <td data-column="created_at">{{ getDateFormat($user->created_at,9) }}</td>
                    <td data-column="updated_at">{{ getDateFormat($user->updated_at,9) }}</td>

                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
