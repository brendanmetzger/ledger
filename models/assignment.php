<?php
namespace models;

/**
  * Assignment
  *
  */

  class Assignment extends \bloc\Model
  {
    use traits\resolver, traits\persist;

    const XPATH = '/course/assignments/';

    static public $fixture = [
      'assignment' => [
        '@' => ['id' => null, 'title' => '', 'points' => ''],
        'CDATA' => '',
      ]
    ];

    public function getURL(\DOMElement $context)
    {
      return $context['@case'];
    }
}
