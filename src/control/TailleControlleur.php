<?php

  namespace lbs\control;

  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;
  use lbs\model\Categorie as categorie;
  use lbs\model\Sandwich as sandwich;
  use lbs\model\Commande as commande;
  use lbs\model\Taille as taille;
  use illuminate\database\Eloquent\ModelNotFoundException as ModelNotFoundException;

  class TailleControlleur{
    public $conteneur=null;
    public function __construct($conteneur){
      $this->conteneur=$conteneur;
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
      $json = $this->jsonFormat("tailles",$tab,"collection",$total,$size,$returnPag["page"]);

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
