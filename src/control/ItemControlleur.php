<?php

  namespace lbs\control;

  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;
  use lbs\model\Item as item;
  use lbs\model\Commande as commande;
  use illuminate\database\Eloquent\ModelNotFoundException as ModelNotFoundException;

  class TailleControlleur{
    public $conteneur=null;
    public function __construct($conteneur){
      $this->conteneur=$conteneur;
    }

    /*
     * Créée via une requête POST une un nouvel Item d'une commande
     * @param : Request $req, Response $resp, array $args[]
     * Return Response $resp contenant l'item complet
     */
    public function createItem(Request $req, Response $resp, array $args){
    	$token=$args['token'];
    	
    	if($token != null){
    		$commande = commande::where('token', '=', $token)->firstOrFail();
    		if($commande != null){
		    	$postVar=$req->getParsedBody();
		    	$item = new item();
		    	$item->comm_id=filter_var($postVar['comm_id'],FILTER_SANITIZE_STRING);
		    	$item->tai_id=filter_var($postVar['tai_id'],FILTER_SANITIZE_STRING);
		    	$item->sand_id=filter_var($postVar['sand_id'],FILTER_SANITIZE_STRING);
		    	
		    	if($postVar['quantite'] != null){
		    		$item->quantite=filter_var($postVar['quantite'],FILTER_SANITIZE_STRING);
		    	}else{
		    		$item->quantite=1;
		    	}
		    	
		    	$item->save();
		    	$resp=$resp->withHeader('Content-Type','application/json')
		    	->withStatus(201)
		    	->withHeader('Location', '/items/nouvelle');
		    	$resp->getBody()->write('created');
    		}
    	}
    	else{
    		$resp=$resp->withStatus(404);
    		$resp->getBody()->write('not found');
    	}
    	return $resp;
    }
  }
