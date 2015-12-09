<?php
namespace controllers;

use \bloc\view;
use \models\data;

/**
 * Explore
 */

class Manage extends \bloc\controller
{
  public function __construct($request)
  {
    $this->title = '36-1420-01-FA15';
  }

  public function GETindex($path = null)
  {
    $view = new View('views/layout.html');

    return $view->render($this());
  }

  public function GETlist($topic = 'student')
  {
    $view = new View('views/layout.html');
    $view->content = "views/list/{$topic}.html";

    $this->students = \Models\Student::collect();

    return $view->render($this());
  }

  public function GETperson($id)
  {
    $this->student = new \models\Student($id);

    $view = new View('views/layout.html');
    $view->content = "views/form/student.html";


    return $view->render($this());
  }

  public function GEToutline()
  {
    $view = new View('views/layout.html');
    $view->content = "views/list/outline.html";

    $this->weeks = \Models\Outline::collect();

    return $view->render($this());
  }


  public function GETassignment($student_id, $assignment_id, $flag = "edit")
  {
    $view = new View('views/layout.html');
    $view->content = "views/form/assignment.html";

    $this->assessment = new \models\Assessment([
      'reference' => new \models\Assignment($assignment_id),
      'container' => new \models\Student($student_id),
    ]);

    $this->message = $flag;


    return $view->render($this());
  }

  public function POSTassignment($request, $student_id, $assignment_id)
  {
    $instance = new \models\Assessment([
      'reference' => new \models\Assignment($assignment_id),
      'container' => new \models\Student($student_id),
    ], $_POST);

    
    if ($instance && $instance->save()) {
      \bloc\router::redirect($_SERVER['REDIRECT_URL'] . '/saved');
    } else {
      echo "<pre>";
      print_r($_POST);

      print_r($instance->errors);

      echo $instance->context->write('true');

      echo "</pre>";
    }

  }
}
