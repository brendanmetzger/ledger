<?php
namespace models;

/**
  * Project Assignments
  *
  */

  class Project extends Practice
  {
    static public $fixture = [
      'project' => [
        '@' => ['effort' => 0, 'organization' => 0, 'punctuality' => 7, 'mission' => 1, 'created' => 0, 'updated' => 0],
        'CDATA' => '',
      ]
    ];
  }
