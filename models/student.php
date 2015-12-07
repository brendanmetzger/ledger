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
        'assignment' => [],
      ]
    ];

    public function getAssigned(\DOMElement $context)
    {

      return Assignment::collect()->filter(function ($current) use($context){
        $id = $current['assignment']['@id'];
        return $context->find("assignment[@ref='{$id}']")->count() < 1;
      });
    }

    public function getGrades(\DOMElement $context)
    {
      return $context->find('assignment')->map(function ($assignment) {
        return ['grade' => $assignment, 'assignment' => new Assignment($assignment['@ref'])];
      });
    }
}
