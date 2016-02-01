<?php
namespace controllers;

use \bloc\view;
use \models\data;
use \models\Student;
use \bloc\types\authentication as User;

/**
 * Course Controller
 */

class Course extends \bloc\controller
{
  use traits\config;

  const ID = null;
  const layout = 'views/layouts/journal.html';

  protected function GETindex(User $user, $id = '0', $section = '01')
  {
    $view = new View(static::layout);
    $view->content = "views/outline/{$id}.html";
    $view->context = "views/outline/_/schedule.html";
    $view->lecture = "views/outline/".strtolower(static::ID)."/{$id}.html";
    $this->course = new \Models\Course(Data::ID(static::ID));
    $schedule = $this->course->section($section)->schedule;
    $schedule[$id]['selected'] = 'selected';

    $this->timestamp = $schedule[$id]['date'];
    $this->schedule = $schedule;

    $this->index  = $id;

    return $view->render($this());
  }

  protected function GETcriterion(User $user, $type, $index = 0, $course = null)
  {
    $view = new View(self::layout);
    $course = $course ?: static::ID;
    $this->criterion = $c = new \models\Criterion("[@index='{$index}'and @type='{$type}' and @course='{$course}']");
    $path = "views/outline/assignments/".static::ID."/$type/$index.html";
    if (!file_exists(PATH.$path)) {
      $view->content = 'views/layouts/error.html';
      $this->message = "Assignment not ready";
    } else {
      $view->content = $path;

      if ($user instanceof Student) {
        $view->context = "views/outline/_/schedule.html";
        $this->schedule = $user->section->schedule;
      }

    }

    return $view->render($this());
  }

  protected function GETdashboard(Student $student = null)
  {
    $view = new View(self::layout);
    $view->content = 'views/layouts/dashboard.html';
    $view->context = "views/outline/_/schedule.html";
    $this->schedule = $student->section->schedule;
    return $view->render($this());
  }

  protected function GETtemplate(Student $student)
  {
    $file = tempnam('/tmp', 'zip');
    \models\outline::BUILD($student, $file);
    ///Then download the zipped file.
    ob_clean();
    ob_end_flush();
    header("Content-Type: application/zip");
    header("Content-Length: " . filesize($file));
    header("Content-Disposition: attachment; filename='{$student->course}.zip'");
    readfile($file);
    @unlink($file);
    exit();
  }
}
