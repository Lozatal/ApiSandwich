<?php

namespace lbs\model;

class Item extends \Illuminate\Database\Eloquent\Model {

  protected $table = 'item';
  protected $primaryKey = 'id';
  public $timestamps = false;

  public function sandwich(){
  	return $this->belongsTo('mecadoapp\model\Sandwich', 'sand_id');
  }
  public function taille(){
  	return $this->belongsTo('mecadoapp\model\Taille', 'tai_id');
  }
  public function commande(){
  	return $this->belongsTo('mecadoapp\model\Commande', 'comm_id');
  }
}
