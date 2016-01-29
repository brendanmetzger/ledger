<?php
namespace models;

/**
  * Student
  *
  */

  class Student extends \bloc\Model implements \bloc\types\authentication
  {
    use traits\resolver, traits\persist;

    const XPATH = '/model/courses/section/';

    static public $fixture = [
      'student' => [
        '@' => ['id' => null, 'name' => '', 'email' => '', 'url' => ''],
        'practice'  => [],
        'project'   => [],
        'quiz'      => [],
        'discourse' => [],
      ]
    ];

    static public function BLEAR($key)
    {
      return strtr(strtoupper(base_convert((int)$key, 10, 26)), '0123456789', 'QRSTUVWXYZ');
    }

    public function authenticate($token)
    {
      $computed = \bloc\types\token::generate($this->context['@email'], getenv('EMAIL_TOKEN'));
      if ($token === $computed && $token === $this->context['@token']) {
        return $this;
      } else {
        $message =  ($this->context['@token'] !== $computed) ? "Token has expired": "Invalid Request";
        throw new \InvalidArgumentException($message, 401);
      }
    }


    public function getSection(\DOMElement $context)
    {
      $section = new Section($context->parentNode);

      return $section;
    }

    public function getCourse(\DOMElement $context)
    {
      return $context->parentNode['@course'];
    }

    public function getPractice(\DOMElement $context)
    {
      return Criterion::collect()->filter(function ($current) {
        $practice = $current['criterion']->context;
        return $practice['@type'] == 'practice' && $practice['@course'] == $this->course;
      });
    }

    public function getProjects(\DOMElement $context)
    {
      return Criterion::collect(null, "[@type='project']");
    }

    public function getDiscourse(\DOMElement $context, $index = 0)
    {
      $schedule = $this->section->schedule;
      $reviewed = $this->context->find('discourse');
      $total = $reviewed->count();
      \bloc\application::instance()->log($total);
      $collect = Criterion::collect(function ($criterion, $index) use($schedule, $reviewed) {
        $map = ['criterion' => $criterion, 'schedule' => $schedule[$index]];
        if ($node = $reviewed->pick($index)) {
          $map['discourse'] = new Discourse($node);
        }
        return $map;
      }, "[@type='discourse']");

      return $collect;
    }

}
