<?php

namespace lbs\model;

class Categorie extends \Illuminate\Database\Eloquent\Model {

  protected $table = 'categorie';
  protected $primaryKey = 'id';
  public $timestamps = false;


  public function sandwitchs(){
    return $this->belongsToMany( 'lbs\model\Sandwitch',
                                'sand2cat',
                                'cat_id',
                                'sand_id');
  }
}
