<?php

  namespace lbs\control\publique;

  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;
  use lbs\model\Item as item;
  use lbs\model\Commande as commande;
  use lbs\model\Tarif as tarif;
  use illuminate\database\Eloquent\ModelNotFoundException as ModelNotFoundException;

  class ItemControlleur{
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
    	$id=$args['id'];
		
    	if($id != null){
    		
    		//On vérifie que la commande existe
    		$commande = commande::where('id', '=', $id)->firstOrFail();
    		if($commande != null){
    			
    			//On regarde si cette taille et ce sandwich ont un tarif associé
    			$postVar=$req->getParsedBody();
    			$tarif = tarif::where('taille_id', '=', filter_var($postVar['tai_id'],FILTER_SANITIZE_STRING))
    			->where('sand_id', '=', filter_var($postVar['sand_id'],FILTER_SANITIZE_STRING))
    			->first();
    			
    			if ($tarif != null){
    			
			   		$item = new item();
			    	$item->comm_id=$id;
			    	$item->tai_id=filter_var($postVar['tai_id'],FILTER_SANITIZE_STRING);
			    	$item->sand_id=filter_var($postVar['sand_id'],FILTER_SANITIZE_STRING);
	
			    	if($postVar['quantite'] != null){
			    		$item->quantite=filter_var($postVar['quantite'],FILTER_SANITIZE_STRING);
			    	}else{
			    		$item->quantite=1;
			    	}
	
			    	$item->save();
			    	
			    	//On modifie le tarif global de la commande
			    	if(is_null($commande->prix)){
			    		$commande->prix = 0;
			    	}
			    	
			    	$commande->prix += $tarif->prix;
			    	$commande->save();
			    	
			    	$resp=$resp->withHeader('Content-Type','application/json')
			    	->withStatus(201)
			    	->withHeader('Location', '/items/nouvelle');
			    	$resp->getBody()->write(json_encode($item));

		    	//pas de tarif, message d'erreur
    			}else{
    				$resp=$resp->withStatus(404);
    				$resp->getBody()->write("Le sandwich associé à cette taille n'a pas de tarif valide.");
    			}
    		}
    	}
    	else{
    		$resp=$resp->withStatus(404);
    		$resp->getBody()->write('not found');
    	}
    	return $resp;
    }
  }
