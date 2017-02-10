<?php
namespace models;

/**
  * Project Assignments
  *
  */

  class Project extends \bloc\Model
  {
    use traits\indexed, traits\persist, traits\evaluation;

    static public $fixture = [
      'project' => [
        '@' => ['axes' => [0.05, 0.05, 0.05, 0.05, 0.05, 0.05, 0.05, 0.05, 0.05, 0.05], 'value' => 0],
        'file' => [
          ['CDATA'  => '', '@' => ['errors' => 0, 'sloc' => 0, 'length' => 0, 'hash' => '', 'commits' => 0, 'age' => 1, 'aux' => '', 'report' => '', 'path' => '%s/index.html']],
          ['CDATA'  => '', '@' => ['errors' => 0, 'sloc' => 0, 'length' => 0, 'hash' => '', 'commits' => 0, 'age' => 1, 'aux' => '', 'report' => '', 'path' => '%s/README.txt']],
          ['CDATA'  => '', '@' => ['errors' => 0, 'sloc' => 0, 'length' => 0, 'hash' => '', 'commits' => 0, 'age' => 1, 'aux' => '', 'report' => '', 'path' => 'src/css/%s.css']],
          ['CDATA'  => '', '@' => ['errors' => 0, 'sloc' => 0, 'length' => 0, 'hash' => '', 'commits' => 0, 'age' => 1, 'aux' => '', 'report' => '', 'path' => 'src/js/%s.js']],
          ['CDATA'  => '', '@' => ['errors' => 0, 'sloc' => 0, 'length' => 0, 'hash' => '', 'commits' => 0, 'age' => 1, 'aux' => '*', 'report' => '', 'path' => 'src/css/global.css']],
          ['CDATA'  => '', '@' => ['errors' => 0, 'sloc' => 0, 'length' => 0, 'hash' => '', 'commits' => 0, 'age' => 1, 'aux' => '*', 'report' => '', 'path' => 'src/js/global.js']],
        ]
      ]
    ];

    static public $metrics = [
      'Concept'            => 'Is there substance to the work being done',
      'Potential'          => 'Ideas that can continue to be developed',
      'Documentation'      => 'Is the README helpful and is code legible',
      'Experimentation'    => 'Research into ideas and principles of web development',
      'Accessibility'      => 'Semantic markup is used and the proper attributes for media accessibility',
      'Project Management' => 'Does the work show that the author has an agenda and can organize tasks',
      'Interaction Design' => 'Are there elements that utilize interaction, as opposed to static experiences',
      'Style/Voice'        => 'Is the content and or design seem unique, as opposed to templated or generic',
      'User Experience'    => 'Visiting the page a user knows how to navigate and experience the content',
      'Craft'              => 'Does the construction show care and thought but into all details',
    ];
    
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
    
    public function getNotes(\DOMElement $context)
    {
      $repo = $this->student->repo();

      return $context->find('file[not(contains(@path,"README"))]')->map(function($file) use($repo){
        $info = pathinfo($file['@path']);

        $file['@name']     = $info['filename'];
        $file['@basename'] = $info['basename'];
        $file['@type']     = $info['extension'];
        

        $content = file_get_contents($repo->getPath($file["@path"]));
        
        return ['file' => $file, 'content' => $content];
      });
    }
    
    public function getReadme(\DOMElement $context)
    {
      $repo = $this->student->repo();
      $file = $context->find('file[contains(@path,"README")]')->pick();
      $content = file_get_contents($repo->getPath($file['@path']));
      return \vendor\Parsedown::render($content);
    }

    public function getAxes(\DOMElement $context)
    {
      return explode('+', $context['@axes']);
    }
    
    public function getBaseUrl(\DOMElement $context)
    {
      $url = $_SERVER['REQUEST_SCHEME'] . substr($this->student['@url'], 4);
      return $url . $this->criterion->context['@path'] . '/';
    }
    
    public function getContribution(\DOMElement $context)
    {
      $table = [
        'errors'  => 0,
        'length'  => 0,
        'density' => 'NA',
        'sloc'    => 0,
        'commits' => 0,
      ];
      
      foreach ($context['file'] as $file) {
        if (substr($file['@path'], -3) == 'txt') continue;
        $table['commits'] += $file['@commits'];
        $table['sloc']    += $file['@sloc'];
        $table['errors']  += $file['@errors'];
        $table['length']  += $file['@length'];
      }
      
      return new \bloc\types\Dictionary($table);
    }
    
    public function getChart(\DOMElement $context)
    {
      $contributions = $this->contribution['commits'];
      $view = new \bloc\View('views/css/media/blank.svg');
      $dom = $view->dom;
      $dom->documentElement->setAttribute('viewBox', '0 0 70 100');
      for ($i=0; $i < 70; $i++) {
        $x = ($i % 7) * 10;
        $y = floor($i / 7) * 10;
        $r = $dom->documentElement->appendChild($dom->createElement('rect'));
        $r->setAttribute('height', 10);
        $r->setAttribute('width', 10);
        $r->setAttribute('x', $x);
        $r->setAttribute('y', $y);
        $r->setAttribute('class', --$contributions > 0 ? 'y' : 'n');
      }

      return substr($view->render(['title' => 'ok then']), strlen('<?xml version="1.0"?>'));
    }

    public function getIndex(\DOMelement $context)
    {
      return [$this->criterion->context['@path']];
    }

    public function __toString()
    {
      return base64_decode($this->context->nodeValue);
    }
        
    public function getTitle(\DOMElement $context)
    {
      return $this->criterion->context['@title'];
    }

    public function getInputs(\DOMElement $context)
    {
      $metrics = array_keys(self::$metrics);
      return (new \bloc\types\Dictionary($this->axes))->map(function ($axis, $idx) use($metrics) {
        return ['value' => $axis, 'key' => $metrics[$idx]];
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
