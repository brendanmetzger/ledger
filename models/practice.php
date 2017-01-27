<?php
namespace models;

/**
  * Practice Assignments
  *
  */

  class Practice extends \bloc\Model
  {
    use traits\indexed, traits\persist, traits\evaluation, traits\report;

    const XPATH = false;

    static public $fixture = [
      'practice' => [
        '@' => ['value' => 0],
        'CDATA' => '',
      ]
    ];

    public function setCommitsAttribute(\DOMElement $context, $commits)
    {
      $context->setAttribute('commits', implode($commits));

      $exponent = 2.5;
      $value    = $this->total / 5;  // 5 is based on 5 days in a week;
      $score    = $value**$exponent / ($value**$exponent + ((1-$value)**$exponent));
      $context->setAttribute('value', $score);
      
    }

    public function setPractice(\DOMElement $context, $value)
    {
      $context->nodeValue = base64_encode($value);
    }

    public function __toString()
    {
      return base64_decode($this->context->nodeValue);
    }

    public function getScore(\DOMElement $context)
    {
      $report = $this->report;
      return (float)$context['@value'];
    }
    
    public function getTotal(\DOMElement $context)
    {
      return array_sum(str_split($context['@commits']));
    }
    
    public function getReport(\DOMElement $context)
    {
      $log = $this->student->log;
      
      // cross reference the log to assign commits for that week
      
      $index = $this->criterion["@index"];
      $week = $this->student->section->schedule[$index];
      if ($week['status'] != 'transpired') return;
      
      $end = clone $week['object'];
      $end->add(new \DateInterval('P7D'));
      $commits = array_map(function($date) use ($log) {
        return array_key_exists($date->format('yz'), $log) ? 1 : 0;
      }, iterator_to_array(new \DatePeriod($week['object'], new \DateInterval('P1D') , $end)));
      $this->setCommitsAttribute($context, $commits ?? []);
      $context->setAttribute('updated', (new \DateTime())->format('Y-m-d H:i:s'));
      return $commits;
    }
  }
