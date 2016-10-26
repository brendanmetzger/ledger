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
        '@' => ['effort' => 0, 'organization' => 0, 'punctuality' => 7, 'mission' => 1, 'created' => 0, 'updated' => 0],
        'CDATA' => '',
      ]
    ];

    public function setValueAttribute(\DOMElement $context, $value)
    {
      $context->setAttribute('value', 0);
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
      $deductions = ($context['@punctuality'] / 7) * $context['@mission'];
      return round(($context['@effort'] + $context['@organization']) * $deductions, 2);
    }

    public function getWeighted(\DOMElement $context)
    {
      return $this->status == 'open' ? 'NA' : $this->stats['standard'];
    }
  }
