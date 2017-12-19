<?php

  namespace lbs\control;

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

      $returnPag=pagination::page($q,$size,$page,$total);
      $listeSandwichs = $returnPag["request"]->get();

      $tab = writer::addLink($listeSandwichs, 'sandwichs', 'sandwichsLink');
      $json = writer::jsonFormat("sandwichs",$tab,"collection",$total,$size,$returnPag["page"]);

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
      $sandwich = sandwich::find($id);

      $sandwich["categories"]=$sandwich->categories()->select("id","nom")->get();
      $sandwich["tailles"]=$sandwich->tailles()->select("id","nom","prix")->get();

      $href["href"]=$this->conteneur->get('router')->pathFor("categoriesBySandwich", ['id'=>$id]);
      $links["categories"]=$href;
      $href["href"]=$this->conteneur->get('router')->pathFor("taillesBySandwich", ['id'=>$id]);
      $links["tailles"]=$href;

      $tabRendu["type"]="ressource";
      $tabRendu["sandwich"]=$sandwich;
      $tabRendu["links"]=$links;

      $categorie = json_encode($tabRendu);
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

      $tab = writer::addLink($belongsToMany, 'tailles', 'tailleID');
      $json = writer::jsonFormat("tailles",$tab,"collection");

      $resp=$resp->withHeader('Content-Type','application/json');
      $resp->getBody()->write($json);
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

      $tab = writer::addLink($belongsToMany, 'categories', 'categoriesID');
      $json = writer::jsonFormat("categories",$tab,"collection");

      $resp=$resp->withHeader('Content-Type','application/json');
      $resp->getBody()->write($json);
      return $resp;
    }
  }
