<?php

namespace lbs\control\publique;

use Firebase\JWT\JWT;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use lbs\utils\Writer as Writer;

use lbs\model\Commande as commande;
use lbs\model\Carte as Carte;

class CarteControlleur {

  public $conteneur=null;
    public function __construct($conteneur){
      $this->conteneur=$conteneur;
    }

    //Normalement fonctionne mais doit ajouter quelques vérif supplémentaires....ca peut toujours être utile

  public function authenticate(Request $req,Response $resp,array $args) {

      if(!$req->hasHeader('Authorization')) {

      $resp = $resp->withHeader('WWW-authenticate', 'Basic realm="lbs api" ');
      $resp= $resp->withStatus(401);
      $temp = array("type" => "error", "error" => '401', "message" => "No Authorization in header");

      $resp->getBody()->write(json_encode($temp));

      return $resp;

    }

    $auth=base64_decode( explode( " ", $req->getHeader('Authorization')[0]) [1] );
    list($user, $pass) = explode(':', $auth);

    $carte = Carte::select('id', 'nom', 'password', 'mail')
            ->where('id', '=', $args['id'])
            ->firstOrFail();


    if($carte->password != $pass) {
      $resp = $resp->withHeader('WWW-authenticate', 'Basic realm="lbs api" ');
      $resp= $resp->withStatus(403);
      $temp = array("type" => "error", "error" => '403', "message" => "Nom d'utilisateur ou mot de passe incorrecte");

      $resp->getBody()->write(json_encode($temp));

      return $resp;
    }
    else{
      $resp = $resp->withHeader('WWW-authenticate', 'Basic realm="lbs api" ');
      $resp= $resp->withStatus(200);
      $temp = array("type" => "OK", "CODE" => '200', "message" => "Connexion reussi");

      $resp->getBody()->write(json_encode($temp));

      return $resp;
    }
    
    $secret = 'lbs';

    $token = JWT::encode( [
          'iat'=>time(), 
          'exp'=>time()+3600,
          'uid' =>  $carte->id], 
          $secret, 'HS512' );

    $resp= $resp->withStatus(201);

    $resp->getBody()->write(json_encode($token));
    return $resp;
  }

  public function getCarte(Request $req, Response $resp, array $args){
    $id=$args['id'];

    /*Pour le token
    $secret = 'lbs';

    $token = JWT::encode( [
      'iat'=>time(), 
      'exp'=>time()+3600,
      'uid' =>  $carte->id], 
      $secret, 'HS512' );*/

    $carte = Carte::select("id", "nom", "nbcommande", "montant")
        ->where("id", "=", $id)
        ->first();

    $resp=$resp->withHeader('Content-Type','application/json');
    $resp->getBody()->write(json_encode($carte));
      
    return $resp;
  }

  public function payerCommande (Request $req, Response $resp, array $args) {
    $id=$args['id'];

    $carte = Carte::find($id);

    

    $resp=$resp->withHeader('Content-Type','application/json');
    $resp->getBody()->write(json_encode());
      
    return $resp;
  }
}