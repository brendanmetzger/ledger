<?php
namespace controllers;

use \bloc\view;
use \models\data;

/**
 * Course Controller
 */

abstract class Course extends \bloc\controller
{
  use traits\config;

  const ID = null;
  const layout = 'views/layouts/journal.html';

  public function GETindex($id = '0', $section = '01')
  {
    $view = new View(static::layout);
    $view->content = "views/outline/{$id}.html";
    $view->context = "views/outline/resources/schedule.html";

    $this->course = new \Models\Course(Data::ID(static::ID));
    $schedule = $this->course->section($section)->schedule;
    $schedule[$id]['selected'] = 'selected';

    $this->timestamp = $schedule[$id]['date'];
    $this->schedule = $schedule;

    $this->index  = $id;

    return $view->render($this());
  }

  protected function GETstudents($section = null)
  {
    // show list of students, show attendance.
  }
}
