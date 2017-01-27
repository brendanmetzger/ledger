<?php
namespace models;

/**
 * Event
 */
 class Source
 {
    private $path, $git;

    public function __construct($semester)
    {
      $this->git  = exec('which git');
      $this->path = sprintf('%sdata/%s/work/', PATH, $semester);
    }
    
    public function checkout($branch)
    {
      return $this->execute("checkout {$branch}");
    }
    
    public function commit($subject, $body = null)
    {
      $this->execute("commit --all -m \"{$subject}\"", $result);
      return $result;
    }
    
    public function diff($options = '--shortstat')
    {
      return $this->execute("diff {$options}");
    }
    public function log($options = '--no-merges --date-order --reverse')
    {
      $this->execute("log {$options} --pretty=format:'{\"hash\":\"%h\", \"date\":\"%aI\", \"msg\":\"%s\"}'", $result);
      return array_map(function($item) {
        return json_decode($item, true);
      }, $result);
    }
    
    private function execute($command, &$output = null, &$status = null)
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
    
    public function __destruct()
    {
      $this->checkout('master');
    }
}
