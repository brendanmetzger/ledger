<?php
namespace models;

/**
 * Event
 */
 class Editorial
 {
    private $path;

    public function __construct($path)
    {
      $this->path = PATH . $path;
    }
    
    public function getColumns($path = null)
    {
      $columns = [];
      foreach (new \DirectoryIterator($path ?? $this->path) as $file) {
        $name = $file->getBasename();
        if ($file->isDir() && ! $file->isDot() && substr($name, 0, 2) !== '__') {
          $columns[] = [
            'name'   => trim(str_replace('_', ' ', $name)),
            'essays' => $this->getEssays($name, $file->getPathName()),
         ];
        }
      }
      usort($columns, function($a, $b) {
        return $a['name'] > $b['name'];
      });
      return $columns;
    }
    
    public function getEssays($colname, $colpath)
    {
      $essays = [];
      foreach (new \DirectoryIterator($colpath) as $file) {
        if ($file->isFile()) {
          $doc = new \DOMDocument();
          $doc->load($file->getPathname());
          if (!$doc->documentElement) continue;
          $xpath = new \DOMXpath($doc);
          $name  = $file->getBasename('.html');
          $title = $xpath->query('/*/h1|/*/h2')->item(0)->nodeValue ?? $name;
          // get h2 later
          $essays[] = [
            'path'  => "{$colname}/{$name}",
            'name' => $title,
            'intro' => $doc->getElementsByTagName('p')->item(0)->nodeValue ?? 'WRITE SOMETHING',
            // 'tags' => preg_split('/\s+/', $doc->documentElement->getAttribute('data-classification') ?? ''),
            'tags' => $doc->documentElement->getAttribute('data-classification') ?: '(no tags)',
            'fullpath' => $file->getPathname(),
          ];
        }
      }
      return $essays;
    }

}
