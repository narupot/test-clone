<?php

namespace App\Http\Controllers\User;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\MarketPlace;
use App\Helpers\GeneralFunctions;
use App\Helpers\CustomHelpers;
use Illuminate\Http\Request;
use App\Order;
use App\OrderDetail;
use App\OrderShop;
use App\PaymentOption;
use Hash;
use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Config;
use Session;
use Exception;

class OrderController extends MarketPlace
{
    public function __construct()
    {
        $this->middleware('authenticate');
    }

    public function orderHistory(Request $request)
    {

        return view('user.order.order_history');
    }

    public function orderHistoryData(Request $request)
    {
        $column_arr = ['end_shopping_date', 'formatted_id', 'order_status', 'shipping_method', 'action'];
        $draw = $request->draw;
        $start = $request->start; //Start is the offset
        $length = $request->length; //How many records to show
        $column = $request->order[0]['column']; //Column to orderBy
        $dir = $request->order[0]['dir']; //Direction of orderBy
        $searchValue = $request->search['value']; //Search value
        $order_by_column = $column_arr[$column];

        //Sets the current page
        Paginator::currentPageResolver(function () use ($start, $length) {
            return ($start / $length + 1);
        });

        $user_id = Auth::id();

        $order_data = Order::where('user_id', $user_id);

        if (!empty($searchValue)) {
            $order_data->where('formatted_id', 'LIKE', "%{$searchValue}%");
        }

        $order_list = $order_data->with('getOrderStatus')->orderBy($order_by_column, $dir)->paginate($length);
        $data = array();
        if (!empty($order_list)) {

            foreach ($order_list as $ord_val) {

                $nestedData['end_shopping_date'] = getDateFormat($ord_val->end_shopping_date, 7);


                $nestedData['formatted_id'] = '<a href="' . action('User\OrderController@mainOrderDetail', $ord_val->formatted_id) . '" class="link-skyblue">' . $ord_val->formatted_id . '</a>';

                $nestedData['status'] = $ord_val->getOrderStatus->status ?? '';

                $nestedData['shipping_method'] = GeneralFunctions::getShippingMethod($ord_val->shipping_method);
                $nestedData['action'] = "<a href='" . action('User\OrderController@mainOrderDetail', $ord_val->formatted_id) . "' class='skyblue'>" . Lang::get('common.view_detail') . "</a>";
                $data[] = $nestedData;
            }
        }

        $json_data = array(
            "draw" => intval($draw),
            "recordsTotal" => intval($order_list->total()),
            "recordsFiltered" => intval($order_list->total()),
            "data" => $data
        );

        return $json_data;
    }

    public function pendingOrder(Request $request)
    {

        return view('user.order.pending_order');
    }

    public function pendingOrderData(Request $request)
    {
        $column_arr = ['formatted_id', 'order_status', 'shipping_method', 'action'];
        $draw = $request->draw;
        $start = $request->start; //Start is the offset
        $length = $request->length; //How many records to show
        $column = $request->order[0]['column']; //Column to orderBy
        $dir = $request->order[0]['dir']; //Direction of orderBy
        $searchValue = $request->search['value']; //Search value
        $order_by_column = $column_arr[$column];

        //Sets the current page
        Paginator::currentPageResolver(function () use ($start, $length) {
            return ($start / $length + 1);
        });

        $user_id = Auth::id();

        $order_data = Order::where(['user_id' => $user_id, 'payment_status' => 0, 'order_status' => 1]);

        if (!empty($searchValue)) {
            $order_data->where('formatted_id', 'LIKE', "%{$searchValue}%");
        }

        $order_list = $order_data->with('getOrderStatus')->orderBy($order_by_column, $dir)->paginate($length);
        $data = array();
        if (!empty($order_list)) {

            foreach ($order_list as $ord_val) {

                $nestedData['formatted_id'] = '<a href="' . action('User\OrderController@mainOrderDetail', $ord_val->formatted_id) . '" class="link-skyblue">' . $ord_val->formatted_id . '</a>';

                $nestedData['status'] = $ord_val->getOrderStatus->status ?? '';

                $nestedData['shipping_method'] = GeneralFunctions::getShippingMethod($ord_val->shipping_method);
                $nestedData['action'] = "<a href='" . action('User\OrderController@mainOrderDetail', $ord_val->formatted_id) . "' class='skyblue'>" . Lang::get('common.view_detail') . "</a>";
                $data[] = $nestedData;
            }
        }

        $json_data = array(
            "draw" => intval($draw),
            "recordsTotal" => intval($order_list->total()),
            "recordsFiltered" => intval($order_list->total()),
            "data" => $data
        );

        return $json_data;
    }

