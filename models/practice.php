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

    public function getPlain(\DOMElement $context)
    {
      $doc = $this->student->records;
      $idx = $this->criterion->context['@index'];
      $filename = substr($this->url, -1) === '/' ? 'index.html' : pathinfo(parse_url($this->url)['path'])['basename'];


      $file = $doc->pick("/records/file[@index='{$idx}' and @name='{$filename}']");
      if ($file instanceof \bloc\dom\element) {
        $content = base64_decode($file->getAttribute('content'));
      } else {

        $content = file_get_contents($this->url);
        $file = $doc->documentElement->appendChild($doc->createElement('file'));

        $file->setAttribute('index', $idx);
        $file->setAttribute('name', $filename);
        $file->setAttribute('content', base64_encode($content));
        $file->setAttribute('created', (new \DateTime())->format('Y-m-d H:i:s'));
        $doc->save();
      }


      return $content;
    }

    public function getMarkup(\DOMElement $context)
    {
      $doc = new \DOMDocument();
      $doc->loadHTML($this->plain);
      return $doc;
    }

    public function getHours(\DOMElement $context)
    {
      $xpath = new \DOMXpath($this->markup);
      $meta = $xpath->query("//meta[@name='hours']");

      return $meta->length > 0 ? (float)$meta->item(0)->getAttribute('content') : 'NA';
    }

    public function getReadme(\DOMElement $context)
    {
      $doc = $this->student->records;
      $idx = $this->criterion->context['@index'];
      $file = $doc->pick("/records/file[@index='{$idx}' and @name='readme.txt']");

      if ($file instanceof \bloc\dom\element) {
        $content = base64_decode($file->getAttribute('content'));
      } else {
        $content = file_get_contents($this->url.'readme.txt');
        $file = $doc->documentElement->appendChild($doc->createElement('file'));
        $file->setAttribute('index', $idx);
        $file->setAttribute('name', 'readme.txt');
        $file->setAttribute('content', base64_encode($content));
        $file->setAttribute('created', (new \DateTime())->format('Y-m-d H:i:s'));
        // errors should be a serialized php thing.
        $doc->save();
      }
      return new \bloc\types\dictionary(['summary' => substr($content, 0, 50).'...', 'content' => nl2br(trim($content))]);
    }

    public function getLinkedFiles(\DOMElement $context)
    {
      $xpath = new \DOMXpath($this->markup);
      $links = $xpath->query("//meta[@name='hours']");
    }

    public function getValidate(\DOMelement $context)
    {
      $doc = $this->student->records;
      $markup = $this->markup;
      $idx = $this->criterion->context['@index'];
      $filename = substr($this->url, -1) === '/' ? 'index.html' : pathinfo(parse_url($this->url)['path'])['basename'];

      $file = $doc->pick("/records/file[@index='{$idx}']");
      if ($errors = $file->getAttribute('errors')) {

        $content = base64_decode($errors);
      } else {
        $handle = curl_init("https://validator.nu/?level=error&doc={$this->url}&out=json");
        curl_setopt_array($handle, [
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
        ]);

        $content = curl_exec($handle);

        // errors should be a serialized php thing.
        $file->setAttribute('errors', base64_encode($content));
        $doc->save();
      }

      return  json_decode($content);

    }

    public function getFormatted(\DOMElement $context)
    {
      $parsedown = new \vendor\Parsedown;
      return $parsedown->text(trim((string)$this));
    }

  }
