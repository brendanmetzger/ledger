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

    public function getURL(\DOMElement $context)
    {
      return $context['@case'];
    }

    public function getPending(\DOMElement $context)
    {
      return Student::collect()->filter(function($item) use($context) {
        return $item['student']->context->find("criterion[@ref='{$context['@id']}']")->count() < 1;
      })->count();
    }

    public function getSubmissions(\DOMElement $context)
    {
      $students = Student::collect(function($student) use ($context) {
        $complete = $student->find("criterion[@ref='{$context['@id']}']")->count();
        return ['student' => $student, 'criterion' => $context, 'status' => $complete];
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
