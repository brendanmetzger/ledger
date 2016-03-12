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


    public function getPath(\DOMElement $context)
    {
      $type  = $context['@type'];
      $index = $type == 'project' ? ['midterm', 'final'][$context['@index']] : $context['@index'];
      return "{$type}/{$index}";
    }
}
