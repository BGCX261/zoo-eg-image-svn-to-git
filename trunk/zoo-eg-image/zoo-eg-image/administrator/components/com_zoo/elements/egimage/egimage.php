<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

// register parent class
JLoader::register('ElementSimple', ZOO_ADMIN_PATH.'/elements/simple/simple.php');
define( 'EG_PHP_FOLDER', 'eg_php/' );

include_once JPATH_ROOT.DS.EG_PHP_FOLDER.'eg_util.php';

class ElementEgImage extends ElementSimple {
/** @var string */
    var $img_width;

    /** @var string */
    var $img_th_width;

    /** @var string */
    var $clean_chance;

    static $_eg_fileFolder = 'images/eg_zoo/eg_image/';
    static $_eg_uploadTypes = 'png,jpeg,jpg,gif';
    static $_eg_tableName = 'zoo_eg_image';

    function ElementEgImage() {
    // call parent constructor
        parent::ElementSimple();

        $this->type  = 'egimage';
        $this->_table_columns = array('_value' => 'TEXT', '_params' => 'TEXT');

        // set callbacks
        $this->registerCallback('upload');

        $this->_eg_elementFolder = ZOO_ADMIN_PATH.'/elements/egimage/';
    }

    function hasValue() {
        return ($this->getImage() != false);
    }

    /**
     * Функция, определеляющая статистическиую частоту
     * проведения чисток директории изображений.
     * @param <type> $chanse
     * @return <type>
     */
    function isTimeToClean($chanse) {
        return rand(0, $chanse) == 0;
    }

    function edit() {
        if ($this->isTimeToClean($this->clean_chance)) {
            $this->deleteNotBoundImages();
        }

        $root = JURI::root(true);

        $uploadURL = $root.'/index.php?option=com_zoo&view=element&task=callelement&format=raw&item_id='.$this->_item->id.'&element='.$this->name.
            '&method=upload';
        $deleteURL = $root.'/index.php?option=com_zoo&view=element&task=callelement&format=raw&item_id='.$this->_item->id.'&element='.$this->name.
            '&method=deleteFlash';
        $reorderURL = $root.'/index.php?option=com_zoo&view=element&task=callelement&format=raw&item_id='.$this->_item->id.'&element='.$this->name.
            '&method=reorder';

        $itemId = $this->_item->id;
        if ($itemId == null) {
            include $this->_eg_elementFolder.'save_first.php';
            return $output;
        }

        $image =& $this->getImage();
        $noImage = !$image->fname;
        $imgLink = $this->getImageSrc();
        $thLink = $this->getImageThSrc();

        include $this->_eg_elementFolder.'mgmt_form.php';
        return $output;
    }

    function render($view = ZOO_VIEW_ITEM) {

        // render layout
        if ($layout = $this->getLayout()) {
            return Element::renderLayout($layout, array(
            'thLink' => $this->getImageThSrc(),
            'imgLink' => $this->getImageSrc(),
            'view' => ($view == ZOO_VIEW_ITEM? 'detail': 'category')
            ));
        }

        return null;
    }

    function getImageSrc() {
        $image =& $this->getImage();
        return JURI::root(true).'/'.self::$_eg_fileFolder.$image->fname;
    }

    function getImageThSrc() {
        $image =& $this->getImage();
        return JURI::root(true).'/'.self::$_eg_fileFolder.$image->th_fname;
    }

    function &getImageInternal($itemId) {
        $db     =& JFactory::getDBO();

        $query = "SELECT * FROM `#__".self::$_eg_tableName."`
 WHERE item_id = $itemId AND element = '$this->name' LIMIT 1";


        $db->setQuery($query);

        $res =& $db->loadObjectList();
        if (sizeof($res)) {
            foreach ( $res as &$image ) {
                return $image;
            }
        }

        return null;
    }

    function &getImage() {
        $itemId = $this->_item->id;
        $image =& $this->_eg_cached_image;

        if (!$image || !$image->fname) {
            $this->_eg_cached_image = $this->getImageInternal($itemId);
        }
        return $this->_eg_cached_image;
    }

    function deleteFromTable($item_id) {
        $db     =& JFactory::getDBO();

        $query = "DELETE FROM `#__".self::$_eg_tableName."`
 WHERE item_id = $item_id AND element = '$this->name'";

        $db->setQuery($query);

        if (!$db->query()) {
        //$this->setError($db->getErrorMsg());
            return array(false, $db->getErrorMsg());
        }

        return array(true);
    }

