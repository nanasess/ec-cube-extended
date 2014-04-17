<?php
use WindowsAzure\Table\Models\Entity;
use WindowsAzure\Table\Models\EdmType;

class BlobFile extends Entity {

    var $file_name;
    var $real_filepath;

    public function __construct() {
  
    }

    public function getResources($flag = 'r') {
        return fopen($this->real_filepath, $flag);
    }

    public function getMtime() {
        return filemtime($this->real_filepath);
    }

    public function getMimeType() {
        $objFinfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $objFinfo->file($this->real_filepath, $finfo);
        finfo_close($finfo);
        return $mimeType;
    }
}