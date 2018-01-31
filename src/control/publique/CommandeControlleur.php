<?php

  namespace lbs\control\publique;

  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;
  use lbs\model\Commande as commande;
  use lbs\model\Sandwich as sandwich;
  use lbs\model\Taille as taille;
  use lbs\model\Tarif as tarif;
  use illuminate\database\Eloquent\ModelNotFoundException as ModelNotFoundException;
  use Ramsey\Uuid\Uuid as Uuid;

  class CommandeControlleur{
    public $conteneur=null;
    public function __construct($conteneur){
      $this->conteneur=$conteneur;
    }

    /*
     * Créée via une requête POST une nouvelle commandes
     * @param : Request $req, Response $resp, array $args[]
     * Return Response $resp contenant la page complète
     */
    public function createCommande(Request $req, Response $resp, array $args){

    	$postVar=$req->getParsedBody();
    	$commande = new commande();
    	$commande->id= Uuid::uuid1();
    	$commande->nom=filter_var($postVar['nom'],FILTER_SANITIZE_STRING);
    	$commande->prenom=filter_var($postVar['prenom'],FILTER_SANITIZE_STRING);
    	$commande->mail=filter_var($postVar['mail'],FILTER_SANITIZE_STRING);
    	$commande->livraison= \DateTime::createFromFormat('d-m-Y H:i',$postVar['livraison']['date'].' '.$postVar['livraison']['heure']);
    	$commande->token=bin2hex(random_bytes(32));
    	$commande->etat=0; // created

    	$commande->save();

    	//Maintenant on va créer un objet que l'on va présenter à l'utilisateur
    	$commande->livraison = $postVar['livraison']['date'].' '.$postVar['livraison']['heure'];
    	$commandeFormate = $this->returnCommandeFormate($commande);

    	$resp=$resp->withHeader('Content-Type','application/json')
    	->withStatus(201)
    	->withHeader('Location', '/commandes/nouvelle');
    	$resp->getBody()->write(json_encode($commandeFormate));
    	return $resp;
    }
    
    /*
     * Mettre à jour la date de livraison via une requête PUT
     * @param : Request $req, Response $resp, array $args[]
     * Return Response $resp contenant la page complète
     */
    public function updateCommande(Request $req, Response $resp, array $args){
    	$id=$args['id'];
    	$postVar=$req->getParsedBody();
    	
    	if($id != null){
    		try{
    			$commande = commande::where('id', '=', $id)->firstOrFail();
    			
    			if($commande->etat == 2){ // payée et livrée
    				$resp=$resp->withStatus(403);
    				$resp->getBody()->write('La commande a déja été livrée');
    			}else{
    				$date = filter_var($postVar['date'],FILTER_SANITIZE_STRING);
    				$heure = filter_var($postVar['heure'],FILTER_SANITIZE_STRING);
    				$commande->livraison= \DateTime::createFromFormat('d-m-Y H:i',$date.' '.$heure);
    				$commande->save();
    				
    				//$commande->livraison = $commande->livraison->date;
    				$commande->livraison = $postVar['date'].' '.$postVar['heure'];
    				$commandeFormate = $this->returnCommandeFormate($commande);
    				
    				$resp=$resp->withHeader('Content-Type','application/json')
    				->withStatus(201)
    				->withHeader('Location', '/commandes/update');
    				$resp->getBody()->write(json_encode($commandeFormate));
    			}
    		}catch(ModelNotFoundException $ex){
    			$resp=$resp->withStatus(404);
    			$resp->getBody()->write('not found');
    		}
    	}else{
    		$resp=$resp->withStatus(404);
    		$resp->getBody()->write('not found');
    	}
    	return $resp;
    	
    }

    /*
     * Retourne la commande
     * @param : array $args[], Response $resp
     * Return Response $resp contenant la page complète
     */
    public function getCommande(Response $resp, array $args){
    	$id=$args['id'];
    	$resp=$resp->withHeader('Content-Type','application/json');
    	$commande = commande::where('id', '=', $id)->firstOrFail();

    	//Maintenant on va créer un objet que l'on va présenter à l'utilisateur
    	$commandeFormate = $this->returnCommandeFormate($commande);

    	$resp->getBody()->write(json_encode($commandeFormate));
    	return $resp;
    }
    
    /*
     * Retourne la facture de la commande
     * @param : array $args[], Response $resp
     * Return Response $resp contenant la page complète
     */
    public function getFactureCommande(Response $resp, array $args){
    	$id=$args['id'];
    	$resp=$resp->withHeader('Content-Type','application/json');
    	$commande = commande::where('id', '=', $id)->firstOrFail();
    	$items = $commande->items()->get();
    	
    	$itemsFormate = [];

            //$itemsFormate[] = $items[0];

        //$toto = $items[0]=>sandwich();

        
    	foreach($items as $item){
            $sand = sandwich::where('id', '=', $item->sand_id)->first();
            $taille = taille::where('id', '=', $item->tai_id)->first();
            $prix = tarif::where('taille_id', '=', $item->tai_id)->where('sand_id', '=', $item->sand_id)->first();
            
    		$itemsFormate[] = (object)[
    			"nom_sandwich" => $sand->nom,
    			"taille_sandwich" => $taille->nom,	
    			"quantite" => $item->quantite,
                "prix" => $item->quantite * $prix->prix
    		];
            
    	}
        
    	
    	$dateTime = null;
    	$date = '';
    	$heure = '';
    	
    	if ($commande->livraison != null){
    		$dateTime = explode(' ' , $commande->livraison);
    		$date = $dateTime[0];
    		$heure = $dateTime[1];
    	}
    	
    	$facture = (object)[
    			"nom_client"=> $commande->nom,
    			"prenom_client"=> $commande->prenom,
    			"mail_client"=> $commande->mail,
    			"livraison"=>[
    					"date"=> $date,
    					"heure"=> $heure
    			],
                "prix_total" => $commande->prix,
    			"sandwich(s)"=>$itemsFormate
    	];
    	
    	$resp->getBody()->write(json_encode($facture));
    	return $resp;
    }

    /*
     * Renvoie l'objet commande formate
     * @param : $commande => l'objet commande d'origine
     * Return l'objet formate
     */
    public function returnCommandeFormate($commande){

    	$etat = "créé";
    	if($commande->etat == 1){
    		$etat = "payé";
    	}elseif($commande->etat == 2){
    		$etat = 'payée et facturée';
    	}

    	$dateTime = null;
    	$date = '';
    	$heure = '';
    	
    	if ($commande->livraison != null){
    		$dateTime = explode(' ' , $commande->livraison);
    		$date = $dateTime[0];
    		$heure = $dateTime[1];
    	}

    	$commandeFormate = (object)[
    			"nom_client"=> $commande->nom,
    			"prenom_client"=> $commande->prenom,
    			"mail_client"=> $commande->mail,
    			"livraison"=>[
    					"date"=> $date,
    					"heure"=> $heure
    			],
    			"etat"=>$etat,
    			"id"=>$commande->id,
    			"token"=>$commande->token
    	];

    	return $commandeFormate;
    }
  }
