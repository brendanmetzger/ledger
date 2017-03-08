<?php
namespace controllers;

use \bloc\view;
use \models\data;
use \models\Instructor as Admin;
use \models\Student as Student;
use \bloc\types\authentication as User;
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

  protected function GETquiz(Admin $instructor, $course = 'SWM')
  {
    $this->course = new \Models\Course(\models\Data::ID($course));

    $operator = $course == 'SWM' ? '<' : '>=';
    $data = new \bloc\dom\Document("data/questions", ['validateOnParse' => false]);
    $questions = iterator_to_array($data->find("//question[@level {$operator} 3 and @priority = 1]")->map(function ($item) {
      return ['question' => $item, 'answer' => null];
    }));
    // shuffle($questions);
    $this->questions = $questions;
    $view = new View(self::layout);
    $view->content = 'views/layouts/quiz.html';
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


  protected function GETbook(Admin $instructor, $course, $section, $type = null, $index = null)
  {
    $view = new View(self::layout);

    $this->course = new \models\Course($course);
    $this->courses = \Models\Course::collect();
    $this->section = $this->course->section($section);
    $this->records = $this->section;

    if ($type === $index) {
      $view->content = "views/layouts/assignments.html";
      $this->records = \models\Assessment::GRADEBOOK($this->section);
    } else if ($type === 'practice' || $type == 'project'){
      if ($type == 'project' && $index != 0 && $index != 1) {
        $index = ['midterm' => 0, 'final' => 1][$index];
      }
      $criteria = $this->section->assignments($type)->pick($index);
      $this->assignment = new \models\Criterion($criteria);
      $this->submissions = $this->section->students->map(function ($item) use($type, $index, $criteria) {
        $student = new \models\student($item);
        return [
          'student'    => $student,
          'evaluation' => $student->evaluation($type, $index, $criteria),
        ];
      });
      $view->content = "views/layouts/assignment.html";
      $view->details = "views/outline/assignments/{$this->course['@id']}/{$type}/{$index}.html";
    }

    $view->context = "views/layouts/list/courses.html";
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
  protected function GETevaluate(Admin $instructor, $topic, int $index, $sid = null, $review = false)
  {
    $this->student = new Student($sid);
    $this->topic = $topic;
    $this->index = $index;
    $this->dashboard  = "/records/student/{$this->student['@id']}";
    $this->edit = [
      'path' => "/records/evaluate/{$topic}/$index/{$sid}",
      'name' => 'edit',
    ];
    
    $view = new View(self::layout);

    if ($topic == 'project') {
      $this->{$topic} = $this->item = $this->student->{$topic}['list'][$index][$topic];
      $this->template = 'editor';
      $view->context = sprintf("views/layouts/%s.html", $review ? "notes" : "forms/assignment");
      $view->content  = "views/layouts/inspector.html";
    } else {
      $this->{$topic} = $this->item =  Data::FACTORY($topic, $this->student->evaluation($topic, $index));
      $this->section = $this->student->section;
      $view->context = "views/layouts/list/section.html";
      $view->content = "views/layouts/forms/assignment.html";
    }
    $view->topic = "views/layouts/forms/{$topic}.html";
    return $view->render($this());
  }

  protected function POSTevaluate(Admin $instructor, $request, $topic, int $index, $sid)
  {
    $student = new Student($sid);
    $item    = Data::FACTORY($topic, $student->evaluation($topic, $index), $_POST);
    if ($item->save()) {
      // add a bit of entropy so the response is not cached.
      \bloc\router::redirect($_POST['redirect'] . '?'. time());
    } else {
      \bloc\application::instance()->log($item);
      $view = new View(self::layout);
      $view->content = "views/layouts/error.html";
      return $view->render($this(['message' => "did not save"]));
    }
  }
  
  protected function GETversion(User $user, $sid, $file, $hash)
  {
    // if user is a student, make sure id matches
    if ($user instanceof \models\student && $user['@id'] == $sid) {
      $student = $user;
    } else if ($user instanceof \models\instructor) {
      $student = new Student($sid);
    }
    $file = base64_decode($file);
    $content = $student->repo()->getSource($file, $hash);
    $doc = new \bloc\DOM\Document();
    $doc->loadXML('<pre/>');
    $doc->documentElement->setAttribute('class', "prettyprint linenums html");
    $doc->documentElement->appendChild($doc->createCDATASection($content));
    return $doc->documentElement->write();
  }
  
  /*
    TODO make sure this is as student
  */
  protected function POSTquiz(User $student, $request)
  {
    
    print_r($_POST);
    die();
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
