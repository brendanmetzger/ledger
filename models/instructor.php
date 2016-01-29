<?php
namespace models;

/**
  * Instructor
  *
  */

class Instructor extends \bloc\Model implements \bloc\types\authentication
{
  use traits\resolver, traits\persist;

  const XPATH = '/model/';

  static public $fixture = [
    'instructor' => [
      '@' => ['id' => null, 'email' => '', 'hash' => '']
    ]
  ];

  public $path = 'records/evaluate';

  public function authenticate($token)
  {
    if (! password_verify($token, $this->context->getAttribute('hash'))) {
      throw new \InvalidArgumentException("Credentials do not match", 1);
    }
    return $this;
  }
}
