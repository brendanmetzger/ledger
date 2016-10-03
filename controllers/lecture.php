<?php
namespace controllers;

use \models\Instructor as Admin;
use \bloc\view;
use \models\data;

/**
 * Lecture
 */

class Lecture extends \bloc\controller
{
  use traits\config;

  const layout = 'views/layouts/journal.html';

  public function GETindex()
  {
    $view = new View(self::layout);
    return $view->render($this());
  }

  public function GETtopic($subject, $topic)
  {
    $view = new View(self::layout);
    // get all topics
    $view->content =  sprintf("views/topics/%s/%s.html", $subject, $topic);
    return $view->render($this());
  }

  public function GETessay($topic = null)
  {
    $view = new View(self::layout);
    // get all topics
    $view->content =  sprintf("views/prologue/%s.html", $topic);
    return $view->render($this());
  }

}
