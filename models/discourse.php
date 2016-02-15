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
        'CDATA' => '',
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

    public function getStatus(\DOMElement $context)
    {
      return $context['@updated'] ? 'marked' : 'open';
    }

    public function getPercentage(\DOMElement $context)
    {
      return $this->status == 'open' ? 'NA' : ($this->score * 100) . '‰';
    }

    public function getLetter(\DOMElement $context)
    {
      return Assessment::LETTER($this->score);
    }
  }
