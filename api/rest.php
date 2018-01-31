<?php
  require_once __DIR__ . '/../src/vendor/autoload.php';
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;
  use \DavidePastore\Slim\Validation\Validation as Validation;
  use \Respect\Validation\Validator as Validator;
  use illuminate\database\Eloquent\ModelNotFoundException as ModelNotFoundException;

  /* Appel des contrôleurs */


  use \lbs\control\publique\CategorieControlleur as Categorie;
  use \lbs\control\publique\SandwichControlleur as Sandwich;
  use \lbs\control\publique\TailleControlleur as Taille;
  use \lbs\control\publique\CommandeControlleur as Commande;
  use \lbs\control\publique\CarteControlleur as Carte;
  use \lbs\control\publique\ItemControlleur as Item;
  
  /* Appel des modèles */

  use \lbs\model\Commande as ModelCommande;

  /* Appel des utilitaires */

  use \lbs\utils\Writer as writer;


  $config=parse_ini_file("../src/config/lbs.db.conf.ini");
  $db = new Illuminate\Database\Capsule\Manager();
  $db->addConnection($config);
  $db->setAsGlobal();
  $db->bootEloquent();

  //Création et configuration du container
  $configuration=[
    'settings'=>[
      'displayErrorDetails'=>true,
      'production' => false
    ]
  ];

  $errors = require_once __DIR__ . '/../src/config/api_errors.php';

  $c=new \Slim\Container(array_merge( $configuration, $errors) );
  $app=new \Slim\App($c);
  $c = $app->getContainer();

  //Initialisation du conteneur pour le writer
  new writer($c);

  //Application

  function checkToken(Request $rq, Response $rs, callable $next){
    // récupérer l'identifiant de commde dans la route et le token
    $id = $rq->getAttribute('route')->getArgument( 'id');
    $token = $rq->getQueryParam('token', null);
    // vérifier que le token correspond à la commande
    try
    {
        ModelCommande::where('id', '=', $id)->where('token', '=',$token)->firstOrFail();
    } catch (ModelNotFoundException $e) {
        $rs= $rs->withStatus(404);
        $temp = array("type" => "error", "error" => '404', "message" => "Le token n'est pas valide");
        $rs->getBody()->write(json_encode($temp));
        return $rs;
    };
    return $next($rq, $rs);
  };
  
  function afficheError(Response $resp, $location, $errors){
  	$resp=$resp->withHeader('Content-Type','application/json')
  	->withStatus(400)
  	->withHeader('Location', $location);
  	$resp->getBody()->write(json_encode($errors));
  	return $resp;
  }

  //Categorie

  $app->get('/categories[/]',
    function(Request $req, Response $resp, $args){
      $ctrl=new Categorie($this);
      return $ctrl->getCatalogue($req,$resp,$args);
    }
  )->setName("categories");

  $app->get('/categories/{id}',
    function(Request $req, Response $resp, $args){
      $ctrl=new Categorie($this);
      return $ctrl->getCatalogueId($req,$resp,$args);
    }
  )->setName("categoriesID");

  $app->put('/categories/{id}',
  		function(Request $req, Response $resp, $args){
  			$ctrl=new Categorie($this);
  			return $ctrl->updateCategorieId($req,$resp,$args);
  		}
  )->setName("categoriesUpdateID");

  $app->post('/categories[/]',
    function(Request $req, Response $resp, $args){
      $ctrl=new Categorie($this);
      return $ctrl->createCategorie($req,$resp,$args);
    }
  )->setName('createCategorie');

  $app->get('/categories/{id}/sandwichsCategorie',
      function(Request $req, Response $resp, $args){
        $ctrl=new Categorie($this);
        return $ctrl->getSandwichsByCategorie($req,$resp,$args);
      }
  )->setName('sandwichsByCategorie');

  //Sandwichs

  $app->get('/sandwichs[/]',
    function(Request $req, Response $resp, $args){
      $ctrl=new Sandwich($this);
      return $ctrl->getSandwichs($req,$resp,$args);
    }
  )->setName('sandwichsListe');

  $app->get('/sandwichs/{id}',
    function(Request $req, Response $resp, $args){
      $ctrl=new Sandwich($this);
      return $ctrl->getSandwichsId($req,$resp,$args);
    }
  )->setName('sandwichsLink');

  $app->get('/sandwichs/{id}/tailles',
    function(Request $req, Response $resp, $args){
      $ctrl=new Sandwich($this);
      return $ctrl->getTailleBySandwich($req, $resp, $args);
    }
  )->setName('taillesBySandwich');

  $app->get('/sandwichs/{id}/categories',
    function(Request $req, Response $resp, $args){
      $ctrl=new Sandwich($this);
      return $ctrl->getCategoriesBySandwich($req,$resp,$args);
    }
  )->setName('categoriesBySandwich');

  //Tailles

  $app->get('/tailles/{id}',
    function(Request $req, Response $resp, $args){
      $ctrl=new Taille($this);
      return $ctrl->getTailleId($req,$resp,$args);
    }
  )->setName("tailleID");

  $app->get('/tailles[/]',
    function(Request $req, Response $resp, $args){
      $ctrl=new Taille($this);
      return $ctrl->getTailles($req,$resp,$args);
    }
  )->setName("taille");

  //Commande

  $app->get('/commandes/{id}',
  		function(Request $req, Response $resp, $args){
  			$ctrl=new Commande($this);
  			return $ctrl->getCommande($resp,$args);
  		}
  )->setName('commandeToken')->add('checkToken');
  
  $validators= [
  		'nom' => Validator::StringType()->alpha(),
  		'prenom' => Validator::optional(Validator::StringType()->alpha()),
  		'mail' => Validator::email(),
  		'livraison' => [
  			'date' => Validator::date('d-m-Y'),
  			'heure' => Validator::date('H:i')
  		],

  ];

  $app->post('/commandes[/]',
  		function(Request $req, Response $resp, $args){
  			if($req->getAttribute('has_errors')){
  				$errors = $req->getAttribute('errors');
  				return afficheError($resp, '/commandes/nouvelle', $errors);
  			}else{
	  			$ctrl=new Commande($this);
	  			return $ctrl->createCommande($req,$resp,$args);
  			}
  		}
  )->setName('createCommande')->add(new Validation($validators));
  
  $validators= [
  		'date' => Validator::date('d-m-Y'),
  		'heure' => Validator::date('H:i')
  ];
  
  $app->put('/commandes/{id}[/]',
  		function(Request $req, Response $resp, $args){
  			if($req->getAttribute('has_errors')){
  				$errors = $req->getAttribute('errors');
  				return afficheError($resp, '/commandes/update', $errors);
  			}else{
  				$ctrl=new Commande($this);
  				return $ctrl->updateCommande($req,$resp,$args);
  			}
  		}
  		)->setName('updateCommande')->add(new Validation($validators))->add('checkToken');
  		
  	$app->get('/commandes/{id}/facture[/]',
  		function(Request $req, Response $resp, $args){
  			$ctrl=new Commande($this);
  			return $ctrl->getFactureCommande($resp,$args);
  		}
  	)->setName('factureCommande')->add('checkToken');

  //Item
  
  $validators= [
  		'tai_id' => Validator::numeric(),
  		'sand_id' => Validator::numeric(),
  		'quantite' => Validator::optional(Validator::numeric())
  ];

  $app->post('/commandes/{id}/sandwichs[/]',
  		function(Request $req, Response $resp, $args){
  			if($req->getAttribute('has_errors')){
  				$errors = $req->getAttribute('errors');
  				return afficheError($resp, '/items/nouvelle', $errors);
  			}else{
  				$ctrl=new Item($this);
  				return $ctrl->createItem($req,$resp,$args);
  			}
  		}
  )->setName('createItem')->add(new Validation($validators))->add('checkToken');;
  
  $app->delete('/commandes/{id}/sandwichs/{id_sand}[/]',
  		function(Request $req, Response $resp, $args){
  			$ctrl=new Item($this);
  			return $ctrl->deleteItem($resp,$args);
  		}
  		)->setName('deleteItem')->add('checkToken');
  		
  $validators= [
  		'tai_id' => Validator::numeric(),
  		'sand_id' => Validator::numeric(),
  		'quantite' => Validator::optional(Validator::numeric())
  ];
  		
  $app->put('/commandes/{id}/sandwichs/{id_sand}[/]',
  		function(Request $req, Response $resp, $args){
  			if($req->getAttribute('has_errors')){
  				$errors = $req->getAttribute('errors');
  				return afficheError($resp, '/items/update', $errors);
  			}else{
  				$ctrl=new Item($this);
  				return $ctrl->updateItem($req,$resp,$args);
  			}
  		}
  )->setName('updateItem')->add(new Validation($validators))->add('checkToken');
  				

  //Carte de fidélité

  $app->post('/carte/{id}/auth[/]',
  		function(Request $req, Response $resp, $args){
  			$ctrl=new Carte($this);
  			return $ctrl->authenticate($req,$resp,$args);
  		}
  )->setName('authenticate'); //Avec token JWT

  $app->get('/carte/{id}[/]',
      function(Request $req, Response $resp, $args){
        $ctrl=new Carte($this);
        return $ctrl->getCarte($req,$resp,$args);
      }
  )->setName('getCarte');

  $app->post('/carte/{id}/commande/{id_commande}',
      function(Request $req, Response $resp, $args){
        $ctrl=new Carte($this);
        return $ctrl->payerCommande($req,$resp,$args);
      }
  )->setName('payerCommande');

  $app->post('/newcarte[/]',
      function(Request $req, Response $resp, $args){
        $ctrl=new Carte($this);
        return $ctrl->createCarte($req,$resp,$args);
      }
  )->setName('createCarte');

  
  $app->run();
?>
