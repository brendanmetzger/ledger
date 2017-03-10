<?php
namespace models;

/**
 * Event
 */
 class Source
 {
    private $path,$git;

    public function __construct($semester, $path = '%sdata/%s/work/')
    {
      $this->git  = exec('which git');
      $this->path = sprintf($path, PATH, $semester);
    }
    
    public function checkout($branch)
    {
      return $this->execute("checkout {$branch}");
    }
    
    public function commit($subject, $options = '', $body = null)
    {
      $this->execute("commit --all -m \"{$subject}\" {$options}", $result);
      return $result;
    }
    
    public function diff($options = '--shortstat')
    {
      return $this->execute("diff {$options}");
    }
    
    public function log($file = '.', $options = '--no-merges --date-order --reverse')
    {
      $this->execute("log {$options} --shortstat --pretty=format:'{\"hash\":\"%H\", \"date\":\"%aI\", \"msg\":\"%s\"}' {$file}", $result);
      $result = array_values(array_filter($result));

      $re = '/([0-9]+)[^,]+\((\+|\-)\)/';
      $out = [];
      for ($i=0; $i < count($result); $i+=2) { 
        $out[$i] = json_decode($result[$i], true);
        
        preg_match_all($re, $result[$i+1], $matches);

        $out[$i]['stats'] = array_combine($matches[2], $matches[1]);
        $out[$i]['diff']  = implode(', ', array_map(function ($item) {
          return preg_replace('/[+\-()]/', '', $item);
        }, $matches[0]));
        $out[$i]['title'] = (new \DateTime($out[$i]['date']))->format('D M d');
      }
      
      return $out;
    }
    
    public function execute($command, &$output = null, &$status = null)
    {
      $output = null;
      $cwd = getcwd();
      chdir($this->path);
      $out = exec("{$this->git} {$command}", $output, $status);
      chdir($cwd);
      return $out;
    }
    
    public function push($branch, $options)
    {
      $this->checkout($branch);
      $this->execute("push {$options}", $result);
      return $result;
    }
    
    public function getSource($file = '', $hash = false)
    {
      $path = $this->path . $file;
      if ($hash) {
        $cmd = "show {$hash}:./{$file}";
        $this->execute($cmd, $result);        
        return implode("\n", $result);
      }
      return file_get_contents($path);
    }
    
    public function __destruct()
    {
      $this->checkout('master');
    }
}
