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

    static public $fixture = [
      'quiz' => [
        '@' => ['credit' => 0],
        'CDATA' => '',
      ]
    ];

    public function getScore(\DOMElement $context)
    {
      return $context['@credit'] / $this->criterion['@points'];
    }
  }