    public function sellerOrderHistory(Request $request)
    {

        return view('user.order.seller_order_history');
    }

    public function sellerOrderHistoryData(Request $request)
    {
        $column_arr = ['end_shopping_date', 'shop_formatted_id', 'order_status', 'shipping_method', 'action'];
        $draw = $request->draw;
        $start = $request->start; //Start is the offset
        $length = $request->length; //How many records to show
        $column = $request->order[0]['column']; //Column to orderBy
        $dir = $request->order[0]['dir']; //Direction of orderBy
        $searchValue = $request->search['value']; //Search value
        $order_by_column = $column_arr[$column];

        //Sets the current page
        Paginator::currentPageResolver(function () use ($start, $length) {
            return ($start / $length + 1);
        });

        $user_id = Auth::id();

        $order_data = OrderShop::where('user_id', $user_id)->where('end_shopping_date', '!=', null);

        if (!empty($searchValue)) {
            $order_data->where('shop_formatted_id', 'LIKE', "%{$searchValue}%");
        }

        $order_list = $order_data->with('getOrderStatus')->orderBy($order_by_column, $dir)->paginate($length);
        $data = array();
        if (!empty($order_list)) {

            foreach ($order_list as $ord_val) {

                $nestedData['end_shopping_date'] = getDateFormat($ord_val->end_shopping_date, 7);


                $nestedData['shop_formatted_id'] = '<a href="' . action('User\OrderController@shopOrderDetails', $ord_val->shop_formatted_id) . '" class="link-skyblue">' . $ord_val->shop_formatted_id . '</a>';

                $nestedData['status'] = $ord_val->getOrderStatus->status ?? '';

                $nestedData['shipping_method'] = GeneralFunctions::getShippingMethod($ord_val->shipping_method);
                $nestedData['action'] = "<a href='" . action('User\OrderController@shopOrderDetails', $ord_val->shop_formatted_id) . "' class='skyblue'>" . Lang::get('common.view_detail') . "</a>";
                $data[] = $nestedData;
            }
        }

        $json_data = array(
            "draw" => intval($draw),
            "recordsTotal" => intval($order_list->total()),
            "recordsFiltered" => intval($order_list->total()),
            "data" => $data
        );

        return $json_data;
    }

    public function orderDetails(Request $request)
    {
        $orderShopData = \App\OrderShop::where('shop_formatted_id', $request->order_id)->with(['getUser'])->first();
        if ($orderShopData == null)
            abort('404');


        $orderItems = \App\OrderDetail::getShopOrderDetail(Auth::user()->id, null, $orderShopData->order_id);
        $mainOrderData = \App\Order::where('id', $orderShopData->order_id)->first();

        return view('user.order_details', ['orderItems' => $orderItems, 'mainOrderData' => $mainOrderData, 'orderShopData' => $orderShopData]);
    }

    public function shopOrderdetails(Request $request)
    {
        $orderShopData = \App\OrderShop::where('shop_formatted_id', $request->order_id)->with(['getUser'])->first();
        if ($orderShopData == null)
            abort('404');

        $orderItems = \App\OrderDetail::getShopOrderDetail(Auth::user()->id, $orderShopData->id, $orderShopData->order_id);
        $mainOrderData = \App\Order::where('id', $orderShopData->order_id)->first();

        return view('user.order.shop_order_details', ['orderItems' => $orderItems, 'mainOrderData' => $mainOrderData, 'orderShopData' => $orderShopData]);
    }

