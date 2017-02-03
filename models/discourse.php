<?php
namespace models;

/**
  * Participation
  *
  */

  class Discourse extends \bloc\Model
  {
    use traits\indexed, traits\persist, traits\evaluation;

    const XPATH = false;

    static public $fixture = [
      'discourse' => [
        '@' => ['punctuality' => 0, 'persistance' => 1, 'observation' => 1],
        'CDATA' => '',
      ]
    ];
    
    public function setDiscourse(\DOMElement $context, $value)
    {
      $context->nodeValue = base64_encode($value);
    }

    public function __toString()
    {
      return base64_decode($this->context->nodeValue);
    }
    
    public function getScore(\DOMElement $context)
    {
      return $context['@punctuality'] + $context['@persistance'] + $context['@observation'] - 2;
    }
  }
