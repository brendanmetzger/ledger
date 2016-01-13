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

  public function GETtopic($topic, $lesson)
  {
    $view = new View('views/layouts/journal.html');
    $view->content =  "views/topics/{$topic}/{$lesson}.html";
    return $view->render($this());
  }

  public function GEToverview($week = 1)
  {
    $view = new View('views/layouts/journal.html');
    $view->content = "views/topics/lectures/{$week}.html";
    return $view->render($this());
  }
}
