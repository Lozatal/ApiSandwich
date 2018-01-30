<?php

namespace lbs\model;

class Tarif extends \Illuminate\Database\Eloquent\Model {

  protected $table = 'tarif';
  public $timestamps = false;
  
  public function sandwich(){
  	return $this->belongsTo('mecadoapp\model\Sandwich', 'sand_id');
  }
  public function taille(){
  	return $this->belongsTo('mecadoapp\model\Taille', 'taille_id');
  }
}
