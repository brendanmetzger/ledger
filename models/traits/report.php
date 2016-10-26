<?php
namespace models\traits;

trait report {
  public function getURL(\DOMElement $context)
  {
    if ($this->criterion === null) {
      throw new \RuntimeException("you must use load crition", 1);
    }
    $id = (string)$this->criterion->context['@id'];

    return  "{$this->student->context['@url']}/{$this->criterion->context['@type']}/{$this->index}/";
  }

  public function getPlain(\DOMElement $context)
  {
    $doc = $this->student->records;
    $idx = $this->criterion->context['@index'];
    $filename = substr($this->url, -1) === '/' ? 'index.html' : pathinfo(parse_url($this->url)['path'])['basename'];


    $file = $doc->last("/records/file[@index='{$idx}' and @name='{$filename}']");
    if ($file instanceof \bloc\dom\element && (new \DateTime($file->getAttribute('created')))->diff(new \DateTime())->format('%a') < 1) {
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
    $idx = $this->index;
    $file = $doc->last("/records/file[@index='{$idx}' and @name='readme.txt']");

    if ($file instanceof \bloc\dom\element) {
      if ((new \DateTime($file->getAttribute('created')))->diff(new \DateTime())->format('%a') < 1) {
        $file->setAttribute('content', base64_encode(file_get_contents($this->url.'readme.txt')));
      }
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

  public function linkedFile($name)
  {
    $doc = $this->student->records;

    $url = $this->url . $name;
    $filename = pathinfo(parse_url($url)['path'])['basename'];
    $idx = substr($filename, 0, 6) == 'global' ? '*' : $this->criterion->context['@index'];

    $file = $doc->last("/records/file[@index='{$idx}' and @name='{$filename}']");

    if ($file instanceof \bloc\dom\element && (new \DateTime($file->getAttribute('created')))->diff(new \DateTime())->format('%a') < 1) {
      $content = base64_decode($file->getAttribute('content'));
    } else {
      $content = file_get_contents($url);
      $file = $doc->documentElement->appendChild($doc->createElement('file'));
      $file->setAttribute('index', $idx);
      $file->setAttribute('name', $filename);
      $file->setAttribute('content', base64_encode($content));
      $file->setAttribute('created', (new \DateTime())->format('Y-m-d H:i:s'));

      $cssvalidator = "https://jigsaw.w3.org/css-validator/validator?output=json&warning=0&profile=css3svg&uri=";
      $code = substr(get_headers($url)[0], 9, 3);
      $report = base64_encode(($code < 400) ? file_get_contents($cssvalidator . $url) : 'NA');

      $file->setAttribute('errors', $report);
      $doc->save();
    }

    return $file;
  }

  public function getValidate(\DOMelement $context)
  {
    $doc = $this->student->records;

    $markup = $this->markup;

    $idx = $this->criterion->context['@index'];
    $filename = substr($this->url, -1) === '/' ? 'index.html' : pathinfo(parse_url($this->url)['path'])['basename'];

    $file = $doc->last("/records/file[@index='{$idx}' and @name='{$filename}']");
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

    if (empty($content)) {
      \bloc\application::instance()->log(curl_error($handle));
    }
    return  json_decode($content);

  }

  public function getFormatted(\DOMElement $context)
  {
    $parsedown = new \vendor\Parsedown;
    return $parsedown->text(trim((string)$this));
  }
}
