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

  protected function GETindex(User $user, $index = null, $section = '01')
  {
    $view = new View(static::layout);
    $this->course = new \Models\Course(Data::ID(static::ID));
    $this->section = $this->course->section($section);
    $schedule = $this->section->schedule;
    
    if ($index === null) {
      // automatically set to current day.
      $index = \models\Calendar::INDEX($schedule);
    }
    
    $view->content = "views/outline/{$index}.html";
    $view->context = "views/layouts/list/schedule.html";
    $view->lecture = "views/outline/".strtolower(static::ID)."/{$index}.html";
    $schedule[$index]['selected'] = 'selected';

    $this->timestamp = $schedule[$index]['date'];
    $this->datetime  = $schedule[$index]['datetime'];
    $this->schedule  = $schedule;
    $this->index     = $index;
    
    return $view->render($this());
  }

  protected function GETcriterion(User $user, $type, $index = 0, $section = '01', $specificity = '*')
  {
    $view = new View(self::layout);
    $this->course = static::ID;
    $this->type = $type;
    $this->criterion = new \models\Criterion("[@index='{$index}'and @type='{$type}' and @course='{$specificity}']");
    $this->section = (new \Models\Course($this->course))->section($section);
    $this->schedule = $this->section->schedule;

    $this->timeline = [
      'assigned' => $this->schedule[$this->criterion['@assigned']],
      'due'      => $this->schedule[$this->criterion['@due']],
    ];
    
    $path = "views/outline/assignments/".static::ID."/$type/$index.html";
    if (!file_exists(PATH.$path)) {
      $view->content = 'views/layouts/error.html';
      $this->message = "Assignment deatails pending";
    } else {
      $view->content = 'views/outline/assignments/template.html';
      $view->overview = $path;
      $view->context = "views/layouts/list/schedule.html";
    }

    return $view->render($this());
  }

  protected function GETdashboard(Student $student)
  {
    $view = new View(self::layout);
    $view->content = 'views/layouts/dashboard.html';
    $view->context = "views/layouts/list/schedule.html";
    $this->schedule = $student->section->schedule;
    $this->review = "Review";
    return $view->render($this());
  }


  protected function GETnotes(Student $student, $topic, $index)
  {
    $view = new View(self::layout);
    // $this->evaluation = Data::FACTORY($topic, $student->evaluation($topic, $index));

    $criterion  = \models\Criterion::Collect(null, "[@type='{$topic}' and (@course = '{$student->course}' or @course = '*')]")->pick($index);
    $this->{$topic} = $this->item = $student->evaluation($topic, $index, $criterion);      
    $this->template   = 'editor';
    $view->context    = "views/layouts/notes.html";
    $view->content    = "views/layouts/inspector.html";
    return $view->render($this());
  }

  protected function GETtemplate(Student $student)
  {
    $file = tempnam('/tmp', 'zip');
    \models\outline::TEMPLATE($student, $file);
    ///Then download the zipped file.
    ob_clean();
    ob_end_flush();
    header("Content-Type: application/zip");
    header("Content-Length: " . filesize($file));
    header("Content-Disposition: attachment; filename={$student->course}.zip");
    readfile($file);
    @unlink($file);
    exit();
  }
  
  /*
    TODO This needs to switch to student in due time
  */
  protected function GETquiz(User $student)
  {
    $view = new View(self::layout);
    $view->content = 'views/layouts/quiz.html';
    return $view->render($this());
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

  public function GEThelper($id, $file)
  {    
    $format = \bloc\application::instance()->getExchange('request')->format;
    if ($format == 'css') {
      $out  = file_get_contents(PATH . 'views/css/iam.css');
    } else if ($format == 'js') {
      $validator = DOMAIN.'/task/validate/';
      $out  = substr_replace(trim(file_get_contents(PATH . 'views/javascript/iam.js')), "('{$id}', '{$validator}');", -1);
    }
    return $out;
  }

  public function GETrubric($type = 'project')
  {
    $view = new View(self::layout);
    $view->content = 'views/layouts/forms/project.html';
    $this->project =  ['percentage' => 50, 'inputs' => (new \bloc\types\Dictionary(\models\project::$metrics))->map(function ($item) {
      return ['key' => $item, 'value' => rand(0,100)/1000];
    })];

    return $view->render($this());
  }
}
