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

    private $assessment = null, $repository = null;
    
    static public $fixture = [
      'student' => [
        '@' => ['id' => null, 'name' => '', 'email' => '', 'url' => ''],
        'practice'  => [],
        'project'   => [],
        'quiz'      => [],
        'discourse' => [],
      ]
    ];


    static public function BLEAR($key, $direction = 0)
    {
      if ($direction === 0) {
        return strtr(strtoupper(base_convert((int)$key, 10, 26)), '0123456789', 'QRSTUVWXYZ');
      }
      if ($direction === 1) {
        return (int) base_convert(strtr($key, 'QRSTUVWXYZ', '0123456789'), 26, 10);
      }
    }

    public function authenticate($token)
    {
      if ($token === $this->context['@email'] && $this->context->hasAttribute('token')) {
        $this->context->removeAttribute('token');
        return $this;
      }
      throw new \InvalidArgumentException("This token is no longer active", 3);
    }

    public function getRecords(\DOMElement $context)
    {
      return new \bloc\DOM\Document("data/student/{$context['@id']}");
    }

    private function assessment()
    {
      return $this->assessment ?: $this->assessment = new Assessment($this);
    }

    public function evaluation($topic, $index, $criteria = null)
    {
      // get the Element
      $element = $this->context->getElement($topic, $index);
      // load the criterion & student if specified
      if ($criteria) {
        return Data::FACTORY($topic, $element, null, [new \models\Criterion($criteria), $this]);
      }

      return $element;
    }

    public function getShortname(\DOMElement $context)
    {
      return explode(' ', $context['@name'])[0];
    }
    
    public function repo()
    {
      if ($this->repository === null) {
        $this->repository = new \models\source(Data::$SEMESTER);
        $this->repository->checkout($this->context['@key']);
      }

      return $this->repository;
    }
    
    public function getLog(\DOMElement $context)
    {
      $log = $this->repo()->log();

      $keys = array_map(function(&$log) {
        return (new \DateTime($log['date']))->format('yz');
      }, $log);
      
      return array_combine($keys, $log);
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
      return $this->getQuiz($context);
    }

    public function getQuiz(\DOMElement $context)
    {
      return $this->assessment()->getEvaluation('quiz');
    }

    public function getProjects(\DOMElement $context)
    {
      return $this->getProject($context);
    }

    public function getProject(\DOMElement $context)
    {
      return $this->assessment()->getEvaluation('project');
    }

    public function getDiscourse(\DOMElement $context)
    {
      return $this->assessment()->getEvaluation('discourse');
    }

    public function getPractice(\DOMElement $context)
    {
      return $this->assessment()->getEvaluation('practice');
    }

    public function getGrade(\DOMElement $context)
    {
      $score = array_reduce(['quizzes', 'projects', 'discourse', 'practice'], function ($carry, $item) {
        return $carry + $this->{$item}['score'];
      }, 0);
      $apr = $score > 96 ? 'over' : ($score <= 70 ? 'under' : 'meet');
      return new \bloc\types\Dictionary(['score' => $score, 'letter' => Assessment::LETTER($score, 100), 'apr' => $apr]);
    }
    
    public function getDomain(\DOMElement $context)
    {
      return $context['@url'] . '/';
    }
}
