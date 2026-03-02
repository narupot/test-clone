<?php
## Function to set file permissions to 0644 and folder permissions to 0755
function AllDirChmod( $dir = "./", $dirModes = 0755, $fileModes = 0644 ){
   $start_time = time();
   $max_execution_time = 30; // จำกัดเวลาไว้ที่ 30 วินาที
   $file_count = 0;
   
   try {
       $d = new RecursiveDirectoryIterator( $dir );
       foreach( new RecursiveIteratorIterator( $d, 1 ) as $path ){
           // ตรวจสอบ timeout ทุก 100 ไฟล์
           if (($file_count % 100) == 0 && (time() - $start_time) > $max_execution_time) {
               echo "Timeout reached, stopping permission changes<br/>";
               break;
           }
           
           if( $path->isDir() ) chmod( $path, $dirModes );
           else if( is_file( $path ) ) chmod( $path, $fileModes );
           
           $file_count++;
       }
   } catch (Exception $e) {
       echo "Error during permission changes: " . $e->getMessage() . "<br/>";
   }
}
## Function to clean out the contents of specified directory
function cleandir($dir) {
    if ($handle = opendir($dir)) {
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..' && is_file($dir.'/'.$file)) {
                if (unlink($dir.'/'.$file)) { }
                else { echo $dir . '/' . $file . ' (file) NOT deleted!<br />'; }
            }
            else if ($file != '.' && $file != '..' && is_dir($dir.'/'.$file)) {
                cleandir($dir.'/'.$file);
                if (rmdir($dir.'/'.$file)) { }
                else { echo $dir . '/' . $file . ' (directory) NOT deleted!<br />'; }
            }
        }
        closedir($handle);
    }
}
function isDirEmpty($dir){
     return (($files = @scandir($dir)) && count($files) <= 2);
}
echo "----------------------- CLEANUP START -------------------------<br/>";
$start = (float) array_sum(explode(' ',microtime()));
echo "<br/>*************** SETTING PERMISSIONS ***************<br/>";
echo "Setting all folder permissions to 755<br/>";
echo "Setting all file permissions to 644<br/>";
AllDirChmod( "." );

echo "Setting storage and public folder permissions to 777<br/>";

chmod_r("storage");
chmod_r("public");

#chmod("pear", 550);
chmod("public", 0755);

function chmod_r($path) {
    $dir = new DirectoryIterator($path);
    foreach ($dir as $item) {
        chmod($item->getPathname(), 0777);
        if ($item->isDir() && !$item->isDot()) {
            chmod_r($item->getPathname());
        }
    }
}


echo "<br/>****************** CLEARING CACHE ******************<br/>";
if (file_exists("storage/framework/cache")) {
    echo "Clearing cache<br/>";
    cleandir("var/cache");
}
if (file_exists("storage/framework/sessions")) {
    echo "Clearing session<br/>";
    cleandir("var/session");
}
if (file_exists("storage/framework/views")) {
    echo "Clearing views<br/>";
    cleandir("var/minifycache");
}

$end = (float) array_sum(explode(' ',microtime()));
echo "<br/>------------------- CLEANUP COMPLETED in:". sprintf("%.4f", ($end-$start))." seconds ------------------<br/>";
?>