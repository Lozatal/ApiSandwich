<?php
  require_once __DIR__ . '/../src/vendor/autoload.php';
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;
  use \lbs\control\CatalogueControlleur as Catalogue;

  $config=parse_ini_file("../src/config/lbs.db.conf.ini");
  $db = new Illuminate\Database\Capsule\Manager();
  $db->addConnection($config);
  $db->setAsGlobal();
  $db->bootEloquent();

  //Création et configuration du container
  $configuration=[
    'settings'=>[
      'displayErrorDetails'=>true
    ]
  ];

  $errors = require_once __DIR__ . '/../src/config/api_errors.php';

  $c=new \Slim\Container(array_merge( $configuration, $errors) );
  $app=new \Slim\App($c);
  $c = $app->getContainer();

  //Application
  $app->get('/categories[/]',
    function(Request $req, Response $resp, $args){
      $ctrl=new Catalogue($this);
      return $ctrl->getCatalogue($resp);
    }
  )->setName("categories");
  $app->get('/categories/{name}',
    function(Request $req, Response $resp, $args){
      $ctrl=new Catalogue($this);
      return $ctrl->getCatalogueId($args,$resp);
    }
  )->setName("categoriesID");



  $app->put('/categories/{id}',
  		function(Request $req, Response $resp, $args){
  			$ctrl=new Catalogue($this);
  			return $ctrl->updateCategorieId($req,$resp,$args);
  		}
  )->setName("categoriesUpdateID");


  $app->post('/categories[/]',
    function(Request $req, Response $resp, $args){
      $ctrl=new Catalogue($this);
      return $ctrl->createCategorie($req,$resp,$args);
    }
  )->setName('createCategorie');

  $app->get('/sandwichs[/]',
    function(Request $req, Response $resp, $args){
      $ctrl=new Catalogue($this);
      return $ctrl->getSandwichs($req,$resp,$args);
    }
  )->setName('sandwichsListe');

  $app->get('/sandwichs/{id}',
    function(Request $req, Response $resp, $args){
      $ctrl=new Catalogue($this);
      return $ctrl->getSandwichsId($args,$resp);
    }
  )->setName('sandwichsLink');

  $app->get('/sandwichs/{id}/taille[/]',
    function(Request $req, Response $resp, array $args){
      $ctrl=new Catalogue($this);
      return $ctrl->getTailleBySandwich($req, $resp, $args);
    }
  )->setName('sandwichsTaille');


  $app->run();
?>
