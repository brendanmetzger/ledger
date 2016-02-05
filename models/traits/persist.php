<?php
namespace models\traits;
use \models\Data;

trait persist {
  public function save()
  {
    if (empty($this->errors) && Data::instance()->storage->validate()) {
      $this->beforeSave();
      return Data::instance()->storage->save();
    } else {

      $this->errors = array_merge(["Did not save"], $this->errors, array_map(function($error) {
        return $error->message;
      }, Data::instance()->storage->errors()));
      return false;
    }
  }
}
