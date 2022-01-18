<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Multiple tables relations
class CreateAddonGroupsTable extends Migration
{
	public function up()
	{
		Schema::create('addon_groups', function (Blueprint $table) {
			$table->id();
			$table->string('name');
			$table->enum('size', ['S','M','L',"XL","XXL","XXXL"])->nullable()->default('S');
			$table->string('about')->nullable()->default('');
			$table->tinyInteger('multiple')->unsigned()->nullable()->default(1);
			$table->tinyInteger('required')->unsigned()->nullable()->default(0);
			$table->integer('sorting')->nullable()->default(0);
			$table->timestamps();
			$table->softDeletes();

			$table->unique(['name','size']);
		});

		Schema::create('addon_group_variant', function (Blueprint $table) {
			$table->id('id');
			$table->unsignedBigInteger('variant_id')->default(0)->index();
			$table->unsignedBigInteger('addon_group_id')->default(0)->index();
			$table->timestamps();
			$table->softDeletes();

			$table->foreign('variant_id')->references('id')->on('variants')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('addon_group_id')->references('id')->on('addon_groups')->onUpdate('cascade')->onDelete('cascade');
		});

		Schema::create('addon_addon_group', function (Blueprint $table) {
			$table->id('id');
			$table->unsignedBigInteger('addon_id')->default(0)->index();
			$table->unsignedBigInteger('addon_group_id')->default(0)->index();
			$table->timestamps();
			$table->softDeletes();

			$table->foreign('addon_id')->references('id')->on('addons')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('addon_group_id')->references('id')->on('addon_groups')->onUpdate('cascade')->onDelete('cascade');
		});
	}

	public function down()
	{
		Schema::disableForeignKeyConstraints();

		Schema::dropIfExists('addon_group_variant');
		Schema::dropIfExists('addon_addon_group');
		Schema::dropIfExists('addon_groups');

		Schema::enableForeignKeyConstraints();
	}
}
