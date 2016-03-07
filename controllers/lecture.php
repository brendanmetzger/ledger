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

  public function GETtopic($topic = null, $lesson = null)
  {
    $view = new View(self::layout);
    // get all topics
    $view->content =  sprintf("views/topics/%s.html", $topic ? "{$topic}/{$lesson}" : 'glossary' );
    return $view->render($this());
  }

  public function GETessay($topic = null)
  {
    $view = new View(self::layout);
    // get all topics
    $view->content =  sprintf("views/prologue/%s.html", $topic);
    return $view->render($this());
  }

  protected function GETquiz(Admin $instructor, $type = '36-1420')
  {
    $operator = $type == '36-1420' ? '<' : '>=';
    $data = new \bloc\dom\Document("data/questions", ['validateOnParse' => false]);
    $questions = iterator_to_array($data->find("//question[@level {$operator} 3 and @priority = 1]")->map(function ($item) {
      return ['question' => $item];
    }));
    shuffle($questions);
    $this->questions = $questions;
    $view = new View(self::layout);
    $view->content = 'views/layouts/quiz.html';
    return $view->render($this());
  }

}
