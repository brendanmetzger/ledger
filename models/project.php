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
        '@' => ['axes' => [0.05, 0.05, 0.05, 0.05, 0.05, 0.05, 0.05, 0.05, 0.05, 0.05], 'commits' => 0],
        'file' => [
          ['CDATA'  => '', '@' => ['errors' => 0, 'sloc' => 0, 'length' => 0, 'hash' => '', 'image' => '', 'report' => '', 'path' => '%s/index.html']],
          ['CDATA'  => '', '@' => ['errors' => 0, 'sloc' => 0, 'length' => 0, 'hash' => '', 'image' => '', 'report' => '', 'path' => '%s/README.txt']],
          ['CDATA'  => '', '@' => ['errors' => 0, 'sloc' => 0, 'length' => 0, 'hash' => '', 'image' => '', 'report' => '', 'path' => 'src/css/%s.css']],
          ['CDATA'  => '', '@' => ['errors' => 0, 'sloc' => 0, 'length' => 0, 'hash' => '', 'image' => '', 'report' => '', 'path' => 'src/js/%s.js']],
        ]
      ]
    ];

    static public $metrics = ['Concept', 'Organized', 'Syntax/Errors', 'Explanations', 'Presentation', 'Research', 'Detail', 'Delivery/Time', 'Completion', 'Authorship'];
    
    public function getFixture()
    {
      $fixture = self::$fixture;
      foreach ($fixture['project']['file'] as &$file) {
        $file['@']['path'] = sprintf($file['@']['path'], $this->title);
      }
      
      // final project is in the root course directory, strongarm the path
      if ($this->title == 'final') {
        $fixture['project']['file'][0]['@']['path'] = 'index.html';
        $fixture['project']['file'][1]['@']['path'] = 'README.txt';
      }
      
      return $fixture;
    }
    
    public function setFile(\DOMElement $context, $data)
    {

      if (empty($data['@']['hash'])) {

        $data['@']['hash'] = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';
        $data['@']['path'] = sprintf($data['@']['path'], $this->title);
        // final project is in the root course directory, strongarm the path
        if ($this->title == 'final' && substr($data['@']['path'], 0, 3) != 'src') {
          $data['@']['path'] = substr($data['@']['path'], strlen($this->title) + 1);
        }
      }
      
      foreach ($data['@'] as $name => $value) {
        $context->setAttribute($name, $value);
      }
      
      if (array_key_exists('CDATA', $data)) {
        $context->nodeValue = base64_encode($data['CDATA']);
      }
    }
    
    // public function getFile(\DOMElement $context)
    // {
    //   print_r($context['file']);
    //   return "BURRRRRRRRRRRRRP\n";
    // }
    

    public function getAxes(\DOMElement $context)
    {
      return explode('+', $context['@axes']);
    }

    public function getIndex(\DOMelement $context)
    {
      return [$this->criterion->context['@path']];
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
