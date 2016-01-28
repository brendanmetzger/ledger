<?php

namespace models;

/**
  * Assessment
  *
  */

  class Assessment extends \bloc\Model
  {
    use traits\transfer, traits\persist;

    static public $fixture = [
      'assignment' => [
        '@' => ['ref' => null, 'points' => 0, 'case' => ''],
        'CDATA' => '',
      ]
    ];

    public function getCase(\DOMElement $context)
    {
      $output = [];
      if ($context['@case']) {
        $output['url']  = $context['@case'];
        $output['text'] = "See Work";
      }

      return new \bloc\types\Dictionary($output);
    }

    
}
