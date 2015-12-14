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

    public function getPending(\DOMElement $context)
    {
      return Student::collect()->filter(function($item) use($context) {
        return $item['student']->context->find("assignment[@ref='{$context['@id']}']")->count() < 1;
      })->count();
    }

    public function getSubmissions(\DOMElement $context)
    {
      $students = Student::collect(function($student) use ($context) {
        $complete = $student->find("assignment[@ref='{$context['@id']}']")->count();
        return ['student' => $student, 'assignment' => $context, 'status' => $complete];
      });

      $graded = function($item) {
        return $item['status'] == $this->scalar;
      };

      return new \bloc\types\dictionary([
        'complete' => $students->filter($graded->bindTo((object)1)),
        'pending' => $students->filter($graded->bindTo((object)0)),
      ]);
    }
}
