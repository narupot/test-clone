<?php
 
namespace App\Http\Controllers\Api;
 
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Lcobucci\JWT\Parser;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use Validator;
use App\Helpers\GeneralFunctions;
use App\Order;
use App\OrderShop;
use App\OrderDetail;
use App\OrderUpdateApiLog;
/**
  @OA\Info(
      description="",
      version="1.0.0",
      title="Update Order API",
 )
 */
 
/**
  @OA\SecurityScheme(
      securityScheme="bearerAuth",
          type="http",
          scheme="bearer",
          bearerFormat="JWT"
      ),
 */

class OrderController extends Controller {
 
/**
      @OA\Post(
          path="/api/updateOrderStatus",
          tags={"update order status"},
          summary="update order status",
          operationId="updateOrderStatus",
      
          @OA\Parameter(
              name="shop_formatter_id",
              in="query",
              required=true,
              @OA\Schema(
                  type="string"
              )
          ),    
          @OA\Parameter(
              name="status_order",
              in="query",
              required=true,
              @OA\Schema(
                  type="string"
              )
          ),  
          @OA\Parameter(
              name="api_date",
              in="query",
              required=true,
              @OA\Schema(
                  type="string",
                  format="date-time"
              )
          ), 
          @OA\Parameter(
              name="remark",
              in="query",
              required=false,
              @OA\Schema(
                  type="string"
              )
          ),  
          @OA\Parameter(
              name="item_details",
              in="query",
              required=false,
              @OA\Schema(
                  type="array",
                  @OA\Items(
                      type="object",
                        @OA\Property(property="order_detail_id", type="integer"),
                        @OA\Property(property="status_line", type="integer"),
                        @OA\Property(property="line_no", type="integer"),
                        @OA\Property(property="item_no", type="integer"),
                        @OA\Property(property="sku", type="string"),
                        @OA\Property(property="item_description", type="string"),
                        @OA\Property(property="qty", type="integer"),
                        @OA\Property(property="base_unit_type", type="string")
                     )
              )
          ),

          @OA\Response(
              response=200,
              description="Success",
              @OA\MediaType(
                  mediaType="application/json",
              )
          ),
          @OA\Response(
              response=401,
              description="Unauthorized"
          ),
          @OA\Response(
              response=400,
              description="Invalid request",
          ),
          @OA\Response(
              response=404,
              description="not found"
          ),
      )
     */
 
    /*
      update order status API
      
      @return \Illuminate\Http\Response 
     */


