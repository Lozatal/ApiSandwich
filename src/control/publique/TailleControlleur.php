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
      $json=writer::jsonFormatRessource("taille",taille::find($args['id']),null);
      $resp=$resp->withHeader('Content-Type','application/json');
      $resp->getBody()->write($json);
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
      $returnPag=pagination::page($tailles,$size,$page,$total);
      $tailles=$returnPag["request"]->get();

      $tab = writer::addLink($tailles, 'tailles', 'tailleID');
      $json = writer::jsonFormatCollection("tailles",$tab,$total,$size,$returnPag["page"]);

      $resp=$resp->withHeader('Content-Type','application/json');
      $resp->getBody()->write($json);
      return $resp;
    }
  }
