<?php

namespace models;

/**
  * Student
  *
  */

  class Student extends \bloc\Model
  {
    use traits\resolver, traits\persist;

    const XPATH = '/course/members/';

    static public $fixture = [
      'student' => [
        '@' => ['id' => null, 'name' => '', 'email' => '', 'url' => ''],
        'assignment' => [],
      ]
    ];

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
