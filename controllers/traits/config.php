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
    $this->cdn         = $this->mode === 'local' ? '' : '//cdn.thirty.cc';
    
    //TODO: this should be done automatically
    $this->semester    = "FA16";
    $this->email       = 'bmetzger@colum.edu';
    $this->template    = $request->controller;
    $this->redirect    = $request->redirect;
  }

  public function authenticate($user = null)
  {
    if ($user === null && array_key_exists('token', $_COOKIE)) {

      $token = (new \bloc\types\token(DOMAIN))->validate($_COOKIE['token'], getenv('EMAIL_TOKEN'));
      $node = \models\Data::ID($token->sub);
      $user = \models\Data::Factory($node->nodeName, $node);
    }
    
    if (! $user instanceof \bloc\types\authentication) return false;   

    $type = $user::type();
    $this->{$type} = $user;
    Render::PARTIAL('helper', "views/layouts/helpers/{$type}.html");
    
    return $user;
  }

  public function GETlogout($user)
  {
    setcookie('token', '', time()-3600, '/');
    \bloc\router::redirect('/');
  }

  public function GETlogin($status = 0)
  {
    \bloc\Application::instance()->getExchange('response')->addHeader("HTTP/1.0 401 Unauthorized");
    $messages = [
      'Request Token',
      'Check Credentials',
      'ID malformed',
      'A password link was alread sent (you can still use it).',
      "A token was previously requested and has not been claimed. That token is revoked—please generate a new email.",
    ];

    $view = new \bloc\View(self::layout);
    $view->content = "views/layouts/forms/authenticate.html";
    if ($status === 'instructor') {
      $view->user = \bloc\dom\Document::ELEM('<input type="text" value="" name="uid" placeholder="instructor"/>');
    }

    $this->status = $messages[(int)$status];
    return $view->render($this());
  }

  public function GETtoken($encoded_email, $encoded_token)
  {
    // authentication of a user will throw an exception if unavailable.
    $email  = base64_decode($encoded_email);
    $token  = base64_decode($encoded_token);
    $secret = getenv('EMAIL_TOKEN');

    $payload = (new \bloc\types\token(DOMAIN))->validate($token, $secret);
    $user = (new \models\student($payload->sub))->authenticate($email);
    $user->save();
    \bloc\types\token::save($token, $payload->exp);
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
        $user    = (new \models\instructor(\models\Data::ID($uid)))->authenticate($pin);
        $token   = new \bloc\types\token(DOMAIN);
        $secret  = getenv('EMAIL_TOKEN');
        $value   = $token->generate($uid, $secret);
        $payload = $token->validate($value, $secret);
        
        \bloc\types\token::save($value, $payload->exp);
        \bloc\router::redirect('/overview/instructor');
      } else if(!empty($pin)){
        $sub = \models\Student::BLEAR($pin);
        // user must be in database based on oasis id, find them: ;
        $user = \models\Data::ID($sub);
        $token = (new \bloc\types\token(DOMAIN))->generate($sub, getenv('EMAIL_TOKEN'));
        
        // set the token on the user field
        if ($user->hasAttribute('token') && $user->getAttribute('token') !== $token) {
          $user->removeAttribute('token');
          \models\Data::instance()->storage->save();
          throw new \InvalidArgumentException("Authentication Error", 4);
        } else {
          $user->setAttribute('token', $token);
          \models\Data::instance()->storage->save();
          // email the user a link.
          $template = new \bloc\View('views/layouts/email.html');
          $template->content = 'views/layouts/forms/transaction.html';
          
          $encoded_email = base64_encode($user['@email']);
          $encoded_token = base64_encode($token);
          $output = [
            'link' =>  DOMAIN."/records/token/{$encoded_email}/{$encoded_token}",
            'title' => $user['@name'],
            'message' => 'login to course site'
          ];
          
          $time = date('M jS, g:ia');
          \models\Message::TRANSACTION("Login Link: {$time}", $user['@email'], (string)$template->render($output));
        }
        
      }
    } catch (\InvalidArgumentException $e) {
      $path = sprintf('/%s/login/%s/',$this->template, $e->getCode() ?? 1);
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
