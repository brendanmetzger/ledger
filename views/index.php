<?php
namespace bloc;

define('DOMAIN', "http://{$_SERVER['SERVER_NAME']}");

date_default_timezone_set ('America/Chicago');

#1. Frow where index.php is located, load the application file. This is the only mandatory part I think.
require  '../bloc/application.php';



#2. Create an instance of the application
$app = Application::instance(['mode' => getenv('MODE') ?: 'production']);

#3. Get a Session ready
$app->prepare('session-start', function ($app) {
  $app->session('COLUM');
});

$app->prepare('clean-up', function ($app) {
  session_write_close();
});


# main page deal
$app->prepare('http-request', function ($app, $params) {
  $request  = new Request($params);
  $response = new Response($request);

  $app->setExchanges($request, $response);

  // Provide a namespace (also a directory) to load objects that can respond to controller->action
  $router  = new Router('controllers', $request);

  try {
    $output = $router->delegate('lecture', 'index');
  } catch (\Exception $e) {
    \bloc\application::instance()->log($e->getTrace());
    $view = new View(\controllers\records::layout);
    $view->content = 'views/layouts/error.html';
    $output = $view->render(['message' => $e->getMessage()]);
  }

  // default controller and action as arguments, in case nothin doin in the request
  $response->setBody($output);

  if (getenv('MODE') === 'local' && count($app->log()) > 0) {
    $app->execute('debug', $response);
  }

  echo $response;
});



$app->prepare('debug', function ($app, $response) {
    $app::instance()->log('Peak Memory: ' . round(memory_get_peak_usage() / pow(1024, 2), 4). "Mb");
    $app::instance()->log('Executed in: ' . round(microtime(true) - $app->benchmark, 4) . "s");

    $output = $response->getBody();
    if ($output instanceof \bloc\view) {
      $elem = (new DOM\Element('pre'))->insert($output->dom->documentElement->lastChild);
      $elem->setAttribute('class', 'error console');
      foreach ($app->log() as $message) {
        $elem->appendChild($elem->ownerDocument->createTextNode(print_r($message, true)."\n"));
      }
    }


  return $output;
});

#4. Run the app. Nothing happens w/o this. Can call different stuff from the queue.
$app->execute('session-start');
$app->execute('http-request', $_REQUEST);
$app->execute('clean-up');
