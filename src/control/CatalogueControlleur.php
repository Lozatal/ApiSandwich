<?php

  namespace lbs\control;

  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;
  use lbs\model\Categorie as categorie;
  use lbs\model\Sandwich as sandwich;

  class CatalogueControlleur{
    public $conteneur=null;
    public function __construct($conteneur){
      $this->conteneur=$conteneur;
    }

    public function getCatalogue($resp){
      $resp=$resp->withHeader('Content-Type','application/json');
      $listeCategorie = json_encode(categorie::get());
      $resp->getBody()->write($listeCategorie);
      return $resp;
    }

    public function getCatalogueId($args,$resp){
      $id=$args['name'];
      $resp=$resp->withHeader('Content-Type','application/json');
      $categorie = json_encode(categorie::find($id));
      $resp->getBody()->write($categorie);
      return $resp;
    }

    public function createCategorie(Request $req, Response $rs, array $args){
      $postVar=$req->getParsedBody();
      $categorie = new categorie();
      $categorie->nom=filter_var($postVar['nom'],FILTER_SANITIZE_STRING);
      $categorie->description=filter_var($postVar['description'],FILTER_SANITIZE_STRING);
      $categorie->save();
      $rs=$rs->withHeader('Content-Type','application/json')
            ->withStatus(201)
            ->withHeader('Location', '/categories/nouvelle');
      $rs->getBody()->write('created');
      return $rs;
    }

    public function updateCategorieId(Request $req, Response $rs, array $args){
    	$id=$args['id'];

    	$postVar=$req->getParsedBody();

    	$categorie = categorie::find($id);
    	if($categorie){
    		if (!is_null($postVar['nom']) && !is_null($postVar['description'])){
		    	$categorie->nom = filter_var($postVar['nom'],FILTER_SANITIZE_STRING);
		    	$categorie->description= filter_var($postVar['description'],FILTER_SANITIZE_STRING);
		    	$categorie->save();

		    	$rs=$rs->withHeader('Content-Type','application/json')
		    	->withStatus(200)
		    	->withHeader('Location', '/categories/update');
		    	$rs->getBody()->write($categorie);
    		}
    		else{
    			$rs=$rs->withStatus(400);
    			$rs->getBody()->write('Bad request');
    		}
    	}
    	else{
    		$rs=$rs->withStatus(404);
    		$rs->getBody()->write('not found');
    	}
    	return $rs;
    }

    public function getSandwichs($req,$resp,$args){

      $type = $req->getQueryParam('type',null);
      $img = $req->getQueryParam('img',null);
      $size = $req->getQueryParam('size',10);
      $page = $req->getQueryParam('page',1);
      $skip = $size*($page-1);


      $q = sandwich::select('id','nom','type_pain');

      if(!is_null($type)){
        $q=$q->where('type_pain','LIKE','%'.$req->getQueryParam('type').'%');
      }
      if(!is_null($img)){
        $q=$q->where('img','LIKE','%'.$req->getQueryParam('img').'%');
      }

      $q=$q->skip($skip)->take($size);

      $listeSandwichs = $q->get();
      $resp=$resp->withHeader('Content-Type','application/json');
      for($i=0;$i<sizeof($listeSandwichs);$i++){
        $sandwichs[$i]["sandwich"]=$listeSandwichs[$i];
        $href["href"]=$this->conteneur->get('router')->pathFor('sandwichsLink', ['id'=>$listeSandwichs[$i]['id']]);
        $tab["self"]=$href;
        $sandwichs[$i]["links"]=$tab;
      }
      $resp->getBody()->write(json_encode($sandwichs));
      return $resp;
    }

    public function getSandwichsId($args,$resp){
      $id=$args['id'];
      $resp=$resp->withHeader('Content-Type','application/json');
      $categorie = json_encode(sandwich::find($id));
      $resp->getBody()->write($categorie);
      return $resp;
    }
  }
