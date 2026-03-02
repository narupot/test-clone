<?php

namespace App\Http\Controllers\Checkout;

use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use App\Order;
use \Illuminate\Support\Facades\Auth;
use Config;
use Route;
use Exception;
use DB;
use PDF;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;

class OrderConfirmationController extends MarketPlace
{

	public function downloadOrderConfirmation(Request $request)
	{
		if (!$request->has('order_id')) {
			return redirect()->back()->withErrors(['error' => 'Order ID is required']);
		}

		$orderId = $request->order_id;
		$order = Order::query()
			->where('formatted_id', $orderId)
			->whereIn('order_status', [2, 3])
			->whereNotNull('end_shopping_date')
			->where('user_id', Auth::id())
			->with(['getOrderShop.shop.shopDesc', 'getOrderShop.orderDetail.product', 'paymentOption.paymentOptName'])
			->first();

		if (!$order) {
			return redirect()->back()->withErrors(['error' => 'Order not found']);
		}
		$shippingInfo = $order->order_json ? json_decode($order->order_json, true) : [];
		$response = [
			"user_info" => [
				'name' => $order->user_name ?? '',
				'email' => $order->user_email ?? '',
				'phone' => $order->ph_number ?? '',
			],
			"order_info" => $order->getAttributes(),
			"payment_name" => $order->paymentOption->paymentOptionDesc->payment_option_name ?? '',
			"shipping_info" => $shippingInfo,
			"order_details" => $order->getOrderShop->map(function ($orderShop) {
				$shop = $orderShop->shop_json ? json_decode($orderShop->shop_json, true) : null;
				return [
					'shop_name' => $shop ? $shop['shop_name'][0] : null,
					'panel_no' => $shop ? $shop['panel_no'] : null,
					"total_final_price" => $orderShop->total_final_price ?? 0,
					'items' => $orderShop->orderDetail->map(function ($detail) {
						$json_detail = $detail->order_detail_json ? json_decode($detail->order_detail_json, true) : null;
						return [
							'status' => $detail->status,
							'badge' => data_get($json_detail, 'badge.title', ''),
							'product_name' => $detail->category_name ?? '',
							'sku' => $detail->sku ?? '',
							'weight_per_unit' => $detail->total_weight ?? '',
							'total_weight' => $detail->total_weight ?? '',
							'unit_name' => $detail->base_unit ?? '',
							'package_name' => $detail->package_name ?? '',
							'quantity' => $detail->quantity ?? 0,
							'original_price' => $detail->original_price ?? 0,
							'total_price' => $detail->total_price ?? 0,
							'last_price' => $detail->last_price ?? 0,
							'price_per_unit' => ($detail->last_price ?? 0) / ($detail->total_weight ?? 0),
						];
					})->toArray(),
				];
			})->toArray(),
		];
		$pdf = PDF::loadView('pdf.orderConfirmation.template', $response);
		return $pdf->stream('ใบสรุปรายการสั่งซื้อ_' . $orderId . '.pdf');
	}
}