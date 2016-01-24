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
    'student' => [
      '@' => ['id' => null, 'email' => '', 'hash' => ''],
      'assignment' => [],
    ]
  ];

  public function authenticate($token)
  {
    if (! password_verify($token, $this->context->getAttribute('hash'))) {
      throw new \InvalidArgumentException("Credentials do not match", 1);
    }
    return $this;
  }
}