    public function updateOrder(Request $request) {

        //dd($request->all());
        /**************
        *****item and order status description that value logistic will send
        ***Item Status => Pending => 1,Item not match with ordered qty => 2,Item ready to delivery => 3,Item cancelled => 4,Item received => 5
        *******
        ***shop Order Status => Pending => 1,Order already at warehouse center=>2,Order delivery => 3,Order received=>4,Cancel Reason reject => 5,Cancel Reason returned => 6,Cancel Reason failed delivery => 7,Cancel Reason cancelled => 8
        *****This value will be replaced by order status table
        *****Shop status same as main order status******
        **********/
        $item_status_arr = [1=>1,2=>7,3=>8,4=>4,5=>3];
        $order_status_arr = [2=>5,3=>8,4=>3,5=>9,6=>10,7=>11,8=>12,9=>12];
        $main_order_status_arr = [2=>5,3=>8,4=>3,5=>9,6=>10,7=>11,8=>12,9=>12];

        /**Note=> In table logistic status id is our main status id get from status arr because cancel has more id**/
        /****updating log **/
        $log_obj = new OrderUpdateApiLog;
        $log_obj->api_json = json_encode($request->all());
        $log_obj->save();
        $log_id = $log_obj->id;
        $order_id = $order_shop_id = 0;
        try {
            $validator = Validator::make($request->all(), [
                'main_orer_id' => 'required',
                'main_order_status' => 'required',
                /*'line_no' => 'required',
                'item_no' => 'required',
                'sku' => 'required',
                'qty' => 'required',
                'status_line' => 'required',*/
            ]);
            
            $data = [];
            
            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $field => $validationMessage) {
                    $data['error'][] = $validationMessage;
                }
                $success = [
                    'status' => ResponseHTTP::HTTP_BAD_REQUEST,
                    'data' => $data
                ];
                $message = 'Validation failed!.';
            } else {
                $errordata = [];

                $main_orer_formatted_id = $request->main_orer_id;
                $l_main_order_status = $request->main_order_status;
                $main_order_remark = $request->main_order_remark;

                $main_status_id = isset($order_status_arr[$l_main_order_status])?$order_status_arr[$l_main_order_status]:0;
                
                $main_order = \App\Order::where('formatted_id',$main_orer_formatted_id)->first();

                if(empty($main_order)){
                    $errordata['error'][] = 'Invalid main order formatted id';
                }

                if(!$main_status_id){
                    $errordata['error'][] = 'Invalid main order status id';
                }
                if(empty($errordata)){
                    foreach ($request->order_detail as $key => $value) {

                        $shop_formatter_id = $value['shop_formatter_id'];
                        $shop_ord_detail = OrderShop::where(['shop_formatted_id'=>$shop_formatter_id,'order_id'=>$main_order->id])->first();
                        
                        if(empty($shop_ord_detail)){
                            $errordata['error'][] = 'Invalid shop formatted id '.$shop_formatter_id;
                            break;
                        }
                        $l_shop_order_status = (int)$value['shop_order_status'];
                        $shop_order_remark = $value['shop_order_remark'];

                        /***getting mysql table shop status id****/
                        $shop_status_id = isset($order_status_arr[$l_shop_order_status])?$order_status_arr[$l_shop_order_status]:0;

                        $logistic_shop_status = $l_shop_order_status;

                        if($shop_status_id == 0){
                            $errordata['error'][] = 'Invalid shop status for '.$shop_formatter_id;
                            break;
                        }

                        /***getting all item details******/
                        $ord_detail_id_arr  = $item_detail_arr = [];
                        foreach ($value['item_details'] as $ikey => $ivalue) {
                            $order_detail_id = (int)$ivalue['order_detail_id'];

                            if(isset($item_status_arr[(int)$ivalue['status_line']])){
                                $item_status_id = $item_status_arr[(int)$ivalue['status_line']];
                                $l_item_status_id = (int)$ivalue['status_line'];

                                $order_details = OrderDetail::where(['order_shop_id'=>$shop_ord_detail->id,'id'=>$order_detail_id])->first();

                                if($order_details && empty($errordata)){

                                    if($order_details->status !=$item_status_id){
                                        $line_remark = $ivalue['line_remark'];
                                        $order_details->status = $item_status_id;
                                        $order_details->logistic_status = $item_status_arr[$l_item_status_id];
                                        if($line_remark){
                                            $order_details->api_remark = $line_remark;
                                        }
                                        $order_details->save();

                                        if($item_status_id == 3)
                                            $comment = 'Received';
                                        else
                                            $comment = \App\OrderStatusDesc::getStatusVal($item_status_id);
                                       
                                        $comment = $comment.' ('.$order_details->category_name.')';
                                        
                                        /****update entry in order transaction******/
                                        $transaction_arr = ['order_id'=>$order_details->order_id,'order_shop_id'=>$order_details->order_shop_id,'order_detail_id'=>$order_details->id,'event'=>'delivery','comment'=>$comment,'updated_by'=>'logistic'];

                                        $update_transaction = \App\OrderTransaction::updateOrdTrans($transaction_arr);
                                    }

                                }else{
                                    $errordata['error'][] = 'invalid item detai id for item id '.$order_detail_id;
                                }
                                
                            }else{
                                $errordata['error'][] = 'invalid status for item id '.$order_detail_id;
                            }
                        }/**end foreach for item details**/

                        if(empty($errordata)){
                            $order_shop_id = $shop_ord_detail->id;
                            /****updating shop order status******/
                            if($logistic_shop_status >=5 && $logistic_shop_status <= 9){
                                $shop_status_id = 4;
                            }

                            $shop_ord_detail->order_status = $shop_status_id;
                            $shop_ord_detail->logistic_status = $order_status_arr[$logistic_shop_status];
                            if($shop_order_remark){
                                $shop_ord_detail->api_remark = $shop_order_remark;
                            }
                            if($shop_status_id !=4){
                                $shop_ord_detail->seller_status_at = date('Y-m-d H:i:s');
                            }
                            $shop_ord_detail->save();

                            $comment = '';
                            
                            if($shop_status_id == 3){
                                $comment = GeneralFunctions::getOrderText('order_completed');
                            }else{
                                if($logistic_shop_status >=5 && $logistic_shop_status <= 9){
                                    $comment = GeneralFunctions::getOrderText('order_cancelled');
                                }
                                
                            }
                            if($comment){
                                /****update entry in order transaction******/
                                $transaction_arr = ['order_id'=>$shop_ord_detail->order_id,'order_shop_id'=>$shop_ord_detail->id,'order_detail_id'=>0,'event'=>'delivery','comment'=>$comment,'updated_by'=>'logistic'];

                                $update_transaction = \App\OrderTransaction::updateOrdTrans($transaction_arr);
                            }

                        }/**end updating shop error**/
                        
                    }/**end foreach for shop details**/
                }
                
                if(empty($errordata)){
                    $order_id = $main_order->id;
                    if($l_main_order_status >=5 && $l_main_order_status <= 9){
                        $main_status_id = 4;
                    }
                    /*******updating main order********/
                    $main_order->order_status = $main_status_id;
                    $main_order->logistic_status_id = $order_status_arr[$l_main_order_status];
                    if($main_order_remark){
                        $main_order->api_remark = $main_order_remark;
                    }
                    $main_order->save();

                    $comment = '';
                        
                    if($main_status_id == 3){
                        $comment = GeneralFunctions::getOrderText('order_completed');
                    }else{
                        if($l_main_order_status >=5 && $l_main_order_status <= 9){
                            $comment = GeneralFunctions::getOrderText('order_cancelled');
                        }
                        
                    }
                    if($comment){
                        /****update entry in order transaction******/
                        $transaction_arr = ['order_id'=>$main_order->id,'order_shop_id'=>0,'order_detail_id'=>0,'event'=>'delivery','comment'=>$comment,'updated_by'=>'logistic'];

                        $update_transaction = \App\OrderTransaction::updateOrdTrans($transaction_arr);
                    }
                    $success = [
                        'status' => ResponseHTTP::HTTP_OK ,
                        'data' => [],
                    ];

                    $message = 'Order updated';
                }else{
                    if(count($ord_detail_id_arr) != count($order_details)){
                        $errordata['error'][] = 'invalid item id';
                    }
                    $success = [
                        'status' => ResponseHTTP::HTTP_BAD_REQUEST,
                        'data' => $errordata
                    ];
                    $message = 'Invalid item status or order_detail_id';
                }
            }

            /***updating log response***/
            $update_log_res = OrderUpdateApiLog::where('id',$log_id)->update(['order_id'=>$order_id,'order_shop_id'=>$order_shop_id,'response'=>json_encode($success)]);

            return $this->APIResponse($success ,$message);
        } catch (\Exception $e) {
            $success = [
                        'status' => 0,
                    ];
            return $this->APIResponse($success,$e->getMessage());
        }
    }

    protected function APIResponse($success,$message){
  
        $response = [];
       
        switch ($success['status']) {
            case 200:
                $arr = ['description'=>'success'];
                break;
            case 401:
                $arr = [ "description"=> "Unauthorized"];
                break;
            case 400:
                $arr = [ "description"=> "Invalid request",'error'=>$success['data']['error']];
                break;
            case 404:
                $arr = [ "description"=> "not found"];
                break;
            default:
                $arr = [ "description"=> $message];
                break;
        }
        $response['response'][$success['status']] = $arr;
        return json_encode($response);
    }
 
}
?>