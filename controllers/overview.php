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
      $path = sprintf("views/topics/%s/%s.html", $subject, $topic);
      if ($_SERVER['SERVER_ADDR'] == '127.0.0.1') {
        $this->edit = PATH.$path;
      }
      $view->content = "views/layouts/topic.html";
      $view->topic   =  $path;
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
  
  
  public function CLIgit()
  {
    $git = exec('which git');
    
    $cmd = 'cd ' . PATH . 'data/ && ' . $git . ' checkout test && ' . $git . ' status';
    ob_start();
    passthru($cmd, $ret);
    $output = ob_get_clean();
    
    
    echo $output;
    echo $ret;
    
  }

}
