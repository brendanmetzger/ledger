<?php
namespace controllers;

use \bloc\view;
use \models\data;

/**
 * Lecture
 */

class Lecture extends \bloc\controller
{
  use traits\config;

  public function GETindex($topic, $lesson)
  {
    $view = new View('views/layout.html');

    $view->content =  "views/topics/{$topic}/{$lesson}.html";


    return $view->render($this());
  }
}
