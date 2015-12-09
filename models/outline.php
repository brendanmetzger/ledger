<?php

namespace models;

/**
  * Outline
  *
  */

  class Outline extends \bloc\Model
  {
    use traits\resolver, traits\persist;

    const XPATH = '/course/classes/';

    static public $fixture = [
      'outline' => [
        '@' => ['title' => ''],
        'assignment' => [],
      ]
    ];

    public function getAssignments(\DOMElement $context)
    {
      return $context->find('assignment')->map(function($item) {
        return ['assignment' => new Assignment($item['@ref'])];
      });
    }
}
