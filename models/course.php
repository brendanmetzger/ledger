<?php
namespace models;

/**
  * Course
  *
  */

class Course extends \bloc\Model
{
  use traits\resolver, traits\persist;

  const XPATH = '/model/courses/';

  static public $fixture = [
    'course' => [
      '@' => ['title' => '', 'id' => null, 'code' => ''],
      'introduction' => ['CDATA' => ''],
      'description'  => ['CDATA' => '',],
    ]
  ];

  public function getSections(\DOMElement $context)
  {
    return $this->references('section');
  }

  public function section($section_id)
  {
    foreach ($this->sections as $section) {
      if ($section['section']['@id'] == $section_id) return $section['section'];
    }
  }
}
