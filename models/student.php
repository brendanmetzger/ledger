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
        'assignment' => [],
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
      return $context->parentNode['@id'];
    }

    public function getCourse(\DOMElement $context)
    {
      return $context->parentNode['@course'];
    }

    public function getPractice(\DOMElement $context)
    {
      return Assignment::collect()->filter(function ($current) {
        $practice = $current['assignment']->context;
        return $practice['@type'] == 'practice' && $practice['@course'] == $this->course;
      });
    }

    public function getProjects(\DOMElement $context)
    {
      return Assignment::collect()->filter(function ($current) use ($context) {
        return $current['assignment']->context->getAttribute('type') == 'project';
      });
    }


    public function getAssigned(\DOMElement $context)
    {
      return Assignment::collect(function ($assignment) use ($context){
        return ['assignment' => $assignment, 'student' => $context['@id']];
      })->filter(function ($current) use ($context) {
        $id = $current['assignment']['@id'];
        return $context->find("assignment[@ref='{$id}']")->count() < 1;
      });
    }


    public function getGrades(\DOMElement $context)
    {
      return $context->find('assignment')->map(function ($item) {
        return [
          'item' => new Assessment([
            'reference' => new Assignment($item['@ref']),
            'container' => $this,
          ])
        ];
      });
    }

    // TODO: I want to be able to have count method called via tostring
    public function getStatus(\DOMElement $context)
    {
      return ($this->assigned->count() - $this->grades->count()) . ' left';
    }


    public function getGrade(\DOMElement $context)
    {
      $grades = $this->grades;
      $score = 5;
      $avail = 1;

      foreach ($grades as $grade) {
        $avail += $grade['item']['assignment']['@points'];
        $score += $grade['item']['@points'];
      }

      $percent = round(($score / $avail) * 100);
      if ($percent > 90){
        $status = 'success';
      } else if ($percent < 64) {
        $status = 'error';
      } else if ($percent < 75) {
        $status = 'warn';
      } else {
        $status = 'info';
      }

      if ($percent >= 93){
        $letter = 'A';
      } else if ($percent >= 90) {
        $letter = 'A-';
      } else if ($percent >= 87) {
        $letter = 'B+';
      } else if ($percent >= 83) {
        $letter = 'B';
      } else if ($percent >= 80) {
        $letter = 'B-';
      } else if ($percent >= 77) {
        $letter = 'C+';
      } else if ($percent >= 73) {
        $letter = 'C';
      } else if ($percent >= 70) {
        $letter = 'C-';
      } else if ($percent >= 60) {
        $letter = 'D';
      } else {
        $letter = 'F';
      }

      return new \bloc\types\Dictionary([
        'score' => $score,
        'letter' => $letter,
        'available' => $avail,
        'percent' => $percent,
        'status' => $status,
      ]);
    }
}
