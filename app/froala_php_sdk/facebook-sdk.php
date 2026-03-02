<?php
require('Facebook-php-gsdk.v5.4/autoload.php');
class FB{
	var $user_id,$graph_url='graph.facebook.com/',$myPhotos_url='me/photos/';
	var $appID = '853378044801613',$appSecret='10ef5385f247e6774f4dff1fa12de75f';
	var $fb,$accessToken=false,$ready=false,$response;
	
	public function __construct(){
		$this->fb = new Facebook\Facebook([
			'app_id' => $this->appID,
			'app_secret' => $this->appSecret,
			'default_graph_version' => 'v2.8',
		]);
		//$this->getAppToken();
		if($this->accessToken){
			$this->ready=true;
		}
	}
	
	public function callback_login(){


$helper = $this->fb->getRedirectLoginHelper();

try {
  $this->accessToken = $helper->getAccessToken();
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

if (! isset($accessToken)) {
  if ($helper->getError()) {
    header('HTTP/1.0 401 Unauthorized');
    echo "Error: " . $helper->getError() . "\n";
    echo "Error Code: " . $helper->getErrorCode() . "\n";
    echo "Error Reason: " . $helper->getErrorReason() . "\n";
    echo "Error Description: " . $helper->getErrorDescription() . "\n";
  } else {
    header('HTTP/1.0 400 Bad Request');
    echo 'Bad request';
  }
  exit;
}

// The OAuth 2.0 client handler helps us manage access tokens
$oAuth2Client = $fb->getOAuth2Client();

// Get the access token metadata from /debug_token
$tokenMetadata = $oAuth2Client->debugToken($this->accessToken);

// Validation (these will throw FacebookSDKException's when they fail)
$tokenMetadata->validateAppId($this->appID); // Replace {app-id} with your app id
// If you know the user ID this access token belongs to, you can validate it here
//$tokenMetadata->validateUserId('123');
$tokenMetadata->validateExpiration();

if (! $accessToken->isLongLived()) {
  // Exchanges a short-lived access token for a long-lived one
  try {
    $this->accessToken = $oAuth2Client->getLongLivedAccessToken($this->accessToken);
  } catch (Facebook\Exceptions\FacebookSDKException $e) {
    echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n";
    exit;
  }
  
}

$_SESSION['fb_access_token'] = (string) $this->accessToken;
$this->getMyImages();
$this->ready=true;

	}
	
	private function UserAuth(){
		$helper = $this->fb->getRedirectLoginHelper();
		$permissions = ['email']; // Optional permissions
		$loginUrl = $helper->getLoginUrl('http://froala.local.com/froala_php_sdk/facebook-sdk.php?callback_login=true', $permissions);
		//echo '<a href="' . htmlspecialchars($loginUrl) . '">Log in with Facebook!</a>';
		echo json_encode(array('link'=>htmlspecialchars($loginUrl)));
	}
	
	public function getMyImages(){
		if(!$this->ready){$this->UserAuth();}

		try {
		  // Returns a `Facebook\FacebookResponse` object
		  $response = $this->fb->get('/me?fields=id,name', $this->accessToken);
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
		  echo 'Graph returned an error: ' . $e->getMessage();
		  exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
		  echo 'Facebook SDK returned an error: ' . $e->getMessage();
		  exit;
		}
		
		$user = $response->getGraphUser();
		return $user;
		echo 'Name: ' . $user['name'];
		// OR
		// echo 'Name: ' . $user->getName();

	}
	
	public function getByUserID($id){
		//codes goes here
		
	}
	
	private function getAppToken(){
		//$url = 'oauth/access_token?client_id='.$this->appID.'&client_secret='.$this->appSecret.'&grant_type=client_credentials&fb_exchange_token=';
		//$response = $this->curlGET($this->graph_url.$url);
		
		$helper = $this->fb->getCanvasHelper();

		try {
		  $this->accessToken = $helper->getAccessToken();
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
		  // When Graph returns an error
		  echo 'Graph returned an error: ' . $e->getMessage();
		  exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
		  // When validation fails or other local issues
		  echo 'Facebook SDK returned an error: ' . $e->getMessage();
		  exit;
		}
		
		if (! isset($accessToken)) {
		  echo 'No OAuth data could be obtained from the signed request. User has not authorized your app yet.';
		  exit;
		}
	}
	
	private function curlGET($url){
		$ch = curl_init();
		if($ch){  
			echo "URL: ".$url;
			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_HEADER, 0); 
			curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
			$output=curl_exec($ch);
			if($output===false){
				echo "Error: ".curl_error($ch);
			}
			curl_close($ch);
			return $output;
		}else{
			die("ERROR: CURL Disabled.");
		}
	}
	
}

$obj = new FB;
if(isset($_GET['callback_login'])){
	echo $obj->callback_login();
}else{
echo $response = $obj->getMyImages();
}
?>