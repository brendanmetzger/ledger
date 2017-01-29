<?php
namespace models;

/**
  * Project Assignments
  *
  */

  class Project extends Practice
  {
    use traits\indexed, traits\persist, traits\evaluation, traits\report;

    static public $fixture = [
      'project' => [
        '@' => ['axes' => [0.05, 0.05, 0.05, 0.05, 0.05, 0.05, 0.05, 0.05, 0.05, 0.05]],
        'file' => [
          ['CDATA'  => '', '@' => ['errors' => 0, 'sloc' => 0, 'length' => 0, 'hash' => '', 'report' => '', 'type' => '', 'path' => '%s/index.html']],
          ['CDATA'  => '', '@' => ['errors' => 0, 'sloc' => 0, 'length' => 0, 'hash' => '', 'report' => '', 'type' => '', 'path' => '%s/README.txt']],
          ['CDATA'  => '', '@' => ['errors' => 0, 'sloc' => 0, 'length' => 0, 'hash' => '', 'report' => '', 'type' => '', 'path' => 'src/css/%s.css']],
          ['CDATA'  => '', '@' => ['errors' => 0, 'sloc' => 0, 'length' => 0, 'hash' => '', 'report' => '', 'type' => '', 'path' => 'src/js/%s.js']],
        ]
      ]
    ];

    static public $metrics = ['Concept', 'Organized', 'Syntax/Errors', 'Explanations', 'Presentation', 'Research', 'Detail', 'Delivery/Time', 'Completion', 'Authorship'];
    
    public function getFixture()
    {
      foreach (self::$fixture['project']['file'] as &$file) {
        $file['@']['path'] = sprintf($file['@']['path'], $this->title);
      }
      
      return self::$fixture;
    }

    public function getAxes(\DOMElement $context)
    {
      return explode('+', $context['@axes']);
    }

    public function getIndex(\DOMelement $context)
    {
      return [$this->criterion->context['@path']];
    }

    public function setProject(\DOMElement $context, $value)
    {
      $context->nodeValue = base64_encode($value);
    }

    public function __toString()
    {
      return base64_decode($this->context->nodeValue);
    }
    
    public function getBaseUrl(\DOMElement $context)
    {
      return $this->student['@url'] . $this->criterion->context['@path'] . '/';
    }
    
    public function getTitle(\DOMElement $context)
    {
      return $this->criterion->context['@title'];
    }

    public function getInputs(\DOMElement $context)
    {
      return (new \bloc\types\Dictionary($this->axes))->map(function ($axis, $idx) {
        return ['value' => $axis, 'key' => self::$metrics[$idx]];
      });
    }

    public function setAxesAttribute(\DOMElement $context, $data)
    {
      $context->setAttribute('axes', implode($data, '+'));
    }

    public function getScore(\DOMElement $context)
    {
      return $this->status == 'open' ? null : array_sum($this->axes);
    }

    public function getWeighted(\DOMElement $context)
    {
      return $this->status == 'open' ? 'NA' : $this->stats['standard'];
    }
    
  }
