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
        $tab[$i]['id']=$categorie['id'];
        $tab[$i]['sandwichs']=$sandwich;
        foreach($tab[$i]['sandwichs'] as $sand){
          $sand['modifier']=$this->conteneur->get('router')->pathFor('sandwichModifierGet',['id'=>$sand['id']]);
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
    * Retourne la page de modification d'un sandwich
    * @param : Request $req, Response $resp, array $args[]
    * Return Response $resp contenant la page complète
    */
    public function modifierSandwichGet(Request $req,Response $resp,array $args){
      $id=$args['id'];
      $sandwich=sandwich::find($id);
      $categories=categorie::get();
      $tailles=$sandwich->tailles()->get();
      $i=1;
      foreach($tailles as $taille){
        $taille['prix']=$sandwich->tailles()->find($i)->pivot->prix;
        $i++;
      }

      $categories_sandwich=$sandwich->categories()->get();
      foreach($categories_sandwich as $categorie){
        $sand_cat[]=$categorie->nom;
      }

      /* Lien pour ajouter un modifier */

      $modifier=$this->conteneur->get('router')->pathFor('sandwichModifierPost',['id'=>$id]);

      return $this->conteneur->view->render($resp,'SandwichBackendModification.twig',['sandwich'=>$sandwich,
                                                                                      'categories'=>$categories,
                                                                                      'tailles'=>$tailles,
                                                                                      'sand_cat'=>$sand_cat,
                                                                                      'modifier'=>$modifier
                                                                                    ]);
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
        $sandwich->categories()->detach($sandwich->categories()->get());
        $sandwich->tailles()->detach($sandwich->tailles()->get());
    		$sandwich->delete();
        $resp=$resp->withStatus(200);
    		$resp->getBody()->write('Delete Complete');
    	}
    	else{
    		$resp=$resp->withStatus(404);
    		$resp->getBody()->write('not found');
    	}

      $redirect=$this->conteneur->get('router')->pathFor('sandwichsListe');
      $resp=$resp->withStatus(301)->withHeader('Location', $redirect);

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
      //Création du sandwich
      $sandwich->nom=filter_var($postVar['nom'],FILTER_SANITIZE_STRING);
      $sandwich->description=filter_var($postVar['description'],FILTER_SANITIZE_STRING);
      $sandwich->type_pain=filter_var($postVar['type_pain'],FILTER_SANITIZE_STRING);
      $sandwich->save();
      //Ajout dans Catégories
      $categories=categorie::get();
      foreach($categories as $categorie){
        if(isset($postVar[$categorie->nom])){
          $sandwich->categories()->attach([$postVar[$categorie->nom]]);
        }
      }
      //Ajout dans tailles
      $tailles=taille::get();
      foreach($tailles as $taille){
        if(isset($postVar[$taille->id])){
          $sandwich->tailles()->attach([$taille->id=>['prix'=>$postVar[$taille->id]]]);
        }
      }

      $redirect=$this->conteneur->get('router')->pathFor('sandwichsListe');
      $resp=$resp->withStatus(301)->withHeader('Location', $redirect);

      return $resp;
    }

    /*
    * Modifie un sandwich via son ID
    * @param : Request $req, Response $resp, array $args[]
    * Return Response $resp contenant la page complète
    */
    public function modifierSandwichPost(Request $req,Response $resp,array $args){
      $id=$args['id'];

    	$postVar=$req->getParsedBody();

    	$sandwich = sandwich::find($id);
    	if($sandwich){
    		if (!is_null($postVar['nom']) && !is_null($postVar['description'])&& !is_null($postVar['type_pain'])){
          $sandwich->nom=filter_var($postVar['nom'],FILTER_SANITIZE_STRING);
          $sandwich->description=filter_var($postVar['description'],FILTER_SANITIZE_STRING);
          $sandwich->type_pain=filter_var($postVar['type_pain'],FILTER_SANITIZE_STRING);
          $sandwich->save();
          //Ajout dans Catégories
          $categories=categorie::get();
          $sandwich->categories()->detach($sandwich->categories()->get());
          foreach($categories as $categorie){
            if(isset($postVar[$categorie->nom])){
              $sandwich->categories()->attach([$postVar[$categorie->nom]]);
            }
          }
          //Ajout dans tailles
          $tailles=taille::get();
          $sandwich->tailles()->detach($sandwich->tailles()->get());
          foreach($tailles as $taille){
            if(isset($postVar[$taille->id])){
              $sandwich->tailles()->attach([$taille->id=>['prix'=>$postVar[$taille->id]]]);
            }
          }

          $redirect=$this->conteneur->get('router')->pathFor('sandwichsListe');
          $resp=$resp->withStatus(301)->withHeader('Location', $redirect);
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
