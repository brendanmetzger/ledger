<?php

$serv = new swoole_websocket_server("http://127.0.0.1/", 9502);

$serv->on('open', function($server, $req) {
  echo "connection open: {$req->fd}\n";
  print_r($req);
});

$serv->on('message', function($server, $frame) {
  print_r($frame);
  foreach($server->connections as $fd) {
    
    $out = [
      'message' => $frame->data,
      'frame'   => $fd,
    ];
    
    $server->push($fd, json_encode($out));
  }
});

$serv->on('close', function($server, $fd) {
  echo "connection close: {$fd}\n";
});

$serv->start();


?>
