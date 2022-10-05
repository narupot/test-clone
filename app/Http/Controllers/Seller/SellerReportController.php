<?php
namespace App\Http\Controllers\Seller;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Helpers\CustomHelpers;
use DateTime;

use Exception;

use Config;
use Auth;
use Lang;
use DB;


class SellerReportController extends Marketplace
{
	public function __construct(){
		$this->middleware('authenticate');
	}

	public function index(Request $request){

		if(Auth::user()->user_type!='seller')
			abort(404);
			
		$shopData = \App\Shop::where(['user_id'=>Auth::user()->id])->select('id','shop_url')->first();
		$total_sale_scince_opening = \App\OrderDetail::where(['shop_id'=>$shopData->id])->sum('total_price');
		$remaining_balance_from_smm = 30089.00;
		$recieved_balance_from_smm = 70200.00;
		$total_orders = $this->getTotalOrders($shopData->id);
		$total_users = $this->getTotalUsers($shopData->id);
		$today_ord_anount = $this->getOrdersByTime($shopData->id,0);
		$last7days_ord_anount = $this->getOrdersByTime($shopData->id,6);
		$last30days_ord_anount = $this->getOrdersByTime($shopData->id,29);

	    $best_perfor_products = $this->getBestPerformingproducts($shopData->id);
	    $badgeSize = CustomHelpers::getBadgeSize();
        $badgeGrade = CustomHelpers::getBadgeGrade();

        $to_date = date("Y-m-d");
        $from_date = date('Y-m-d', strtotime($to_date.'- 365 days'));
        //dd($to_date,$from_date);
	    ///dd($best_perfor_products);
		return view('seller.report.index',['total_sale_scince_opening'=>$total_sale_scince_opening,'recieved_balance_from_smm'=>$recieved_balance_from_smm,'remaining_balance_from_smm'=>$remaining_balance_from_smm,'total_orders'=>$total_orders,'total_users'=>$total_users,'today_ord_anount'=>$today_ord_anount,'last7days_ord_anount'=>$last7days_ord_anount,'last30days_ord_anount'=>$last30days_ord_anount,'best_perfor_products'=>$best_perfor_products,'badgeSize'=>$badgeSize,'badgeGrade'=>$badgeGrade,'from_date'=>$from_date,'to_date'=>$to_date]);
	}

	protected function getTotalOrders($shop_id){
		$total_orders = \App\OrderDetail::where(['shop_id'=>$shop_id])->count(DB::raw('DISTINCT order_id'));
		return $total_orders;
	}

	protected function getTotalUsers($shop_id){
		$total_users = \App\OrderDetail::where(['shop_id'=>$shop_id])->count(DB::raw('DISTINCT user_id'));
		return $total_users;
	}

	protected function getOrdersByTime($shop_id,$days){
		$from_date = date('Y-m-d H:i:s',strtotime(date('Y-m-d')."-".$days." days"));
		$to_date = $date = date('Y-m-d H:i:s');

		$total_sale = \App\OrderDetail::where(['shop_id'=>$shop_id])->whereBetween('created_at',[$from_date,$to_date])->sum('total_price');
		return $total_sale;
	}

	protected function getBestPerformingproducts($shop_id,$total_prod=10){
		try{
			$resp = \App\ShopBestSellingProducts::where(['shop_id'=>$shop_id])->whereDate('created_at','>=',date('Y-m-d'))->count();

			if(!$resp){
				$top_products =  DB::table(with(new \App\OrderDetail)->getTable().' as od')
					->leftJoin(with(new \App\Product)->getTable().' as prd', 'od.product_id', '=', 'prd.id')
					->leftJoin(with(new \App\CategoryDesc)->getTable().' as cde', 'prd.cat_id', '=', 'cde.cat_id')
					->leftJoin(with(new \App\Badge)->getTable().' as bdg', 'prd.badge_id', '=', 'bdg.id')
					->where('od.shop_id',$shop_id)
					->select(DB::raw('SUM(total_price) as total_sale'), 'od.shop_id','od.product_id', 'prd.badge_id','prd.cat_id','cde.category_name','bdg.icon','bdg.size','bdg.grade')
					->groupBy('od.product_id')->orderByRaw('total_sale desc')->take($total_prod)->get();

				$insert_array = [];
				foreach ($top_products as $key => $prod) {
					$insert_array[$key]['total_sale'] = $prod->total_sale;
					$insert_array[$key]['shop_id'] = $prod->shop_id;
					$insert_array[$key]['product_id'] = $prod->product_id;
					$insert_array[$key]['cat_id'] = $prod->cat_id;
				}

				\App\ShopBestSellingProducts::where('shop_id',$shop_id)->delete();

				\App\ShopBestSellingProducts::insert($insert_array);
			}

			$best_sell_prd = DB::table(with(new \App\ShopBestSellingProducts)->getTable().' as sbsp')
			->leftJoin(with(new \App\Product)->getTable().' as prd','sbsp.product_id', '=', 'prd.id')
			->leftJoin(with(new \App\CategoryDesc)->getTable().' as cdesc','sbsp.cat_id', '=', 'cdesc.cat_id')
			->leftJoin(with(new \App\Badge)->getTable().' as bdg','prd.badge_id', '=', 'bdg.id')
			->where(['cdesc.lang_id'=>session('default_lang'),'sbsp.shop_id'=>$shop_id])
			->select('sbsp.total_sale','sbsp.product_id','prd.thumbnail_image','bdg.size','bdg.grade','cdesc.category_name')
			->get();
		}
		catch(Exception $e){
			$best_sell_prd = [];
		}

		return $best_sell_prd;
	}

