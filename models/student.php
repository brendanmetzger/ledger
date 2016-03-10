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

    private $assessment = null;

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

    private function assessment()
    {
      return $this->assessment ?: $this->assessment = new Assessment($this);
    }


    public function getSection(\DOMElement $context)
    {
      return new Section($context->parentNode);
    }

    public function getCourse(\DOMElement $context)
    {
      return $context->parentNode['@course'];
    }

    public function getQuizzes(\DOMElement $context)
    {
      return $this->assessment()->getEvaluation('quiz', $this->course);
    }

    public function getProjects(\DOMElement $context)
    {
      return $this->assessment()->getEvaluation('project');
    }

    public function getDiscourse(\DOMElement $context)
    {
      return $this->assessment()->getEvaluation('discourse');
    }

    public function getPractice(\DOMElement $context)
    {
      return $this->assessment()->getEvaluation('practice', $this->course);
    }

    public function getGrade(\DOMElement $context)
    {
      $score = array_reduce(['quizzes', 'projects', 'discourse', 'practice'], function ($carry, $item) {
        return $carry + $this->{$item}['score'];
      }, 0);
      $apr = $score > 96 ? 'over' : ($score <= 70 ? 'under' : 'meet');
      return new \bloc\types\Dictionary(['score' => $score, 'letter' => Assessment::LETTER($score, 100), 'apr' => $apr]);
    }

}
