<?php

namespace lbs\control\publique;

use Firebase\JWT\JWT as JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException ;
use Firebase\JWT\BeforeValidException;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use lbs\utils\Writer as Writer;

use lbs\model\Commande as Commande;
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
          'uid' => $carte->id], 
          $secret, 'HS512' );

    $resp= $resp->withStatus(201);

    $resp->getBody()->write(json_encode($token));
    return $resp;
  }

  public function getCarte(Request $req, Response $resp, array $args){
    $id=$args['id'];
    $secret='lbs';

    try {
      $carte = Carte::findOrFail($id);

      $header= $req->getHeader('Authorization')[0];
      $tokenstring= sscanf($header, "Bearer %s")[0];
      $token= JWT::decode($tokenstring, $secret, ['HS512']);

      if($carte->id != $token->uid){
        return $resp->withJson(array(
          'type' => 'Error',
          'error' => 401,
          'message' => "echec auth"
        ), 401);
      }
      else{
       return $resp->withJson([
          "id" => $carte->id,
          "nom"=>$carte->nom,
          "nbcommande"=>$carte->nbcommande,
          "montant"=>$carte->montant,
          "created_at"=>$carte->created_at,
          "validate_at"=>$carte->validate_at
        ]); 
      }
    }
    catch (ExpiredException $e) { 
      return $resp->withJson(array(
          'type' => 'Error',
          'error' => 401,
          'message' => "JWT token expiré"
        ), 401);
    } 
    catch (SignatureInvalidException $e) { 
      return $resp->withJson(array(
          'type' => 'Error',
          'error' => 401,
          'message' => "signature du token non valide"
        ), 401);
    } 
    catch (BeforeValidException $e) { 
      return $resp->withJson(array(
          'type' => 'Error',
          'error' => 401,
          'message' => "token plus valide"
        ), 401);
    } 
  }
}
