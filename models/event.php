<?php
namespace models;

/**
 * Event
 */
 class Event extends \bloc\Model
 {
   use traits\resolver, traits\persist;

   const XPATH = '/model/calendar';

    static public $fixture = [
      'event' => [
        '@' => ['type' => '', 'start' => '', 'interval' => ''],
        'CDATA' => '',
      ]
    ];

    public function getHolidays(\DOMElement $context)
    {
      return $context->find('item[@type="holiday"]');
    }
}
