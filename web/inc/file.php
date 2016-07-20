<?php
class file {
  private static $fileNameLength = 16;
  private static $uploadsFolder = __DIR__."/../uploads";

  private static function generateRandomName($file) {
  	$chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
  	$name = '';
  	for($i = 0; $i < $this->fileNameLength; $i++) {
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
