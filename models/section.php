<?php
namespace models;

/**
  * Section
  *
  */

  class Section extends \bloc\Model
  {
    use traits\resolver, traits\persist;

    const XPATH = '/model/courses/';

    static public $fixture = [
      'section' => [
        '@' => ['course' => null, 'id' => null, 'start' => '', 'room' => 0],
        'student' => [],
      ]
    ];

    public function assignments($type)
    {
      $key = ($type == 'practice' || $type == 'quiz') ? $this['@course'] : '*';
      return \models\Criterion::collect(function ($item) use($type){
        return Data::FACTORY($type, $item);
      }, "[@type='{$type}' and @course='{$key}']");
    }

    public function getSize(\DOMElement $context)
    {
      return $this->students->count();
    }

    public function getStudents(\DOMElement $context)
    {
      return $context->find('student[@major!="instructor"]')->map(function($student) {
        return ['student' => new Student($student)];
      });
    }

    public function getTimestamp(\DOMElement $context)
    {
      return new \DateTime($context['@start']);
    }

    public function getTime(\DOMElement $context)
    {
      return $this->timestamp->format("l, g:ia");
    }

    public function getSchedule(\DOMElement $context)
    {
      // TODO: Schedule should be a class by itself, this is too big.
      $calendar = new Calendar;
      $begin    = clone $this->timestamp;
      $holidays = $calendar->holidays;
      $now      = new \DateTime();
      $week     = new \DateInterval('P7D');
      $out      = [];
      $index    = 0;
      $course   = $context['@course'];
      $section  = $context['@id'];

      while ($begin < $calendar->semester['end']) {
        $interval = $now->diff($begin);
        \bloc\application::instance()->log();
        $key = $index;
        $date = [
          'section'  => $section,
          'course'   => $course,
          'date'     => $begin->format('M d'),
          'datetime' => $begin->format(\DateTime::RFC3339),
          'status'   => $now->format('U') > $begin->format('U') - 1000 ? 'transpired' : 'pending',
          'index'    => $index++,
          'ticker'   => $interval->format('%r') === '-' ? $interval->format('%a days ago') : $interval->format('%a days'),
        ];
        foreach ($holidays as $idx => $holiday) {
          if ($holiday['start'] <= $begin && $holiday['end'] >= $begin) {
            $date['index'] = 'Break';
            unset($date['section']);
            $date['status'] = 'holiday';
            $index--;
            $key = 'holiday' . $idx;
            break;
          }
        }
        $out[$key] = $date;
        $begin->add($week);
      }
      return $out;
    }
}
