<?php

namespace models;

/**
  * Student
  *
  */

  class Student extends \bloc\Model
  {
    use traits\resolver;

    const XPATH = '/course/members/';

    static public $fixture = [
      'student' => [
        '@' => ['id' => null, 'name' => '', 'email' => '', 'url' => ''],
        'CDATA' => '',
      ]
    ];
}