    public function mainOrderDetail(Request $request, $ord_id)
    {

        $user_id = Auth::id();

        $main_order = Order::where(['formatted_id' => $ord_id, 'user_id' => $user_id])
            ->with('paymentOption.paymentOptName')
            ->first();
        if (empty($main_order)) {
            abort(404);
        }

        $order_detail = [];
        $shop_order = [];
        if (!empty($main_order)) {
            $order_detail = OrderDetail::getMainOrderDetail($main_order->id)->map(function ($item) {
                if (!empty($item->order_detail_json) && is_string($item->order_detail_json)) {
                    $decoded = json_decode($item->order_detail_json, true);
                    $item->order_detail_json = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
                } else {
                    $item->order_detail_json = [];
                }
                return $item;
            });

            $shop_ord = \App\OrderShop::where('order_id', $main_order->id)->select('id', 'shop_formatted_id', 'order_status')->with('getOrderStatus')->get();
            if (count($shop_ord)) {
                foreach ($shop_ord as $key => $value) {
                    $status = $value->getOrderStatus->status ?? '';
                    $shop_order[$value->id] = ['shop_formatted_id' => $value->shop_formatted_id, 'status' => $status, 'order_status' => $value->order_status];
                }
            }

        }

        if ($main_order->shipping_method == 3 && $main_order->pickup_time) {
            $new_time = date("Y-m-d H:i:s", strtotime('+3 hours', strtotime($main_order->pickup_time)));

            $plus_two = date('H', strtotime($new_time));
            $main_order->plus_two_time = $new_time;
            $main_order->plus_two_hr = $plus_two;
        }

        $orderInfoJson = jsonDecodeArr($main_order->order_json);
        $total_logistic_cost = isset($orderInfoJson['total_logistic_cost']) ? $orderInfoJson['total_logistic_cost'] : 0;
        $odc = $main_order->orderDiscountCode;

        // ดึงข้อมูล transaction_fee_name เฉพาะสำหรับ Beam payment methods เท่านั้น
        $transaction_fee_name = '';
        $current_tf_percentage = '';
        if (isset($main_order->transaction_fee) && $main_order->transaction_fee > 0 && isset($main_order->payment_slug)) {
            $payment_slug = $main_order->payment_slug;

            // ตรวจสอบว่าเป็น Beam payment method หรือไม่
            if (strpos($payment_slug, 'beam') === 0) {
                $paymentOption = PaymentOption::where('slug', $payment_slug)->first();
                if ($paymentOption) {
                    $transaction_fee_config = $paymentOption->transactionFeeConfig;
                    if ($transaction_fee_config) {
                        $transaction_fee_name = $transaction_fee_config->name ?? '';
                        $current_tf_percentage = $transaction_fee_config->current_tf ?? '';
                    }
                }

            }
        }

        return view('user.order.main_order_detail', [
            'main_order' => $main_order,
            'order_detail' => $order_detail,
            'shop_order' => $shop_order,
            'total_logistic_cost' => $total_logistic_cost,
            'order_discount_code' => $odc,
            'transaction_fee_name' => $transaction_fee_name,
            'current_tf_percentage' => $current_tf_percentage

        ]);
    }

