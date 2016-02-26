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

  protected function GETcriterion(User $user, $type, $index = 0, $section = '01', $specificity = '*')
  {
    $view = new View(self::layout);
    $course = static::ID;
    $this->criterion = new \models\Criterion("[@index='{$index}'and @type='{$type}' and @course='{$specificity}']");
    $this->schedule = (new \Models\Course($course))->section($section)->schedule;
    $this->timeline = [
      'assigned' => $this->schedule[$this->criterion['@assigned']],
      'due'      => $this->schedule[$this->criterion['@due']],
    ];

    $path = "views/outline/assignments/".static::ID."/$type/$index.html";
    if (!file_exists(PATH.$path)) {
      $view->content = 'views/layouts/error.html';
      $this->message = "Assignment not ready";
    } else {
      $view->content = $path;
      $view->context = "views/outline/_/schedule.html";
    }

    return $view->render($this());
  }

  protected function GETdashboard(Student $student)
  {
    $view = new View(self::layout);
    $view->content = 'views/layouts/dashboard.html';
    $view->context = "views/outline/_/schedule.html";
    $this->schedule = $student->section->schedule;
    $this->review = "Review";
    return $view->render($this());
  }

  protected function GETnotes(Student $student, $topic, $index)
  {
    $view = new View(self::layout);
    $this->evaluation = Data::FACTORY($topic, $student->context->getElement($topic, $index));
    $this->url        = $student->context['@url'] . "/{$topic}/{$index}";
    $this->files      = \models\Assessment::Links($this->url);
    $this->template   = 'editor';
    $view->context    = "views/layouts/notes.html";
    $view->content    = "views/layouts/inspector.html";
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

  protected function GETpeers(Student $student)
  {
    $out = "<pre style='font-family:Courier;'>\n";
    foreach ($student->section->students as $model) {
      if (substr($model['student']['@name'], 0, 6) != 'Course') {
        $out .= $model['student']['@name'] . ' ('. metaphone($model['student']['@name'], 5).') ' . $model['student']['@url'] . "/\n";
      }

    }
    return $out . "</pre>";
  }
}
