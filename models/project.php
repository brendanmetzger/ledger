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

    static public $metrics = ['Concept', 'Precision/Care', 'Syntax/Errors', 'Documentation', 'Presentation', 'Research', 'UX/Accessible', 'Proj. Manage', 'IxD', 'Style/Voice'];
    
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
      return $this->student['@url'] . $this->criterion->context['@path'] . '/';
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
