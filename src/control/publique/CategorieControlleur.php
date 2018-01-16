<?php

  namespace lbs\control\publique;

  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  use lbs\model\Categorie as categorie;
  use lbs\model\Sandwich as sandwich;
  use lbs\model\Commande as commande;
  use lbs\model\Taille as taille;

  use lbs\utils\Writer as writer;
  use lbs\utils\Pagination as pagination;

  use illuminate\database\Eloquent\ModelNotFoundException as ModelNotFoundException;

  class CategorieControlleur{
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
      $returnPag=pagination::page($categories,$size,$page,$total);
      $categories=$returnPag["request"]->get();

      $tab = writer::addLink($categories, 'categories', 'categoriesID');
      $json = writer::jsonFormatCollection("categories",$tab,$total,$size,$returnPag["page"]);

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

      $categorie = categorie::find($id);

      $links["sandwichs"] = writer::addLinks("sandwichsByCategorie",$id);
      $json =writer::jsonFormatRessource("categorie",$categorie,$links);

      $resp=$resp->withHeader('Content-Type','application/json');
      $resp->getBody()->write($json);

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
        $tabRendu["type"]="collection";
        $tabRendu["sandwichs"]=$listeSandwichs;
	    	$resp->getBody()->write(json_encode($tabRendu));
	    }
    	return $resp;
    }
  }
