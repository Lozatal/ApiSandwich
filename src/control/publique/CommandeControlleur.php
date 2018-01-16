<?php

  namespace lbs\control\publique;

  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;
  use lbs\model\Commande as commande;
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
    	$commande->etat=1; // created

    	$commande->save();

    	//Maintenant on va créer un objet que l'on va présenter à l'utilisateur
    	$commande->livraison = $postVar['livraison']['date'].' '.$postVar['livraison']['heure'];
    	$commandeFormate = $this->returnCommandeFormate($commande);
/*
    	$commandeFormate = (object)[
    			"nom_client"=> $commande->nom,
    			"prenom_client"=> $commande->prenom,
    			"mail_client"=> $commande->mail,
    			"livraison"=>[
    					"date"=> $commande->livraison->format('d-m-Y'),
    					"heure"=> $commande->livraison->format('H-i')
    			],
    			"etat"=>"créé",
    			"id"=>$commande->id,
    			"token"=>$commande->token
    	];
*/
    	$resp=$resp->withHeader('Content-Type','application/json')
    	->withStatus(201)
    	->withHeader('Location', '/commandes/nouvelle');
    	$resp->getBody()->write(json_encode($commandeFormate));
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
     * Renvoie l'objet commande formate
     * @param : $commande => l'objet commande d'origine
     * Return l'objet formate
     */
    public function returnCommandeFormate($commande){
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
    			"etat"=>"créé",
    			"id"=>$commande->id,
    			"token"=>$commande->token
    	];

    	return $commandeFormate;
    }
  }
