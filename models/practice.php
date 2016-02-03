<?php
namespace models;

/**
  * Practice Assignments
  *
  */

  class Practice extends \bloc\Model
  {
    use traits\indexed, traits\persist;

    static public $fixture = [
      'practice' => [
        '@' => ['effort' => 0, 'organization' => 0, 'punctuality' => 7, 'mission' => 1],
        'CDATA' => '',
      ]
    ];

    public function setValueAttribute(\DOMElement $context, $value)
    {
      $context->setAttribute('value', 0);
    }

    public function getScore(\DOMElement $context)
    {
      $deductions = (7 / $context['@punctuality']) * $context['@mission'];
      return ($context['@effort'] + $context['@organization']) * $deductions;
    }

    public function getPercentage(\DOMElement $context)
    {
      return $this->score * 100;
    }
  }
