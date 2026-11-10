<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ImportBatch extends Model {
 protected $guarded=['id'];
 protected function casts(): array { return ['payload'=>'array','warnings'=>'array','expires_at'=>'datetime']; }
 public function stos() { return $this->hasMany(Sto::class); }
}
