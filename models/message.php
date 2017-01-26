<?php
namespace models;
use \Postmark\PostmarkClient as Postmark;

require PATH . 'vendor/autoload.php';

/**
 * Rubric
 */

class Message
{
  const KEY = "c446d292-5969-4148-8910-d683afd54905";
  const SENDER = "bmetzger@colum.edu";

  public function send($recipient, $subject, $message)
  {
    $client = new Postmark(self::KEY);

    $sendResult = $client->sendEmail(
      self::SENDER,
      $recipient,
      $subject,
      $message
    );
  }

  static public function TRANSACTION($subject, $recipient, $message)
  {
    $instance = new self;
    $instance->send($recipient, $subject, $message);
  }
}