    public function orderPayment(Request $request, $ord_id)
    {
        $user_id = Auth::id();

        $main_order = Order::where(['formatted_id' => $ord_id, 'user_id' => $user_id, 'payment_status' => 0, 'order_status' => 1])->first();
        if (empty($main_order)) {
            abort(404);
        }

        $order_detail = [];
        $shop_order = [];
        if (!empty($main_order)) {
            $order_detail = OrderDetail::getMainOrderDetail($main_order->id);
            $shop_ord = \App\OrderShop::where('order_id', $main_order->id)->select('id', 'shop_formatted_id', 'order_status')->with('getOrderStatus')->get();
            if (count($shop_ord)) {
                foreach ($shop_ord as $key => $value) {
                    $status = $value->getOrderStatus->status ?? '';
                    $shop_order[$value->id] = ['shop_formatted_id' => $value->shop_formatted_id, 'status' => $status, 'order_status' => $value->order_status];
                }
            }

        }

        $cur_hr = date('H');
        $center_estimate_time = 0;
        $all_del_time = \App\DeliveryTime::get();
        $delivery_time_arr = [];
        foreach ($all_del_time as $key => $delivery_time) {
            if ($delivery_time->delivery_type == 'pickup_center') {
                $center_estimate_time = $delivery_time->delivery_time_after;
            }
            $cur_time_start = $cur_hr + 1 + $delivery_time->delivery_time_after;
            $time_slot = explode(',', $delivery_time->time_slot);
            $time_arr = [];
            $c_arr = $n_arr = [];
            $nd_arr = [];

            if ($delivery_time->delivery_type != 'shop_address') {
                foreach ($time_slot as $tkey => $tvalue) {

                    $same_next_day_cut_off_order_time = $tvalue - $delivery_time->delivery_time_after;
                    $cut_off_order_time = $tvalue - $delivery_time->delivery_time_after;
                    if ($cut_off_order_time < 0) {
                        $cut_off_order_time = 24 + $cut_off_order_time;
                    }

                    if ($delivery_time->delivery_type == 'buyer_address') {
                        $add_two = ($tvalue + 3);
                        $add_day = 1;
                        $ndate = date('Y-m-d', strtotime(' +' . $add_day . ' day'));
                        $val_show = $tvalue . ':00 - ' . ($add_two);

                        if ($tvalue >= $cur_time_start) {
                            if ($add_two >= 24) {
                                $add_two = $add_two - 24;
                                $val_show = $tvalue . ':00 - ' . ($add_two);
                                $ndate = date('Y-m-d', strtotime(' +1 day'));
                                $expdate = explode('-', $ndate);
                                //$c_arr[] = ['key'=>$tvalue,'val'=>$val_show.':00 ('.$expdate[2].' '.getThaiMonth(date($expdate[1])).')'];
                                $c_arr[] = ['key' => $tvalue, 'val' => '(' . $expdate[2] . ' ' . getThaiMonth(date($expdate[1])) . ') ' . $val_show . ':00'];
                            } else {
                                $c_arr[] = ['key' => $tvalue, 'val' => $val_show . ':00'];
                            }

                        } else {
                            if ($add_two >= 24) {
                                $ndate = date('Y-m-d', strtotime(' +2 day'));
                                $add_two = $add_two - 24;
                                $val_show = $tvalue . ':00 - ' . ($add_two);
                            } else {
                                if ($cut_off_order_time <= $cur_hr && $same_next_day_cut_off_order_time <= 0) {
                                    $ndate = date('Y-m-d', strtotime(' +2 day'));
                                    $expdate = explode('-', $ndate);
                                    //$n_arr[] = ['key'=>$tvalue.'nd_n','val'=>$val_show.':00 ('. $expdate[2].' '.getThaiMonth(date($expdate[1])).')'];
                                    $nd_arr[] = ['key' => $tvalue . 'nd_n', 'val' => '(' . $expdate[2] . ' ' . getThaiMonth(date($expdate[1])) . ') ' . $val_show . ':00'];

                                } else {
                                    $expdate = explode('-', $ndate);
                                    //$n_arr[] = ['key'=>$tvalue.'_n','val'=>$val_show.':00 ('. $expdate[2].' '.getThaiMonth(date($expdate[1])).')'];
                                    $n_arr[] = ['key' => $tvalue . '_n', 'val' => '(' . $expdate[2] . ' ' . getThaiMonth(date($expdate[1])) . ') ' . $val_show . ':00'];
                                }
                            }

                        }
                    } else {
                        if ($tvalue >= $cur_time_start) {
                            $c_arr[] = ['key' => $tvalue, 'val' => $tvalue . ':00'];
                        } else {
                            $ndate = date('Y-m-d', strtotime(' +1 day'));
                            $expdate = explode('-', $ndate);
                            //$n_arr[] = ['key'=>$tvalue.'_n','val'=>$tvalue.':00 ('. $expdate[2].' '.getThaiMonth($expdate[1]).')'];
                            $n_arr[] = ['key' => $tvalue . '_n', 'val' => '(' . $expdate[2] . ' ' . getThaiMonth($expdate[1]) . ')' . $tvalue . ':00'];
                        }
                    }

                }
            } else {
                $j = 0;
                $next_time = 0;
                for ($i = 1; $i <= 12; $i++) {
                    if ($i == 1) {
                        $next_time = $cur_time_start;
                    } else {
                        $next_time = $next_time + 1;
                    }

                    if ($next_time >= 24) {
                        $ndate = date('Y-m-d', strtotime(' +1 day'));
                        $expdate = explode('-', $ndate);
                        //$n_arr[] = ['key'=>$j.'_n','val'=>$j.':00 ('. $expdate[2].' '.getThaiMonth($expdate[1]).')'];
                        $n_arr[] = ['key' => $j . '_n', 'val' => '(' . $expdate[2] . ' ' . getThaiMonth($expdate[1]) . ') ' . $j . ':00'];
                        $j++;
                    } else {
                        $c_arr[] = ['key' => $next_time, 'val' => $next_time . ':00'];
                    }
                }
            }

            $time_arr = array_merge($c_arr, $n_arr, $nd_arr);
            $delivery_time_arr[$delivery_time->delivery_type] = $time_arr;
        }

        $pickup_center = \App\SystemConfig::where('system_name', 'PICKUP_CENTER')->value('system_val');
        $pickup_center_address = $pickup_center ? jsonDecodeArr($pickup_center) : '';
        $item_pickup_time = 0;
        $estimate = $center_estimate_time;
        $item_pickup_time = $estimate;
        $cal_time = $cal_hour = $tomorrow = null;
        if ($item_pickup_time > 0) {
            $cal_time = date("Y-m-d H:i:s", strtotime('+' . $item_pickup_time . ' hours'));
            $cal_time = date("Y-m-d H:i:s", strtotime('2019-09-19 16:00:00'));
            $cal_hour = date('H', strtotime($cal_time));
            if ($cal_hour >= 23) {
                $tomorrow = true;
            }
        }

        $delivery_details = ['item_pickup_time' => $item_pickup_time, 'cal_time' => $cal_time, 'cal_hour' => $cal_hour, 'tomorrow' => $tomorrow];

        // หน้า payment: แสดง payment options ทั้งหมด (รวม Beam)
        $payment_option = PaymentOption::where(['status' => '1', 'payment_type' => '1'])
            ->where('slug', '!=', 'credit')
            ->with(['paymentOptName', 'transactionFeeConfig'])
            ->get();

        // ดึงข้อมูลค่าธรรมเนียมสำหรับการชำระเงินแต่ละประเภทจากฐานข้อมูล
        // $transaction_fees = [
        //     'qr_code' => \App\TransactionFeeConfig::where('name', 'QR Code')->first(),
        //     'mobile_banking' => \App\TransactionFeeConfig::where('name', 'Mobile Banking')->first(),
        //     'credit_card' => \App\TransactionFeeConfig::where('name', 'Credit Card')->first(),
        //     'wallet' => \App\TransactionFeeConfig::where('name', 'E-Wallet')->first(),
        // ];

        // คำนวณค่าธรรมเนียมการโอนสำหรับ payment method แรกที่เป็น Beam (default)
        // $default_transaction_fee = 0;
        // $default_transaction_fee_rate = 0;
        // $default_transaction_fee_name = '';
        // $default_payment_method_id = null;

        // หา payment method แรกที่เป็น Beam เพื่อใช้เป็น default
        // foreach ($payment_option as $payment) {
        //     if (strpos($payment->slug, 'beam') === 0) {
        //         $default_payment_method_id = $payment->id;

        //         // Map payment slug ไปยัง transaction fee config name
        //         $config_name = '';
        //         if (strpos($payment->slug, 'beam-qr') === 0) {
        //             $config_name = 'QR Code';
        //         } elseif (strpos($payment->slug, 'beam-credit') === 0) {
        //             $config_name = 'Credit Card';
        //         } elseif (strpos($payment->slug, 'beam-banking') === 0) {
        //             $config_name = 'Mobile Banking';
        //         } elseif (strpos($payment->slug, 'beam-ewallet') === 0) {
        //             $config_name = 'E-Wallet';
        //         }

        //         // หาข้อมูลจาก transaction_fees
        //         if ($config_name) {
        //             foreach ($transaction_fees as $key => $fee) {
        //                 if ($fee && $fee->name === $config_name) {
        //                     $default_transaction_fee_rate = $fee->current_tf;
        //                     $default_transaction_fee = ($main_order->total_final_price * $fee->current_tf) / 100;
        //                     $default_transaction_fee_name = $fee->message ?: $config_name;
        //                     break;
        //                 }
        //             }
        //         }
        //         break;
        //     }
        // }

        // หากไม่พบ Beam payment method ให้ใช้ payment method แรก
        // if (!$default_payment_method_id && count($payment_option) > 0) {
        //     $default_payment_method_id = $payment_option[0]->id;
        // }

        $user_odd_info = \App\UserInfo::getUserInfo('odd-register');
        $pickup_time_arr = [];
        if ($main_order->shipping_method == 1 && isset($delivery_time_arr['pickup_center'])) {
            $pickup_time_arr = $delivery_time_arr['pickup_center'];
        } elseif ($main_order->shipping_method == 2 && isset($delivery_time_arr['shop_address'])) {
            $pickup_time_arr = $delivery_time_arr['shop_address'];
        } else {
            $pickup_time_arr = $delivery_time_arr['buyer_address'];
        }
        $total_logistic_cost = isset($orderInfoJson['total_logistic_cost']) ? $orderInfoJson['total_logistic_cost'] : 0;

        $order_discount_code = $main_order->orderDiscountCode;

        return view('user.order.order_payment', [
            'main_order' => $main_order,
            'delivery_time_arr' => $delivery_time_arr,
            'delivery_details' => $delivery_details,
            'payment_option' => $payment_option,
            'pickup_time_arr' => $pickup_time_arr,
            'user_odd_info' => $user_odd_info,
            'order_detail' => $order_detail,
            'shop_order' => $shop_order,
            'total_logistic_cost' => $total_logistic_cost,
            // 'transaction_fees'=>$transaction_fees,
            // 'default_payment_method_id'=>$default_payment_method_id,
            // 'default_transaction_fee'=>$default_transaction_fee,
            // 'default_transaction_fee_rate'=>$default_transaction_fee_rate,
            // 'default_transaction_fee_name'=>$default_transaction_fee_name,
            'order_discount_code' => $order_discount_code
        ]);
    }

