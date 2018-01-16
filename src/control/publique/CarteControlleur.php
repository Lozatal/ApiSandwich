<?php

  namespace lbs\control\publique;

  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  use lbs\utils\Writer as Writer;

  use lbs\model\Commande as commande;
  use lbs\model\Carte as carte;

  use illuminate\database\Eloquent\ModelNotFoundException as ModelNotFoundException;

  class CarteControlleur{
    public $conteneur=null;
    public function __construct($conteneur){
      $this->conteneur=$conteneur;
    }

    public function authentification(Request $req, Response $resp, array $args){
      //If requête authentification basique
      if(!$req->hasHeader('Authorization')){
        $resp = $resp->withHeader('WWW-authenticate','Basic realm="lbs api"');
        return Writer::json_error($resp, 401, 'Authorization header present');
      }

      //
      $authstring = base64_decode(explode(" ", $req->getHeader('Authorization')[0]));
      list($user, $pass)=explode(':', $authstring);


      try{
        $carte = Carte::select('id', 'nom' 'password')
                ->where('id', '=', $args['id'])
                ->where('nom', '=', $user)
                ->firstOrFail();
        if(!password_verify($pass, $carte->password))
          throw new AuthException("password check failed");

        unset($carte->password);
      }
      catch(AuthException $e){
        $resp = $resp->withHeader('WWW-authenticate','Basic realm="lbs api"');
        return Writer::json_error($resp, 401, 'carte ou nom de client non reconnu');
      }catch(AuthException $e){
        $resp = $resp->withHeader('WWW-authenticate','Basic realm="lbs api"');
        return Writer::json_error($resp, 401, 'mauvais mot de passe');
      }

      $secret = $this->c->settings['secret'];
      $token = JWT::encode(['iat' => time(),
                            'exp' => time()+3600,
                            'cid' => $carte->id],
                            $secret, 'HS512');
      $data = ['token' => $token];

      return Writer::json_outpout($resp, 200, $data);

    }
  }

  public function getCarte(Request $req, Response $resp, array $args){

  }
