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
    * Retourne la liste des Sandwichs par categories
    * @param : Request $req, Response $resp, array $args[]
    * Return Response $resp contenant la page complète
    */
    public function getSandwichs(Request $req,Response $resp,array $args){

      /* Creation tableau de categorie avec sandwichs */
      $categories=categorie::select("*")->get();
      $sandwichs=[];
      $i=0;
      foreach($categories as $categorie){
        $sandwich=$categorie->sandwichs()->select('id','nom','type_pain')->get();

        $tab[$i]['nom']=$categorie['nom'];
        $tab[$i]['sandwichs']=$sandwich;
        foreach($tab[$i]['sandwichs'] as $sand){
          $sand['modifier']=$this->conteneur->get('router')->pathFor('sandwichModifier',['id'=>$sand['id']]);
          $sand['supprimer']=$this->conteneur->get('router')->pathFor('sandwichDelete',['id'=>$sand['id']]);
        }
        $i++;
      }

      /* Récupération liste des tailles_Sandwichs */

      $tailles=taille::get();

      /* Lien pour ajouter un sandwich */

      $ajouter=$this->conteneur->get('router')->pathFor('sandwichAjouter');

      return $this->conteneur->view->render($resp,'SandwichBackend.twig',['tab'=>$tab,'ajouter'=>$ajouter,'tailles'=>$tailles]);
    }

    /*
    * Supprime un sandwich par son ID
    * @param : Request $req, Response $resp, array $args[]
    * Return Response $resp contenant la page complète
    */
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

    /*
    * Ajoute un sandwich
    * @param : Request $req, Response $resp, array $args[]
    * Return Response $resp contenant la page complète
    */
    public function ajouterSandwich(Request $req,Response $resp,array $args){
      $postVar=$req->getParsedBody();
      $sandwich = new sandwich();
      $sandwich->nom=filter_var($postVar['nom'],FILTER_SANITIZE_STRING);
      $sandwich->description=filter_var($postVar['description'],FILTER_SANITIZE_STRING);
      $sandwich->type_pain=filter_var($postVar['type_pain'],FILTER_SANITIZE_STRING);
      $sandwich->save();

      $redirect=$this->conteneur->get('router')->pathFor('sandwichsListe');
      $resp=$resp->withStatus(301)->withHeader('Location', $redirect);

      return $resp;
    }

    /*
    * Modifie un sandwich via son ID
    * @param : Request $req, Response $resp, array $args[]
    * Return Response $resp contenant la page complète
    */
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
