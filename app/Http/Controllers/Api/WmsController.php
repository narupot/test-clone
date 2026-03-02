<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Order;
use App\OrderDetail;
use App\WmsPickupLog;
use DB;
use Validator;

class WmsController extends Controller
{
    /**
     * Receive truck plan data and update order locations
     * 
     * @param Request $request
     * @return JSON response
     */
    public function updateTruckPlan(Request $request)
    {
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'Pickuptime' => 'required|date_format:Y-m-d H:i',
                'TruckPlan' => 'required|array',
                'TruckPlan.*.MainOrder' => 'required|string',
                'TruckPlan.*.Location' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $pickupTime = $request->input('Pickuptime');
            $truckPlan = $request->input('TruckPlan');
            
            $updatedCount = 0;
            $failedCount = 0;
            $failedOrders = [];

            // Process each order in truck plan
            foreach ($truckPlan as $item) {
                $mainOrder = $item['MainOrder'];
                $location = $item['Location'];

                try {
                    // Find order by formatted_id (MainOrder could be formatted ID like SMM260108225455)
                    $order = Order::where('formatted_id', $mainOrder)
                        ->orWhere('id', $mainOrder)
                        ->first();

                    if ($order) {
                        // Update all order details for this order with the location
                        $updated = OrderDetail::where('order_id', $order->id)
                            ->update(['wms_location' => $location]);

                        if ($updated > 0) {
                            $updatedCount += $updated;
                        } else {
                            $failedCount++;
                            $failedOrders[] = [
                                'order' => $mainOrder,
                                'location' => $location,
                                'reason' => 'No order details found for this order'
                            ];
                        }
                    } else {
                        $failedCount++;
                        $failedOrders[] = [
                            'order' => $mainOrder,
                            'location' => $location,
                            'reason' => 'Order not found'
                        ];
                    }
                } catch (\Exception $e) {
                    $failedCount++;
                    $failedOrders[] = [
                        'order' => $mainOrder,
                        'location' => $location,
                        'reason' => $e->getMessage()
                    ];
                }
            }

            // Create log record
            $logStatus = $failedCount === 0 ? 'success' : ($updatedCount > 0 ? 'partial' : 'failed');
            
            $log = WmsPickupLog::create([
                'pickup_time' => $pickupTime,
                'truck_plan' => $truckPlan,
                'total_orders' => count($truckPlan),
                'updated_count' => $updatedCount,
                'failed_count' => $failedCount,
                'failed_orders' => $failedOrders,
                'request_data' => $request->all(),
                'status' => $logStatus
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Truck plan processed',
                'log_id' => $log->id,
                'summary' => [
                    'total_orders' => count($truckPlan),
                    'updated' => $updatedCount,
                    'failed' => $failedCount,
                    'pickup_time' => $pickupTime,
                    'process_status' => $logStatus
                ],
                'failed_orders' => $failedOrders
            ], 200);

        } catch (\Exception $e) {
            // Log error
            WmsPickupLog::create([
                'pickup_time' => $request->input('Pickuptime', null),
                'truck_plan' => $request->input('TruckPlan', []),
                'total_orders' => count($request->input('TruckPlan', [])),
                'updated_count' => 0,
                'failed_count' => count($request->input('TruckPlan', [])),
                'failed_orders' => [['reason' => $e->getMessage()]],
                'request_data' => $request->all(),
                'status' => 'failed'
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error processing truck plan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pickup logs
     * 
     * @param Request $request
     * @return JSON response
     */
    public function getPickupLogs(Request $request)
    {
        try {
            $logs = WmsPickupLog::orderBy('created_at', 'desc');

            // Filter by date range if provided
            if ($request->has('from_date') && $request->has('to_date')) {
                $logs = $logs->whereBetween('created_at', [
                    $request->input('from_date'),
                    $request->input('to_date')
                ]);
            }

            // Filter by status if provided
            if ($request->has('status')) {
                $logs = $logs->where('status', $request->input('status'));
            }

            $logs = $logs->paginate($request->input('per_page', 20));

            return response()->json([
                'status' => 'success',
                'data' => $logs
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error fetching logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single log detail
     * 
     * @param int $id
     * @return JSON response
     */
    public function getLogDetail($id)
    {
        try {
            $log = WmsPickupLog::find($id);

            if (!$log) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Log not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $log
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error fetching log detail',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
