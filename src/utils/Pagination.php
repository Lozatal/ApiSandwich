<?php

  namespace lbs\utils;

  class Pagination{
    public static function page($request, $taille, $page, $tailleTotale){
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
  }
