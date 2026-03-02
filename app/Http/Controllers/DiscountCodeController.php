<?php

namespace App\Http\Controllers;

use App\Campaign;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use Config;
use Lang;
use Auth;
use App\CustomCss;
use Exception;
use App\DiscountCode;
use App\DiscountCodeCriteria;
use App\Helpers\CustomHelpers;
use App\Order;
use App\OrderDiscountCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use DateTime;
use Illuminate\Auth\Events\Validated;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Response;


class DiscountCodeController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('authenticate');
    }
    
    public function validateDiscountCode( $code, $purchase){
        try {
            $userId = auth()->id();
            if(!$code) {
                return response()->json([ 'status' => 'fail', 'message' => 'กรุณากรอกโค้ดส่วนลด', ],200);
            }
            if(!$purchase) {
                return response()->json([ 'status' => 'fail', 'message' => 'ไม่พบยอดการสั่งซื้อ', ],200);
            }
            $discountCode = DiscountCode::where('code', $code)->first();

            if (!$discountCode) {
                return response()->json([ 'status' => 'fail', 'message' => 'ขออภัย! ไม่พบโค้ดส่วนลดในระบบ กรุณากรอกใหม่', ],200);
            }
            $campaign = optional($discountCode->criteria)->campaign;
            if ($campaign && $campaign->status == 0) {
                return response()->json([ 'status' => 'fail', 'message' => 'ขออภัย! แคมเปญหมดอายุ', ],200);
            }
            $megacampaign = optional($campaign->megacampaign);
            if ($megacampaign && $megacampaign->status == 0) {
                return response()->json([ 'status' => 'fail', 'message' => 'ขออภัย! เมกาแคมเปญหมดอายุ', ],200);
            }
            $errorLogs = [];
            if ($discountCode->status == 0 || $discountCode->criteria->status == 0) {
                $errorLogs[] = 'ขออภัย! โค้ดส่วนลดหมดอายุ';
            }else if ($discountCode->criteria->start_date && Carbon::parse($discountCode->criteria->start_date)->isFuture()) {
                $errorLogs[] = 'ขออภัย! โค้ดส่วนลดนี้ยังไม่เปิดให้ใช้งานในขณะนี้';
            }else if ($discountCode->criteria->end_date && Carbon::parse($discountCode->criteria->end_date)->isPast()) {
                $errorLogs[] = 'โค้ดส่วนลดนี้ไม่สามารถใช้งานได้ เนื่องจากโปรโมชั่นได้สิ้นสุดลงแล้ว';
            }else if ($purchase < $discountCode->criteria->purchase_amount_threshold) {
                $errorLogs[] = 'ขออภัย! ยอดสั่งซื้อไม่เข้าเงื่อนไขในการใช้โค้ดส่วนลด';
            }else if ($discountCode->criteria->is_limit == true && $discountCode->remaining_quantity <= 0) {
                $errorLogs[] = 'ขออภัย! โค้ดนี้ถูกใช้ครบตามจำนวนที่กำหนดแล้ว';
            }else if ($discountCode->criteria->limit_per_account>0) {
                $odc = OrderDiscountCode::query()
                ->where('discount_code',$code)
                ->whereHas('order',function($qry)use($userId){
                    $qry->where('user_id',$userId);
                })->count();
                if($odc >= $discountCode->criteria->limit_per_account){
                    $errorLogs[] = 'ขออภัย! คุณได้ใช้โค้ดส่วนลดครบตามจำนวนที่กำหนดแล้ว';
                }
            }
            
            // if ($discountCode->criteria->required_purchase_count > 0) {
            //     $order_count = Order::query()->where('user_id',$userId)->count();
            //     if($order_count != $discountCode->criteria->required_purchase_count+1){
            //         $errorLogs[] = "โค้ดส่วนลดสามารถใช้ได้เมื่อคุณมีการสั่งซื้อครบ ".$discountCode->criteria->required_purchase_count." ครั้ง";
            //     }
            // }

            if ($errorLogs) {
                // throw new Exception(implode(', ', $errorLogs), 400);
                return response()->json([
                    'status' => 'fail',
                    'message' => implode(', ', $errorLogs),
                ],200);
            }else{
             
                return response()->json([
                    'status' => 'success',
                    'message' => 'สามารถใช้งานได้',
                    'code' => $discountCode->code
                ], 200);
            }
            
        }catch (Exception $e) {
            Log::error('Failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
            ], $e->getCode() > 0 ? $e->getCode() : 500);
        }
    }

    public function calulateDiscount( Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
                'purchase' => 'required|numeric|min:1',
                'shippingCost' => 'nullable|numeric',
            ]);
            if ($validator->fails()) {
                $errors =  $validator->errors(); 
                return array('status'=>'fail','message'=>$errors,'validation'=>true);
            }
            
            $discountPurchase = 0;
            $discountShipping = 0;

            $code = $request->code;
            $purchase = $request->purchase;
            $shippingCost = $request->shippingCost;
            $checkResult = self::validateDiscountCode($code, $purchase);
            $checkResult = $checkResult->getData();
            if($checkResult->status == 'fail'){
                return response()->json($checkResult, 200);
            }
            $discountCode = DiscountCode::where('code', $code)->first();
            if(!$discountCode) throw new Exception("ไม่พบโค้ดส่วนลด");
            $criteria = $discountCode->criteria;
            if(!$criteria){ throw new Exception('ไม่พบข้อมูล',400); }
            $discountCodeName = $criteria->name;
            if($criteria->purechase_amount_threshold > $purchase){
                return response()->json([
                    'status' => 'fail',
                    'message' => 'ยอดซื้อไม่ถึงเกณฑ์ที่กำหนด',
                ], 200);
            }

            if($criteria->discount_target == 'purchase'){
                if($criteria->discount_type == 'fixed'){
                    $discountPurchase = $criteria->discount_value;
                }else if($criteria->discount_type == 'percentage'){
                    $discountPurchase = ($purchase * $criteria->discount_value) / 100;
                    if($criteria->max_discount && $criteria->max_discount > 0){
                        if($discountPurchase > $criteria->max_discount){
                            $discountPurchase = $criteria->max_discount;
                        }
                    }
                    if ($discountPurchase > $purchase){
                        $discountPurchase = $purchase;
                    }
                }
            }
            else if($criteria->discount_target == 'shipping'){
                if($criteria->discount_type == 'fixed'){
                    $discountShipping = $criteria->discount_value;
                }else if($criteria->discount_type == 'percentage'){
                    $discountShipping = ($shippingCost * $criteria->discount_value) / 100;
                    if($criteria->max_discount && $criteria->max_discount > 0){
                        if($discountShipping > $criteria->max_discount){
                            $discountShipping = $criteria->max_discount;
                        }
                    }
                    if ($discountShipping > $shippingCost){
                        $discountShipping = $shippingCost;
                    }
                }
            }
            if($criteria->is_free_shipping){
                $discountShipping = $shippingCost;
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Calulate successfully',
                'data' => [
                    'discountCodeName' => $discountCodeName,
                    'discountPurchase' => round($discountPurchase, 2),
                    'discountShipping' => round($discountShipping, 2),
                ]
            ], 200);
            
        }catch (Exception $e) {
            Log::error('Update failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
            ], $e->getCode() > 0 ? $e->getCode() : 500);
        }
    }

    public function checkUsable(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
                'purchase' => 'required|numeric|min:1',
            ]);
            if ($validator->fails()) {
                $errors =  $validator->errors(); 
                return array('status'=>'fail','message'=>$errors,'validation'=>true);
            }
            
            $code = $request->code;
            $purchase = $request->purchase;
            $checkResult = self::validateDiscountCode($code, $purchase);
            $checkResult = $checkResult->getData();
            return response()->json($checkResult, 200);
            
        }catch (Exception $e) {
            Log::error('Update failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
            ], $e->getCode() > 0 ? $e->getCode() : 500);
        }
    }

}