    function upload() {
        include JPATH_ROOT.DS.EG_PHP_FOLDER.'eg_file_upload.php';

        $fileFolder = JPATH_ROOT.DS.self::$_eg_fileFolder;

        $resArr = array();

        $resArr['res'] = false;

        $elementName = $_REQUEST['element'];

        if($_FILES['upfile']['name']) {

        //Получение файла.
            list($error,$file) = upload('upfile',$fileFolder,$this->_eg_imgTypes);

            if($error) {
                $resArr['error'] = $error;
            }else {
            //Определение имен.
                $initFile = $fileFolder.$file;
                $resImgName = 'res_'.$file;
                $thImgName = 'th_'.$file;
                $resizedFile = $fileFolder.$resImgName;
                $thFile = $fileFolder.$thImgName;

                //Ресайзинг.
                smartImageResize($this->img_width, $initFile, $resizedFile);
                smartImageResize($this->img_th_width, $initFile, $thFile);

                $root = JURI::root(true).'/';

                //Формирование JSON.
                $resArr['img'] = $root.self::$_eg_fileFolder.$resImgName;
                $resArr['th'] = $root.self::$_eg_fileFolder.$thImgName;
                $resArr['fname'] = $resImgName;

                list($opRes, $error) = $this->addToTable($resImgName, $thImgName);
                if (!$opRes) {
                    $resArr['error'] = $error;
                }else {
                    $resArr['res'] = true;
                }

            }

        }else {
            $resArr['error'] = 'Файл не выбран.';
        }

        return json_encode($resArr);
    }


    function addToTable($fileName, $thFileName) {
        $itemId = $this->_item->id;
        $db     =& JFactory::getDBO();

        $query = "DELETE FROM #__".self::$_eg_tableName." WHERE item_id = $itemId AND element = '$this->name'";
        $db->setQuery($query);

        if (!$db->query()) {
            return array(false, $db->getErrorMsg());
        }

        $query = "INSERT INTO `#__".self::$_eg_tableName."` (item_id , fname, th_fname, element )
        VALUES (
        '$itemId', '$fileName', '$thFileName', '$this->name'
        )";

        $db->setQuery($query);

        if (!$db->query()) {
        //$this->setError($db->getErrorMsg());
            return array(false, $db->getErrorMsg());
        }

        return array(true);
    }

    function getFilesDir() {
        return JPATH_ROOT.DS.self::$_eg_fileFolder;
    }

    function createFilesDir() {
        mkdir($this->getFilesDir(), 0777 , true);
    }

    function deleteFilesDir() {
        recursiveDelete($this->getFilesDir());
    }

    /*
	   Function: configAddTable
	   	   Добавление таблицы связей картинок в БД, если еще нет.

	   Returns:
		  boolean - true при успехе
	*/
    function configAddTable() {

    // init vars
        $db     =& JFactory::getDBO();
        $tables = $db->getTableList();

        // create rating table, if not exists
        if (!$this->tableExists()) {

            $this->createFilesDir();

            $query = "CREATE TABLE #__".self::$_eg_tableName." ("
                ." id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,"
                ." element VARCHAR(255),"
                ." item_id INT,"
                ." fname VARCHAR(255),"
                ." th_fname VARCHAR(255)"
                //." init_name VARCHAR(255)"
                .") TYPE=MyISAM";

            $db->setQuery($query);

            if (!$db->query()) {
                $this->setError($db->getErrorMsg());
                return false;
            }
        }

        return true;
    }


/*
	   Function: configAdd
	   	  Добавление конфигурации элемента.
	   	  Перегружаем здесь, чтобы создавалась таблица в БД.

	   Parameters:
          $data - конфигурация элемента

	   Returns:
		  boolean - true при успехе
	*/
    function configAdd($data) {

    // create rating table, if not exists
        if (!$this->configAddTable()) {
            return false;
        }

        return parent::configAdd($data);
    }

    function configSave($data) {

    // create rating table, if not exists
        if (!$this->configAddTable()) {
            return false;
        }

        return parent::configSave($data);
    }


    /**
     * Проверка, находится ли такое имя файла в таблице.
     * @param <type> $db
     * @param <type> $fname
     * @return <type>
     */
    function checkIfImageInDB($db, $fname) {
        $query = "SELECT * FROM `#__".self::$_eg_tableName."`
WHERE fname='$fname' or th_fname='$fname'";
        $db->setQuery($query);
        $db->query();
        return ($db->getNumRows() != 0);
    }

    /**
     * Проверка существования таблицы изображений.
     * @return <type>
     */
    function tableExists() {
        $db     =& JFactory::getDBO();
        $tables = $db->getTableList();
        return in_array($db->getPrefix().self::$_eg_tableName, $tables);
    }

    /**
     * Удаление несвязанных с таблицей БД файлов в директории.
     */
    function deleteNotBoundImages() {
        $db     =& JFactory::getDBO();

        if ($this->tableExists()) {


            $dir = $this->getFilesDir();
            if ($handle = opendir($dir)) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
                        if (!$this->checkIfImageInDB($db, $file)) {
                            deleteFile($dir.$file);
                        }
                    }
                }
                closedir($handle);
            }

        }else {
            try {
                $this->deleteFilesDir();
            }catch (Exception $e ) {
                $this->setError($e->getMessage());
            }
            $this->createFilesDir();
        }

    }

}

?>