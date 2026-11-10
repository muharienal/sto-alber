<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('users',function(Blueprint $t){$t->id();$t->string('name');$t->string('username')->unique();$t->string('email')->unique();$t->string('password');$t->rememberToken();$t->timestamps();});
  Schema::create('import_batches',function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained()->restrictOnDelete();$t->string('original_name');$t->string('path');$t->string('sha256',64);$t->json('payload');$t->json('warnings');$t->string('status')->default('preview')->index();$t->timestamp('expires_at');$t->timestamps();});
  Schema::create('stos',function(Blueprint $t){$t->id();$t->string('number',40)->unique();$t->date('document_date')->index();$t->string('activity',10);$t->text('origin');$t->text('destination');$t->string('soj',100)->nullable();$t->string('mmj',100)->nullable();$t->decimal('total',18,2);$t->foreignId('import_batch_id')->nullable()->constrained()->restrictOnDelete();$t->unsignedInteger('source_page')->nullable();$t->timestamps();});
  Schema::create('sto_items',function(Blueprint $t){$t->id();$t->foreignId('sto_id')->constrained()->cascadeOnDelete();$t->unsignedInteger('line');$t->text('name');$t->decimal('quantity',18,3);$t->string('unit',20);$t->decimal('rate',18,2);$t->decimal('amount',18,2);$t->timestamps();$t->unique(['sto_id','line']);});
 }
 public function down(): void {foreach(['sto_items','stos','import_batches','users'] as $table) Schema::dropIfExists($table);}
};
