<?php
namespace models\traits;
use \models\Data;

trait principle {
  protected function identify($identity) {
    return null;
  }

  protected function initialize() {
    return Data::instance()->query(self::XPATH)->pick('.');
  }

  public function save()
  {
    return false;
  }
}
