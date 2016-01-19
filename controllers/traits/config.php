<?php
namespace controllers\traits;

trait config {
  public function __construct($request)
  {
    \models\Data::$DB = 'SP16';

    \bloc\view::addRenderer('after', \bloc\view\renderer::HTML());
    \bloc\view::addRenderer('after', function($view) {
      foreach ($view->parser->queryCommentNodes('preview') as $stub) {
        $path = trim(substr(trim($stub->nodeValue), 8));
        $expression = '/([\/a-z0-9\-\_]+\.[a-z]{2,4})\s([0-9]+)\.\.([0-9]+)/i';
        preg_match($expression, $path, $r);
        $file = file(PATH . $r[1]);
        $start = $r[2]-1;
        $output = array_slice($file, $start, $r[3] - $start);
        $text = "\n";
        $whitespace = strlen($output[0]) - strlen(preg_replace('/^\s*/', '', $output[0]));
        foreach ($output as $line) {
          $text .= substr($line, $whitespace);
        }
        $stub->parentNode->replaceChild($view->dom->createTextNode($text), $stub);
      }
    });
		$this->year        = date('Y');
    $this->title       = "Gradebook";
    $this->email       = 'bmetzger@colum.edu';
    $this->_controller = $request->controller;
    $this->_action     = $request->action;
  }

  public function authenticate()
  {
    return (isset($_SESSION) && array_key_exists('id', $_SESSION));
  }

  public function GETlogin($redirect, $status = "default")
  {
    \bloc\Application::instance()->getExchange('response')->addHeader("HTTP/1.0 401 Unauthorized");

    $messages = [
      'default'   => 'Request Token',
      'invalid'   => 'Username/password mismatch.',
      'duplicate' => 'A password link was alread sent',
    ];

    $view = new \bloc\View(self::layout);
    $this->status   = $messages[$status];
    $this->redirect = $redirect;
    $view->content = "views/form/authenticate.html";
    return $view->render($this());
  }

  public function GETtoken($pin, $token)
  {
    // find the user based on the pin
    try {
      $user = \models\Data::ID($pin);


      $computed = sha1($user['@email'] . date('z') . $_SERVER['REMOTE_ADDR']);
      if ($token === $computed && $token === $user['@token']) {


        $data = \bloc\Application::instance()->session('COLUM', [
          'id' =>  $pin,
          'name' => $user['@name'],
          'section' => $user->parentNode['@id'],
          'course' => $user->parentNode['@course'],
        ]);

        \bloc\router::redirect('/'.$data['course']);

      } else {
        $message =  ($user['@token'] !== $computed) ? "Token has expired": "Invalid Request";
        throw new \RuntimeException($message, 401);
      }
    } catch (\RuntimeException $e) {
      return $this->GETError($e->getMessage(), $e->getCode());
    }


    // compare the user's token field to a computed token field and the given token field. All must match.


    // if successful, remove login token
  }

  public function POSTlogin($request)
  {
    $pin = $request->post('pin') ?: -1;
    $redirect = $request->post('redirect');
    try {
      // user must be in database based on oasis id, find them: ;
      $user = \models\Data::ID(\models\Student::BLEAR($pin));
      // if found, generate token with sha1 of $pin and token that lasts til 12:00am
      $email = $user['@email'];

      $token = sha1($email . date('z') . $_SERVER['REMOTE_ADDR']);
      $output = [
        'link' =>  "http://pedagogy/records/token/{$user['@id']}/{$token}",
        'title' => $user['@name'],
        'message' => 'login to course site'
      ];

      // set the token on the user field
      if ($user->hasAttribute('token') && $user->getAttribute('token') === $token) {
        throw new \InvalidArgumentException("Token Already Requested", 2);
      } else {
        $user->setAttribute('token', $token);
        \models\Data::instance()->storage->save();

        // for testing
        $email = 'brendan.metzger@gmail.com';

        // email the user a link.
        $template = new \bloc\View('views/layouts/email.html');
        $template->content = 'views/form/transaction.html';

        \models\Message::TRANSACTION('login', $email, (string)$template->render($output));
      }
    } catch (\InvalidArgumentException $e) {
        $type = $e->getCode() == 1 ? 'invalid' : 'duplicate';
        \bloc\router::redirect(sprintf('/%s/login/%s/%s',$this->_controller, base64_encode($redirect), $type));
    }

      $this->subject = "Step 1 complete.";
      $view = new \bloc\View(self::layout);
      $view->content = 'views/form/transaction.html';
      return $view->render(['link' => 'http://www.colum.edu/loopmail', 'title' => 'Email Sent', 'message' => 'check your email']);
  }
}
