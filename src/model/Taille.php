<?php

namespace lbs\model;

class Taille extends \Illuminate\Database\Eloquent\Model {

  protected $table = 'taille_sandwich';
  protected $primaryKey = 'id';
  protected $hidden = ['pivot'];

  public function sandwichs(){
    return $this->belongsToMany( 'lbs\model\Sandwich',
                                'tarif',
                                'taille_id',
                                'sand_id')
                ->withPivot("prix");
  }
}
