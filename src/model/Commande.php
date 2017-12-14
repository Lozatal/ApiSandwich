<?php

namespace lbs\model;

class Commande extends \Illuminate\Database\Eloquent\Model {

  protected $table = 'commande';
  protected $primaryKey = 'id';
  public $timestamps = false;
  protected $hidden = ['pivot'];

  public function sandwichs(){
    return $this->belongsToMany( 'lbs\model\Sandwich',
                                'comm2sand',
                                'comm_id',
                                'sand_id');
  }
}
