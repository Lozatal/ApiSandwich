<?php

	namespace lbs\api\control;

	use Firebase\JWT\JWT;

	class AuthController {

	private $container;

		public function  __construct(\Slim\Container $c)
		{
			$this->container = $c;
		}

		public function authenticate($req, $resp, $args) {

			$rs= $resp->withHeader( 'Content-type', "application/json;charset=utf-8");

			if(!$req->hasHeader('Authorization')) {

				$rs = $rs->withHeader('WWW-authenticate', 'Basic realm="lbs api" ');
				$resp= $resp->withStatus(401);
				$temp = array("type" => "error", "error" => '401', "message" => "No Authorization in header");

				$rs->getBody()->write(json_encode($temp));

				return $rs;

			}

			$auth = base64_decode( explode( " ", $req->getHeader('Authorization')[0]) [1] );
			list($user, $pass) = explode(':', $auth);

			try {
				$carte = \lbs\common\models\Carte::select('id', 'nom', 'passwd')
					->where('id', '=', $args['id'])
					->where('nom', '=', $user)
					->firstOrFail();

				if(!password_verify($pass, $carte->passwd)) {
					throw new \Exception("Authentification incorrecte");

				}

			} catch(\Exception $e){

				$rs = $rs->withHeader('WWW-authenticate', 'Basic realm="lbs api" ');
				$resp= $resp->withStatus(401);
				$temp = array("type" => "error", "error" => '401', "message" => $e->getMessage());

				$rs->getBody()->write(json_encode($temp));

				return $rs;

			}

			$secret = 'lbs';

			$token = JWT::encode( [ 'iss'=>'http://api.lbs.local/auth',
				'aud'=>'http://api.lbs.local',
				'iat'=>time(), 
				'exp'=>time()+3600,
				'uid' =>  $carte->id], 
				$secret, 'HS512' );

			$resp= $resp->withStatus(201);

			$rs->getBody()->write(json_encode($token));

			return $rs;
		}
	}