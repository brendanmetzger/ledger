<?php
namespace models;

/**
  * Participation
  *
  */

  class Discourse extends \bloc\Model
  {
    use traits\indexed, traits\persist;

    const WEIGHT = 30;

    static public $fixture = [
      'discourse' => [
        '@' => ['punctuality' => 0, 'persistance' => 0, 'observation' => 0],
        'CDATA' => '',
      ]
    ];

    public function setValueAttribute(\DOMElement $context, $value)
    {
      $context->setAttribute('value', 0);
    }

    public function getScore(\DOMElement $context)
    {
      return $context['@punctuality'] + $context['@persistance'] + $context['@observation'] - 2;
    }

    public function getPercentage(\DOMElement $context)
    {
      return $this->score * 100;
    }
  }
