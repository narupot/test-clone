<?php

namespace FroalaEditor;

use FroalaEditor\Utils\DiskManagement;

class Image {
  public static $defaultUploadOptions = array(
    'fieldname' => 'file',
    'validation' => array(
      'allowedExts' => array('gif', 'jpeg', 'jpg', 'png', 'svg', 'blob'),
      'allowedMimeTypes' => array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/x-png', 'image/png', 'image/svg+xml')
    ),
    'resize' => NULL
  );

  /**
  * Image upload to disk.
  *
  * @param fileRoute string
  * @param options [optional]
  *   (
  *     fieldname => string
  *     validation => array OR function
  *     resize: => array
  *   )
  * @return {link: 'linkPath'} or error string
  */
  public static function upload($fileRoute, $options = NULL) {
    // Check if there are any options passed.
    if (is_null($options)) {
      $options = Image::$defaultUploadOptions;
    } else {
      $options = array_merge(Image::$defaultUploadOptions, $options);
    }

    // Upload image.
    return DiskManagement::upload($fileRoute, $options);
  }

  /**
  * Delete image from disk.
  *
  * @param src string
  * @return boolean
  */
  public static function delete($src) {
    // Delete image.
    return DiskManagement::delete($src);
  }
  
  /**
  * Delete Folder from disk.
  *
  * @param path string
  * @return boolean
  */
  public static function deleteDir($src) {
    // Delete image.
    return DiskManagement::deleteDir($_SERVER['DOCUMENT_ROOT'].$src);
  }
  
  /**
  * Delete Selected Files from disk.
  *
  * @return boolean
  */
  public static function deleteSelected() {
    // Delete image.
    return DiskManagement::deleteSelected();
  }
  
  /**
  * Delete All Files from disk.
  *
  * @return boolean
  */
  public static function deleteAllFiles($folder) {
    // Delete image.
    return DiskManagement::deleteAllFiles($folder);
  }
  
  /**
  * Create New Folder.
  *
  * @param1 path string
  * @param2 name string
  * @return array
  */
  public static function newFolder($path,$name) {
	 /* $response = array();
	  if(mkdir($_SERVER['DOCUMENT_ROOT'].$path.$name,0700)){
			$response = array("name" => $name,"type"=>"folder","url"=>$path.$name,"thumb"=>"froala_editor1/img/folder-icon.png");
		}else{
			throw new Exception('Problem in creating New Folder');
		}
	  return $response;*/
    return DiskManagement::newFolder($path,$name);

  }


  public static function newFolderWithName($path,$name) {
    return DiskManagement::newFolderWithName($path,$name);
  }

  /**
  * Rename Folder.
  *
  * @param1 path string
  * @param2 oldName string
  * @param3 newName string
  * @return array
  */
  public static function renameFolder($path,$oldName,$newName) {
	  $response = array();
	  if(is_dir($_SERVER['DOCUMENT_ROOT'].$path.$oldName) && !is_dir($_SERVER['DOCUMENT_ROOT'].$path.$newName)){
			if(rename($_SERVER['DOCUMENT_ROOT'].$path.$oldName, $_SERVER['DOCUMENT_ROOT'].$path.$newName)){
				$res = $newName;
			}else{
				$res = $oldName;
			}
			$response = array("status" => "success", "newName" => $res);
		}else{
			throw new Exception('Problem in creating New Folder or Folder Already exists');
		}
	  return $response;
  }


  /**
  * List images from disk
  *
  * @param folderPath string
  *
  * @return array of image properties
  *     - on success : [images: [{url: 'url', thumb: 'thumb', name: 'name'}], folder: [{name: 'folder name'}], ...]
  *     - on error   : {error: 'error message'}
  */
  public static function getList($folderPath, $thumbPath = null) {
	  //$folderPath = str_replace('//','/',$folderPath);
    if (empty($thumbPath)) {
      $thumbPath = $folderPath;
    }

    // Array of image objects to return.
    $response = array();

    $absoluteFolderPath = $_SERVER['DOCUMENT_ROOT'] . $folderPath;
    
    // Image types.
    $image_types = Image::$defaultUploadOptions['validation']['allowedMimeTypes'];

    // Filenames in the uploads folder.
    $fnames = scandir($absoluteFolderPath);

/*echo json_encode($fnames);
die;*/

    // Check if folder exists.
    if ($fnames) {
		$folder_icon = '/froala_editor1/img/folder-icon-2.png';
		$folders=array();$images=array();
      // Go through all the filenames in the folder.
      foreach ($fnames as $name) {
		if($name=='.' || $name == '..'){continue;}
        // Filename must not be a folder.
        if (is_dir($absoluteFolderPath.'/'.$name)) {

          $tFiles = getFilesCount($absoluteFolderPath.'/'.$name);
			// if is directory
			array_push($folders, array(
				'type' => 'folder',
				'url' => $folderPath.$name,
				'thumb' => $folder_icon,
				'name' => $name,
        'totalfiles' => ' ('.$tFiles.')',
				'datetime'=>filemtime($absoluteFolderPath.'/'.$name)
			));
			
		}else{
          // Check if file is an image.
		  
          if (in_array(mime_content_type($absoluteFolderPath . $name), $image_types)) {
            // Build the image.
            $img = new \StdClass;
            $img->type = 'image';
			$img->url = $folderPath . $name;
            $img->thumb = $thumbPath . $name;
            $img->name = $name;
			$img->datetime = filemtime($absoluteFolderPath.'/'.$name);

            // Add to the array of image.
            array_push($images, $img);
          }
        }
      }
	  foreach($folders as $a){array_push($response,$a);}
	  foreach($images as $a){array_push($response,$a);}
    }

    // Folder does not exist, respond with a JSON to throw error.
    else {
      throw new Exception('Images folder does not exist!');
    }

    return $response;
  }
    
}

class_alias('FroalaEditor\Image', 'FroalaEditor_Image');
?>
