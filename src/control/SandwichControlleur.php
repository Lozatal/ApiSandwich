<?php

  namespace lbs\control;

  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;
  use lbs\model\Categorie as categorie;
  use lbs\model\Sandwich as sandwich;
  use lbs\model\Commande as commande;
  use lbs\model\Taille as taille;
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

      $returnPag=$this->pagination($q,$size,$page,$total);
      $listeSandwichs = $returnPag["request"]->get();

      $tab = $this->addLink($listeSandwichs, 'sandwichs', 'sandwichsLink');
      $json = $this->jsonFormat("sandwichs",$tab,"collection",$total,$size,$returnPag["page"]);

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

      $tab = $this->addLink($belongsToMany, 'tailles', 'tailleID');
      $json = $this->jsonFormat("tailles",$tab,"collection");

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

      $tab = $this->addLink($belongsToMany, 'categories', 'categoriesID');
      $json = $this->jsonFormat("categories",$tab,"collection");

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

    public function jsonFormat($categorie, array $tab, $type, $total=null, $size=null, $page=null){
      $tabRendu["type"]=$type;
      if($total!=null){
        $tabRendu["meta"]["count"]=$total;
      }
      if($size!=null){
        $tabRendu["meta"]["items"]=$size;
      }
      if($page!=null){
        $tabRendu["meta"]["page"]=$page;
      }
      $tabRendu[$categorie]=$tab;
      return json_encode($tabRendu);
    }

    /*
     * Ajoute les links aux objets
     * @param : $listeSandwich : collection d'objet
     * @param : $nameObject : nom de l'objet
     * @param : $pathFor : nom/alias de la route dans le fichier rest.php
     * Return la liste des sandwichs modifiés
     */
    protected function addLink($tabObjet, $nameObject, $pathFor){
      for($i=0;$i<sizeof($tabObjet);$i++){
        $tabRendu[$i][$nameObject]=$tabObjet[$i];
        $href["href"]=$this->conteneur->get('router')->pathFor($pathFor, ['id'=>$tabObjet[$i]['id']]);
        $tab["self"]=$href;
        $tabRendu[$i]["links"]=$tab;
      }
      return $tabRendu;
    }
  }
