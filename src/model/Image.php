<?php

namespace lbs\model;

class Image extends \Illuminate\Database\Eloquent\Model {

  protected $table = 'image';
  protected $primaryKey = 'id';
  public $timestamps = false;

  public function sandwich(){
    return $this->belongsTo('lbs\model\Sandwich', 's_id');
  }
}

?>
