<?php

  namespace lbs\utils;

  class Writer{
    public static $conteneur=null;
    public function __construct($contain){
      self::$conteneur=$contain;
    }

    public static function jsonFormat($categorie, array $tab, $type, $total=null, $size=null, $page=null){
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

    public static function addLink($tabObjet, $nameObject, $pathFor){
      for($i=0;$i<sizeof($tabObjet);$i++){
        $tabRendu[$i][$nameObject]=$tabObjet[$i];
        $href["href"]=self::$conteneur->get('router')->pathFor($pathFor, ['id'=>$tabObjet[$i]['id']]);
        $tab["self"]=$href;
        $tabRendu[$i]["links"]=$tab;
      }
      return $tabRendu;
    }
  }
