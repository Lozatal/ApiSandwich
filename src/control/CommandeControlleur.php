<?php

  namespace lbs\control;

  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;
  use lbs\model\Categorie as categorie;
  use lbs\model\Sandwich as sandwich;
  use lbs\model\Commande as commande;
  use lbs\model\Taille as taille;
  use illuminate\database\Eloquent\ModelNotFoundException as ModelNotFoundException;

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
    	$commande->nom=filter_var($postVar['nom'],FILTER_SANITIZE_STRING);
    	$commande->mail=filter_var($postVar['mail'],FILTER_SANITIZE_STRING);
    	$commande->livraison=filter_var($postVar['livraison'],FILTER_SANITIZE_STRING);

    	//A MODIFIER, on vérifie pas si le random n'est pas déja utilisé
    	$commande->token=str_random(5);

    	$commande->save();
    	$resp=$resp->withHeader('Content-Type','application/json')
    	->withStatus(201)
    	->withHeader('Location', '/commandes/nouvelle');
    	$resp->getBody()->write('created');
    	return $resp;
    }

    /*
     * Retourne la commande
     * @param : array $args[], Response $resp
     * Return Response $resp contenant la page complète
     */
    public function getCommandeToken(Response $resp, array $args){
    	$token=$args['token'];
    	$resp=$resp->withHeader('Content-Type','application/json');
    	$commande = json_encode(commande::where('token', '=', $token)->firstOrFail());
    	$resp->getBody()->write(json_encode($commande));
    	return $resp;
    }
  }
