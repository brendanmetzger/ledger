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
    $idx = $idx = $this->index;
    $filename = substr($this->url, -1) === '/' ? 'index.html' : pathinfo(parse_url($this->url)['path'])['basename'];

    return '';
    $file = $doc->last("/records/file[@index='{$idx}' and @name='{$filename}']");
    if (! $file instanceof \bloc\dom\element || (new \DateTime($file->getAttribute('created')))->diff(new \DateTime())->format('%a') > 1) {
      $content = trim(file_get_contents($this->url));
      $file = $doc->documentElement->appendChild($doc->createElement('file'));
      $file->setAttribute('index', $idx);
      $file->setAttribute('sloc', count(preg_split('/\n/', $content)));
      $file->setAttribute('name', $filename);
      $file->setAttribute('content', base64_encode($content));
      $file->setAttribute('created', (new \DateTime())->format('Y-m-d H:i:s'));
      $doc->save();
    }

    return $file;
  }

  public function getMarkup(\DOMElement $context)
  {
    $doc = new \DOMDocument();
    $content = base64_decode($this->plain->getAttribute('content'));
    $doc->loadHTML($content, LIBXML_NOBLANKS);
    $doc->formatOutput = true;
    $doc->normalizeDocument();
    return $doc;
  }

  public function getHours(\DOMElement $context)
  {
    $xpath = new \DOMXpath($this->markup);
    $meta = $xpath->query("//meta[@name='hours']");

    return $meta->length > 0 ? (float)$meta->item(0)->getAttribute('content') : 'NA';
  }

  public function getREADME(\DOMElement $context)
  {
    $doc = $this->student->records;
    $idx = $this->index;
    $file = $doc->last("/records/file[@index='{$idx}' and @name='readme.txt']");
    if ($file instanceof \bloc\dom\element && (new \DateTime($file->getAttribute('created')))->diff(new \DateTime())->format('%a') < 1) {
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
    $parsedown = new \vendor\Parsedown;
    $markup = $parsedown->text($content);
    return new \bloc\types\dictionary(['summary' => strlen($content) == 2250 ? ' - ' : sprintf('<em>%s...</em>', substr(strip_tags($markup), 0, 50)), 'content' => nl2br(trim($content)), 'markup' => $markup]);
  }

  public function linkedFile($name)
  {
    $doc = $this->student->records;

    $url = $this->url . $name;
    $filename = pathinfo(parse_url($url)['path'])['basename'];
    $idx = substr($filename, 0, 6) == 'global' ? '*' : $this->index;

    $file = $doc->last("/records/file[@index='{$idx}' and @name='{$filename}']");

    if ($file instanceof \bloc\dom\element && (new \DateTime($file->getAttribute('created')))->diff(new \DateTime())->format('%a') < 1) {
      $content = base64_decode($file->getAttribute('content'));
    } else {
      $content = trim(file_get_contents($url));
      $file = $doc->documentElement->appendChild($doc->createElement('file'));
      $file->setAttribute('index', $idx);
      $file->setAttribute('name', $filename);
      $file->setAttribute('content', base64_encode($content));
      $file->setAttribute('sloc', count(preg_split('/\n/', $content)));
      $file->setAttribute('created', (new \DateTime())->format('Y-m-d H:i:s'));

      $cssvalidator = "https://jigsaw.w3.org/css-validator/validator?output=json&warning=0&profile=css3svg&uri=";
      $code = substr(get_headers($url)[0], 9, 3);
      $report = base64_encode(($code < 400) ? file_get_contents($cssvalidator . $url) : 'NA');

      $file->setAttribute('errors', $report);
      $file->setAttribute('stats', exec("echo '{$content}' | analyze-css -"));
      $doc->save();
    }

    return $file;
  }
  
  public function getStructure(\DOMelement $context                                                               )
  {
    $doc = $this->markup;
    $xpath = new \DOMXpath($this->markup);
    $counts = [];
    foreach ($xpath->query("/html/body//*") as $element) { 
      $counts[$element->nodeName] = ($counts[$element->nodeName] ?? 0) + 1;
    }
    
    arsort($counts);

    return new \bloc\types\Dictionary([
      'total'    => array_sum($counts),
      'distinct' => count($counts),
      'list'     =>  (new \bloc\types\Dictionary($counts))->map(function($count, $key) {
        return ['name' => $key, 'count' => $count];
      })
    ]);
    
  }
  
  public function getLint(\DOMElement $context)
  {
    // This will use eslint to gather errors
    // "echo {$content} | eslint --no-eslintrc --parser-options=ecmaVersion:7  --env browser -f json  --stdin"
  }

  public function getValidate(\DOMelement $context)
  {
    $doc = $this->student->records;

    $markup = $this->markup;

    $idx = $this->index;;
    $filename = substr($this->url, -1) === '/' ? 'index.html' : pathinfo(parse_url($this->url)['path'])['basename'];

    $file = $doc->last("/records/file[@index='{$idx}' and @name='{$filename}']");
    if ($errors = $file->getAttribute('errors')) {
      $content = base64_decode($errors);
    } else {
      $handle = curl_init("https://validator.nu/?outline=yes&doc={$this->url}&out=json");
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
