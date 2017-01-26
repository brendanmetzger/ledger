<?php
namespace controllers\traits;
use \bloc\view\Renderer as Render;

trait config {

  public function __construct($request)
  {
    \models\Data::$SEMESTER = 'SP17';
    \bloc\view::addRenderer('before', Render::PARTIAL());
    \bloc\view::addRenderer('after', Render::HTML());
    \bloc\view::addRenderer('after', Render::EXAMPLES());
    \bloc\view::addRenderer('after', Render::PREVIEWS());
    \bloc\view::addRenderer('after', Render::REVIEW());

    $this->year        = date('Y');
    $this->mode        = getenv('MODE');
    $this->title       = ucwords($request->controller . ' - ' . $request->action);
    $this->entropy     = rand();
    $this->cdn         = $this->mode === 'local' ? '' : 'http://cdn.thirty.cc'; 
    
    //TODO: this should be done automatically
    $this->semester    = "FA16";
    $this->email       = 'bmetzger@colum.edu';
    $this->template    = $request->controller;
    $this->redirect    = $request->redirect;

    if (($user = $this->authenticate()) instanceof \bloc\types\authentication) {
      $type = $user::type();
      $this->{$type} = $user;
      Render::PARTIAL('helper', "views/layouts/helpers/{$type}.html");
    }
  }

  public function authenticate($user = null)
  {
    if ((isset($_SESSION) && array_key_exists('id', $_SESSION))) {
      $node = \models\Data::ID($_SESSION['id']);
      return \models\Data::FACTORY($node->nodeName, $node);
    } else if (array_key_exists('token', $_COOKIE)) {
      $credentials = explode('-', $_COOKIE['token']);
      $node = \models\Data::ID($credentials[0]);
      $user = \models\Data::Factory($node->nodeName, $node)->authenticate($credentials[1]);
    }
    
    if ($user && $user instanceof \bloc\types\authentication) {
      \bloc\Application::instance()->session('COLUM', ['id' => $user['@id']]);
    }
    
    return $user;
  }

  public function GETlogout($user)
  {
    session_destroy();
    // destroy cookie
    setcookie('token', '', time()-3600, '/');
    
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

    $this->status = $messages[$status];
    return $view->render($this());
  }

  public function GETtoken($pin, $token)
  {
    // authentication of a user will throw an exception if unavailable.
    $user = $this->authenticate((new \models\student(\models\Data::ID($pin)))->authenticate($token));

    // set a cookie that can be used on subsuquent logins
    setcookie('token', $pin.'-'.$token, time()+60*60*24*30, '/');
    
    \bloc\router::redirect("/{$user->course}/dashboard");
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
        \bloc\router::redirect('/overview/instructor');
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
          $time = date('M j');
          \models\Message::TRANSACTION("Login Link: {$time}", $user['@email'], (string)$template->render($output));
        }
      }
    } catch (\InvalidArgumentException $e) {
      $type = $e->getCode() == 1 ? 'invalid' : 'duplicate';
      $path = sprintf('/%s/login/%s/',$this->template, $type);
      \bloc\router::redirect($path);
    }
    $view = new \bloc\View(self::layout);
    $view->content = 'views/layouts/forms/transaction.html';

    return $view->render(array_merge((array)$this(), [
      'link' => 'http://www.colum.edu/loopmail',
      'title' => 'Email Sent',
      'message' => 'check your email'
    ]));
  }
}
