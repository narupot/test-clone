<?php
namespace App\Http\Controllers\Admin\Transaction;

use App\Http\Controllers\MarketPlace;
use App\Helpers\GeneralFunctions;
use App\Helpers\CustomHelpers;

use App\Helpers\EmailHelpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use Auth;
use App\Order;
use App\OrderDetail;
use App\OrderShop;
use App\User;
use App\Product;
use Lang;
use Config;
use Excel;
use PDF;
use App\ShippingAddress;

use Carbon\Carbon;
use Illuminate\Support\Facades\View;


class OrderController extends MarketPlace
{
    public function __construct()
    {
        $this->middleware('admin.user');
    }

    /**
     * Print shipping receipt for orders with status = 3, grouped by pickup_time
     */
    public function printShippingReceipt(Request $request)
    {
        $deliveryDate = $request->input('delivery_date', Carbon::today()->format('Y-m-d'));

        $orders = \App\Order::where('order_status', 3)
            ->whereDate('pickup_time', $deliveryDate)
            ->where(function ($query) {
                $query->where('shipping_method', 3)
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('shipping_method', 1)
                            ->where('transaction_fee', '>', 0);
                    });
            })
            // ->orderBy('pickup_time', 'asc')
            ->orderBy('shipping_rept_no', 'asc')  // เพิ่มการเรียงตาม shipping_rept_no
            ->orderBy('id', 'asc')                // เพิ่มการเรียงตาม id
            ->get();



        // 

        return view('admin.transaction.printShippingReceipt', [
            'ordersByPickupTime' => $orders,
            'deliveryDate' => $deliveryDate
        ]);
    }

    public function shippingReceiptBulkPdf(Request $request)
    {
        $deliveryDate = $request->input('delivery_date', Carbon::today()->format('Y-m-d'));

        $orders = \App\Order::where('order_status', 3)
            ->whereDate('pickup_time', $deliveryDate)
            ->where(function ($query) {
                $query->where('shipping_method', 3)
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('shipping_method', 1)
                            ->where('transaction_fee', '>', 0);
                    });
            })
            // ->orderBy('pickup_time', 'asc')
            ->orderBy('shipping_rept_no', 'asc')  // เพิ่มการเรียงตาม shipping_rept_no
            ->orderBy('id', 'asc')                // เพิ่มการเรียงตาม id
            ->get();

        foreach ($orders as $order) {
            // กระจายข้อมูลจาก order_json
            $orderJson = json_decode($order->order_json, true);

            // กำหนดค่าจาก JSON ไปยัง properties ใหม่ของ order object
            if ($orderJson) {
                // Shipping address
                if (isset($orderJson['shipping_address'])) {
                    $shipping = $orderJson['shipping_address'];
                    $order->shipping_title = $shipping['title'] ?? '';
                    $order->shipping_first_name = $shipping['first_name'] ?? '';
                    $order->shipping_last_name = $shipping['last_name'] ?? '';
                    $order->shipping_province = $shipping['provice'] ?? ''; // สังเกตว่ามีการสะกดผิดเป็น provice
                    $order->shipping_district = $shipping['district'] ?? '';
                    $order->shipping_sub_district = $shipping['sub_district'] ?? '';
                    $order->shipping_address = $shipping['address'] ?? '';
                    $order->shipping_road = $shipping['road'] ?? '';
                    $order->shipping_zip_code = $shipping['zip_code'] ?? '';
                    $order->shipping_phone = $shipping['ph_number'] ?? '';
                    $order->shipping_company_name = $shipping['company_name'] ?? '';
                }

                // Billing address (ถ้าต้องการ)
                if (isset($orderJson['billing_address'])) {
                    $billing = $orderJson['billing_address'];
                    $order->billing_title = $billing['title'] ?? '';
                    $order->billing_first_name = $billing['first_name'] ?? '';
                    $order->billing_last_name = $billing['last_name'] ?? '';
                    $order->billing_province = $billing['provice'] ?? ''; // สังเกตว่ามีการสะกดผิดเป็น provice
                    $order->billing_district = $billing['district'] ?? '';
                    $order->billing_sub_district = $billing['sub_district'] ?? '';
                    $order->billing_address = $billing['address'] ?? '';
                    $order->billing_road = $billing['road'] ?? '';
                    $order->billing_zip_code = $billing['zip_code'] ?? '';
                    $order->billing_phone = $billing['ph_number'] ?? '';
                    $order->billing_company_name = $billing['company_name'] ?? '';
                    $order->billing_branch = $billing['branch'] ?? '';
                    $order->billing_tax_id = $billing['tax_id'] ?? '';
                    $order->billing_company_address = $billing['company_address'] ?? '';
                }

                // Total logistic cost
                $order->total_logistic_cost = $orderJson['total_logistic_cost'] ?? 0;
            }

        }
        // ถ้าใช้ Blade HTML แสดงตรง ๆ (Preview)
        // return view('admin.transaction.shippingReceiptBulkPdf', [
        //     'orders' => $orders,
        //     'deliveryDate' => $deliveryDate
        // ]);

        // ถ้าจะ generate PDF (แนะนำ)


        $pdf = Pdf::loadView(
            'admin.transaction.shippingReceiptBulkPdf',
            [
                'orders' => $orders,
                'deliveryDate' => $deliveryDate
            ],
            [],
            [
                'format' => 'A5',
                'orientation' => 'landscape',
            ]
        );
        return $pdf->stream('shipping-receipt-' . $deliveryDate . '.pdf');

    }

    public function index()
    {

        $permission = $this->checkUrlPermission('main_order');
        if ($permission === true) {
            $filter = $this->getFilter('main_order');
            $order_status = \App\OrderStatusDesc::where('lang_id', session('default_lang'))->select('order_status_id', 'status')->get();
            $status_arr = [];
            foreach ($order_status as $key => $value) {
                $status_arr[] = [$value->order_status_id => $value->status];
            }


            $shipping_method = [['1' => Lang::get('checkout.pick_up_at_center')], ['2' => Lang::get('checkout.pick_up_at_the_store')], ['3' => Lang::get('checkout.delivery_at_the_address')]];


            return view('admin.transaction.listOrder', ['filter' => $filter, 'ord_status' => json_encode($status_arr), 'shipping_method' => json_encode($shipping_method)]);
        }
    }

    public function listOrderData(Request $request)
    {
        $perpage = !empty($request->pq_rpp) ? $request->pq_rpp : getPagination('limit');
        $request->page = $current_page = !empty($request->pq_curpage) ? $request->pq_curpage : 0;
        $qsh = '';
        $start_index = ($current_page - 1) * $perpage;
        //dd($perpage,$request->page);

        $order_by = 'id';
        $order_by_val = 'desc';
        if (isset($request->pq_sort)) {
            $sort_data = jsonDecodeArr($request->pq_sort);
            $order_by = $sort_data[0]['dataIndx'];
            $order_by_val = ($sort_data[0]['dir'] == 'up') ? 'asc' : 'desc';

            if ($order_by == 'end_shopping_date_time') {
                $order_by = 'end_shopping_date';
            }
        }

        try {

            //Order::select('*');
            $prefix = DB::getTablePrefix();
            // $query = DB::table(with(new Order)->getTable().' as o')->select("o.*", DB::raw("(SELECT sum(total_weight*quantity) FROM ".$prefix.with(new OrderDetail)->getTable()." WHERE order_id = ".$prefix."o.id) as total_weight"));
            $query = DB::table(with(new Order)->getTable() . ' as o')
                ->select(
                    'o.*',
                    DB::raw("(SELECT SUM(total_weight * quantity) 
                                FROM {$prefix}" . with(new OrderDetail)->getTable() . " 
                                WHERE order_id = {$prefix}o.id) AS total_weight"),
                    DB::raw("(SELECT ot.updated_by 
                                FROM {$prefix}order_transaction ot 
                                WHERE ot.order_id = {$prefix}o.id                            
                                ORDER BY ot.id DESC LIMIT 1) AS updated_by"),
                    'odc.discount_code'

                )
                ->leftJoin("order_discount_code as odc", 'odc.order_id', '=', 'o.id');

            //OrderDetail::select(DB::raw('sum(total_weight*quantity) as sum_total_weight') )->where('order_id', $value->id)->value('sum_total_weight');


            if (isset($request->pq_filter)) {
                $filter_req = json_decode($request->pq_filter, true);
                if (!empty($filter_req['data'])) {
                    $filter_arr = $filter_req['data'];
                    foreach ($filter_arr as $fkey => $fvalue) {
                        $searchval = $fvalue['value'];
                        switch ($fvalue['dataIndx']) {
                            case 'formatted_id':
                                $query->where('formatted_id', 'like', '%' . $searchval . '%');
                                break;
                            case 'user_id':
                                $query->where('user_id', 'like', '%' . $searchval . '%');
                                break;
                            case 'user_name':
                                $query->where('user_name', 'like', '%' . $searchval . '%');
                                break;
                            case 'admin_remark':
                                $query->where('admin_remark', 'like', '%' . $searchval . '%');
                                break;
                            case 'total_final_price':
                                $query->where('total_final_price', '=', $searchval);
                                break;
                            case 'total_core_cost':
                                $query->where('total_core_cost', '=', $searchval);
                                break;
                            case 'dcc_shipping_discount':
                                $query->where('dcc_shipping_discount', '=', $searchval);
                                break;
                            case 'transaction_fee':
                                $query->where('transaction_fee', '=', $searchval);
                                break;
                            case 'dcc_purchase_discount':
                                $query->where('dcc_purchase_discount', '=', $searchval);
                                break;
                            case 'order_status':
                                $query->whereIn('order_status', $searchval);
                                break;
                            case 'time':

                                $query->where(function ($query) use ($searchval) {
                                    $count = 0;
                                    foreach ($searchval as $searchdata) {
                                        $count++;
                                        if ($count == 1) {
                                            $query = $query->where('pickup_time', 'like', '%' . $searchdata . '%');
                                        } else {
                                            $query = $query->orwhere('pickup_time', 'like', '%' . $searchdata . '%');

                                        }
                                    }

                                });
                                break;
                            case 'dob':
                                $from_date = $fvalue['value'] ?? '';
                                $to_date = $fvalue['value2'] ?? '';
                                createDateFilter($query, 'u.dob', $from_date, $to_date);
                                break;
                            case 'end_shopping_date_time':
                                $from_date = $fvalue['value'] ?? '';
                                $to_date = $fvalue['value2'] ?? '';
                                createDateFilter($query, 'end_shopping_date', $from_date, $to_date);
                                break;
                            case 'end_shopping_date':
                                $from_date = $fvalue['value'] ?? '';
                                $to_date = $fvalue['value2'] ?? '';
                                createDateFilter($query, 'end_shopping_date', $from_date, $to_date);
                                break;
                            case 'pickup_time':
                                $from_date = $fvalue['value'] ?? '';
                                $to_date = $fvalue['value2'] ?? '';
                                createDateFilter($query, 'pickup_time', $from_date, $to_date);
                                break;
                            case 'shipping_method':
                                $query->whereIn('shipping_method', $searchval);
                                break;
                            case 'total_weight':
                                $query->where(DB::raw("(SELECT sum(total_weight*quantity) FROM " . $prefix . with(new OrderDetail)->getTable() . " WHERE order_id = " . $prefix . "o.id)"), '=', $searchval);
                                break;
                            case 'payment_slug':
                                $paymentLabelMapF = [
                                    'QR พร้อมเพย์' => 'beam-qr',
                                    'บัตรเครดิต' => 'beam-credit',
                                    'Mobile Banking' => 'beam-banking',
                                    'E-Wallet' => 'beam-ewallet',
                                    'ลูกค้าเครดิต 1 วัน' => 'credit_acc1',
                                    'ลูกค้าเครดิต 7 วัน' => 'credit_acc7',
                                    'ลูกค้าเครดิต' => 'credit_acc',
                                    'โอนตรง' => 'direct_transfer',
                                ];

                                $mappedValues = [];
                                foreach ((array) $searchval as $val) {
                                    if (isset($paymentLabelMapF[$val])) {
                                        $mappedValues[] = $paymentLabelMapF[$val];
                                    }
                                }
                                if (!empty($mappedValues)) {
                                    $query->whereIn('payment_slug', $mappedValues);
                                }
                                break;

                            case 'updated_by':
                                $values = (array) $searchval;
                                $query->havingRaw(
                                    collect($values)
                                        ->map(fn($v) => "updated_by LIKE ?")
                                        ->implode(' OR '),
                                    collect($values)->map(fn($v) => "%{$v}%")->toArray()
                                );
                                break;
                            case 'discount_code':
                                $query->where('discount_code', 'like', '%' . $searchval . '%');
                                break;
                        }

                    }
                }
            }

            //dd( $query->toSql());
            $response = $query->orderBy($order_by, $order_by_val)->paginate($perpage, ['*'], 'page', $current_page);
            $totrec = $response->total();

            // ดึงข้อมูล payment options จากฐานข้อมูลแทนการ hard code
            $paymentOptions = \App\PaymentOption::with('paymentOptName')->get();
            $paymentLabelMap = [];
            foreach ($paymentOptions as $paymentOption) {
                $paymentLabelMap[$paymentOption->slug] = $paymentOption->paymentOptName->payment_option_name ?? $paymentOption->slug;
            }

            //dd($response);
            if ($start_index >= $totrec) {
                $current_page = ceil($totrec / $perpage);

                $response = $query->orderBy($order_by, $order_by_val)->paginate($perpage, ['*'], 'page', $current_page);
            }

            foreach ($response as $key => $value) {

                $response[$key]->end_shopping_date_time = $value->end_shopping_date;
                $response[$key]->end_shopping_date = $value->end_shopping_date ? date('Y-m-d', strtotime($value->end_shopping_date)) : null;
                $response[$key]->pickup_time = $value->pickup_time ? date('Y-m-d H:i:s', strtotime($value->pickup_time)) : null;
                $response[$key]->time = $value->pickup_time ? date('H:i:s', strtotime($value->pickup_time)) : null;

                $response[$key]->total_final_price = number_format((float) $value->total_final_price, 2, '.', ',');
                $response[$key]->total_core_cost = number_format((float) $value->total_core_cost, 2, '.', ',');
                $response[$key]->dcc_shipping_discount = number_format((float) $value->dcc_shipping_discount, 2, '.', ',');
                $response[$key]->dcc_purchase_discount = number_format((float) $value->dcc_purchase_discount, 2, '.', ',');
                // Calculate delivery fee: total_shipping_cost - dcc_shipping_discount
                $delivery_fee = $value->total_shipping_cost;
                $response[$key]->delivery_fee = number_format($delivery_fee, 2, '.', ',');
                // Format transaction fee
                $response[$key]->transaction_fee = number_format((float) ($value->transaction_fee ?? 0), 2, '.', ',');

                $response[$key]->shipping_method = GeneralFunctions::getShippingMethod($value->shipping_method);
                $response[$key]->payment_slug = $paymentLabelMap[$value->payment_slug] ?? $value->payment_slug;
                $response[$key]->detail_url = action('Admin\Transaction\OrderController@orderDetail', $value->formatted_id);
                $response[$key]->payment_status = ($value->payment_status == 1) ? 'c-tot' : '';
                $response[$key]->discount_code = $value->discount_code ?? '-';
                //$to_weight = OrderDetail::select(DB::raw('sum(total_weight*quantity) as sum_total_weight') )->where('order_id', $value->id)->value('sum_total_weight');
                //dd($to_weight);
                //$response[$key]->total_weight = $to_weight;


            }

            /***save filter****/
            $this->setFilter('main_order', $request);

        } catch (QueryException $e) {
            $response = ['status' => 'fail', 'msg' => $e->getMessage()];
        }


        return $response;
    }

    public function orderDetailTest(Request $request)
    {

        $formatted_id = $request->oid;
        $main_order = Order::where('formatted_id', $formatted_id)->with(['getUser', 'getOrderStatus'])->first();
        // dd($main_order,$formatted_id);
        if (empty($main_order)) {
            abort(404);
        }

        $order_shop = OrderShop::where('order_id', $main_order->id)->with(['getOrderStatus'])->get();
        if (count($order_shop)) {
            foreach ($order_shop as $key => $value) {
                $order_detail = OrderDetail::getShopOrderDetail('', $value->id);
                $order_shop[$key]->details = $order_detail;
            }
        }
        $main_order->tot_shop = count($order_shop);

        //dd($order_shop);
        $transaction = \App\OrderTransaction::where('order_id', $main_order->id)->orderBy('id')->get();

        $main_order->pickup_time = null;
        if ($main_order->id > 0) {
            $order_info = Order::where('id', $main_order->id)->first();
            if ($order_info) {
                $main_order->pickup_time = $order_info->pickup_time;
            }
        }

        /* Start:: If Product Detail Not Available in Order Details */
        if (count($order_shop)) {
            foreach ($order_shop as $skey => $shop_ord_val) {
                foreach ($shop_ord_val->details as $key => $val) {
                    if ($val->description == '' || $val->description == null) {
                        $productDetail = \App\Product::getProductDetailAll($val->sku);
                        $order_shop[$skey]->details[$key]->description = isset($productDetail->productDesc) ? $productDetail->productDesc->description : "";
                    }
                }
            }
        }
        /* Start:: If Product Detail Not Available in Order Details */
        /* Start := Get Pickup Time Data */
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


            if ($delivery_time->delivery_type != 'shop_address') {
                foreach ($time_slot as $tkey => $tvalue) {
                    $pickup_dt = date('Y-m-d', strtotime($main_order->pickup_time));
                    $c_arr[] = ['key' => $tvalue, 'val' => $pickup_dt . ' 0' . $tvalue . ':00'];
                    /*
                    if($delivery_time->delivery_type=='buyer_address'){
                        $add_two = ($tvalue+3);
                        $add_day = 1;
                        $ndate = date('Y-m-d', strtotime(' +'.$add_day.' day'));
                        $val_show = $tvalue;

                        if($tvalue >= $cur_time_start){
                            if($add_two>=24){
                                $add_two = $add_two-24;
                                $val_show = $tvalue.':00';
                                $ndate = date('Y-m-d', strtotime(' +1 day'));
                                $expdate = explode('-', $ndate);
                                $c_arr[] = ['key'=>$ndate.' '.$val_show.':00','val'=>$ndate.' '.$val_show.':00'];
                            }else{
                                $c_arr[] = ['key'=>$tvalue,'val'=>$val_show.':00'];
                            }

                        }else{
                            if($add_two>=24){
                                $ndate = date('Y-m-d', strtotime(' +2 day'));
                                $add_two = $add_two-24;
                                $val_show = $tvalue;
                            }
                            $expdate = explode('-', $ndate);
                            $n_arr[] = ['key'=>$ndate.' '.$val_show.':00','val'=>$ndate.' '.$val_show.':00'];
                        }
                    }else{
                        if($tvalue >= $cur_time_start){
                            $c_arr[] = ['key'=>$tvalue,'val'=>$tvalue.':00'];
                        }else{
                            $ndate = date('Y-m-d', strtotime(' +1 day'));
                            $expdate = explode('-', $ndate);
                            $n_arr[] = ['key'=>$ndate.' '.$val_show.':00','val'=>$ndate.' '.$tvalue.':00'];
                        }
                    }
                    */
                }
            } else {
                $j = 0;

                for ($i = 1; $i <= 12; $i++) {
                    if ($i == 1) {
                        $next_time = $cur_time_start;
                    } else {
                        $next_time = $next_time + 1;
                    }

                    if ($next_time >= 24) {
                        $ndate = date('Y-m-d', strtotime(' +1 day'));
                        $expdate = explode('-', $ndate);
                        $n_arr[] = ['key' => $ndate . ' ' . $j . ':00', 'val' => $ndate . ' ' . $j . ':00'];
                        // $n_arr[] = ['key'=>$j.':00','val'=>$j.':00'];
                        $j++;
                    } else {
                        $c_arr[] = ['key' => $next_time, 'val' => $next_time . ':00'];
                    }
                }
            }

            $time_arr = array_merge($c_arr, $n_arr);
            $delivery_time_arr[$delivery_time->delivery_type] = $time_arr;
        }
        $pickup_time_arr = [];
        if ($main_order->shipping_method == 1 && isset($delivery_time_arr['pickup_center'])) {
            $pickup_time_arr = $delivery_time_arr['pickup_center'];
        } elseif ($main_order->shipping_method == 2 && isset($delivery_time_arr['shop_address'])) {
            $pickup_time_arr = $delivery_time_arr['shop_address'];
        } else {
            $pickup_time_arr = $delivery_time_arr['buyer_address'];
        }
        /*Stop := Get Pickup time Data*/
        /* tong J start 250125*/
        $user_address = [];
        $billing_address = $shipping_address = $ship_province_str = '';
        $user_address = ShippingAddress::getUserAddress($main_order->user_id);
        foreach ($user_address as $address) {
            if ($address->is_default == '1') {
                if ($address->address_type == '1') {
                    $shipping_address = $address;
                } elseif ($address->address_type == '2') {
                    $billing_address = $address;
                } elseif ($address->address_type == '3') {
                    $shipping_address = $billing_address = $address;
                }
            }
        }
        /*tong J start 250125 */

        //  dd($pickup_time_arr);

        return view('admin.transaction.mainOrddetailTest', compact('user_address', 'shipping_address'), ['main_order' => $main_order, 'order_shop' => $order_shop, 'transaction' => $transaction, 'delivery_time_arr' => $delivery_time_arr, 'pickup_time_arr' => $pickup_time_arr]);
    }
    /* tongJ Test */
    public function orderDetail(Request $request)
    {
        $formatted_id = $request->oid;
        $main_order = Order::where('formatted_id', $formatted_id)->with(['getUser', 'getOrderStatus'])->first();

        if (empty($main_order)) {
            abort(404);
        }

        $order_shop = OrderShop::where('order_id', $main_order->id)->with(['getOrderStatus'])->get();
        if (count($order_shop)) {
            foreach ($order_shop as $key => $value) {
                $order_detail = OrderDetail::getShopOrderDetail('', $value->id);
                $order_shop[$key]->details = $order_detail;
            }
        }
        $main_order->tot_shop = count($order_shop);

        $transaction = \App\OrderTransaction::where('order_id', $main_order->id)->orderBy('id')->get();

        if (count($order_shop)) {
            foreach ($order_shop as $skey => $shop_ord_val) {
                foreach ($shop_ord_val->details as $key => $val) {
                    if (empty($val->description)) {
                        $productDetail = \App\Product::getProductDetail($val->sku);
                        $order_shop[$skey]->details[$key]->description = isset($productDetail->productDesc) ? $productDetail->productDesc->description : "";
                    }
                }
            }
        }
        $delivery_time_arr = [
            'pickup_center' => [],
            'buyer_address' => [],
            'shop_address'  => []
        ];

        $zip_code = null;
        $cleanSub = '';
        $cleanDist = '';

        if (empty($main_order->shipping_address_id) || $main_order->shipping_address_id == 0) {
            $orderJson = json_decode($main_order->order_json, true);
            $addrJson = $orderJson['shipping_address'] ?? [];
            $zip_code = $addrJson['zip_code'] ?? null;
            $cleanSub = str_replace(['แขวง', 'ตำบล'], '', $addrJson['sub_district'] ?? '');
            $cleanDist = str_replace(['เขต', 'อำเภอ'], '', $addrJson['city_district'] ?? '');
        } else {
            $orderAddress = \App\ShippingAddress::find($main_order->shipping_address_id);
            if ($orderAddress) {
                $zip_code = $orderAddress->zip_code;
                $cleanSub = str_replace(['แขวง', 'ตำบล'], '', $orderAddress->sub_district);
                $cleanDist = str_replace(['เขต', 'อำเภอ'], '', $orderAddress->city_district);
            }
        }

        $now = \Carbon\Carbon::now();

        $regCenter = \App\DeliveryRegion::where('reg_type', 2)->where('status', 1)->first();
        if ($regCenter) {
            $centerSlots = \App\DeliveryTimeSlot::where('reg_id', $regCenter->reg_id)->where('status', 1)->get();
            foreach ($centerSlots as $slot) {
                $delivery_time_arr['pickup_center'][] = $this->processSlotForDisplay($slot, $now);
            }
        }

        if ($zip_code) {
            $regBuyer = \DB::table('delivery_region_detail as detail')
                ->join('master_sub_districts as ms_sub', 'detail.subdistrict_id', '=', 'ms_sub.id')
                ->join('master_districts as ms_dist', 'ms_sub.district_id', '=', 'ms_dist.id')
                ->where('detail.postcode', $zip_code)
                ->where('detail.status', 1)
                ->where(function($query) use ($cleanSub, $cleanDist) {
                    if (!empty($cleanSub)) $query->where('ms_sub.name_th', 'LIKE', "%{$cleanSub}%");
                    if (!empty($cleanDist)) $query->where('ms_dist.name_th', 'LIKE', "%{$cleanDist}%");
                })
                ->select('detail.region_id')->first();

            if (!$regBuyer) {
                $regBuyer = \DB::table('delivery_region_detail')->where('postcode', $zip_code)->where('status', 1)->first();
            }

            if ($regBuyer) {
                $buyerSlots = \App\DeliveryTimeSlot::where('reg_id', $regBuyer->region_id)->where('status', 1)->get();
                foreach ($buyerSlots as $slot) {
                    $formatted = $this->processSlotForDisplay($slot, $now);
                    $delivery_time_arr['buyer_address'][] = $formatted;
                    $delivery_time_arr['shop_address'][] = $formatted;
                }
            }
        }

        $now = \Carbon\Carbon::now();
        $pickup_time_arr = [];

        if (!empty($main_order->del_t_s_id)) {
            $currentSlot = \App\DeliveryTimeSlot::find($main_order->del_t_s_id);
            
            if ($currentSlot) {
                $slots = \App\DeliveryTimeSlot::where('reg_id', $currentSlot->reg_id)
                            ->where('status', 1)
                            ->orderBy('order_cutoff_time', 'asc')
                            ->get();

                foreach ($slots as $slot) {
                    $pickup_time_arr[] = $this->processSlotForDisplay($slot, $now);
                }
            }
        } 
        if (empty($pickup_time_arr)) {
                $method = (string)$main_order->shipping_method;
                if ($method === '1') {
                    $pickup_time_arr = $delivery_time_arr['pickup_center'];
                } elseif ($method === '2') {
                    $pickup_time_arr = $delivery_time_arr['shop_address'];
                } else {
                    $pickup_time_arr = $delivery_time_arr['buyer_address'];
                }
        }

        $user_address = \App\ShippingAddress::getUserAddress($main_order->user_id);
        $billing_address = $shipping_address = null;
        foreach ($user_address as $address) {
            if ($address->is_default == '1') {
                if ($address->address_type == '1') $shipping_address = $address;
                elseif ($address->address_type == '2') $billing_address = $address;
                elseif ($address->address_type == '3') $shipping_address = $billing_address = $address;
            }
        }

        $payment_options = \App\PaymentOption::where(['status' => '1', 'payment_type' => '1'])
            ->where('slug', '!=', 'credit')
            ->with(['paymentOptName', 'transactionFeeConfig'])->get();

        return view('admin.transaction.mainOrddetail', compact('user_address', 'shipping_address'), [
            'main_order' => $main_order, 
            'order_shop' => $order_shop, 
            'transaction' => $transaction, 
            'delivery_time_arr' => $delivery_time_arr, 
            'pickup_time_arr' => $pickup_time_arr, 
            'payment_options' => $payment_options
        ]);
    }

    private function processSlotForDisplay($slot, $now)
    {
        $plusDays = (int)($slot->deli_plus_days ?? 0);
        $cutoff = $slot->order_cutoff_time;

        if ($cutoff && $now->format('H:i:s') > $cutoff) {
            $plusDays += 1;
        }

        $targetDate = \Carbon\Carbon::now()->addDays($plusDays);

        $startTime = date('H:i', strtotime($cutoff) + ($slot->start_deli_time * 60));
        $endTime   = date('H:i', strtotime($cutoff) + ($slot->end_deli_time * 60));

        return [
            'key'             => $slot->del_t_s_id,
            'val'             => $startTime . " - " . $endTime ,
            'calculated_date' => $targetDate->format('Y-m-d'),
        ];
    }

    /**
     * คำนวณเวลาจัดส่ง (Format HH:mm)
     * @param string $cutoff เวลา cutoff (HH:mm:ss)
     * @param string $targetTime เวลาเป้าหมาย (HH:mm:ss)
     * @return string
     */
    private function calcRelativeTime($cutoff, $targetTime)
    {
        if (empty($targetTime)) {
            return '00:00';
        }

        // ตัดวินาทีออกให้เหลือแค่ HH:mm
        return \Carbon\Carbon::parse($targetTime)->format('H:i');
    }
    /* end tongJ Test */
    /*********** for check create order json ************/
    public function orderJson($order_id)
    {

        $main_order = Order::where('id', 35)->first();

        if (empty($main_order)) {
            abort(404);
        }

        $order_shop = OrderShop::where('order_id', $main_order->id)->get()->toArray();
        if (count($order_shop)) {
            foreach ($order_shop as $key => $value) {

                unset($order_shop[$key]['shipping_method'], $order_shop[$key]['total_discount'], $order_shop[$key]['total_final_weight'], $order_shop[$key]['seller_status'], $order_shop[$key]['shop_json'], $order_shop[$key]['order_json']);

                $order_detail = OrderDetail::where(['order_shop_id' => $value['id']])->get()->toArray();

                $line = 0;
                foreach ($order_detail as $dkey => $dvalue) {

                    $detail_arr = json_decode($dvalue['order_detail_json'], true);

                    $detail_arr['name'] = $detail_arr['name'][0] ?? '';
                    $detail_arr['package'] = $detail_arr['package'][0] ?? '';
                    $detail_arr['shop_name'] = $detail_arr['shop_name'][0] ?? '';
                    $detail_arr['payment_method'] = $detail_arr['payment_method'][0] ?? '';

                    $order_detail[$dkey]['item_detail_json'] = $detail_arr;

                    unset($order_detail[$dkey]['order_detail_json'], $order_detail[$dkey]['user_id'], $order_detail[$dkey]['shop_id'], $order_detail[$dkey]['order_id'], $order_detail[$dkey]['created_at'], $order_detail[$dkey]['updated_at']);

                    $arr = [];
                    $arr = ['line_no' => ++$line] + $order_detail[$dkey];
                    $order_detail[$dkey] = $arr;

                }

                $order_shop[$key]['order_detail'] = $order_detail;
            }
        }

        if ($main_order->shipping_method == 2) {
            $main_ord_json = json_decode($main_order->order_json, true);
            $ord_json_arr = [];
            if (count($main_ord_json)) {
                foreach ($main_ord_json as $key => $value) {
                    $value['shop_name'] = $value['shop_name'][0] ?? '';
                    $ord_json_arr[] = $value;
                }
            }
            $main_order->order_json = $ord_json_arr;
        } else {
            $json_arr = json_decode($main_order->order_json, true);
            $key_arr = ['first_name', 'last_name', 'provice', 'district', 'address', 'road', 'zip_code', 'ph_number', 'company_name', 'branch', 'tax_id', 'company_address', 'name', 'location', 'contact', 'estimate'];
            $arr_json = [];
            if ($main_order->shipping_method == 3) {
                foreach ($key_arr as $key => $value) {
                    $arr_json[$value] = $json_arr['shipping_address'][$value] ?? '';
                }
            } else {
                foreach ($key_arr as $key => $value) {
                    $arr_json[$value] = $json_arr[$value] ?? '';
                }
            }

            $main_order->order_json = $arr_json;
        }

        $shop_order_arr = $order_shop;

        $main_order->api_date = date('Y-m-d H:i:s');
        $main_order->order_shop_json = $shop_order_arr;
        $making_json = $main_order->toArray();
        //$a = array_map('strval', $making_json);
        /**********/
        //unset($making_json['total_discount']);

        dd($making_json, json_encode($making_json), json_encode($making_json, JSON_FORCE_OBJECT));
        return view('admin.transaction.mainOrddetail', ['main_order' => $main_order, 'order_shop' => $order_shop]);
    }/*********/

    public function ordChangeItemStatus(Request $request)
    {
        $order_detail_id = $request->order_detail_id ?? 0;
        $type = $request->type ?? '';
        $order_detail = OrderDetail::where('id', $order_detail_id)->first();

        if ($order_detail_id && $order_detail && $type) {

            if ($type == 'cancel') {
                $status = 4;
                $comment = GeneralFunctions::getOrderText('item_cancel', $order_detail->category_name);
                $msg = Lang::get('admin_order.item_cancelled');
            } elseif ($type == 'receive') {
                $status = 5;
                $comment = GeneralFunctions::getOrderText('item_center_receive', $order_detail->category_name);
                $msg = Lang::get('admin_order.item_center_received');
            }
            /* tong j start 250315 */ elseif ($type == 'updateQuantity') {
                $item_quantity = $request->item_quantity ?? 0;
                if ($item_quantity == 0 || $item_quantity > $order_detail->quantity) {
                    return ['status' => 'fail', 'msg' => 'จำนวนสินค้าน้อยกว่าจำนวนสั่งซื้อ'];
                }
                $order_detail->quantity = $item_quantity;
                $order_detail->total_price = $item_quantity * $order_detail->last_price;
                $status = $order_detail->status;
            }
            /* tong j stop 250315 */
            $order_detail->status = $status;
            $order_detail->save();

            /****update entry in order transaction******/
            $transaction_arr = ['order_id' => $order_detail->order_id, 'order_shop_id' => $order_detail->order_shop_id, 'order_detail_id' => $order_detail->id, 'event' => 'delivery', 'comment' => $comment, 'updated_by' => 'admin'];

            $update_transaction = \App\OrderTransaction::updateOrdTrans($transaction_arr);

            /*****function change order status*******/
            $change_status = \App\Order::updateOrdStatus($order_detail->order_id);

            /**new status****/
            $item_status = \App\OrderStatusDesc::getStatusVal($status);

            /* tong j start 250315 - FIX TOTAL PRICE By Pump*/

            //$total_final_price = OrderDetail::where('id', $order_detail_id)
            $total_final_price = OrderDetail::where('order_shop_id', $order_detail->order_shop_id)
                ->whereIn('status', [1, 2, 3, 5, 6, 7, 8]) // ไม่รวม item ที่ cancel
                ->sum('total_price');

            $update_total_price = \App\OrderShop::where('id', $order_detail->order_shop_id)->first();
            $update_total_price->total_final_price = $total_final_price;
            $update_total_price->total_core_cost = $total_final_price;  
            $update_total_price->save();
            /* tong j stop 250315 */


            // ===== FIX MAIN ORDER TOTAL PRICE By Pump =====
            // 1. คำนวณยอดรวมสินค้าที่ไม่ได้ถูกยกเลิก (Subtotal)
            $current_product_subtotal = OrderDetail::where('order_id', $order_detail->order_id)
                ->whereIn('status', [1, 2, 3, 5, 6, 7, 8])
                ->sum('total_price');

            // 2. ดึงข้อมูล Order (Eager Load criteria เพื่อลด Query)
            $order = \App\Order::with('orderDiscountCodes.criteria')
                ->where('id', $order_detail->order_id)
                ->whereIn('payment_slug', ['credit_acc', 'credit_acc7', 'credit_acc1'])
                ->first();

            if ($order) {
                $total_purchase_discount = 0;
                $total_shipping_discount = 0;
                
                // ดึงค่าขนส่งจากออเดอร์ (ใช้สำหรับกรณี Free Shipping)
                $order_shipping_cost = $order->dcc_shipping_discount ?? 0;

                foreach ($order->orderDiscountCodes as $appliedCode) {
                    $criteria = $appliedCode->criteria;
                    if (!$criteria) continue;

                    $temp_purchase_discount = 0;
                    $temp_shipping_discount = 0;

                    // ตรวจสอบยอดซื้อขั้นต่ำก่อนให้ส่วนลด
                    if ($current_product_subtotal >= $criteria->purchase_amount_threshold) {
                        
                        // --- Logic: ส่วนลดค่าจัดส่ง ---
                        if ($criteria->is_free_shipping == 1) {
                            // ถ้าเป็น Free Shipping ให้ใช้ยอดค่าส่งของ Order นั้นๆ เป็นส่วนลด
                            $temp_shipping_discount = $order_shipping_cost;
                        } elseif ($criteria->discount_target == 'shipping') {
                            if ($criteria->discount_type == 'percentage') {
                                $temp_shipping_discount = ($order_shipping_cost * $criteria->discount_value) / 100;
                                if ($criteria->max_discount > 0) {
                                    $temp_shipping_discount = min($temp_shipping_discount, $criteria->max_discount);
                                }
                            } else {
                                $temp_shipping_discount = $criteria->discount_value;
                            }
                        }

                        // --- Logic: ส่วนลดค่าสินค้า (Purchase) ---
                        if ($criteria->discount_target == 'purchase') {
                            if ($criteria->discount_type == 'percentage') {
                                $temp_purchase_discount = ($current_product_subtotal * $criteria->discount_value) / 100;
                                if ($criteria->max_discount > 0) {
                                    $temp_purchase_discount = min($temp_purchase_discount, $criteria->max_discount);
                                }
                            } else {
                                $temp_purchase_discount = $criteria->discount_value;
                            }
                        }
                    }

                    // 3. อัปเดตข้อมูลลงตาราง Mapping (smm_order_discount_code)
                    $appliedCode->purchase_discount_amount = $temp_purchase_discount;
                    $appliedCode->shipping_discount_amount = $temp_shipping_discount;
                    $appliedCode->save();

                    $total_purchase_discount += $temp_purchase_discount;
                    $total_shipping_discount += $temp_shipping_discount;
                }

                // 4. สรุปยอดและอัปเดตลงตาราง Order
                $total_all_discount = $total_purchase_discount + $total_shipping_discount;
                $summary_net_total = max(0, $current_product_subtotal - $total_all_discount);

                $order->total_final_price       = $summary_net_total + $order->total_shipping_cost;
                $order->total_discount          = $total_all_discount;
                $order->dcc_purchase_discount   = $total_purchase_discount;
                $order->dcc_shipping_discount   = $total_shipping_discount;
                $order->total_core_cost         = $current_product_subtotal;
                $order->save();
            }

            $shop_status_id = \App\OrderShop::where('id', $order_detail->order_shop_id)->value('order_status');
            $shop_status = \App\OrderStatusDesc::getStatusVal($shop_status_id);

            return ['status' => 'success', 'msg' => $msg, 'item_status' => $item_status, 'shop_status' => $shop_status];
        } else {
            return ['status' => 'fail', 'msg' => 'Invalid order id'];
        }

    }

    public function ordChangeItemQuantity(Request $request)
    {
        $order_detail_id = $request->order_detail_id ?? 0;
        // $change_item_qty = (int) $request->change_item_qty??0;
        $change_item_qty = $request->change_item_qty ?? 0;

        $order_detail = OrderDetail::where('id', $order_detail_id)->first();
        $order_detail_first = OrderDetail::where([['order_id', $order_detail->order_id], ['sku', $order_detail->sku]])->first();
        $frist_qty = $order_detail_first->quantity;

        if ($order_detail_id && $order_detail) {

            if ($change_item_qty == $order_detail->quantity) {
                return ['status' => 'fail', 'msg' => 'คุณใส่จำนวนเท่ากับจำนวนสินค้าที่แก้ไขล่าสุดไปแล้ว'];
            } else if ($change_item_qty > $frist_qty) {
                return ['status' => 'fail', 'msg' => 'คุณใส่จำนวนที่มากกว่าจำนวนสินค้าที่สั่งซื้อ จะทุจริตใช่มั้ย'];
            }
            $current_status = $order_detail->status;
            $current_paydate = $order_detail->payment_date;
            $current_pay_status = $order_detail->payment_status;
            $current_logis_status = $order_detail->logistic_status;

            $order_detail->status = 11;
            $order_detail->payment_status = 0;
            $order_detail->logistic_status = 0;
            $order_detail->payment_date = null;

            $order_detail->save();

            $new_order_detail = new OrderDetail;
            $new_order_detail->user_id = $order_detail->user_id;
            $new_order_detail->shop_id = $order_detail->shop_id;
            $new_order_detail->order_id = $order_detail->order_id;
            $new_order_detail->order_shop_id = $order_detail->order_shop_id;
            $new_order_detail->product_id = $order_detail->product_id;
            $new_order_detail->cat_id = $order_detail->cat_id;
            $new_order_detail->total_weight = $order_detail->total_weight;
            $new_order_detail->category_name = $order_detail->category_name;
            $new_order_detail->package_name = $order_detail->package_name;
            $new_order_detail->description = $order_detail->description;
            $new_order_detail->base_unit = $order_detail->base_unit;
            $new_order_detail->sku = $order_detail->sku;
            $new_order_detail->quantity = $change_item_qty;
            $new_order_detail->original_price = $order_detail->original_price;
            $new_order_detail->last_price = $order_detail->last_price;
            $new_order_detail->total_price = $order_detail->last_price * $change_item_qty;
            $new_order_detail->payment_type = $order_detail->payment_type;
            $new_order_detail->payment_slug = $order_detail->payment_slug;

            $new_order_detail->order_detail_json = $order_detail->order_detail_json;
            $new_order_detail->status = $current_status;
            $new_order_detail->payment_date = $current_paydate;
            $new_order_detail->logistic_status = $current_logis_status;
            $new_order_detail->payment_status = $current_pay_status;
            $new_order_detail->save();


            $admin_udate = Auth::guard('admin_user')->user()->nick_name;
            /****update entry in order transaction******/
            $transaction_arr = ['order_id' => $order_detail->order_id, 'order_shop_id' => $order_detail->order_shop_id, 'order_detail_id' => $order_detail->id, 'event' => 'delivery', 'comment' => 'adjust product qty', 'updated_by' => $admin_udate];

            $update_transaction = \App\OrderTransaction::updateOrdTrans($transaction_arr);

            /*****function change order status*******/
            //$change_status = \App\Order::updateOrdStatus($order_detail->order_id);

            /**new status****/
            //$item_status = \App\OrderStatusDesc::getStatusVal($status);

            //$shop_status_id = \App\OrderShop::where('id',$order_detail->order_shop_id)->value('order_status');
            //$shop_status = \App\OrderStatusDesc::getStatusVal($shop_status_id);
            $total_shop_price = orderDetail::Where('order_shop_id', $order_detail->order_shop_id)->whereNotIn('status', [4, 9, 10, 11, 12])->sum('total_price');

            $shop_price_update = \App\OrderShop::where('id', $order_detail->order_shop_id)->first();
            $shop_price_update->total_final_price = $total_shop_price;
            $shop_price_update->total_core_cost = $total_shop_price;
            $shop_price_update->save();

             //พี่เกมส์
            // $arr_payment = ['credit_acc', 'credit_acc1', 'credit_acc7'];
 
            // $total_final_price = orderDetail::Where('order_id', $order_detail->order_id)->whereNotIn('status', [4, 9, 10, 11, 12])->sum('total_price');
 
 
            //  \App\Order::where('id', $order_detail->order_id)
            // ->whereIn('payment_slug', ['credit_acc', 'credit_acc7', 'credit_acc1'])
            // ->update([
            //     'total_final_price' => $total_final_price,
            //     'total_core_cost' => $total_final_price
            // ]);
            //end พี่เกมส์

            // 1. คำนวณยอดรวมสินค้าปัจจุบัน 
        $current_product_subtotal = OrderDetail::where('order_id', $order_detail->order_id)
            ->whereIn('status', [1, 2, 3, 5, 6, 7, 8])
            ->sum('total_price');

        // 2. ดึงข้อมูล Order (Eager Load criteria เพื่อลด Query)
        $order = \App\Order::with('orderDiscountCodes.criteria')
            ->where('id', $order_detail->order_id)
            ->whereIn('payment_slug', ['credit_acc', 'credit_acc7', 'credit_acc1'])
            ->first();

        if ($order) {
            $total_purchase_discount = 0;
            $total_shipping_discount = 0;
            
            // ดึงค่าขนส่งจากออเดอร์ (ใช้สำหรับกรณี Free Shipping)
            $order_shipping_cost = $order->dcc_shipping_discount ?? 0; 

            foreach ($order->orderDiscountCodes as $appliedCode) {
                $criteria = $appliedCode->criteria;
                if (!$criteria) continue;

                $temp_purchase_discount = 0;
                $temp_shipping_discount = 0;

                // ตรวจสอบยอดซื้อขั้นต่ำก่อนให้ส่วนลด
                if ($current_product_subtotal >= $criteria->purchase_amount_threshold) {
                    
                    // --- Logic: ส่วนลดค่าจัดส่ง ---
                    if ($criteria->is_free_shipping == 1) {
                        // ถ้าเป็น Free Shipping ให้ใช้ยอดค่าส่งของ Order นั้นๆ เป็นส่วนลด
                        $temp_shipping_discount = $order_shipping_cost;
                    } elseif ($criteria->discount_target == 'shipping') {
                        if ($criteria->discount_type == 'percentage') {
                            $temp_shipping_discount = ($order_shipping_cost * $criteria->discount_value) / 100;
                            if ($criteria->max_discount > 0) {
                                $temp_shipping_discount = min($temp_shipping_discount, $criteria->max_discount);
                            }
                        } else {
                            $temp_shipping_discount = $criteria->discount_value;
                        }
                    }

                    // --- Logic: ส่วนลดค่าสินค้า (Purchase) ---
                    if ($criteria->discount_target == 'purchase') {
                        if ($criteria->discount_type == 'percentage') {
                            $temp_purchase_discount = ($current_product_subtotal * $criteria->discount_value) / 100;
                            if ($criteria->max_discount > 0) {
                                $temp_purchase_discount = min($temp_purchase_discount, $criteria->max_discount);
                            }
                        } else {
                            $temp_purchase_discount = $criteria->discount_value;
                        }
                    }
                }

                // 3. อัปเดตข้อมูลลงตาราง Mapping (smm_order_discount_code)
                $appliedCode->purchase_discount_amount = $temp_purchase_discount;
                $appliedCode->shipping_discount_amount = $temp_shipping_discount;
                $appliedCode->save();

                $total_purchase_discount += $temp_purchase_discount;
                $total_shipping_discount += $temp_shipping_discount;
            }

            // 4. สรุปยอดและอัปเดตลงตาราง Order
            $total_all_discount = $total_purchase_discount + $total_shipping_discount;
            $summary_net_total = max(0, $current_product_subtotal - $total_all_discount);

            $order->total_final_price       = $summary_net_total + $order->total_shipping_cost;
            $order->total_discount          = $total_all_discount;
            $order->dcc_purchase_discount   = $total_purchase_discount;
            $order->dcc_shipping_discount   = $total_shipping_discount;
            $order->total_core_cost         = $current_product_subtotal;
            $order->save();
        }
                        

            return ['status' => 'success', 'msg' => 'แก้ไขจำนวนสินค้าสำเร็จ', 'user' => $admin_udate];
        } else {
            return ['status' => 'fail', 'msg' => 'Invalid order detail id'];
        }

    }

    /**resend order to logistic */
    public function resendLogistic(Request $request)
    {
        $order_id = $request->order_id;
        $order = Order::where('id', $request->order_id)->first();
        if ($order) {
            $order->logistic_status = '0';
            $order->save();

            return ['status' => 'success', 'msg' => 'Success ! It will send after one min'];
        }
        return ['status' => 'fail', 'msg' => \Lang::get('admin_common.something_went_wrong')];
    }

    /**resend order to WMS */
    public function resendWMS(Request $request)
    {
        $order_id = $request->order_id;
        $order = Order::where('id', $request->order_id)->first();
        if ($order) {
            $order->wms_status = '0';
            $order->save();

            return ['status' => 'success', 'msg' => 'Success ! It will send after one min'];
        }
        return ['status' => 'fail', 'msg' => \Lang::get('admin_common.something_went_wrong')];
    }

    public function updateRemark(Request $request)
    {
        $order_id = $request->order_id;
        $order = Order::where('id', $request->order_id)->first();
        if ($order) {
            $order->admin_remark = trim($request->remark);
            $order->admin_remark_by = Auth::guard('admin_user')->user()->nick_name;
            $order->save();

            return ['status' => 'success', 'msg' => \Lang::get('admin_order.remark_updated_successfully')];
        }
        return ['status' => 'fail', 'msg' => \Lang::get('admin_common.something_went_wrong')];
    }

    /*start tong j update pickup time*/
    // public function updatePickupTime(Request $request){

    //     $order_id = $request->order_id;        
    //     $order = Order::where('id',$request->order_id)->first();
    //     if($order) {
    //         $order->pickup_time = $request->pickup_time_id;
    //         $order->admin_remark_by = Auth::guard('admin_user')->user()->nick_name;
    //         $order->save();            

    //         /*send noti at mobile*/ 
    //         $orderInfo = Order::find($order_id);

    //         $title = 'New Order';
    //         $body = 'Order id '. $orderInfo->formatted_id;
    //         $post_arr = ['user_id'=>$orderInfo->user_id, 'title'=>$title,'body'=>$body, 'type_redirect'=>'payment_success', 'order_id'=>$orderInfo->id, 'formatted_order_id'=>$orderInfo->formatted_id];
    //         $url = Config::get('constants.mobile_notification_url');
    //         $responce = $this->handleCurlRequest($url,$post_arr);;  

    //         return ['status'=>'success','msg'=>\Lang::get('admin_order.pickup_time_updated_successfully')];
    //     }

    //     return ['status'=>'fail','msg'=>\Lang::get('admin_common.something_went_wrong')];

    // } 
    /*stop tong j*/

    public function updatePickupTime(Request $request)
    {
        $order_id = $request->order_id;
        $full_datetime = $request->full_datetime; 
        $del_t_s_id = $request->del_t_s_id;

        if (!strtotime($full_datetime)) {
            return ['status' => 'fail', 'msg' => 'รูปแบบวันที่และเวลาไม่ถูกต้อง'];
        }

        $order = \App\Order::find($order_id);
        if (!$order) {
            return ['status' => 'fail', 'msg' => 'ไม่พบข้อมูลออเดอร์'];
        }

        $timeSlot = \App\DeliveryTimeSlot::find($del_t_s_id);
        if (!$timeSlot) {
            return ['status' => 'fail', 'msg' => 'ไม่พบข้อมูล Time Slot'];
        }

        \DB::beginTransaction();
        try {
        
            $targetDate = date('Y-m-d', strtotime($full_datetime));
            
            $baseDateTimeStr = $targetDate . ' ' . $timeSlot->order_cutoff_time;
            $baseTimestamp = strtotime($baseDateTimeStr);

            $dc_delivery_starttime = date('Y-m-d H:i:s', $baseTimestamp + ($timeSlot->seller_start_deli_time * 60));
            $dc_delivery_endtime   = date('Y-m-d H:i:s', $baseTimestamp + ($timeSlot->seller_end_deli_time * 60));

            $order->pickup_time = $full_datetime;
            $order->del_t_s_id = $del_t_s_id;
            $order->admin_remark_by = \Auth::guard('admin_user')->user()->nick_name ?? 'Admin';
            $order->save();

            \DB::table('order_shop')
                ->where('order_id', $order_id)
                ->update([
                    'dc_delivery_starttime' => $dc_delivery_starttime,
                    'dc_delivery_endtime'   => $dc_delivery_endtime,
                    'updated_at'            => now()
                ]);

            \DB::commit();

            return [
                'status' => 'success',
                'msg' => 'บันทึกสำเร็จและอัปเดตเวลา DC เรียบร้อยแล้ว'
            ];

        } catch (\Exception $e) {
            \DB::rollBack();
            return ['status' => 'fail', 'msg' => 'Error: ' . $e->getMessage()];
        }
    }

    // แยกฟังก์ชัน Noti เพื่อความสะอาดของโค้ด
    private function sendUpdateNotification($order, $datetime) {
        $post_arr = [
            'user_id' => $order->user_id,
            'title' => 'อัปเดตรอบการจัดส่ง',
            'body' => 'ออเดอร์ #' . $order->formatted_id . ' เปลี่ยนรอบส่งเป็น: ' . $datetime,
            'type_redirect' => 'payment_success',
            'order_id' => $order->id,
            'formatted_order_id' => $order->formatted_id
        ];
        $url = \Config::get('constants.mobile_notification_url');
        if ($url) {
            return $this->handleCurlRequest($url, $post_arr);
        }
    }

    public function updateOrderStatus(Request $request)
    {

        $user_id = Auth::id();
        $updated_by = Auth::guard('admin_user')->user()->nick_name;
        $order_id = $request->order_id;
        $order_status_id = $request->order_status_id ?? '';
        $payment_slug = $request->payment_method;

        // DB::beginTransaction();
        try {
            $order = Order::find($order_id);

            if (!$order) {
                return ['status' => 'error', 'msg' => \Lang::get('admin_order.invalid_order_id')];
            }

            if ($order->payment_slug !== $payment_slug) {
                $order->payment_slug = $payment_slug;
                $order->save();

                OrderShop::where('order_id', $order_id)
                    ->update(['payment_slug' => $payment_slug]);

                // OrderDetail::where('order_id', $order_id)
                //     ->update(['payment_slug' => $payment_slug]);

                OrderDetail::where([['order_id', '=', $order_id], ['status', '!=', '11']]) // เพิ่มเงื่อนไขยกเลิกสินค้า 11 (กรณีแก้ไขจำนวนสินค้า) ไม่อัพเดท
                    ->update(['payment_slug' => $payment_slug]);


                // ✅ เพิ่มบันทึกในตาราง order_transaction
                $comment = GeneralFunctions::getOrderText('edit_payment_status');
                $transaction_arr = [
                    'order_id' => $order_id,
                    'order_shop_id' => 0,
                    'order_detail_id' => 0,
                    'event' => 'order',
                    'comment' => $comment,
                    'updated_by' => $updated_by
                ];
                \App\OrderTransaction::updateOrdTrans($transaction_arr);
            }


            if (!empty($order_status_id) && (in_array($order->order_status, ['1', '4']) && $order_status_id == '2')) {

                Order::updateOrderAfterPayment($order, $updated_by);

                EmailHelpers::sendOrderNotificationEmail($order->formatted_id);
                $title = 'New Order';
                $body = 'Order id ' . $order->formatted_id;
                $post_arr = [
                    'user_id' => $order->user_id,
                    'title' => $title,
                    'body' => $body,
                    'type_redirect' => 'payment_success',
                    'order_id' => $order->id,
                    'formatted_order_id' => $order->formatted_id
                ];
                $url = Config::get('constants.mobile_notification_url');
                $this->handleCurlRequest($url, $post_arr);
            } elseif ($order_status_id == '4') {

                Order::cancelOrder($order, $updated_by);

            }

            // ดึงข้อมูล payment options จากฐานข้อมูลแทนการ hard code
            $paymentOptions = \App\PaymentOption::with('paymentOptName')->get();
            $paymentLabelMap = [];
            foreach ($paymentOptions as $paymentOption) {
                $paymentLabelMap[$paymentOption->slug] = $paymentOption->paymentOptName->payment_option_name ?? $paymentOption->slug;
            }

            // $orderDetails = OrderDetail::where('order_id', $order_id)->get();
            $orderDetails = OrderDetail::where([['order_id', '=', $order_id], ['status', '!=', '11']])->get(); // เพิ่มเงื่อนไขยกเลิกสินค้า 11 (กรณีแก้ไขจำนวนสินค้า) ไม่อัพเดท

            foreach ($orderDetails as $detail) {
                $json = json_decode($detail->order_detail_json, true);
                if (!$json)
                    continue;

                $json['payment_method'] = [
                    $paymentLabelMap[$payment_slug] ?? str_replace('_', ' ', strtoupper($payment_slug))
                ];

                $detail->order_detail_json = json_encode($json, JSON_UNESCAPED_UNICODE);
                $detail->save();
            }

            DB::commit();
            return ['status' => 'success', 'msg' => \Lang::get('admin_order.order_status_updated_successfully')];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'msg' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()];
        }
    }


    public function create()
    {
    }

    function store(Request $request)
    {
    }

    function edit($group_id)
    {
    }

    function update(Request $request)
    {
    }

    public function orderDetailExport(Request $request)
    {

        $formatted_id = $request->oid;
        $main_order = Order::where('formatted_id', $formatted_id)->with(['getUser', 'getOrderStatus'])->first();
        // dd($main_order,$formatted_id);
        if (empty($main_order)) {
            abort(404);
        }

        $order_shop = OrderShop::where('order_id', $main_order->id)->with(['getOrderStatus'])->get();
        if (count($order_shop)) {
            foreach ($order_shop as $key => $value) {
                $order_detail = OrderDetail::getShopOrderDetail('', $value->id);
                $order_shop[$key]->details = $order_detail;
            }
        }
        $main_order->tot_shop = count($order_shop);

        //dd($order_shop);
        $transaction = \App\OrderTransaction::where('order_id', $main_order->id)->orderBy('id')->get();

        $main_order->pickup_time = null;
        if ($main_order->id > 0) {
            $order_info = Order::where('id', $main_order->id)->first();
            if ($order_info) {
                $main_order->pickup_time = $order_info->pickup_time;
            }
        }

        /* Start:: If Product Detail Not Available in Order Details */
        if (count($order_shop)) {
            foreach ($order_shop as $skey => $shop_ord_val) {
                foreach ($shop_ord_val->details as $key => $val) {
                    if ($val->description == '' || $val->description == null) {
                        $productDetail = \App\Product::getProductDetailAll($val->sku);
                        $order_shop[$skey]->details[$key]->description = isset($productDetail->productDesc) ? $productDetail->productDesc->description : "";
                    }
                }
            }
        }
        /* Start:: If Product Detail Not Available in Order Details */
        $pdf = PDF::loadView('admin.transaction.mainOrddetailExport', ['main_order' => $main_order, 'order_shop' => $order_shop, 'transaction' => $transaction]);

        return $pdf->download($main_order->formatted_id . '.pdf');
        //return view('admin.transaction.mainOrddetailExport',['main_order' => $main_order,'order_shop'=>$order_shop,'transaction'=>$transaction]);
    }
    // public function generateOrderPdf(Request $request) {

    //     //$formatted_id = $request->order_list; 
    //     //dd($request->order_list);
    //     $formatted_id = explode(',',$request->order_list); 
    //     $total_order = Order::whereIn('formatted_id',$formatted_id)->with(['getUser','getOrderStatus'])->get();
    //     // dd($main_order,$formatted_id);
    //     if(empty($total_order)){
    //       abort(404);
    //     }
    //     //dd($total_order,$formatted_id);
    //     foreach ($total_order as $key => $main_order) {
    //         $order_shop = OrderShop::where('order_id',$main_order->id)->with(['getOrderStatus'])->get();
    //         if(count($order_shop)){
    //             foreach ($order_shop as $key => $value) {
    //                 $order_detail = OrderDetail::getShopOrderDetail('',$value->id);
    //                 $order_shop[$key]->details = $order_detail;
    //             }
    //         }
    //         $main_order->tot_shop = count($order_shop);

    //         //dd($order_shop);
    //         $transaction = \App\OrderTransaction::where('order_id',$main_order->id)->orderBy('id')->get();

    //         $main_order->pickup_time = null;
    //         if($main_order->id>0)
    //         {
    //             $order_info = Order::where('id',$main_order->id)->first();
    //             if($order_info)
    //             {
    //                 $main_order->pickup_time=$order_info->pickup_time;
    //             }
    //         }

    //         /* Start:: If Product Detail Not Available in Order Details */
    //         if(count($order_shop))
    //         {
    //             foreach($order_shop as $skey => $shop_ord_val)
    //                 {
    //                     foreach($shop_ord_val->details as $key => $val)
    //                     {
    //                         if($val->description=='' || $val->description==null)
    //                         {
    //                             $productDetail = \App\Product::getProductDetail($val->sku);
    //                             $order_shop[$skey]->details[$key]->description=isset($productDetail->productDesc)?$productDetail->productDesc->description:"";
    //                         }
    //                     }
    //                 }
    //         }
    //         /* Start:: If Product Detail Not Available in Order Details */
    //         $pdf = PDF::loadView('admin.transaction.mainOrderListlExport', ['main_order' => $main_order,'order_shop'=>$order_shop,'transaction'=>$transaction]);

    //         return $pdf->download($main_order->formatted_id.'.pdf');
    //         //return view('admin.transaction.mainOrddetailExport',['main_order' => $main_order,'order_shop'=>$order_shop,'transaction'=>$transaction]);
    //     }
    //     //return ['status'=>'success','message'=>'Pdf Download Successfully'];

    // }

    public function generateOrderPdf(Request $request)
    {

        //$formatted_id = $request->order_list; 
        //dd($request->order_list);
        $formatted_id = explode(',', $request->order_list);
        $total_order = Order::whereIn('formatted_id', $formatted_id)->with(['getUser', 'getOrderStatus'])->get();
        // dd($main_order,$formatted_id);
        if (empty($total_order)) {
            abort(404);
        }
        //dd($total_order,$formatted_id);
        $pdf = PDF::loadView('admin.transaction.mainOrderListlExport', ['total_order' => $total_order]);
        return $pdf->download('order.pdf');
        //return view('admin.transaction.mainOrderListlExport',['total_order' => $total_order]);

        //return ['status'=>'success','message'=>'Pdf Download Successfully'];

    }

    public function getShippingAddress(Request $request)
    {
        $array_server = explode('/', $request->server('HTTP_REFERER'));
        $checkout_type = end($array_server);
        $userid = Auth::User()->id;
        $str = "";

        //$main_order = \App\Order::where('id',$request->order_id)->first();



        if (!empty($request->shipping_address_id)) {
            $shipAddress = ShippingAddress::where(['user_id' => $request->userid, 'id' => $request->shipping_address_id])->first();

            if (!empty($shipAddress)) {
                $str = "
                        <div class='card p-3 shadow-sm rounded'>
                            <div class='d-flex align-items-start mb-2'>
                                <ion-icon name='location-outline' class='me-2 fs-5 text-primary'></ion-icon>
                                <div>
                                    <p class='mb-1 fw-bold'>$shipAddress->first_name $shipAddress->last_name</p>
                                    <p class='mb-1'>$shipAddress->address $shipAddress->road</p>
                                    <p class='mb-1'>$shipAddress->city_district $shipAddress->province_state $shipAddress->zip_code</p>
                                </div>
                            </div>
                            <div class='d-flex align-items-center'>
                                <ion-icon name='call-outline' class='me-2 fs-5 text-success'></ion-icon>
                                <span>$shipAddress->ph_number</span>
                            </div>
                        </div>
                        ";

            }
        } else {


            return json_encode(array('status' => 'fail', 'shipVal' => $str));
        }

        return json_encode(array('status' => 'success', 'shipVal' => $str));
    }

    public function changeShippingAddress(Request $request)
    {
        $order_id = $request->order_id;

        $shipAddInfo = ShippingAddress::find($request->shipId);
        $AddressArr = ['shipping_address_id' => $shipAddInfo->id, 'title' => $shipAddInfo->title, 'first_name' => $shipAddInfo->first_name, 'last_name' => $shipAddInfo->last_name, 'provice' => $shipAddInfo->province_state, 'district' => $shipAddInfo->city_district, 'sub_district' => $shipAddInfo->sub_district, 'address' => $shipAddInfo->address, 'road' => $shipAddInfo->road, 'zip_code' => $shipAddInfo->zip_code, 'ph_number' => $shipAddInfo->ph_number, 'company_name' => $shipAddInfo->company_name, 'branch' => $shipAddInfo->branch, 'tax_id' => $shipAddInfo->tax_id, 'company_address' => $shipAddInfo->company_address];

        $order = Order::where('id', $request->order_id)->first();

        $data = jsonDecodeArr($order->order_json);
        //$BillingressArr = ['billing_address_id'=>$billing_address->id,'title'=>$billing_address->title,'first_name'=>$billing_address->first_name,'last_name'=>$billing_address->last_name,'provice'=>$billing_address->province_state,'district'=>$billing_address->city_district,'sub_district'=>$billing_address->sub_district,'address'=>$billing_address->address,'road'=>$billing_address->road,'zip_code'=>$billing_address->zip_code,'ph_number'=>$billing_address->ph_number,'company_name'=>$billing_address->company_name,'branch'=>$billing_address->branch,'tax_id'=>$addInbilling_addressfo->tax_id,'company_address'=>$billing_address->company_address];
        $billAddArr = $data['billing_address'];

        //$total_logistic_cost = jsonDecodeArr($order->order_json);
        $total_logistic_cost = $data['total_logistic_cost'];
        $temp_order_json = jsonEncode(['shipping_address' => $AddressArr, 'billing_address' => $billAddArr, 'total_logistic_cost' => $total_logistic_cost]);

        if ($order) {
            $order->order_json = $temp_order_json;
            $order->admin_remark_by = Auth::guard('admin_user')->user()->nick_name;
            $order->save();

            /*send noti at mobile*/
            $orderInfo = Order::find($order_id);

            $title = 'New Order';
            $body = 'Order id ' . $orderInfo->formatted_id;
            $post_arr = ['user_id' => $orderInfo->user_id, 'title' => $title, 'body' => $body, 'type_redirect' => 'payment_success', 'order_id' => $orderInfo->id, 'formatted_order_id' => $orderInfo->formatted_id];
            $url = Config::get('constants.mobile_notification_url');
            $responce = $this->handleCurlRequest($url, $post_arr);
            ;

            return ['status' => 'success', 'msg' => \Lang::get('admin_order.pickup_time_updated_successfully')];
        }

        return ['status' => 'fail', 'msg' => \Lang::get('admin_common.something_went_wrong')];

    }

}