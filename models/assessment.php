<?php
namespace models;

/**
 * Event
 */
 class Assessment
 {
    static public $fixture = [
      'calendar' => [
        'event' => [],
      ]
    ];

    static public $weight = [
      'discourse' => 30,
      'practice'  => 40,
      'quiz'      => 10,
      'project'   => 20,
    ];

    private $student;

    public function __construct(Student $student)
    {
      $this->student = $student;
    }

    // get total.
    // get individual(type)

}
