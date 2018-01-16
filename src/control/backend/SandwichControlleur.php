<?php

  namespace lbs\control\backend;

  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  use lbs\model\Categorie as categorie;
  use lbs\model\Sandwich as sandwich;
  use lbs\model\Commande as commande;
  use lbs\model\Taille as taille;

  use lbs\utils\Writer as writer;
  use lbs\utils\Pagination as pagination;

  use illuminate\database\Eloquent\ModelNotFoundException as ModelNotFoundException;

  class SandwichControlleur{
    public $conteneur=null;
    public function __construct($conteneur){
      $this->conteneur=$conteneur;
    }

    /*
    * Retourne la liste des Sandwichs, avec filtre et pagination
    * @param : Request $req, Response $resp, array $args[]
    * Return Response $resp contenant la page complète
    */
    public function getSandwichs(Request $req,Response $resp,array $args){

    }

    public function deleteSandwich(Request $req,Response $resp,array $args){
      $id=$args['id'];

    	$postVar=$req->getParsedBody();

    	$sandwich = sandwich::find($id);
    	if($sandwich){
    		$sandwich->delete();
        $resp=$resp->withStatus(200);
    		$resp->getBody()->write('Delete Complete');
    	}
    	else{
    		$resp=$resp->withStatus(404);
    		$resp->getBody()->write('not found');
    	}
    	return $resp;
    }

    public function ajouterSandwich(Request $req,Response $resp,array $args){
      $postVar=$req->getParsedBody();
      $sandwich = new sandwich();
      $sandwich->nom=filter_var($postVar['nom'],FILTER_SANITIZE_STRING);
      $sandwich->description=filter_var($postVar['description'],FILTER_SANITIZE_STRING);
      $sandwich->type_pain=filter_var($postVar['type_pain'],FILTER_SANITIZE_STRING);
      $sandwich->save();
      $resp=$resp->withHeader('Content-Type','application/json')
                 ->withStatus(201)
                 ->withHeader('Location', '/sandwich/nouvelle');
      $resp->getBody()->write('created');
      return $resp;
    }

    public function modifierSandwich(Request $req,Response $resp,array $args){
      $id=$args['id'];

    	$postVar=$req->getParsedBody();

    	$sandwich = sandwich::find($id);
    	if($sandwich){
    		if (!is_null($postVar['nom']) && !is_null($postVar['description'])&& !is_null($postVar['type_pain'])){
		    	$sandwich->nom = filter_var($postVar['nom'],FILTER_SANITIZE_STRING);
		    	$sandwich->description= filter_var($postVar['description'],FILTER_SANITIZE_STRING);
          $sandwich->type_pain= filter_var($postVar['type_pain'],FILTER_SANITIZE_STRING);
		    	$sandwich->save();

		    	$resp=$resp->withHeader('Content-Type','application/json')
		    	           ->withStatus(200)
		    	           ->withHeader('Location', '/sandwich/update');
		    	$resp->getBody()->write($sandwich);
    		}
    		else{
    			$resp=$resp->withStatus(400);
    			$resp->getBody()->write('Bad request');
    		}
    	}
    	else{
    		$resp=$resp->withStatus(404);
    		$resp->getBody()->write('not found');
    	}
    	return $resp;
    }
  }
