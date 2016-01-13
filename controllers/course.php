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

  public function GETindex()
  {
    return "index " . static::ID;
  }

  public function GEToutline($index)
  {
    return "outline {$index}";
  }

  protected function GETstudents($section = null)
  {
    // show list of students, show attendance.
  }
}
