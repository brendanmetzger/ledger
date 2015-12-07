<?php

namespace models;

/**
  * Assessment
  *
  */

  class Assessment extends \bloc\Model
  {
    use traits\transfer;

    static public $fixture = [
      'assignment' => [
        '@' => ['id' => null, 'title' => '', 'points' => ''],
        'CDATA' => 'Enter notes..',
      ]
    ];
}
