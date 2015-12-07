<?php
namespace bloc;

date_default_timezone_set ('America/Chicago');

#1. Frow where index.php is located, load the application file. This is the only mandatory part I think.
require  '../bloc/application.php';



#2. Create an instance of the application
$app = Application::instance(['mode' => getenv('MODE') ?: 'production']);


# main page deal
$app->prepare('http-request', function ($app, $params) {

  $request  = new Request($params);
  $response = new Response($request);

  $app->setExchanges($request, $response);

  // Provide a namespace (also a directory) to load objects that can respond to controller->action
  $router  = new Router('controllers', $request);

  // default controller and action as arguments, in case nothin doin in the request
  $response->setBody($router->delegate('manage', 'index'));
  echo $response;
});


#4. Run the app. Nothing happens w/o this. Can call different stuff from the queue.
$app->execute('http-request', $_REQUEST);
