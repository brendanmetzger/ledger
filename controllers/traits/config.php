<?php
namespace controllers\traits;
use \bloc\view\Renderer as Render;

trait config {
  public function __construct($request)
  {
    \models\Data::$DB = 'FA16';
    \bloc\view::addRenderer('before', Render::PARTIAL());
    \bloc\view::addRenderer('after', Render::HTML());
    \bloc\view::addRenderer('after', Render::PREVIEWS());
    \bloc\view::addRenderer('after', Render::REVIEW());

    $this->year        = date('Y');
    $this->title       = "*SWM";
    $this->email       = 'bmetzger@colum.edu';
    $this->template    = $request->controller;

    if (($user = $this->authenticate()) instanceof \bloc\types\authentication) {
      $type = $user::type();
      $this->{$type} = $user;
      Render::PARTIAL('helper', "views/layouts/helpers/{$type}.html");
    }
  }

  public function authenticate()
  {
    if ((isset($_SESSION) && array_key_exists('id', $_SESSION))) {
      $node = \models\Data::ID($_SESSION['id']);
      return \models\Data::FACTORY($node->nodeName, $node);
    }
    return null;
  }

  public function GETlogout()
  {
    session_destroy();
    \bloc\router::redirect('/');
  }

  public function GETlogin($status = "default")
  {
    \bloc\Application::instance()->getExchange('response')->addHeader("HTTP/1.0 401 Unauthorized");
    $messages = [
      'default'   => 'Request Token',
      'invalid'   => 'ID malformed',
      'duplicate' => 'A password link was alread sent (you can still use it).',
      'instructor' => 'Check Credentials',
    ];

    $view = new \bloc\View(self::layout);
    $view->content = "views/layouts/forms/authenticate.html";

    if ($status == 'instructor') {
      $view->user = \bloc\dom\Document::ELEM('<input type="text" value="" name="uid" placeholder="instructor"/>');
    }

    $this->status   = $messages[$status];
    return $view->render($this());
  }

  public function GETtoken($pin, $token)
  {
    try {
      $user = (new \models\student(\models\Data::ID($pin)))->authenticate($token);
      \bloc\Application::instance()->session('COLUM', ['id' => $pin]);
      \models\Data::instance()->storage->save();
      \bloc\router::redirect("/{$user->course}/dashboard");
    } catch (\InvalidArgumentException $e) {
      return $this->GETError($e->getMessage(), $e->getCode());
    }
  }

  public function GETError($message, $code = 404)
  {
    $this->message = parent::GETerror($message, $code);
    $view = new \bloc\View(self::layout);
    $view->content = 'views/layouts/error.html';
    return $view->render($this());
  }

  public function POSTlogin($request)
  {
    $pin = $request->post('pin') ?: 0;
    $uid = $request->post('uid') ?: false;
    try {
      if ($uid && $pin) {
        // authenticate user based on password
        (new \models\instructor(\models\Data::ID($uid)))->authenticate($pin);
        \bloc\Application::instance()->session('COLUM', ['id'=>  $uid]);
        \bloc\router::redirect('/records/courses');
      } else {
        // user must be in database based on oasis id, find them: ;
        $user = \models\Data::ID(\models\Student::BLEAR($pin));
        // if found, generate token with sha1 of $email address and token
        $token = \bloc\types\token::generate($user['@email'], getenv('EMAIL_TOKEN'));

        // set the token on the user field
        if ($user->hasAttribute('token') && $user->getAttribute('token') === $token) {
          throw new \InvalidArgumentException("Token Already Requested", 2);
        } else {
          $user->setAttribute('token', $token);
          \models\Data::instance()->storage->save();

          // email the user a link.
          $template = new \bloc\View('views/layouts/email.html');
          $template->content = 'views/layouts/forms/transaction.html';

          $output = [
            'link' =>  DOMAIN."/records/token/{$user['@id']}/{$token}",
            'title' => $user['@name'],
            'message' => 'login to course site'
          ];

          \models\Message::TRANSACTION('login', $user['@email'], (string)$template->render($output));
        }
      }
    } catch (\InvalidArgumentException $e) {
      $type = $e->getCode() == 1 ? 'invalid' : 'duplicate';
      $path = sprintf('/%s/login/%s/',$this->template, $type);
      \bloc\router::redirect($path);
    }
    $view = new \bloc\View(self::layout);
    $view->content = 'views/layouts/forms/transaction.html';
    return $view->render([
      'link' => 'http://www.colum.edu/loopmail',
      'title' => 'Email Sent',
      'message' => 'check your email'
    ]);
  }
}