    public function deliveryList(Request $request)
    {

        return view('user.order.delivery_list');
    }

    public function deliveryListData(Request $request)
    {
        $column_arr = ['end_shopping_date', 'formatted_id', 'shipping_method', 'action'];
        $draw = $request->draw;
        $start = $request->start; //Start is the offset
        $length = $request->length; //How many records to show
        $column = $request->order[0]['column']; //Column to orderBy
        $dir = $request->order[0]['dir']; //Direction of orderBy
        $searchValue = $request->search['value']; //Search value
        $order_by_column = $column_arr[$column];
        //Sets the current page
        Paginator::currentPageResolver(function () use ($start, $length) {
            return ($start / $length + 1);
        });


        $user_id = Auth::id();

        $order_data = Order::where('user_id', $user_id)->where('end_shopping_date', '!=', null)->whereIn('order_status', [1, 2]);

        if (!empty($searchValue)) {
            $order_data->where('formatted_id', 'LIKE', "%{$searchValue}%");
        }

        $order_list = $order_data->with('getOrderStatus')->orderBy($order_by_column, $dir)->paginate($length);
        $data = array();
        if (!empty($order_list)) {

            foreach ($order_list as $ord_val) {

                $nestedData['end_shopping_date'] = getDateFormat($ord_val->end_shopping_date, 7);


                $nestedData['formatted_id'] = '<a href="' . action('User\OrderController@mainOrderDetail', $ord_val->formatted_id) . '" class="link-skyblue">' . $ord_val->formatted_id . '</a>';

                $nestedData['status'] = $ord_val->getOrderStatus->status ?? '';

                $nestedData['shipping_method'] = GeneralFunctions::getShippingMethod($ord_val->shipping_method);
                $nestedData['action'] = "<a href='" . action('User\OrderController@mainOrderDetail', $ord_val->formatted_id) . "' class='skyblue'>" . Lang::get('common.view_detail') . "</a>";
                $data[] = $nestedData;
            }
        }

        $json_data = array(
            "draw" => intval($draw),
            "recordsTotal" => intval($order_list->total()),
            "recordsFiltered" => intval($order_list->total()),
            "data" => $data
        );

        return $json_data;
    }

