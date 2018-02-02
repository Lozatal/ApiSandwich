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
			    	
			    	$commande->prix += $tarif->prix * $item->quantite;
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
    
    /*
     * Supprime le sandwich d'une commande
     * @param : array $args[], Response $resp
     * Return Response $resp contenant la page complète
     */
    public function deleteItem(Response $resp, array $args){
    	$id=$args['id'];
    	$item_id=$args['id_sand'];
    	
    	//on vérifie que la commande éxiste
    	$commande = commande::where('id', '=', $id)->first();
    	if($commande != null){
    		try{
    			// on vérifie que l'item éxiste toujours
    			$item = item::where('id', '=', $item_id)->firstOrFail();
    			try{
    				$item->delete();

    				$resp=$resp->withStatus(204);
    				$resp->getBody()->write('no content');
    			}catch(ModelNotFoundException $ex){
    				$resp=$resp->withStatus(403);
    				$resp->getBody()->write($ex);
    			}
    		}catch(ModelNotFoundException $ex){
    			$resp=$resp->withStatus(404);
    			$resp->getBody()->write('item not found');
    		}
    	}else{
    		$resp=$resp->withStatus(404);
    		$resp->getBody()->write('command not found');
    	}
    }
    
    /*
     * Mettre à jour un item via une requête PUT
     * @param : Request $req, Response $resp, array $args[]
     * Return Response $resp contenant la page complète
     */
    public function updateItem(Request $req, Response $resp, array $args){
    	$id=$args['id'];
    	$id_item = $args['id_sand'];
    	$postVar=$req->getParsedBody();
    	
    	if($id != null){
    		try{
    			$commande = commande::where('id', '=', $id)->firstOrFail();
    			
    			if($commande->etat == 2){ // payée et livrée
    				//$resp=$resp->withStatus(403);
    				//$resp->getBody()->write('La commande a déja été livrée');
    				$resp=$resp->withHeader('Content-Type','application/json')
    				->withStatus(403)
    				->withHeader('Location', '/items/nouvelle');
    				$resp->getBody()->write(json_encode($commande));
    			}else{
    				$tarif = tarif::where('taille_id', '=', filter_var($postVar['tai_id'],FILTER_SANITIZE_STRING))
    				->where('sand_id', '=', filter_var($postVar['sand_id'],FILTER_SANITIZE_STRING))
    				->first();
    				
    				if ($tarif != null){
	    				try{
	    					// on vérifie que l'item éxiste toujours
	    					$item = item::where('id', '=', $id_item)->firstOrFail();
	    					
	    					//On va récupérer l'ancien tarif
	    					$oldTarif = tarif::where('taille_id', '=', $item->tai_id)
	    					->where('sand_id', '=', $item->sand_id)
	    					->first();
	    					$oldQuantite = $item->quantite;
	    					
	    					$item->tai_id=filter_var($postVar['tai_id'],FILTER_SANITIZE_STRING);
	    					$item->sand_id=filter_var($postVar['sand_id'],FILTER_SANITIZE_STRING);
	    					
	    					if($postVar['quantite'] != null){
	    						$item->quantite=filter_var($postVar['quantite'],FILTER_SANITIZE_STRING);
	    					}else{
	    						$item->quantite=1;
	    					}
	    					
	    					$item->save();
	    					
	    					//On modifie le tarif global de la commande en soustrayant l'ancien tarif et en ajoutant le nouveau
	    					$commande->prix -= $oldTarif->prix * $oldQuantite;
	    					$commande->prix += $tarif->prix * $item->quantite;
	    					$commande->save();
	    					
	    					$resp=$resp->withHeader('Content-Type','application/json')
	    					->withStatus(201)
	    					->withHeader('Location', '/items/nouvelle');
	    					$resp->getBody()->write(json_encode($item));
	    					
	    				}catch(ModelNotFoundException $ex){
	    					$resp=$resp->withStatus(404);
	    					$resp->getBody()->write('item not found');
	    				}
    				}else{
    					$resp=$resp->withStatus(404);
    					$resp->getBody()->write("Le sandwich associé à cette taille n'a pas de tarif valide.");
    				}
    			}
    		}catch(ModelNotFoundException $ex){
    			$resp=$resp->withStatus(404);
    			$resp->getBody()->write('command not found');
    		}
    	}else{
    		$resp=$resp->withStatus(404);
    		$resp->getBody()->write('command not found');
    	}
    	return $resp;
    	
    }
  }
