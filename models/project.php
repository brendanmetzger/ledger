<?php
namespace models;

/**
  * Project Assignments
  *
  */

  class Project extends Practice
  {
    use traits\indexed, traits\persist, traits\evaluation;

    static public $fixture = [
      'project' => [
        '@' => ['axes' => [0.05, 0.05, 0.05, 0.05, 0.05, 0.05, 0.05, 0.05, 0.05, 0.05]],
        'CDATA' => '',
      ]
    ];

    private $keys = ['Concept', 'Organized', 'Syntax/Errors', 'Explanations', 'Presentation', 'Research', 'Detail', 'Delivery/Time', 'Completion', 'Authorship'];


    public function getAxes(\DOMElement $context)
    {
      return explode('+', $context['@axes']);
    }

    public function setProject(\DOMElement $context, $value)
    {
      $context->nodeValue = base64_encode($value);
    }

    public function __toString()
    {
      return base64_decode($this->context->nodeValue);
    }

    public function getInputs(\DOMElement $context)
    {
      return (new \bloc\types\Dictionary($this->axes))->map(function ($axis, $idx) {
        return ['value' => $axis, 'key' => $this->keys[$idx]];
      });
    }

    public function setAxesAttribute(\DOMElement $context, $data)
    {
      $context->setAttribute('axes', implode($data, '+'));
    }

    public function getScore(\DOMElement $context)
    {
      return array_sum($this->axes);
    }
  }