    public function receiveOrd(Request $request)
    {
        $formatted_id = $request->formatted_id ?? '';
        $user_id = Auth::id();
        $check_ord_detail = Order::where(['formatted_id' => $formatted_id, 'user_id' => $user_id])->first();
        if (!empty($check_ord_detail)) {
            $order_id = $check_ord_detail->id;
            try {
                $check_ord_detail->order_status = 3;
                $check_ord_detail->save();

                /****updating shop order status*****/
                $shop_update = \App\OrderShop::where('order_id', $order_id)->where('order_status', '!=', 4)->update(['order_status' => 3]);

                /****updating shop order item status*****/
                $item_update = \App\OrderDetail::where('order_id', $order_id)->where('status', '!=', 4)->update(['status' => 3]);

                /****update entry in order transaction******/
                $comment = GeneralFunctions::getOrderText('order_receive_buyer', $check_ord_detail->formatted_id);

                $transaction_arr = ['order_id' => $check_ord_detail->id, 'order_shop_id' => 0, 'order_detail_id' => 0, 'event' => 'delivery', 'comment' => $comment, 'updated_by' => 'buyer'];

                $update_transaction = \App\OrderTransaction::updateOrdTrans($transaction_arr);

                /**new status****/
                $main_status = \App\OrderStatusDesc::getStatusVal(3);

                $msg = Lang::get('order.item_receive_successfully');
                return ['status' => 'success', 'msg' => $msg, 'main_status' => $main_status];

            } catch (\Exception $e) {
                return ['status' => 'fail', 'msg' => $e->getMessage()];
            }
        } else {
            return ['status' => 'fail', 'msg' => 'invalid order'];
        }
    }

