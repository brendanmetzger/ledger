<?php
namespace controllers;

use \bloc\view;
use \models\data;

/**
 * Records Management
 */

class Records extends \bloc\controller
{
  use traits\config;

  public function GETstudents($id = null)
  {
    return "show all or just one student";
  }

  public function GETcourses($id = null, $section = null)
  {
    return "show all courses | show all sections of course | show single course/section";
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

    if ($topic == 'course') {
      $this->students = \Models\Student::collect()->sort(function($a, $b) {
        return $a['student']->grades->count() - $b['student']->grades->count();
      });
    }

    if ($topic == 'assignment') {
      $this->assignments = \Models\Assignment::collect();
    }

    return $view->render($this());
  }

  public function GETperson($id)
  {
    $this->student = new \models\Student($id);
    $view = new View('views/layout.html');
    $view->content = "views/list/student.html";
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
    $this->redirect = $_SERVER['HTTP_REFERER'];
    return $view->render($this());
  }

  public function POSTassignment($request, $student_id, $assignment_id)
  {
    $instance = new \models\Assessment([
      'reference' => new \models\Assignment($assignment_id),
      'container' => new \models\Student($student_id),
    ], $_POST);

    if ($instance && $instance->save()) {
      \bloc\router::redirect($_POST['redirect'] . '#' . $assignment_id);
    } else {
      print_r($instance->errors);
    }
  }
}
