<?php
namespace models;

/**
  * Practice Assignments
  *
  */

  class Practice extends \bloc\Model
  {
    use traits\indexed, traits\persist, traits\evaluation;

    const XPATH = false;

    static public $fixture = [
      'practice' => [
        '@' => ['effort' => 0, 'organization' => 0, 'punctuality' => 7, 'mission' => 1, 'created' => 0, 'updated' => 0],
        'CDATA' => '',
      ]
    ];

    public function setValueAttribute(\DOMElement $context, $value)
    {
      $context->setAttribute('value', 0);
    }

    public function setPractice(\DOMElement $context, $value)
    {
      $context->nodeValue = base64_encode($value);
    }

    public function __toString()
    {
      return base64_decode($this->context->nodeValue);
    }

    public function getScore(\DOMElement $context)
    {
      $deductions = ($context['@punctuality'] / 7) * $context['@mission'];
      return round(($context['@effort'] + $context['@organization']) * $deductions, 2);
    }

    public function getURL(\DOMElement $context)
    {
      if ($this->criterion === null) {
        throw new \RuntimeException("you must use load crition", 1);
      }
      $id = (string)$this->criterion->context['@id'];
      return  "{$this->student->context['@url']}/practice/{$this->criterion->context['@index']}/";
    }
    public function getMarkup(\DOMElement $context)
    {
      $content = file_get_contents($this->url);
      $doc = new \DOMDocument();
      $doc->loadHTML($content);
      $xpath = new \DOMXpath($doc);
      $meta = $xpath->query("//meta[@name='hours']");
      return $meta->length > 0 ? $meta->item(0)->getAttribute('content') : 'NA';
    }

    public function getHours(\DOMElement $context)
    {
      return $this->markup;
    }


    public function getFormatted(\DOMElement $context)
    {
      $parsedown = new \vendor\Parsedown;
      return $parsedown->text(trim((string)$this));
    }

  }