    public function receiveOrdItems(Request $request)
    {
        $ord_shop_id = $request->ord_shop_id ?? 0;

        $user_id = Auth::id();
        $check_ord_detail = \App\OrderShop::where(['id' => $ord_shop_id, 'user_id' => $user_id])->first();
        $up_main_status = $this->changeOrderStatus($check_ord_detail->order_id);

        if (!empty($check_ord_detail)) {

            try {

                $check_ord_detail->order_status = 3;
                $check_ord_detail->save();

                /****updating shop order item status*****/
                $item_update = \App\OrderDetail::where('order_shop_id', $ord_shop_id)->where('status', '!=', 4)->update(['status' => 3]);

                /****update entry in order transaction******/
                $comment = GeneralFunctions::getOrderText('all_item_receive_buyer', $check_ord_detail->shop_formatted_id);

                $transaction_arr = ['order_id' => $check_ord_detail->order_id, 'order_shop_id' => $check_ord_detail->id, 'order_detail_id' => 0, 'event' => 'delivery', 'comment' => $comment, 'updated_by' => 'buyer'];

                $update_transaction = \App\OrderTransaction::updateOrdTrans($transaction_arr);

                /*****updating main order and shop order status*****/
                $up_main_status = $this->changeOrderStatus($check_ord_detail->order_id);

                /**new status****/
                $item_status = \App\OrderStatusDesc::getStatusVal(3);
                $main_status = '';
                if ($up_main_status) {
                    $main_status = \App\OrderStatusDesc::getStatusVal($up_main_status);
                }

                $msg = Lang::get('order.item_receive_successfully');
                return ['status' => 'success', 'msg' => $msg, 'item_status' => $item_status, 'main_status' => $main_status];

            } catch (\Exception $e) {
                return ['status' => 'fail', 'msg' => $e->getMessage()];
            }

        } else {
            return ['status' => 'fail', 'msg' => 'invalid items'];
        }
    }

    protected function changeOrderStatus($order_id)
    {
        $order_details = \App\OrderShop::where('order_id', $order_id)->select('id', 'order_id', 'order_status')->get();

        $shop_status_arr = $shop_id_arr = [];
        foreach ($order_details as $key => $value) {
            $shop_status_arr[] = $value->order_status;
            if (!in_array($value->id, $shop_id_arr)) {
                $shop_id_arr[] = $value->id;
            }
        }

        if (count($shop_id_arr)) {
            if (in_array(2, $shop_status_arr) || in_array(1, $shop_status_arr)) {
                /****if any shop item pending then not change status******/
                $status_id = 0;
            } elseif (count(array_unique($shop_status_arr)) === 1 && end($shop_status_arr) == '4') {

                /**means if all shop items cancel then shop order will be cancelled**/
                $status_id = 4;
            } else {

                /**means if all shop items delivered then shop order will be complete**/
                $status_id = 3;
            }

            if ($status_id) {
                $update_shop = \App\Order::where('id', $order_id)->update(['order_status' => $status_id]);
            }
        }

        return $status_id;
    }

