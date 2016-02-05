<?php
namespace models;

/**
  * Participation
  *
  */

  class Discourse extends \bloc\Model
  {
    use traits\indexed, traits\persist;

    const XPATH = false;

    static public $fixture = [
      'discourse' => [
        '@' => ['punctuality' => 0, 'persistance' => 1, 'observation' => 1],
        'CDATA' => 'pending',
      ]
    ];

    public function beforeSave()
    {
      $this->context->setAttribute('updated', (new \DateTime())->format('Y-m-d H:i:s'));
    }
    
    public function getScore(\DOMElement $context)
    {
      return $context['@punctuality'] + $context['@persistance'] + $context['@observation'] - 2;
    }

    public function getPercentage(\DOMElement $context)
    {
      return (string)$context == 'pending' ? 'NA' : ($this->score * 100) . '‰';
    }
  }
