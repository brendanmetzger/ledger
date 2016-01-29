<?php
namespace models\traits;
use \models\Data;

trait indexed {
  protected function identify($identity) {
    // indenity should have and index and a type
    $name = self::type();

    return Data::instance()->query(self::XPATH)
                           ->find("{$name}{$identity}")
                           ->pick(0);
  }

  protected function initialize() {

    throw new \RuntimeException("Index type instantion needs creating", 1);
  }

  static public function collect(callable $callback = null, $filter = '')
  {
    $name = self::type();
    return Data::instance()->query(self::XPATH)
                           ->find($name.$filter)
                           ->map($callback ?: function($item) use($name) {
                             return [$name => new self($item)];
                           });
  }
}
