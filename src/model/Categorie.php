<?php

namespace lbs\model;

class Categorie extends \Illuminate\Database\Eloquent\Model {

  protected $table = 'categorie';
  protected $primaryKey = 'id';
  public $timestamps = false;
  protected $hidden = ['pivot'];

  public function sandwichs(){
    return $this->belongsToMany( 'lbs\model\Sandwich',
                                'sand2cat',
                                'cat_id',
                                'sand_id');
  }
}
