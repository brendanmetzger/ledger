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
    const DB = 'data/36-1420-01-FA15';
    static public $translation = [
      'numbers' => '0123456789',
      'letters' => 'HIKMNXLJTU',
    ];

    public $storage = null;

    static public function instance($resource = null)
    {
      static $instance = null;

      if ($instance === null) {
        $instance = new static($resource ?: self::DB);
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
