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

		$rs= $resp->withHeader( 'Content-type', "application/json;charset=utf-8");

			if(!$req->hasHeader('Authorization')) {

				$rs = $rs->withHeader('WWW-authenticate', 'Basic realm="lbs api" ');
				$resp= $resp->withStatus(401);
				$temp = array("type" => "error", "error" => '401', "message" => "No Authorization in header");

				$rs->getBody()->write(json_encode($temp));

				return $rs;

			}

			$auth = base64_decode( explode( " ", $req->getHeader('Authorization')[0]) [1] );
			list($user, $pass) = explode(':', $auth);

			try {
				$carte = Carte::select('id', 'nom', 'password')
					->where('id', '=', $args['id'])
					->firstOrFail();

				if($pass != $carte->password) {
					throw new \Exception("Authentification incorrecte");

					/*if(!password_verify($pass, $carte->password)) {
					throw new \Exception("Authentification incorrecte");*/

				}

			} catch(\Exception $e){

				$rs = $rs->withHeader('WWW-authenticate', 'Basic realm="lbs api" ');
				$resp= $resp->withStatus(401);
				$temp = array("type" => "error", "error" => '401', "message" => $e->getMessage());

				$rs->getBody()->write(json_encode($temp));

				return $rs;

			}

			$secret = 'lbs';

			$token = JWT::encode( [ 'iss'=>'http://api.lbs.local/auth',
				'aud'=>'http://api.lbs.local',
				'iat'=>time(), 
				'exp'=>time()+3600,
				'uid' =>  $carte->id], 
				$secret, 'HS512' );

			$resp= $resp->withStatus(201);

			$temp = array("token" => $token);

			$rs->getBody()->write(json_encode($temp));

			return $rs;
		}

	public function getCarte(Request $req, Response $resp, array $args) {

		try{

				$carte = Carte::findOrFail($args['id']);
				$authorization_header = $req->getHeader('Authorization')[0];
				$jwt_token = sscanf($authorization_header, 'Bearer %s')[0];
				$decoded_token = JWT::decode($jwt_token, 'lbs', array('HS512'));

				if($carte->id != $decoded_token->uid){
					return $resp->withJson(array(
						'type' => 'error',
						'error' => 401,
						'message' => 'Accès refusé à la ressource /cartes/'.$id
					), 401);
				}
				return $resp->withJson([
					"id" => $carte->id,
						"date_creation" => $carte->created_at,
						"date_valide" => $carte->validate_at
				]);
		} catch(\Illuminate\Database\Eloquent\ModelNotFoundException $ex){
			return $resp->withJson(array(
				'type' => 'error',
				'error' => 404,
				'message' => 'Ressource non trouvée /cartes/'.$id
			), 404);
		} catch(\Firebase\JWT\SignatureInvalidException $ex){
			return $resp->withJson(array(
				'type' => 'error',
				'error' => 401,
				'message' => 'JWT token non valide'
			), 401);
		} catch(\Firebase\JWT\ExpiredException $ex){
			return $resp->withJson(array(
				'type' => 'error',
				'error' => 401,
				'message' => 'JWT token expiré'
			), 401);
		} catch(\BeforeValidException $ex){
			return $resp->withJson(array(
				'type' => 'error',
				'error' => 401,
				'message' => 'token not yet valid'
			), 401);
		}
	}

	public function payerCommande(Request $req, Response $resp, array $args) {
		//Fonctionnel malgré une erreur de l'application
		try{

			$carte = Carte::findOrFail($args['id']);
			$authorization_header = $req->getHeader('Authorization')[0];
			$jwt_token = sscanf($authorization_header, 'Bearer %s')[0];
			$decoded_token = JWT::decode($jwt_token, 'lbs', array('HS512'));

			if($carte->id != $decoded_token->uid){
				return $response->withJson(array(
					'type' => 'error',
					'error' => 401,
					'message' => 'Accès refusé à la ressource /cartes/'.$args['id']
				), 401);
			}

			$commande = Commande::findOrFail($args['id_commande']);

			if ($commande->etat != 0) {
				return $response->withJson(array(
					'type' => 'error',
					'error' => 401,
					'message' => 'La commande'.$args['id'].'a déjà été payé.'
				), 401);
			}
			else{
				$commande->prix = $commande->prix - $carte->montant;
				$carte->nbcommande = $carte->nbcommande + 1;
				$carte->montant = 0;
				$carte->save();

				$commande->etat = 1;
				$commande->save();
			}
			

			return $response->withJson(array(
				'type' => 'message',
				'error' => 200,
				'message' => 'Paiement accépté'
			), 200);

			$resp= $response->withStatus(400);
			$temp = array("type" => "error", "error" => '400', "message" => "Donnée manquante");
			$resp->getBody()->write(json_encode($temp));

			return $resp;

		}
		catch(\Illuminate\Database\Eloquent\ModelNotFoundException $ex){
			return $response->withJson(array(
				'type' => 'error',
				'error' => 404,
				'message' => 'Ressource non trouvée /cartes/'.$args['id']
			), 404);
		}
		catch(\Firebase\JWT\SignatureInvalidException $ex){
			return $response->withJson(array(
				'type' => 'error',
				'error' => 401,
				'message' => 'JWT token non valide'
			), 401);
		}
		catch(\Firebase\JWT\ExpiredException $ex){
			return $response->withJson(array(
				'type' => 'error',
				'error' => 401,
				'message' => 'JWT token expiré'
			), 401);
		}
		catch(\Firebase\JWT\BeforeValidException $ex){
			return $response->withJson(array(
				'type' => 'error',
				'error' => 401,
				'message' => 'token not yet valid'
			), 401);
		}
		catch(\DomainException $ex){
			return $response->withJson(array(
				'type' => 'error',
				'error' => 401,
				'message' => 'Domain Exception !!!!'
			), 401);
		}
		catch(\UnexpectedValueException $ex){
			return $response->withJson(array(
				'type' => 'error',
				'error' => 401,
				'message' => 'UnexpectedValueException'
			), 401);
		}
	}

	/*public function createCarte(Request $req, Response $resp, array $args){

		$postVar=$req->getParsedBody();

  	$carte = new Carte();
  	$carte->id= Uuid::uuid1();
  	$carte->nom=filter_var($postVar['nom'],FILTER_SANITIZE_STRING);
  	$carte->password=filter_var($postVar['password'],FILTER_SANITIZE_STRING);
		$carte->mail=filter_var($postVar['mail'],FILTER_SANITIZE_STRING);

		$carte->save();
	}*/
}
