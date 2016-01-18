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
    $view = new View('views/layouts/journal.html');
    $view->content =  "views/topics/{$topic}/{$lesson}.html";
    return $view->render($this());
  }

  public function GETtopic($topic = 'lectures', $lesson)
  {
    $view = new View('views/layouts/journal.html');
    // get all topics
    $view->content =  "views/topics/{$topic}/{$lesson}.html";
    return $view->render($this());
  }

}
