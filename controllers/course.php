<?php
namespace controllers;

use \bloc\view;
use \models\data;
use \models\Student;

/**
 * Course Controller
 */

class Course extends \bloc\controller
{
  use traits\config;

  const ID = null;
  const layout = 'views/layouts/journal.html';

  protected function GETindex(Student $student, $id = '0', $section = '01')
  {
    $view = new View(static::layout);
    $view->content    = "views/outline/{$id}.html";
    $view->navigation = "views/outline/_/schedule.html";
    $view->lecture    = "views/outline/".static::ID."/{$id}.html";

    $this->course = new \Models\Course(Data::ID(static::ID));
    $schedule = $this->course->section($section)->schedule;
    $schedule[$id]['selected'] = 'selected';

    $this->timestamp = $schedule[$id]['date'];
    $this->schedule = $schedule;

    $this->index  = $id;

    return $view->render($this());
  }

  protected function GETassignment(Student $student, $topic, $index = 0)
  {
    $view = new View(self::layout);

    if ($index != 0) {
      $view->content = 'views/layouts/error.html';
      $this->message = "Assignment not ready";
    } else {
      $view->content = "views/outline/assignments/".static::ID."/$topic/$index.html";
    }

    return $view->render($this());
  }

  protected function GETdashboard(Student $student = null)
  {
    $view = new View(self::layout);
    $view->content = 'views/layouts/dashboard.html';
    return $view->render($this());
  }

  protected function GETtemplate(Student $student)
  {
    $file = tempnam('/tmp', 'zip');
    \models\outline::BUILD($student, $file);
    ///Then download the zipped file.
    header("Content-Type: application/zip");
    header("Content-Length: " . filesize($file));
    header("Content-Disposition: attachment; filename='{$student->course}.zip'");
    readfile($file);
    unlink($file);
  }
}
