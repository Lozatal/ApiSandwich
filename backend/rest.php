<?php
  require_once __DIR__ . '/../src/vendor/autoload.php';
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;
  use illuminate\database\Eloquent\ModelNotFoundException as ModelNotFoundException;

  /* Appel et configuration de twig */
  $loader = new Twig_Loader_Filesystem('../src/view/backend');
  $twig = new Twig_Environment($loader, array(
      'cache' => false
  ));

  /* Appel des contrôleurs */

  use \lbs\control\backend\SandwichControlleur as Sandwich;

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
      'production' => false,
      'tmpl_dir' => __DIR__ . '/../src/view/backend'
    ],
    'view'=>function($c){
      return new \Slim\Views\Twig(
        $c['settings']['tmpl_dir'],
        ['debug'=>true]
      );
    }
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

  $app->get('/sandwichs[/]',
    function(Request $req, Response $resp, $args){
      $ctrl=new Sandwich($this);
      return $ctrl->getSandwichs($req,$resp,$args);
    }
  )->setName('sandwichsListe');

  $app->post('/sandwichs/delete/{id}',
    function(Request $req, Response $resp, $args){
      $ctrl=new Sandwich($this);
      return $ctrl->deleteSandwich($req,$resp,$args);
    }
  )->setName('sandwichDelete');

  $app->post('/sandwichs[/]',
    function(Request $req, Response $resp, $args){
      $ctrl=new Sandwich($this);
      return $ctrl->ajouterSandwich($req,$resp,$args);
    }
  )->setName('sandwichAjouter');

  $app->post('/sandwichs/modifier/{id}',
    function(Request $req, Response $resp, $args){
      $ctrl=new Sandwich($this);
      return $ctrl->modifierSandwichPost($req,$resp,$args);
    }
  )->setName('sandwichModifierPost');

  $app->get('/sandwichs/modifier/{id}',
    function(Request $req, Response $resp, $args){
      $ctrl=new Sandwich($this);
      return $ctrl->modifierSandwichGet($req,$resp,$args);
    }
  )->setName('sandwichModifierGet');


  $app->run();
?>
