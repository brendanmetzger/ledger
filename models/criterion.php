<?php
namespace models;

/**
  * Criterion
  *
  */

  class Criterion extends \bloc\Model
  {
    use traits\indexed, traits\persist;

    const XPATH = '/model/criteria/';

    static public $fixture = [
      'criterion' => [
        '@' => ['index' => null, 'title' => '', 'points' => '', 'type' => ''],
        'CDATA' => '',
      ]
    ];

    public function getTitle(\DOMElement $context)
    {
      return $context['@title'];
    }

    public function getPath(\DOMElement $context)
    {
      $type  = $context['@type'];
      $index = $context['@index'];
      return "{$type}/{$index}";
    }
}
