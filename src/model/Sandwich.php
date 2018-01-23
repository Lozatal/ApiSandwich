<?php

namespace lbs\model;

class Sandwich extends \Illuminate\Database\Eloquent\Model {

  protected $table = 'sandwich';
  protected $primaryKey = 'id';
  public $timestamps = false;
  protected $hidden = ['pivot'];

  public function categories(){
    return $this->belongsToMany( 'lbs\model\Categorie',
                                'sand2cat',
                                'sand_id',
                                'cat_id');
  }

  public function tailles(){
    return $this->belongsToMany( 'lbs\model\Taille',
                                'tarif',
                                'sand_id',
                                'taille_id');
                //->withPivot('tarif');
  }

  public function images(){
    return $this->hasMany( 'lbs\model\Image', 's_id');
  }
  /*
  public function tarifs(){
  	return $this->hasMany( 'lbs\model\tarif', 'sand_id');
  }*/
  
  public function commandes(){
  	return $this->belongsToMany( 'lbs\model\Commande',
					  			'item',
					  			'sand_id',
					  			'comm_id');
  }
}
