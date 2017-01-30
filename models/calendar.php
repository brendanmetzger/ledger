<?php
namespace models;

/**
 * Event
 */
 class Calendar extends \bloc\Model
 {
   use traits\principle;

   const XPATH = '/model/calendar/';

    static public $fixture = [
      'calendar' => [
        'event' => [],
      ]
    ];

    public function getHolidays(\DOMElement $context)
    {
      $output = [];
      foreach ($context->find('event[@type="holiday"]') as $item) {
        $start = new \DateTime($item['@start']);
        $output[] = [
          'start' => clone $start,
          'end' => $start->add(new \DateInterval($item['@interval'])),
          'title' => $item,
        ];
      }
      return $output;
    }

    public function getSemester(\DOMElement $context)
    {
      $output = [];
      foreach ($context->find('event[@type="alert"]') as $item) {
        $output[$item['@interval']] = new \DateTime($item['@start']);
      }
      $output['period'] = new \DatePeriod($output['start'], new \DateInterval('P7D') ,$output['end']);
      return $output;
    }
    
    /**
     * Get Current Week Index
     *
     * @return int
     */
    static public function INDEX(array $schedule)
    {
      $now   = new \DateTime;
      $index = 0;
      foreach ($schedule as $interval) {
        if ($now < $interval['object']) break;
        $index = $interval['index'];
      }
      return $index;
    }
}