    public function trackOrder(Request $request)
    {
        return view('user.order.track_order');
    }


    public function reOrderToCart($orderId, Request $request)
    {
        try {
            DB::beginTransaction();
            $userId = Auth::id();
            $products = $request->input('products', []);

            if (!is_array($products)) {
                return response()->json([
                    'status' => 'fail',
                    'msg' => 'ไม่มีสินค้า'
                ]);
            }

            $products = collect($products)->keyBy('productId');
            $productIds = $products->keys()->filter(fn($key) => !is_null($key) && $key !== '')->unique()->values()->all();

            $orderHistory = \App\Order::where('formatted_id', $orderId)
                ->with('getOrderDetail', function ($q) use ($productIds) {
                    $q->whereIn('product_id', $productIds)
                        ->whereNotIn('status', ['9', '10', '11', '12']);
                })
                ->first();
            if (!$orderHistory || $orderHistory->getOrderDetail->isEmpty()) {
                return response()->json(['status' => 'fail', 'msg' => 'ไม่พบ Order']);
            }
            $carts = \App\Cart::where('user_id', $userId)->whereIn('product_id', $productIds)->get()->keyBy('product_id');
            $orderTemp = \App\OrdersTemp::where('user_id', $userId)->where('order_status', '0')->first();
            if (!$orderTemp) {
                $orderTempId = \App\Http\Controllers\ProductDetailController::insertOrder(['user_id' => $userId]);
                $orderTemp = \App\OrdersTemp::find($orderTempId);
            }

            $addToCartItems = collect();
            $itemNotfound = collect();
            foreach ($orderHistory->getOrderDetail as $orderDetail) {
                $product = $orderDetail->getPrd ?? null;
                $shop = $orderDetail->getShop ?? null;
                $cate = $orderDetail->getCat ?? null;
                $isSelected = true;

                if (!$orderDetail || !$product || !$shop) {
                    $itemNotfound->push($orderDetail);
                    continue;
                }

                $quantity = ceil($orderDetail->quantity ?? 1);
                $isInvalidStatus = ($product->status === '0' || $shop->status === '0' || $shop->shop_status === 'close');
                $isOutOfStock = ($product->stock === '0' && ($product->quantity <= 0 || $product->quantity < $product->min_order_qty));
                $isShortStock = ($product->quantity > $product->min_order_qty &&
                    (($carts[$product->id]->quantity ?? 0) + $quantity) < $product->min_order_qty);

                if ($isInvalidStatus || $isOutOfStock || $isShortStock) {
                    $isSelected = false;
                    if ($isShortStock) {
                        $quantity = $product->min_order_qty;
                    }
                }

                $addToCartItems->push($product);

                if (isset($carts[$product->id])) {
                    $cart = $carts[$product->id];
                    $totalQty = $cart->quantity + $quantity;

                    $cart->quantity = $totalQty;
                    $cart->original_price = $product->unit_price;
                    $cart->cart_price = $product->unit_price;
                    $cart->total_price = $product->unit_price * $totalQty;
                    $cart->is_selected = $isSelected;
                } else {
                    $cart = new \App\Cart;
                    $cart->order_id = $orderTemp->id;
                    $cart->user_id = $userId;
                    $cart->shop_id = $shop->id;
                    $cart->product_id = $product->id;
                    $cart->cat_id = $cate->id ?? null;
                    $cart->quantity = $quantity;
                    $cart->original_price = $product->unit_price;
                    $cart->cart_price = $product->unit_price;
                    $cart->total_price = $product->unit_price * $quantity;
                    $cart->cart_status = 0;
                    $cart->product_from = 'normal';
                    $cart->is_selected = $isSelected;
                }
                $cart->save();

            }

            // update order price
            if (!empty($orderTemp)) {
                \App\OrdersTemp::updateOrderPrice($orderTemp->id);
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'data' => [
                    'addToCartItems' => $addToCartItems,
                    'itemNotfound' => $itemNotfound
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'fail',
                'message' => 'Server error : ' . $e->getMessage(),
            ], 500);
        }
    }

}