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
      $json = writer::jsonFormatCollection("sandwichs",$tab,$total,$size,$returnPag["page"]);

      $resp=$resp->withHeader('Content-Type','application/json');
      $resp->getBody()->write($json);
      return $resp;
    }

    /*
    * Retourne un sandwichs via son id
    * @param : array $args[], Response $resp
    * Return Response $resp contenant la page complète
    */
    public function getSandwichsId(Request $req, Response $resp, array $args){
        $id=$args['id'];
        $resp=$resp->withHeader('Content-Type','application/json');
        
        $sandwich = sandwich::find($id);
        if($sandwich==null){//Si id introuvable
          $json["erreur"]="Id trouvable";
          $resp=$resp->withHeader('Content-Type','application/json')->withStatus(204);
          $resp->getBody()->write(json_encode($json));
        }else{
          $sandwich["categories"]=$sandwich->categories()->select("id","nom")->get();
          $sandwich["tailles"]=$sandwich->tailles()->select("id","nom","prix")->get();

          $link["categories"]=writer::addLinks("categoriesBySandwich",$id);
          $link["tailles"]=writer::addLinks("taillesBySandwich",$id);
          $json=writer::jsonFormatRessource("sandwich",$sandwich,$link);

          $resp=$resp->withHeader('Content-Type','application/json');
          $resp->getBody()->write($json);
        }
        
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

      if($item==null){
          $json["erreur"]="Ce sandwich n'a pas de taille disponibles";
          $resp=$resp->withHeader('Content-Type','application/json')->withStatus(204);
          $resp->getBody()->write(json_encode($json));
        }else{
          $tab = writer::addLink($belongsToMany, 'tailles', 'tailleID');
          $json = writer::jsonFormatCollection("tailles",$tab);
          $resp=$resp->withHeader('Content-Type','application/json');
          $resp->getBody()->write($json);
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

      $tab = writer::addLink($belongsToMany, 'categories', 'categoriesID');
        $json = writer::jsonFormatCollection("categories",$tab);

        $resp=$resp->withHeader('Content-Type','application/json');
        $resp->getBody()->write($json);

      
      return $resp;
    }
  }
