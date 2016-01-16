<?php
namespace models;

/**
  * Section
  *
  */

  class Section extends \bloc\Model
  {
    use traits\resolver, traits\persist;

    const XPATH = '/model/courses/';

    static public $fixture = [
      'section' => [
        '@' => ['course' => null, 'id' => null, 'start' => '', 'room' => 0],
        'student' => [],
      ]
    ];

    public function getStudents(\DOMElement $context)
    {
      return $context->find('student')->map(function($student) {
        return ['student' => new Student($student)];
      });
    }

    public function getTimestamp(\DOMElement $context)
    {
      return new \DateTime($context['@start']);
    }

    public function getTime(\DOMElement $context)
    {
      return $this->timestamp->format("l, g:ia");
    }
}
