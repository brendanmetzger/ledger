<?php
namespace models;

/**
  * Participation
  *
  */

  class Discourse extends \bloc\Model
  {
    use traits\indexed, traits\persist;

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
      $sum = $context['@punctuality'] + $context['@persistance'] + $context['@observation'] - 2;
      return $sum * 100;
    }
  }
