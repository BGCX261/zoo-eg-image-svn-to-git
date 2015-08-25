<?php
function tableExists($table) {
    return mysql_num_rows( mysql_query("SHOW TABLES LIKE '".$table."'"));
}
function deleteFile($f) {
    if (file_exists($f)) {
        unlink($f);
    }
}

function smartImageResize($width, $initImg, $resImg) {
    include_once 'eg_image_smart_resize.php';
    smart_resize_image( $initImg, $width, 0, true, $resImg, false);
}


function destroy($dir) {
    $mydir = opendir($dir);
    while(false !== ($file = readdir($mydir))) {
        if($file != "." && $file != "..") {
            chmod($dir.$file, 0777);
            if(is_dir($dir.$file)) {
                chdir('.');
                destroy($dir.$file.'/');
                rmdir($dir.$file) or DIE("Couldn't delete $dir$file<br />");
            }
            else
                unlink($dir.$file) or DIE("Couldn't delete $dir$file<br />");
        }
    }
    closedir($mydir);
}

/**
 * Delete a file or recursively delete a directory
 *
 * @param string $str Path to file or directory
 */
function recursiveDelete($str) {
    if(is_file($str)) {
        return @unlink($str);
    }
    elseif(is_dir($str)) {
        $scan = glob(rtrim($str,'/').'/*');
        foreach($scan as $index=>$path) {
            recursiveDelete($path);
        }
        return @rmdir($str);
    }
}

function eg_writeOnPageOnce($str, $type = "script", $noEcho = false) {
    static $eg_linked_resourses = array();
    if (!in_array($str, $eg_linked_resourses)) {
        array_push($eg_linked_resourses, $str);
        $res;
        switch ($type) {
            case "style":
                $res =  '<link rel="stylesheet" href="'.JURI::root(true).$str.'" type="text/css" ></link>
';
                break;
            case "raw":
                $res = "$str
";
                break;
            default:
                $res = '<script type="text/javascript" src="'.JURI::root(true).$str.'"></script>
';
        }
        if (!$noEcho)
            echo $res;
        return $res;
    }
}

function eg_loadScriptOnce($str, $noEcho = false) {
    return eg_writeOnPageOnce($str, "script", $noEcho);
}

function eg_loadStyleOnce($str, $noEcho = false) {
    return eg_writeOnPageOnce($str, "style", $noEcho);
}
function eg_writeOnce($str, $noEcho = false){
    return eg_writeOnPageOnce($str, "raw", $noEcho);
}

?>
