<?php
namespace models\traits;
use \models\Data;

trait indexed {
  protected function identify($identity) {
    return data::ID($identity);
  }

  protected function initialize() {
    throw new \RuntimeException("Index too high", 1);

  }
}
