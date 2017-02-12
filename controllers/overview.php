<?php
namespace controllers;

use \models\Instructor as Admin;
use \models\Student;
use \bloc\view;
use \models\data;

/**
 * Overview
 */

class Overview extends \bloc\controller
{
  use traits\config;

  const layout = 'views/layouts/journal.html';

  public function GETindex()
  {
    $view = new View(self::layout);
    return $view->render($this());
    // todo: return $this->GETtopics();
  }

  protected function GETinstructor(Admin $instructor)
  {
    $view = new View(self::layout);
    $view->content = 'views/layouts/instructor.html';
    return $view->render($this());
  }
  
  public function GETglossary()
  {
    $view = new View(self::layout);
    $view->content = 'views/topics/glossary.html';
    return $view->render($this());
  }

  public function GETtopics($subject = null, $topic = null)
  {
    $view = new View(self::layout);
    // get all topics
    
    if ($subject == null) {
      $editorial = new \models\editorial('views/topics');
      $this->columns = $editorial->getColumns();
      $view->content = 'views/layouts/topics.html';
    } else if ($topic != null){
      // sub for localhost
      $view->content = sprintf("views/topics/%s/%s.html", $subject, $topic);
    }
    

    return $view->render($this());
  }
  
  public function GETws()
  {
    $view = new View(self::layout);
    $view->content = 'views/layouts/forms/websocket.html';
    return $view->render();
  }

  public function GETessay($topic = null)
  {
    $view = new View(self::layout);
    // get all topics
    $view->content =  sprintf("views/prologue/%s.html", $topic);
    return $view->render($this());
  }
  
  
  public function GETevaluation()
  {
    $view = new View(self::layout);
    $this->criteria = (new \bloc\types\Dictionary(\models\Project::$metrics))->map(function($value, $key) use(&$count) {
      return ['name' => $key, 'text' => $value ];
    });
    $view->content =  "views/layouts/evaluation.html";
    return $view->render($this());
  }

}
