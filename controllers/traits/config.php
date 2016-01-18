<?php
namespace controllers\traits;

trait config {
  public function __construct($request)
  {
    \models\Data::$DB = 'SP16';

    \bloc\view::addRenderer('after', \bloc\view\renderer::HTML());
    \bloc\view::addRenderer('after', function($view) {
      foreach ($view->parser->queryCommentNodes('preview') as $stub) {
        $path = trim(substr(trim($stub->nodeValue), 8));
        $expression = '/([\/a-z0-9\-\_]+\.[a-z]{2,4})\s([0-9]+)\.\.([0-9]+)/i';
        preg_match($expression, $path, $r);
        $file = file(PATH . $r[1]);
        $start = $r[2]-1;
        $output = array_slice($file, $start, $r[3] - $start);
        $text = "\n";
        $whitespace = strlen($output[0]) - strlen(preg_replace('/^\s*/', '', $output[0]));
        foreach ($output as $line) {
          $text .= substr($line, $whitespace);
        }
        $stub->parentNode->replaceChild($view->dom->createTextNode($text), $stub);
      }
    });
		$this->year        = date('Y');
    $this->title       = "Gradebook";
    $this->email       = 'bmetzger@colum.edu';
    $this->_controller = $request->controller;
    $this->_action     = $request->action;
  }

  public function authenticate()
  {
    return true;
  }

  public function GETlogin()
  {
    $view = new \bloc\View(self::layout);
    $view->content = "views/form/authenticate.html";
    return $view->render();
  }

  public function POSTlogin()
  {
    # code...
  }
}
