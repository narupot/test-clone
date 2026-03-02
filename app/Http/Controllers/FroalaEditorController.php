<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use File;
use Config;
use Image; 
use Storage;
use Lang;



class FroalaEditorController extends Controller
{
    //

    public function index(){
    	//$filepath = '/files/froala_uploads/'.md5(Auth::id()).'/';
    	return view('admin.froala.froalaeditor');
    }


    public function normal(){

     	
    	//$filepath = '/files/froala_uploads/'.md5(Auth::id()).'/';
    	return view('admin.froala.froalaeditor-normal');
    }


    public function normalnew(){

    	return view('admin.froala.froalaeditor-newnormal');
    }


    public function uploadImage(Request $request){

        //dd($request->all());

       /* $checkmedia = checkLimitUsage('media')['status'];
        if(!$checkmedia){

            return ['error'=>Lang::get('admin.package_media_limit_exceeded')];
            //Package Media Limit exceeded
        }*/
    	
		$input 				= $request->all();
    	$location 			= $input['location'];
		$fileData 			= $request->file('image'); //this gets the image data for 1st argument
        // $filename 			= $fileData->getClientOriginalName();
        $file_extension     = $fileData->getClientOriginalExtension();
        $filename           = mt_rand(100,999).'-'.microtime().'-'.'froalaeditor'.".$file_extension";
        // $completePath 		= url('/' . $location . '/' . $filename);
        //$destinationPath 	= 'files/froala_uploads/'.md5(Auth::id()).'/';
        $destinationPath = $request->folder;
        //dd($destinationPath);
        //$checkuse = checkLimitUsage('media');

        $path = public_path().$destinationPath;
                



        //dd($path);
        if(!(is_dir($path))){        	
        	File::makeDirectory($path, $mode = 0777, true, true);
        }

        $img = Image::make($request->file('image'));

		// save image
		$img->save($path.$filename);

        //$request->file('image')->move($destinationPath, $filename);
		$completePath = $destinationPath . $filename;
		//dd($completePath);
		// $fileupload = new FileUpload;
		// $fileupload->title = $filename;
		// $fileupload->path = $completePath;
		// $fileupload->save();
		// if($fileupload->save()){
        $folder_size = getFolderSize($path);

        //updateLimitUsage('media','add',$folder_size); 
		return stripslashes(response()->json(['link' => $completePath])->content());

    }

    public function froalaLoadImages(){


    	include app_path().'/froala_php_sdk/lib/FroalaEditor.php';
    	// Load the images.
		try {
		  $response = \FroalaEditor_Image::getList($_REQUEST['folder']);
		  
		  echo stripslashes(json_encode($response));
		}
		catch (Exception $e) {
		  http_response_code(404);
		}
    }

    public function froalaNewFolder(Request $request){
    	//dd($request->all());
    	//$path = '/files/froala_uploads/'.Auth::id().'/';
    	// Include the editor SDK.
		include app_path().'/froala_php_sdk/lib/FroalaEditor.php';
		
		// Delete the image.
		try {
			if(isset($_POST['renameFolder'])){
				$response = \FroalaEditor_Image::renameFolder($_REQUEST['path'],$_POST['oldName'],$_POST['newName']);
			}else{
				$response = \FroalaEditor_Image::newFolder($_REQUEST['path'],$_POST['name']);
			}
		  echo stripslashes(json_encode($response));
		}
		catch (Exception $e) {
		  http_response_code(404);
		}
    }


    public function froalaDeleteFolder(Request $request){

    	$type = $request->type;
    	$name = $request->name;
    	$src = $request->src;
    	$datetime = $request->datetime;
    	$folder = '/files/froala_uploads/'.md5(Auth::id()).'/';
		require app_path().'/froala_php_sdk/lib/FroalaEditor.php';

		try {
	
			if(isset($_POST['deleteAll']) && isset($_REQUEST['folder']) && $_POST['deleteAll'] == true  && strlen($_REQUEST['folder']) > 0){
				\FroalaEditor_Image::deleteAllFiles($_REQUEST['folder']);
				$response = array('status'=>'success','message'=>'Files & Folders Deleted Successfully');
			}elseif(isset($_POST['deleteSelected']) && $_POST['deleteSelected'] == true && isset($_POST['data']) && count($_POST['data']) > 0){
				if(count($_POST['data'])>0){
					\FroalaEditor_Image::deleteSelected();
					$response = array('status'=>'success','message'=>'Deleted successfully');
				}else{
					$response = array('status'=>'success','message'=>'No Data to Delete');
				}
			}elseif(isset($_POST['type']) && $_POST['type'] == 'folder'){
		  		$response = \FroalaEditor_Image::deleteDir($_REQUEST['folder'].$_POST['name']);
			}else{
		  		$response = \FroalaEditor_Image::delete($_POST['src']);
			}
			echo stripslashes(json_encode('Success'));
			}
			catch (Exception $e) {
			  http_response_code(404);
		}

    }


    // for s3 bucket testing
    public function s3UploadInterface(){
    	return view('image-upload');	

    }

    public function s3ImageUploadPost(Request $request){
    	$this->validate($request, [
    		'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $imageName = time().'.'.$request->image->getClientOriginalExtension();
        $image = $request->file('image');
        $t = Storage::disk('s3')->put($imageName, file_get_contents($image), 'public');
        $imageName = Storage::disk('s3')->url($imageName);
	   	return back()
    		->with('success','Image Uploaded successfully.')
    		->with('path',$imageName);
    }
}
