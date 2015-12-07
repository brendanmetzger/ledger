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
    $this->student = Data::ID($id);

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

  public function GETcreate($form)
  {
    return "get new student";
  }
}
