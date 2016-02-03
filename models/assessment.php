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

    private $context, $schedule;

    public function __construct(Student $student)
    {
      $this->context  = $student->context;
      $this->schedule = $student->section->schedule;
    }

    public function getEvaluation($type, $query)
    {
      $reviewed = $this->context->find($type);
      $average  = 1 / ($reviewed->count() ?: 1);
      $accumulator = 0;

      $collect = Criterion::collect(function ($criterion, $index) use($type, $reviewed, $average, &$accumulator) {
        $map = ['criterion' => $criterion, 'schedule' => $this->schedule[$index]];

        if ($node = $reviewed->pick($index)) {
          $map[$type] = Data::FACTORY($type, $node);
          $accumulator = ($accumulator + ($map[$type]->score * $average));
        }
        return $map;
      }, $query);

      return new \bloc\types\dictionary([
        'list' => iterator_to_array($collect, false),
        'score' => max(0, $accumulator * Assessment::$weight[$type])
      ]);
    }

    // get total.
    // get individual(type)

}
