<?php
namespace models;

use \bloc\dom\document;
use \bloc\dom\query;

/**
  * Data Model
  *
  */

  class Data
  {
    static public $SEMESTER  = '';

    public $storage = null;

    static public function instance($id = null)
    {
      static $instance = null;

      if ($instance === null) {
        $instance = new static(self::$SEMESTER . '/records');
      }

      return $instance;
    }

    static public function ID($id)
    {
      if ($id === null) return null;
      if (! $element = self::instance()->storage->getElementById($id)) {
        throw new \InvalidArgumentException("{$id}... Doesn't ring a bell.", 1);
      }
      return $element;
    }

    static public function FACTORY($model, $initialization, $data = [], $dependencies = [])
    {
      $classname = NS . __NAMESPACE__ . NS . $model;
      return  new $classname($initialization, $data, $dependencies);
    }


    private function __construct($file)
    {
      $this->storage = new Document("data/{$file}", ['validateOnParse' => true]);
    }

    public function getDTD()
    {
      return file_get_contents(PATH . 'models/course.dtd');
    }

    public function query($expression)
    {
      return (new Query($this->storage))->path($expression);
    }

  }
