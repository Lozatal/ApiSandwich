<?php

  namespace lbs\control;

  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;
  use lbs\model\Categorie as categorie;
  use lbs\model\Sandwich as sandwich;
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
      $returnPag=$this->pagination($categories,$size,$page,$total);
      $categories=$returnPag["request"]->get();

      $tab = $this->addLink($categories, 'categories', 'categoriesID');
      $json = $this->jsonFormat("categories",$tab,$total,$size,$returnPag["page"]);

      $resp=$resp->withHeader('Content-Type','application/json');
      $resp->getBody()->write($json);
      return $resp;
    }

    /*
    * Retourne les catégories classés par id
    * @param : array $args[], Response $resp
    * Return Response $resp contenant la page complète
    */
    public function getCatalogueId(Request $req, Response $resp, array $args){
      $id=$args['id'];
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
    * Retourne une taille via son ID
    * @param : Request $req, Response $resp, array $args[]
    */
    public function getTailleId(Request $req,Response $resp,array $args){
      $id=$args['id'];
      $resp=$resp->withHeader('Content-Type','application/json');
      $taille = json_encode(taille::find($id));
      $resp->getBody()->write($taille);
      return $resp;
    }

    /*
    * Retourne la liste des tailles
    * @param : Request $req, Response $resp, array $args[]
    */
    public function getTailles(Request $req,Response $resp,array $args){
      $size = $req->getQueryParam('size',10);
      $page = $req->getQueryParam('page',1);

      $tailles=taille::select("*");
      $total = sizeof($tailles->get());
      $returnPag=$this->pagination($tailles,$size,$page,$total);
      $tailles=$returnPag["request"]->get();

      $tab = $this->addLink($tailles, 'tailles', 'tailleID');
      $json = $this->jsonFormat("tailles",$tab,$total,$size,$returnPag["page"]);

      $resp=$resp->withHeader('Content-Type','application/json');
      $resp->getBody()->write($json);
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
      $total = sizeof($q->get());

      $returnPag=$this->pagination($q,$size,$page,$total);
      $listeSandwichs = $returnPag["request"]->get();

      $tab = $this->addLink($listeSandwichs, 'sandwichs', 'sandwichsLink');
      $json = $this->jsonFormat("sandwichs",$tab,$total,$size,$returnPag["page"]);

      $resp=$resp->withHeader('Content-Type','application/json');
      $resp->getBody()->write($json);
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
      $item=sandwich::find($id);
      $belongsToMany=$item->tailles;

      $tab = $this->addLink($belongsToMany, 'tailles', 'tailleID');
      $json = $this->jsonFormat("tailles",$tab);

      $resp=$resp->withHeader('Content-Type','application/json');
      $resp->getBody()->write($json);
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

	    	$listeSandwichs = $this->addLink($listeSandwichs, 'sandwichs', 'sandwichsLink');
	    	$resp->getBody()->write(json_encode($listeSandwichs));
	    }
    	return $resp;
    }

    /*
    * Retourne la liste des catégories du sandwichs
    * @param : Request $req, Response $resp, array $args[]
    * Return$sandwichCategories
    */
    public function getCategoriesBySandwich(Request $req,Response $resp,array $args){
      $id=$args['id'];
      $item=sandwich::find($id);
      $belongsToMany=$item->categories;

      $tab = $this->addLink($belongsToMany, 'categories', 'categoriesID');
      $json = $this->jsonFormat("categories",$tab);

      $resp=$resp->withHeader('Content-Type','application/json');
      $resp->getBody()->write($json);
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
      $tab["request"]=$request;
      $tab["page"]=$page;
      return $tab;
    }

    public function jsonFormat($type, array $tab, $total=null, $size=null, $page=null){
      $tabRendu["type"]="collection";
      if($total!=null){
        $tabRendu["meta"]["count"]=$total;
      }
      if($size!=null){
        $tabRendu["meta"]["items"]=$size;
      }
      if($page!=null){
        $tabRendu["meta"]["page"]=$page;
      }
      $tabRendu[$type]=$tab;
      return json_encode($tabRendu);
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
  }
