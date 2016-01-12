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

  public function sendTest()
  {
    $client = new Postmark(self::KEY);

    $sendResult = $client->sendEmail(
      "bmetzger@colum.edu",
      "brendan.metzger@gmail.com",
      "Hello from Postmark!",
      "This is just a friendly 'hello' from your friends at Postmark."
    );
  }
}
