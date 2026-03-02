<?php 
namespace App\Http\Controllers\Admin\Broadcast;

use Artisan;
use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;
use App\Http\Controllers\MarketPlace;

use Auth;
use ZipArchive;
use Exception;
use Config;
use Crypt;
use Lang;


class BroadcastController extends MarketPlace
{
	
    public function __construct(){
        $this->middleware("admin.user");
    }

    public function index(Request $request){
    	// $permission = $this->checkUrlPermission('broadcast');
        // if($permission === true) {

			$notificationData = \App\Broadcast::where('id',$request->message)->first();

			if($notificationData->is_readed=='0' || $notificationData->open_in_popup=='1'){
				\App\Broadcast::where('id',$request->message)->update(['is_readed'=>'1','open_in_popup'=>'0']);
			}

    		return view('admin.broadcast.broadcast_notification_details',['notificationData'=>$notificationData]);
    	// }
    }

    public function broadcastNotifications(Request $request){
    	// $permission = $this->checkUrlPermission('broadcast');
        // if($permission === true) {

			$notificationList = \App\Broadcast::get(); 
	    	return view('admin.broadcast.broadcast_notification_list',['broadcastNotificationList'=>$notificationList]);
    	// }
    }

    public function deleteNotification(Request $request){

    	if(\App\Broadcast::where('id',$request->id)->delete()){
            return redirect()->action('Admin\Broadcast\BroadcastController@broadcastNotifications')->with('succMsg', Lang::get('admin_notification.delete_success'));
    	}else{
            return redirect()->action('Admin\Broadcast\BroadcastController@broadcastNotifications')->with('errorMsg', Lang::get('admin_notification.went_wrong'));
    	}
    }

}