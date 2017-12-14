<?php

return[
  'notFoundHandler'=>function($c){
    return function($req,$resp){
      $resp=$resp->withStatus(400)->withHeader('Content-Type',"application/json");
      $resp->getBody()->write(json_encode('URL introuvable'));
      return $resp;
    };
  },
  'notAllowHandler'=>function($c){
    return function($req,$resp,$methods){
      return $resp->withStatus(405)
                  ->withHeader('Allow',implode(',',$methods))
                  ->withHeader('Content-Type',"application/json")
                  ->getBody()
                  ->write(json_encode('Méthode permises :'.implode(',',$methods)));
    };
  },
  'phpErrorHandler'=>function($c){
    return function($req,$resp){
      return $resp->withStatus(500)
                  ->withHeader('Content-Type',"application/json")
                  ->getBody()
                  ->write(json_encode('php Error'));
    };
  }
];
