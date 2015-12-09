<?php
namespace models\traits;
use \models\Data;

trait persist {
  public function save()
  {
    $filepath = PATH . Data::DB . '.xml';

    if (empty($this->errors) && Data::instance()->storage->validate() && is_writable($filepath)) {
      return Data::instance()->storage->save($filepath);
    } else {
      print_r(is_writable($filepath));
      $this->errors = array_merge(["Did not save"], $this->errors, array_map(function($error) {
        return $error->message;
      }, Data::instance()->storage->errors()));

      return false;
    }
  }
}
