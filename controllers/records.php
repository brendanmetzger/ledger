<?php
namespace controllers;

use \bloc\view;
use \models\data;
use \models\Instructor as Admin;

/**
 * Records Management
 */

class Records extends \bloc\controller
{
  use traits\config;
  const layout = 'views/layouts/journal.html';

  public function GETstudents($id = null)
  {
    return "show all or just one student";
  }

  public function GETindex($path = null)
  {
    $view = new View(self::layout);
    return $view->render($this());
  }

  protected function GETcourses(Admin $instructor, $id = null, $section = null)
  {
    $view = new View(self::layout);
    $view->content = 'views/layouts/courses.html';
    $this->courses = \Models\Course::collect();
    $this->todo    = "show single course | show single section";
    return $view->render($this());
  }

  protected function GETstudent(Admin $instructor, $id)
  {
    $view = new View(self::layout);
    $view->content = "views/layouts/dashboard.html";

    $this->student = new \models\Student($id);
    $this->participation = \models\Assignment::collect(null, "[@type='participation']");

    return $view->render($this());
  }

  protected function GETevaluate(Admin $instructor, $topic, $index, $sid = null)
  {
    $view = new View(self::layout);
    $view->content = "views/layouts/forms/assignment.html";
    $view->topic = "views/layouts/forms/{$topic}.html";

    $this->student = new \models\Student($sid);
    $this->topic = $topic;
    $this->index = $index;

    return $view->render($this());
  }

  protected function POSTevaluate(Admin $instructor, $topic, $index, $sid = null)
  {
    \bloc\application::instance()->log(json_encode($_POST['cost'], JSON_NUMERIC_CHECK));
    $view = new View(self::layout);
    return $view->render($this());
  }


  protected function GETlist(Admin $instructor, $topic = 'student')
  {
    $view = new View(self::layout);
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

  protected function GETcreate(Admin $instructor, $type)
  {
    return "not yet";
  }



  public function GEToutline()
  {
    $view = new View(self::layout);
    return $view->render($this());
  }

  protected function GETassignment(Admin $instructor, $student_id, $assignment_id, $flag = "edit")
  {
    $view = new View(self::layout);
    $view->content = "views/form/assignment.html";
    $this->assessment = new \models\Assessment([
      'reference' => new \models\Assignment($assignment_id),
      'container' => new \models\Student($student_id),
    ]);
    $this->message = $flag;
    $this->redirect = $_SERVER['HTTP_REFERER'];
    return $view->render($this());
  }

  protected function POSTassignment(Admin $instructor, $request, $student_id, $assignment_id)
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

  public function GETtemplate($id = 'YNUZ')
  {
    $student = new \models\student(\models\data::ID($id));
    $view = new View('views/student/site/index.html');
    return $view->render(['student' => $student]);
  }
}
