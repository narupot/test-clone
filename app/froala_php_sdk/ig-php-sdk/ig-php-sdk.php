<?php
class IG{
var $response=array(),
	//$client_id = Config::get('services.instagram.client_id'),
	//$client_secret = Config::get('services.instagram.client_secret'),
	//$client_id = Session::get('instagram_client_id'),
	//$client_secret = Session::get('instagram_client_secret'),

	$client_id = 'efd0937de69d4e569bea8732b7410582',
	$client_secret = '7cdb96e00eb84b8abd96bb5497889e28',
	$access_token = '',
	$auth_code = '';

var $urls = array(
	'auth_url' => 'https://api.instagram.com/oauth/authorize/?client_id=efd0937de69d4e569bea8732b7410582&response_type=code&redirect_uri=',
	'access_token_url' => 'https://api.instagram.com/oauth/access_token',
	'user_media' => 'https://api.instagram.com/v1/users/self/media/recent/?access_token='
);

function __construct(){
	//this->urls['auth_callback'] = 'https://'.$_SERVER['HTTP_HOST'].'/insta-callback.php';
	
	$this->urls['auth_callback'] = action('MediaManagerController@instagramCallBack');
	$this->urls['auth_url'] .= urlencode($this->urls['auth_callback']);
	if(Session::get('ig-code')){
		$this->auth_code = Session::get('ig-code');
	}
	
	if(Session::get('access_token')){
		$this->access_token = Session::get('access_token');
	}
}

function checkLogin(){
	//dd(Session::get('ig-code'),Cookie::get('ig-code'));
	if(Session::get('ig-code') or Cookie::get('ig-code')){
		if(!Session::get('ig-code')){
			Session::set('ig-code',base64_decode(Cookie::get('ig-code')));
			$this->auth_code = Session::get('ig-code');
		}
		//$this->response = array('status'=>'success','message'=>'user logged in');
		$this->getAccessToken();
	}else{
		$this->response = array('status'=>'error','code'=>100,'message'=>'user not logged in','link'=>$this->urls['auth_url']);
	}

}



function check_auth_code(){
	if(!Session::get('ig-code')){
		$this->response = array('status'=>'error','code'=>101,'message'=>'user not logged in','link'=>$this->urls['auth_url']); return false;
	}
	return true;
}

function check_access_token(){
	if(!Session::get('access_token')){
		$this->response = array('status'=>'error','code'=>102,'message'=>'user not logged in','link'=>$this->urls['auth_url']); return false;
	}
	return true;
}

function getAccessToken(){
	
	if(!Session::get('ig-code')){
		$this->checkLogin();
		return true;
	}
	if(Session::get('access_token')){
		$this->response = array('status'=>'success','message'=>'access token generated');return true;
		$this->getUserMedia();
		return true;
	}
	//if(!$this->check_auth_code()){return false;}
	$post_data = array(
		'client_id' => $this->client_id,
		'client_secret' => $this->client_secret,
		'grant_type' => 'authorization_code',
		'redirect_uri' => $this->urls['auth_callback'],
		'code' => $this->auth_code,
	);
	$res = $this->curlPost($this->urls['access_token_url'],$post_data);
	//dd($res);
	$r = json_decode($res,true);
	if(isset($r['access_token'])){
		Session::put('access_token',$r['access_token']);
		Session::put('user_data',$r['user']);
		$this->response = array('status'=>'success','message'=>'access token generated');
		$this->getUserMedia();
	}else{
		$this->response = array('status'=>'error','code'=>103,'message'=>'error occured while generating access token');
	}
}

function getUserMedia(){
	if(!Session::get('access_token')){
		$this->getAccessToken(); 
		return false;
	}
	//dd(Session::get('access_token'));
	$res = $this->curlGet($this->urls['user_media'].Session::get('access_token'));
	//$res = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/insta-resp.txt');
	$r = json_decode($res,true);
	
	if(isset($r['data']) && count($r['data']) > 0){
		
		$rr = array();$rdata=$r['data'];
		for($i=0;$i<count($r['data']);$i++){
			if(!isset($rdata[$i]['type']) || $rdata[$i]['type'] != 'image'){continue;}
			//{type: "image",subType: "fb-image",url: r[i].images.standard_resolution.url,thumb: s[j].images[(s[j].images.length-1)].source,name: s[j].id}
			
			$rr[] = array('type'=>'image','subType'=>'fb-image','url'=>$rdata[$i]['images']['standard_resolution']['url'],'thumb'=>$rdata[$i]['images']['thumbnail']['url'],'name'=>$rdata[$i]['id'],'datetime'=>$rdata[$i]['created_time']);
			
		}
		
		$this->response = array('status'=>'success','message'=>'data returned','data' => $rr);
	}else{
		$this->response = array('status' => 'error','code' => 104,'message' => 'error occured while generating access token');
	}
}


function print_response(){
	echo json_encode($this->response);
}

function curlPost($url,$post_data=array()){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

function curlGet($url){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_POST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

}
?>