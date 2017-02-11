<?php
namespace models;

/**
  * Practice Assignments
  *
  */

  class Practice extends \bloc\Model
  {
    use traits\indexed, traits\persist, traits\evaluation;

    const XPATH = false;

    static public $fixture = [
      'practice' => [
        '@' => ['value' => 0, 'commits' => '0000000'],
        'CDATA' => '',
      ]
    ];

    public function setCommitsAttribute(\DOMElement $context, $commits)
    {
      if (is_string($commits)) {
        $commits = str_split($commits);
      }
      $context->setAttribute('commits', implode($commits));
      $total = array_sum($commits);
      $exponent = 2.5;
      $value    = min(5, $total) / 5;  // 5 is based on 5 days in a week;
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
    
    public function getVisual(\DOMElement $context)
    {
      $view = new \bloc\view('views/css/media/blank.svg');
      $view->dom->documentElement->setAttribute('viewBox', '0 0 100 70');
      $view->dom->documentElement->setAttribute('style', 'stroke:rgba(255,255,255,0.1);');
      $view->dom->documentElement->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
      foreach (str_split($context['@commits']) as $idx => $value) {
        $r = $view->dom->documentElement->appendChild($view->dom->createElement('rect'));
        $r->setAttribute('width', 100);
        $r->setAttribute('height', 10);
        $r->setAttribute('x', 0);
        $r->setAttribute('y', 10 * $idx);
        $r->setAttribute('fill',  'rgba(0,0,255,'.($value/10).')');
      };
      
      return 'data:image/svg+xml;base64,'.base64_encode($view->render());
    }

    public function getScore(\DOMElement $context)
    {
      return (float)$context['@value'];
    }
    
    public function getStatus(\DOMElement $context)
    {
      return $this->criterion['@index'] <= Calendar::INDEX($this->student->section->schedule) ? 'marked' : 'open';
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
      $this->setCommitsAttribute($context, $commits ?? '');
      return $commits;
    }
  }
