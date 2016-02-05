<?php
namespace models;

/**
  * Practice Assignments
  *
  */

  class Practice extends \bloc\Model
  {
    use traits\indexed, traits\persist;

    const XPATH = false;

    static public $fixture = [
      'practice' => [
        '@' => ['effort' => 0, 'organization' => 0, 'punctuality' => 7, 'mission' => 1, 'created' => 0, 'updated' => 0],
        'CDATA' => '',
      ]
    ];

    public function beforeSave()
    {
      $this->context->setAttribute('updated', (new \DateTime())->format('Y-m-d H:i:s'));
    }

    public function setValueAttribute(\DOMElement $context, $value)
    {
      $context->setAttribute('value', 0);
    }

    public function getScore(\DOMElement $context)
    {
      $deductions = ($context['@punctuality'] / 7) * $context['@mission'];
      return ($context['@effort'] + $context['@organization']) * $deductions;
    }

    public function getStatus(\DOMElement $context)
    {
      return $context['@updated'] ? 'marked' : 'open';
    }

    public function getPercentage(\DOMElement $context)
    {
      return $this->status == 'open' ? 'NA' : ($this->score * 100) . '‰';
    }
  }
