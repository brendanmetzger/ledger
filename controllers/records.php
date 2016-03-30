<?php
namespace controllers;

use \bloc\view;
use \models\data;
use \models\Instructor as Admin;
use \models\Student as Student;

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
    return $view->render($this());
  }

  protected function GETattendance(Admin $instructor)
  {
    $view = new View(self::layout);
    $view->content = 'views/layouts/attendance.html';
    $this->courses = \Models\Course::collect();
    return $view->render($this());
  }

  protected function GETstudent(Admin $instructor, $id)
  {
    $this->student   = new \models\Student($id);

    $view = new View(self::layout);
    $view->content = "views/layouts/dashboard.html";
    $view->context = "views/layouts/list/section.html";

    $this->section = $this->student->section;

    return $view->render($this());
  }

  /**
   * HTTP Get Evaluation
   *
   * @param Admin $instructor automatically passed based on session_id
   * @param string $topic (participation|practice|project|quiz)
   * @param int $index assignment number
   * @param string $sid student id number
   **/
  protected function GETevaluate(Admin $instructor, $topic, $index, $sid = null)
  {
    $this->student = new Student($sid);

    $this->topic = $topic;
    $this->index = $index;

    $view = new View(self::layout);

    $this->{$topic} = Data::FACTORY($topic, $this->student->evaluation($topic, $index));

    if ($topic == 'practice' || $topic == 'project') {
      $this->url = $this->student->context['@url'] . "/{$topic}/{$index}";
      $this->files = \models\Assessment::LINKS($this->url);
      $this->template = 'editor';
      $view->context = "views/layouts/forms/assignment.html";
      $view->content = "views/layouts/inspector.html";
    } else {
      $this->section = $this->student->section;
      $view->context = "views/layouts/list/section.html";
      $view->content = "views/layouts/forms/assignment.html";
    }
    $view->topic = "views/layouts/forms/{$topic}.html";
    return $view->render($this());
  }

  protected function POSTevaluate(Admin $instructor, $request, $topic, $index, $sid)
  {
    $student = new Student($sid);

    $item = Data::FACTORY($topic, $student->evaluation($topic, $index), $_POST);

    if ($item->save()) {
      \bloc\router::redirect("/records/student/{$sid}");
    } else {
      \bloc\application::instance()->log($item);
      $view = new View(self::layout);
      $view->content = "views/layouts/error.html";
      return $view->render($this(['message' => "did not save"]));
    }
  }

  protected function GETinquiry(Student $student)
  {
    $view = new View(self::layout);
    $view->content = 'views/layouts/forms/inquiry.html';
    $this->placeholder = null;
    return $view->render($this());
  }

  public function GEToutline()
  {
    $view = new View(self::layout);
    return $view->render($this());
  }

  public function GETtemplate($id = 'YNUZ')
  {
    $student = new \models\student(\models\data::ID($id));
    $view = new View('views/student/site/index.html');
    return $view->render(['student' => $student]);
  }
}
