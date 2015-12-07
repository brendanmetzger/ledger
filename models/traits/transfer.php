<?php
namespace models\traits;
use \models\Data;
/*
 * This trait provides the abstract methods needed for a model; the key
 * differenc is that this model is the child of another model.
 */

trait transfer {
  protected $container,
            $reference;

  protected function identify($identity) {
    if (! is_array($identity)) {
      throw new \InvalidArgumentException("Xfer object requires model as argument");
    }
    $this->container = $identity['container'];
    $this->reference = $identity['reference'];

    // make shortcuts
    $this->{$this->reference->_model} = $this->reference;
    $this->{$this->container->_model} = $this->container;

    $path = "{$this->reference->_model}[@ref='{$this->reference['@id']}']";
    $list = $this->container->context->find($path);

    return $list->count() > 0 ? $list->pick() : $this->initialize();
  }

  protected function initialize() {

    $data = Data::instance();
    $node = $data->storage->createElement($this->reference->_model);
    $this->input(self::$fixture, $node);
    return $this->container->context->appendChild($node);
  }

}
