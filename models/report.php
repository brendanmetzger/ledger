<?php
namespace models;

/**
 * Event
 */
 class Report
 {
    private $now, $url, $domain, $file, $info, $source = '', $headers = null, $report = [];
    
    // Some Static Methods
    public function __construct($url, $domain)
    {
      stream_context_set_default(['http' => ['method' => 'HEAD']]);
      $this->now = time();
      $this->url = $url;
      $this->domain = $domain;
      $this->file = substr($url, strlen("{$domain}/"));
      $this->info = pathinfo($this->file);

    }
    
    public function __destruct()
    {
      stream_context_set_default(['http' => ['method' => 'GET']]);
    }
    
    public function getHeaders($key = null)
    {
      if (is_null($this->headers)) {
        $this->headers =  get_headers($this->url, 1);
      }
      return $key ? $this->headers[$key] : $this->headers;
    }
    
    public function getLastModified(int $divisor = 1)
    {
       return ($this->now - strtotime($this->getHeaders('Last-Modified'))) / $divisor;
    }
    
    public function validate()
    {
      $type = $this->info['extension'];
      $validator = "validate{$type}";
      if (! method_exists($this, $validator)) return;
      
      // should return a reliably mapped object
      return $this->{$validator}();
    }
    
    private function validateHTML()
    {
      $path   = 'https://validator.nu/?outline=yes&doc=%s&out=json&showsource=yes';
      $handle = curl_init(sprintf($path, $this->url));
      
      curl_setopt_array($handle, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => 'BrendanBot/1.0',
      ]);
      
      $this->report['messages'] = [];

      if ($result = json_decode(curl_exec($handle))) {
        $this->source = $result->source->code;
        foreach ($result->messages as $message) {
          $this->report['messages'][] = [
            'line'  => $message->lastLine,
            'type'  => $message->type,
            'text'  => $message->message,
          ];
        }        
      }
      return $this->report['messages'];
    }
    
    private function validateCSS()
    {
      $path = 'https://jigsaw.w3.org/css-validator/validator?output=json&warning=0&profile=css3svg&uri=%s';
      $this->report['messages'] = [];
      $result = json_decode(file_get_contents(sprintf($path, $this->url), false, stream_context_create(['http' => ['method' => 'GET']])));
      
      if ($result && $result->cssvalidation->result->errorcount > 0) {

        foreach ($result->cssvalidation->errors as $error) {
          $this->report['messages'][] = [
            'line' => $error->line,
            'type' => $error->type,
            'text' => $error->message,
          ];
        }

      }
      return $this->report['messages'];
    }
    
    
    public function getSource()
    {
      if (empty($this->source)) {
        $GET_context = stream_context_create(['http' => ['method' => 'GET']]);
        $this->source = file_get_contents($this->url, false, $GET_context);
      }
      return $this->source;
    }
    
    
    public function getSLOC()
    {
      return count(preg_split('/\n/', $this->getSource()));
    }
    
    public function getSize()
    {
      // TODO
    }
    
    public function getAnalysis()
    {
      $this->report['analysis'] = null;
      // HTML we need to count nodes n stuff
      if ($this->info['extension'] == 'html') {
        if (empty($this->source)) {
          $this->validate();
        }
        $doc = new \DOMDocument();
        $doc->loadHTML($this->getSource(), LIBXML_NOBLANKS);
        $doc->normalizeDocument();
        $xpath = new \DOMXpath($doc);
        $counts = [];
        foreach ($xpath->query("/html/body//*") as $element) { 
          $counts[$element->nodeName] = ($counts[$element->nodeName] ?? 0) + 1;
        }
    
        arsort($counts);
        $this->report['analysis'] = [
          'total'    => array_sum($counts),
          'distinct' => count($counts),
          'list'     =>  $counts,
        ];
      }
      
      if ($this->info['extension'] == 'css') {
        $result = json_decode(exec("echo '{$this->getSource()}' | analyze-css -"), true);
        $this->report['analysis'] = $result['metrics'];
      }
      
      if ($this->info['extension'] == 'js') {
        // todo
      }
      
      return $this->report['analysis'];
    }
    
    public function toXML()
    {
      // convert the entire report to an xml object
      
    }
    
}