<?php
namespace models;

/**
  * Participation
  *
  */

  class Quiz extends \bloc\Model
  {
    use traits\indexed, traits\persist, traits\evaluation;

    const XPATH = false;

    public $stats = null;

    static public $fixture = [
      'quiz' => [
        '@' => ['credit' => 0],
        'CDATA' => '',
      ]
    ];

    public function getScore(\DOMElement $context)
    {
      return round($context['@credit'] / $this->criterion['@points'], 2);
    }

    public function getLetter(\DOMElement $context)
    {
      return \models\Assessment::LETTER($this->stats['standard'] / 100);
    }
  }
