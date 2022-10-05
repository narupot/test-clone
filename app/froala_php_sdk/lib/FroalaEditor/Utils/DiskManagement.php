<?php

namespace FroalaEditor\Utils;

use FroalaEditor\Utils\Utils;

class DiskManagement {
  /**
  * Upload a file to the specified location.
  *
  * @param options
  *   (
  *     fieldname => string
  *     validation => array OR function
  *     resize: => array [only for images]
  *   )
  *
  * @return {link: 'linkPath'} or error string
  */
  public static function upload($fileRoute, $options) {

    $fieldname = $options['fieldname'];

    if (empty($fieldname) || empty($_FILES[$fieldname])) {
      throw new \Exception('Fieldname is not correct. It must be: ' . $fieldname);
    }

    if (
      isset($options['validation']) &&
      !Utils::isValid($options['validation'], $fieldname)
    ) {
      throw new \Exception('File does not meet the validation.');
    }

    // Get filename.
    $temp = explode(".", $_FILES[$fieldname]["name"]);

    // Get extension.
    $extension = end($temp);

    // Generate new random name.
    $name = sha1(microtime()) . "." . $extension;

    $fullNamePath = $_SERVER['DOCUMENT_ROOT'] . $fileRoute . $name;

    $mimeType = Utils::getMimeType($_FILES[$fieldname]["tmp_name"]);

    if (isset($options['resize']) && $mimeType != 'image/svg+xml') {
      // Resize image.
      $resize = $options['resize'];

      // Parse the resize params.
      $columns = $resize['columns'];
      $rows = $resize['rows'];
      $filter = isset($resize['filter']) ? $resize['filter'] : \Imagick::FILTER_UNDEFINED;
      $blur = isset($resize['blur']) ? $resize['blur'] : 1;
      $bestfit = isset($resize['bestfit']) ? $resize['bestfit'] : false;

      $imagick = new \Imagick($_FILES[$fieldname]["tmp_name"]);

      $imagick->resizeImage($columns, $rows, $filter, $blur, $bestfit);
      $imagick->writeImage($fullNamePath);
      $imagick->destroy();
    } else {
      // Save file in the uploads folder.
      move_uploaded_file($_FILES[$fieldname]["tmp_name"], $fullNamePath);
    }

    // Generate response.
    $response = new \StdClass;
    $response->link = $fileRoute . $name;

    return $response;
  }


  /**
  * Delete file from disk.
  *
  * @param src string
  * @return boolean
  */
  public static function delete($src) {
    $filePath = $_SERVER['DOCUMENT_ROOT'] . $src;
    // Check if file exists.
    if (file_exists($filePath)) {
      // Delete file.
      return unlink($filePath);
    }
    return true;
  }
  
  public static function deleteDir($dirPath) {
    if (! is_dir($dirPath)) {
      //throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
      $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    
    foreach ($files as $file) {
      if (is_dir($file)) {
        self::deleteDir($file);
      } else {
        unlink($file);
      }
    }
    rmdir($dirPath);
  }

  public static function deleteSelected() {
    foreach($_POST['data'] as $k=>$v){
      if($v['type']=='folder'){
        self::deleteDir($_SERVER['DOCUMENT_ROOT'].$v['src']);
      }else{
        self::delete($v['src']);
      }
    }
  }

  public static function deleteAllFiles($folder) {
    $files = glob($_SERVER['DOCUMENT_ROOT'].$folder . '*', GLOB_MARK);
    foreach ($files as $file) {

      if (is_dir($file)) {

        self::deleteDir($file);
      } else {
        unlink($file);
      }
    }
  }

  /**
  * Create New Folder.
  *
  * @param1 path string
  * @param2 name string
  * @return array
  */
  public static function newFolder($path,$name) {
    
    if(isset($name)){
      $response = array();    
      $s = 'New-Folder-';$i=1;
      while(is_dir($_SERVER['DOCUMENT_ROOT'].$path.$s.$i)){
        $i++;
      }
      $name = $s.$i;
    }
   
    
    if(mkdir($_SERVER['DOCUMENT_ROOT'].$path.$name,0755)){
      $response = array("name" => $name,"type"=>"folder","url"=>$path.$name,"thumb"=>"froala_editor1/img/folder-icon-2.png",'datetime'=>time(),'subtype'=>'folder');
    }else{
      throw new Exception('Problem in creating New Folder');
    }
    return $response;
  }

  public static function newFolderWithName($path,$name) {
    
    if(mkdir($_SERVER['DOCUMENT_ROOT'].$path.$name,0755)){
      $response = array("name" => $name,"type"=>"folder","url"=>$path.$name,"thumb"=>"froala_editor1/img/folder-icon-2.png",'datetime'=>time(),'subtype'=>'folder');
    }else{
      throw new Exception('Problem in creating New Folder');
    }
    return $response; 
  }
    
}

// Define alias.
class_alias('FroalaEditor\Utils\DiskManagement', 'FroalaEditor_DiskManagement');