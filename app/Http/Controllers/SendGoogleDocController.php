<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\MarketPlace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Session;
use Route;
use Cache;
use Config;

use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Contract\Firestore;
use Google\Cloud\Firestore\FirestoreClient;

class SendGoogleDocController extends MarketPlace
{
    //
    public $firestore;

    public function __construct(Firestore $firestore){
        //$this->firestore = $firestore;
    }


    public function sendGoogleDoc(Request $request){
        //$firestore = $factory->createFirestore();
        $firebasefile = Config::get('constants.firebase_file_path');
        
        $serviceAccount = ServiceAccount::fromJsonFile($firebasefile);

        $firebase = (new Factory)
        ->withServiceAccount($serviceAccount)
        ->withDatabaseUri('https://smm-mobile-dev.firebaseio.com/')
        ->create();

        $database = $firebase->getDatabase();

        $newPost = $database->getReference('blog/posts')->push([
        'title' => 'Laravel FireBase Tutorial',
        'category' => 'Laravel'
        ]);

        echo '<pre>';
        print_r($newPost->getvalue());
    }

}
