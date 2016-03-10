<?php
namespace models\traits;
use \models\Data;

trait resolver {
  protected function identify($identity) {
    return Data::ID($identity);
  }

  protected function initialize() {
    $data = Data::instance();
    $node = $data->storage->createElement(self::type());
    $this->input(self::$fixture, $node);
    return $data->query(self::XPATH)->pick('.')->appendChild($node);
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

  public function references($type)
  {
    $name  = self::type();
    $query = "{$type}[@{$name}='{$this->context['@id']}']";
    return Data::instance()->query(self::XPATH)
                           ->find($query)
                           ->map(function($item) use($type) {
                             return [$type => Data::Factory($type, $item)];
                           });
  }
}
