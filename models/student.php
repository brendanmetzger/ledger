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

    public function getQuizzes(\DOMElement $context)
    {
      return (new Assessment($this))->getEvaluation('quiz', "[@type='quiz']");
    }

    public function getProjects(\DOMElement $context)
    {
      return (new Assessment($this))->getEvaluation('project', "[@type='project']");
    }

    public function getDiscourse(\DOMElement $context)
    {
      return (new Assessment($this))->getEvaluation('discourse', "[@type='discourse']");
    }

    public function getPractice(\DOMElement $context)
    {
      return (new Assessment($this))->getEvaluation('practice', "[@type='practice' and @course = '{$this->course}']");
    }

    public function getGrade(\DOMElement $context)
    {
      $score = array_reduce(['quizzes', 'projects', 'discourse', 'practice'], function ($carry, $item) {
        return $carry + $this->{$item}['score'];
      }, 0);
      return new \bloc\types\Dictionary(['score' => $score, 'letter' => Assessment::LETTER($score, 100)]);
    }

}
