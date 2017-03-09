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
      'Concept'            => 'Is there a rationale and idea behind the project, and is it substantitive?',
      'Documentation'      => 'Is the README helpful and is code legible. Are notes kept to help others understand process, intentions and goals?',
      'Potential'          => 'The concept is strong enough to continue to be developed',
      'Experimentation'    => 'Projects show research into ideas and principles of web development.',
      'Style/Voice'        => 'Is the content and or design seem unique, as opposed to templated or generic?',
      'Craft'              => 'Does the construction show care and thought put into the necessary details?',
      'Project Management' => 'Does the work show that the author has an agenda and can organize tasks and deliverables?',
      'Interaction Design' => 'Are there elements that utilize interaction, as opposed to static experiences',
      'Accessibility'      => 'Does the designer/developer understand and utilize any methods of universal design and concepts?',
      'User Experience'    => 'Does the designer/developer understand paradigms of a user centered approach to design?',
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
    
    public function getResponse(\DOMElement $context)
    {
      $out = iterator_to_array($context['file']->map(function($file) {
        return strlen((string)$file);
      }));
      return array_sum($out);
    }
    
    public function getFiles(\DOMElement $context)
    {
      return $context['file']->map(function($file, $index) use($context){
        $info = pathinfo($file['@path']);

        $file['@name']     = $info['filename'];
        $file['@basename'] = $info['basename'];
        $file['@type']     = $info['extension'];
        
        $content = $this->student->repo()->getSource($file['@path']);

        if (strpos($file['@path'], 'README')) {
          $content = \vendor\Parsedown::render($content);
        } else {
          $doc = new \bloc\DOM\Document();
          $doc->loadXML('<pre/>');
          $doc->documentElement->setAttribute('class', "prettyprint linenums {$file['@type']}");
          $doc->documentElement->appendChild($doc->createCDATASection($content));
          $content = $doc->documentElement->write();
        }
        $text = base64_decode($file);
        $revisions = $this->getRevisions($context, $file['@path']);
        return [
          'b64path'   => base64_encode($file['@path']),
          'file'      => $file,
          'index'     => $index,
          'text'      => $text,
          'markdown'  => \vendor\Parsedown::render($text),
          'content'   => $content,
          'revisions' => array_reverse($revisions),
        ];
      });
    }

    public function getAxes(\DOMElement $context)
    {
      return explode('+', $context['@axes']);
    }
    
    public function getBaseUrl(\DOMElement $context)
    {
      $url = $_SERVER['REQUEST_SCHEME'] . preg_replace('/https?/', '', $this->student['@url']);
      if ($context['@title'] ) {
        $path = '/' .  $context['@title'];
      } else {
        $path =  $this->criterion->context['@path'];
      }
      
      return $url . $path . '/';
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
    
    
    public function getRevisions(\DOMElement $context, $path = false)
    {
      $revisions = [];
      $start     = $this->student->section->schedule[0]['object']->format(DATE_ISO8601);
      $options   = "--after='{$start}' --no-merges --date-order --reverse";
      
      if ($path) {
        return $this->student->repo()->log($path, $options);
      }
      
      foreach ($context['file'] as $file) {
        if (substr($file['@path'], -3) == 'txt') continue;
        $logs = $this->student->repo()->log($file['@path'], $options);
        foreach ($logs as $log) {
          $revisions[] = ['log' => $log, 'sum' => min(array_sum($log['stats']) / 100, 1)];
        }
      }
      return $revisions;
    }
    
    public function getChart(\DOMElement $context)
    {
      $view = new \bloc\View('views/css/media/blank.svg');
      $dom = $view->dom;
      $dom->documentElement->setAttribute('viewBox', '0 0 100 70');
      
      $bg = $dom->documentElement->appendChild($dom->createElement('rect'));
      $bg->setAttribute('height', 70);
      $bg->setAttribute('width', 100);
      $bg->setAttribute('class', 'background');
      $revisions = $this->revisions;
      
      for ($i=0; $i < 70; $i++) {
        $x = floor($i / 7) * 10;
        $y = ($i % 7) * 10;
        $r = $dom->documentElement->appendChild($dom->createElement('rect'));
        $r->setAttribute('height', 10);
        $r->setAttribute('width', 10);
        $r->setAttribute('x', $x);
        $r->setAttribute('y', $y);
        $r->setAttribute('fill-opacity', 0);
        $r->setAttribute('class', 'n');
        if (array_key_exists($i, $revisions)) {
          $revision = $revisions[$i];
          $add = $dom->documentElement->appendChild($dom->createElement('text', $revision['log']['stats']['+'] ?? '0'));
          $add->setAttribute('x', $x);
          $add->setAttribute('y', $y);
          $add->setAttribute('dy', 10);
          $add->setAttribute('data-type', '+');
          $del = $dom->documentElement->appendChild($dom->createElement('text', $revision['stats']['-'] ?? '0'));
          $del->setAttribute('x', $x);
          $del->setAttribute('y', $y);
          $del->setAttribute('dy', 10);
          $del->setAttribute('data-type', '-');
          $r->setAttribute('class', 'y');
          $r->setAttribute('fill-opacity', $revision['sum']); 
        }
      }
      
      return substr($view->render(), strlen('<?xml version="1.0"?>'));
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
      return $context['@title'] ?:  $this->criterion->context['@title'];
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
    
    public function getCommits(\DOMElement $context)
    {
      return $this->contribution['commits'];
    }

    public function getBenchmark(\DOMElement $context)
    {
      return $this->status == 'open' ? null : round($this->commits / 70, 2);
    }
    
    public function getScore(\DOMElement $context)
    {
      return $this->commits / 70;
    }
    
    public function getPercentage(\DOMElement $context)
    {
      return round($this->score * $this->weighted);
    }
    
    public function getCritique($value='')
    {
      return $this->status == 'open' ? 1 : array_sum($this->axes);
    }

    public function getWeighted(\DOMElement $context)
    {
      return $this->status == 'open' ? 'NA' : round($this->stats['score'] * 100);
    }
    
  }