	public function loadChartData(Request $request){
		try{
			$sale_col = "tot_sale";
			$to_date = new DateTime($request->to_date);
			$from_date = new DateTime($request->from_date);
			$diff = $from_date->diff($to_date);
			$shopData = \App\Shop::where(['user_id'=>Auth::user()->id])->select('id','shop_url')->first();
			$shop_id = isset($request->shop_id)?$request->shop_id:$shopData->id;
			$interval = $diff->days;
			//dd($interval);

			switch ($interval) {
				case ($interval >= 365):
				$start = (int) date('Y',strtotime($request->from_date));
				$end = (int) date('Y',strtotime($request->to_date));
				$mode = 'YEAR(created_at)';
				$intrvl = "year";
				# code...
				break;
				case ($interval < 365 && $interval > 31):
				$start = (int) date('m',strtotime($request->from_date));
				$end = (int) date('m',strtotime($request->to_date));
				$mode = 'MONTH(created_at)';
				$intrvl = "month";
				# code...
				break;

				case ($interval < 31 ):
				$start = (int) date('d',strtotime($request->from_date));
				$end = (int) date('d',strtotime($request->to_date));
				$mode = 'DAY(created_at)';
				$intrvl = "day";
				break;
			}

			//dd($shop_id);
			//DB::enableQueryLog();
			$top_products =  DB::table(with(new \App\OrderDetail)->getTable().' as od')
					->where('od.shop_id',$shop_id)
					->select(DB::raw('SUM(total_price) as '.$sale_col), DB::raw($mode." as ".$intrvl))
					->whereBetween('created_at',[$from_date,$to_date])
					->groupBy(DB::raw($mode))
					->get()->toArray();

			//dd($top_products);
			//dd(DB::getQueryLog());
			$rep_data_array = array();
			for($i=$start; $i<=$end;$i++){
				$tot_sale = $this->multiKeyExists($top_products,$intrvl,$sale_col,$i);

				switch ($intrvl) {
					case 'year':
						$i_str = (string) $i;
					break;
					
					case 'month':
						$monthNum  = $i;
						$dateObj   = DateTime::createFromFormat('!m', $monthNum);
						$i_str = Lang::get('seller_report.'.$dateObj->format('F')); // March
					break;

					case 'day':
						$i_str = (string) $i;
					break;
				}

				$itm_array = array($intrvl=> $i_str,$sale_col=>$tot_sale);
				array_push($rep_data_array, $itm_array);
			}

			//dd($rep_data_array);
		    $chart_html = view('seller.report.report_chart',['chart_data'=>$rep_data_array,'intrvl'=>$intrvl,'currency'=>Lang::get('common.baht'),'chart_title'=>Lang::get('seller_report.chart_title'),'sale_label'=>Lang::get('seller_report.sale_label'),'sale'=>$sale_col])->render();
		    $status = 'success';
		}
		catch(Exception $e){
			//echo $e; die;
			$status = 'error';
			$chart_html = '';
		}

		return ['status'=>$status,'chart_html'=>$chart_html];
	}

	protected function multiKeyExists($arr, $key, $val,$i) {
	    foreach ($arr as $element) {
	        if($i==$element->$key){
	            return $element->$val; 
	        }
	    }

	    return 0;
	}
}