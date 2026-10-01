<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up():void { Schema::create('categories',function(Blueprint $t){$t->id();$t->string('name');$t->string('slug')->unique();$t->text('description')->nullable();$t->boolean('is_active')->default(true);$t->unsignedInteger('sort_order')->default(0);$t->timestamps();});Schema::create('menu_items',function(Blueprint $t){$t->id();$t->foreignId('category_id')->constrained()->cascadeOnDelete();$t->string('name');$t->string('slug')->unique();$t->text('description')->nullable();$t->decimal('price',10,2);$t->string('image_path')->nullable();$t->boolean('is_available')->default(true);$t->timestamps();}); } public function down():void {Schema::dropIfExists('menu_items');Schema::dropIfExists('categories');} };
