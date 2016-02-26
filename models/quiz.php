<?php
namespace models;

/**
  * Participation
  *
  */

  class Quiz extends \bloc\Model
  {
    use traits\indexed, traits\persist;

    const XPATH = false;

    static public $fixture = [
      'quiz' => [
        '@' => ['credit' => 0],
        'CDATA' => '',
      ]
    ];

    public function beforeSave()
    {
      $this->context->setAttribute('updated', (new \DateTime())->format('Y-m-d H:i:s'));
    }

    public function getScore(\DOMElement $context)
    {
      return $context['@credit'];
    }

    public function getStatus(\DOMElement $context)
    {
      return $context['@updated'] ? 'marked' : 'open';
    }

    public function getPercentage(\DOMElement $context)
    {
      return $this->status == 'open' ? 'NA' : ($this->score * 100);
    }

    public function getLetter(\DOMElement $context)
    {
      return Assessment::LETTER($this->score);
    }
  }
