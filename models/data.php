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
    static public $DB  = 'data/36-1420-01-FA15';


    public $storage = null;

    static public function instance()
    {
      static $instance = null;

      if ($instance === null) {
        $instance = new static(self::$DB);
      }

      return $instance;
    }

    static public function ID($id)
    {
      if ($id === null || strpos(strtolower($id), 'pending') === 0) return null;
      if (! $element = self::instance()->storage->getElementById($id)) {
        throw new \InvalidArgumentException("{$id}... Doesn't ring a bell.", 1);
      }
      return $element;
    }

    static public function FACTORY($model, $element)
    {
      $classname = NS . __NAMESPACE__ . NS . $model;
      return  new $classname($element);
    }


    private function __construct($file)
    {
      $this->storage = new Document($file, ['validateOnParse' => true]);
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
