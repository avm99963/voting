<?php
class file {
  private static $fileNameLength = 16;
  private static $uploadsFolder = __DIR__."/../uploads";

  public static function canUpload() {
    return is_writable(file::$uploadsFolder);
  }

  public static function generateRandomName($filename) {
  	$chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
  	$name = '';
  	for($i = 0; $i < file::$fileNameLength; $i++) {
	    $name .= $chars[mt_rand(0, 35)];
    }
    $explode = explode(".", $filename);
    $extension = end($explode);
    $return = $name.".".$extension; // random_name.png
  	return $return;
  }

  public static function save($file, $contents, $overwrite=false) {
    if ($overwrite === false) {
      $file = generateRandomName($file);
      while (file_exists($this->uploadsFolder."/".$file)) {
        $file = generateRandomName($file);
      }
    }
    if (file_put_contents($this->uploadsFolder."/".$file, $contents)) {
      return $file;
    } else {
      return false;
    }
  }

  public static function fileExists($file) {
    return file_exists($this->uploadsFolder."/".$file);
  }

  public static function remove($file) {
    if (file::fileExists($file)) {
      unlink($this->uploadsFolder."/".$file);
      return true;
    } else {
      return false;
    }
  }

  public static function read($file) {
    return file_get_contents($this->uploadsFolder."/".$file);
  }
}
