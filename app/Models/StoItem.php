<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StoItem extends Model {
 protected $guarded=['id'];
 protected function casts(): array { return ['quantity'=>'decimal:3','rate'=>'decimal:2','amount'=>'decimal:2']; }
 public function sto() { return $this->belongsTo(Sto::class); }
}
