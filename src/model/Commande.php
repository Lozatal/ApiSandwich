<?php

namespace lbs\model;

class Commande extends \Illuminate\Database\Eloquent\Model {

  protected $table = 'commande';
  protected $primaryKey = 'id';
  public $timestamps = false;
  public $incrementing = false;
  public $keyType = 'string';

/*
  public function items(){
  	return $this->hasMany( 'lbs\model\Item', 'comm_id');
  }
  */
}
