<?php

namespace models;

/**
  * Assignment
  *
  */

  class Assignment extends \bloc\Model
  {
    use traits\resolver;

    const XPATH = '/course/assignments/';

    static public $fixture = [
      'assignment' => [
        '@' => ['id' => null, 'title' => '', 'points' => ''],
        'CDATA' => '',
      ]
    ];
}
