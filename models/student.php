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
        return Data::FACTORY($topic, $element)->loadCriterion($criteria)->loadStudent($this);
      }

      return $element;
    }

    public function getShortname(\DOMElement $context)
    {
      return explode(' ', $context['@name'])[0];
    }
    
    public function getLog(\DOMElement $context)
    {
      $cwd = getcwd();
      $git = new \models\source(Data::$SEMESTER);
      $git->checkout($context['@key']);
      $log = $git->log();
            
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
      return $this->assessment()->getEvaluation('quiz', $this->course);
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
    
    public function getFiles(\DOMElement $context)
    {
      $files = [];
      $domain = $context['@url'];
      $files[] = ['project' => 'global', 'url' => "{$domain}/src/js/global.js"];
      $files[] = ['project' => 'global', 'url' => "{$domain}/src/css/global.css"];
      // iterate projects
      foreach ($this->projects['list'] as $iterator) {
        $check = $iterator['project']->getFixture();
        print_r($check);
        $url   = $iterator['project']->baseurl;
        $title = $iterator['project']->title;
        $files[] = ['project' => $title, 'url' => "{$url}index.html"];
        $files[] = ['project' => $title, 'url' => "{$url}README.txt"];
        $files[] = ['project' => $title, 'url' => "{$domain}/src/js/{$title}.js"];
        $files[] = ['project' => $title, 'url' => "{$domain}/src/css/{$title}.css"];
      }
      return $files;
    }
}
