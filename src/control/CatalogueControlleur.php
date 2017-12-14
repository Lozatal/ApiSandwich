<?php

  namespace lbs\control;

  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;
  use lbs\model\Categorie as categorie;
  use lbs\model\Sandwich as sandwich;
  use lbs\model\Commande as commande;
  use illuminate\database\Eloquent\ModelNotFoundException as ModelNotFoundException;
  use lbs\model\Taille as taille;

  class CatalogueControlleur{
    public $conteneur=null;
    public function __construct($conteneur){
      $this->conteneur=$conteneur;
    }

    /*
    * Retourne l'intégralité des catégories de sandwichs
    * @param : Response $resp
    * Return Response $resp contenant la page complète
    */
    public function getCatalogue(Request $req, Response $resp, array $args){
      $size = $req->getQueryParam('size',10);
      $page = $req->getQueryParam('page',1);

      $categories=categorie::select("*");
      $categoriesTotal=$categories->get();
      $total = sizeof($categoriesTotal);
      $categories=$this->pagination($categories,$size,$page,$total);
      $categories=$categories->get();

      $i=0;
      foreach($categories as $categorie){
        $tabCategorie[$i]=$categorie;
        $href["href"]=$this->conteneur->get('router')->pathFor('categoriesID', ['name'=>$categorie['id']]);
        $tab["self"]=$href;
        $tabCategorie[$i]["links"]=$tab;
        $i++;
      }

      $resp=$resp->withHeader('Content-Type','application/json');
      $tabRendu["type"]="collection";
      $tabRendu["meta"]["count"]=$total;
      $tabRendu["meta"]["items"]=$size;
      $tabRendu["meta"]["page"]=$page;
      $tabRendu["categories"]=$tabCategorie;
      $resp->getBody()->write(json_encode($tabRendu));
      return $resp;
    }

    /*
    * Retourne les catégories classés par id
    * @param : array $args[], Response $resp
    * Return Response $resp contenant la page complète
    */
    public function getCatalogueId(Request $req, Response $resp, array $args){
      $id=$args['name'];
      $resp=$resp->withHeader('Content-Type','application/json');
      $categorie = json_encode(categorie::find($id));
      $resp->getBody()->write($categorie);
      return $resp;
    }

    /*
    * Créée via une requête POST une nouvelle catégorie
    * @param : Request $req, Response $resp, array $args[]
    * Return Response $resp contenant la page complète
    */
    public function createCategorie(Request $req, Response $resp, array $args){
      $postVar=$req->getParsedBody();
      $categorie = new categorie();
      $categorie->nom=filter_var($postVar['nom'],FILTER_SANITIZE_STRING);
      $categorie->description=filter_var($postVar['description'],FILTER_SANITIZE_STRING);
      $categorie->save();
      $resp=$resp->withHeader('Content-Type','application/json')
            ->withStatus(201)
            ->withHeader('Location', '/categories/nouvelle');
      $resp->getBody()->write('created');
      return $resp;
    }

    /*
    * Met à jour une catégorie via une requête PUT
    * @param : Request $req, Response $resp, array $args[]
    * Return Response $resp contenant la page complète
    */
    public function updateCategorieId(Request $req, Response $resp, array $args){
    	$id=$args['id'];

    	$postVar=$req->getParsedBody();

    	$categorie = categorie::find($id);
    	if($categorie){
    		if (!is_null($postVar['nom']) && !is_null($postVar['description'])){
		    	$categorie->nom = filter_var($postVar['nom'],FILTER_SANITIZE_STRING);
		    	$categorie->description= filter_var($postVar['description'],FILTER_SANITIZE_STRING);
		    	$categorie->save();

		    	$resp=$resp->withHeader('Content-Type','application/json')
		    	->withStatus(200)
		    	->withHeader('Location', '/categories/update');
		    	$resp->getBody()->write($categorie);
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

    /*
    * Retourne la liste des categories
    *
    */
    public function getTailleId(array $args, Response $resp){
      $id=$args['name'];
      $resp=$resp->withHeader('Content-Type','application/json');
      $categorie = json_encode(taille::find($id));
      $resp->getBody()->write($categorie);
      return $resp;
    }

    /*
    * Retourne la liste des Sandwichs, avec filtre et pagination
    * @param : Request $req, Response $resp, array $args[]
    * Return Response $resp contenant la page complète
    */
    public function getSandwichs(Request $req,Response $resp,array $args){

      $type = $req->getQueryParam('type',null);
      $img = $req->getQueryParam('img',null);
      $size = $req->getQueryParam('size',10);
      $page = $req->getQueryParam('page',1);

      $q = sandwich::select('id','nom','type_pain');

      if(!is_null($type)){
        $q=$q->where('type_pain','LIKE','%'.$req->getQueryParam('type').'%');
      }
      if(!is_null($img)){
        $q=$q->where('img','LIKE','%'.$req->getQueryParam('img').'%');
      }

      //Récupération du total d'élement de la recherche
      $requeteComplete = $q->get();
      $total = sizeof($requeteComplete);

      $q=$this->pagination($q,$size,$page,$total);
      $listeSandwichs = $q->get();

      //Construction de la réponse
      $resp=$resp->withHeader('Content-Type','application/json');
      for($i=0;$i<sizeof($listeSandwichs);$i++){
        $sandwichs[$i]["sandwich"]=$listeSandwichs[$i];
        $href["href"]=$this->conteneur->get('router')->pathFor('sandwichsLink', ['id'=>$listeSandwichs[$i]['id']]);
        $tab["self"]=$href;
        $sandwichs[$i]["links"]=$tab;
      }
      $tabRendu["type"]="collection";
      $tabRendu["meta"]["count"]=$total;
      $tabRendu["meta"]["items"]=$size;
      $tabRendu["meta"]["page"]=$page;
      $tabRendu["sandwichs"]=$sandwichs;
      $resp->getBody()->write(json_encode($tabRendu));
      return $resp;
    }

    /*$sandwichCategories
    * Retourne un sandwichs via son id
    * @param : array $args[], Response $resp
    * Return Response $resp contenant la page complète
    */
    public function getSandwichsId(Request $req, Response $resp, array $args){
      $id=$args['id'];
      $resp=$resp->withHeader('Content-Type','application/json');
      $categorie = json_encode(sandwich::find($id));
      $resp->getBody()->write($categorie);
      return $resp;
    }

    /*
    * Retourne la liste des tailles de sandwichs disponibles pour 1 sandwichs
    *
    */
    public function getTailleBySandwich(Request $req, Response $resp, array $args){
      $id=$args['id'];
      $sandwich=sandwich::find($id);
      $tailles=$sandwich->tailles;
      $i=0;
      foreach($tailles as $taille){
        unset($taille['pivot']);
        $tabTaille[$i]=$taille;
        $href["href"]=$this->conteneur->get('router')->pathFor('tailleID', ['name'=>$taille['id']]);
        $tab["self"]=$href;
        $tabTaille[$i]["links"]=$tab;
        $i++;
      }
      $resp=$resp->withHeader('Content-Type','application/json');
      $listeTaille = json_encode($tabTaille);
      $resp->getBody()->write($listeTaille);
      return $resp;
    }   

    /*
     * Retourne les sandwitchs d'une categorie
     * @param : array $args[], Response $resp
     * Return Response $resp contenant la page complète
     */
    public function getSandwichsByCategorie(Request $req, Response $resp, array $args){
    	$idCateg=$args['id'];
    	$resp=$resp->withHeader('Content-Type','application/json');
    	
    	try{
    		$categorie = categorie::findOrFail($idCateg);
	    } catch (ModelNotFoundException $ex) {
	    	$resp=$resp->withStatus(404);
	    	$resp->getBody()->write(json_encode('Not found'));

	    	return $resp;
	    	
	    	//return Writer::json_error($resp, code:404, message:'ressource non disponible :'. $this->c->get('router')->pathFor('sandwich', ['id'=>$args['id']]));
	    }
	    
	    if($categorie != null){
	    	
	    	$listeSandwichs = $categorie->sandwichs()
	    					->select('id','nom','type_pain')
	    					->get();
	    	
	    	$listeSandwichs = $this->addLink($listeSandwichs, 'sandwich', 'sandwichsLink');
	    	$resp->getBody()->write(json_encode($listeSandwichs));
	    }	
    	return $resp;
    }

    /*
     * Ajoute les links aux objets
     * @param : $listeSandwich : collection d'objet
     * @param : $nameObject : nom de l'objet
     * @param : $pathFor : nom/alias de la route dans le fichier rest.php
     * Return la liste des sandwichs modifiés
     */
    protected function addLink($listeSandwichs, $nameObject, $pathFor){
    	
    	for($i=0;$i<sizeof($listeSandwichs);$i++){
    		$sandwichs[$i][$nameObject]=$listeSandwichs[$i];
    		$href["href"]=$this->conteneur->get('router')->pathFor($pathFor, ['id'=>$listeSandwichs[$i]['id']]);
    		$tab["self"]=$href;
    		$sandwichs[$i]["links"]=$tab;
    	}

    	return $sandwichs;
    }

    /*
    * Retourne la liste des catégories du sandwichs
    * @param : Request $req, Response $resp, array $args[]
    * Return$sandwichCategories
    */
    public function getCategoriesBySandwich(Request $req,Response $resp,array $args){
      $id=$args['id'];
      $sandwich=sandwich::find($id);
      $categories=$sandwich->categories;
      foreach($categories as $categorie){
        $tabCategorie[]=$categorie;
        $href["href"]=$this->conteneur->get('router')->pathFor('categoriesID', ['name'=>$categorie['id']]);
        $tab["self"]=$href;
        $tabCategorie[]=["links"=>$tab];
      }
      $resp=$resp->withHeader('Content-Type','application/json');
      $listeCategorie = json_encode($tabCategorie);
      $resp->getBody()->write($listeCategorie);
      return $resp;
    }

    /*
    * Retourne la requête avec pagination
    * @param : requete, int taille, int page, int tailleTotale
    */
    public function pagination($request, $taille, $page, $tailleTotale){
      $skip = $taille*($page-1);
      $totalItem = $taille + $skip;
      if($totalItem>$tailleTotale){
        if(is_float($tailleTotale/$taille)){
          $page=floor(($tailleTotale/$taille))+1;
        }else{
          $page=floor(($tailleTotale/$taille));
        }
      }
      if($page<=0){
          $page=1;
      }
      $skip = $taille*($page-1);
      $request=$request->skip($skip)->take($taille);
      return $request;
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
  }
