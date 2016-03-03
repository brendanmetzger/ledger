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
        '@' => ['effort' => 0, 'organization' => 0, 'ambition' => 20, 'mission' => 1, 'created' => 0, 'updated' => 0],
        'CDATA' => '',
      ]
    ];

    public function getScore(\DOMElement $context)
    {
      $deductions = $context['@ambition'] * $context['@mission'];
      return round(($context['@effort'] + $context['@organization']) * $deductions, 2);
    }
  }
