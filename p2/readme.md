# Model

### File
```sh
app/Models/Area.php
```

### Create Model class with controller, requests, policy, facroty, seeder, migrations ...
```sh
# Create
php artisan make:model --all
```

### Create only Model class
```sh
php artisan make:model Area
```

### Area model class
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Database\Factories\AreaFactory;
use App\Models\Restaurant;
use App\Models\Cart;

class Area extends Model
{
	use HasFactory, SoftDeletes;
	
	protected $guarded = [];

	protected $hidden = [
		'created_at',
		'updated_at',
		'deleted_at',
	];

	protected $casts = [
		'created_at' => 'datetime:Y-m-d H:i:s',
	];

	protected static function newFactory()
	{
		return AreaFactory::new();
	}

	function restaurant() {
		return $this->belongsTo(Restaurant::class);
	}

	public function carts()
	{
		return $this->hasMany(Cart::class);
	}

	function setPolygonAttribute($geo_json)
	{
		$this->attributes['polygon'] = DB::raw("ST_GeomFromGeoJSON('".$geo_json."')");
	}

	function getPolygonAttribute()
	{
		return $this->selectRaw('ST_AsGeoJSON(polygon) as poly')->where('id',$this->id)->first()->poly;
	}

	function geoJsonPolySample()
	{
		return '{"type": "Polygon", "coordinates": [[[21.01752050781249, 52.16553065086626], [21.018035491943348, 52.12265533376558], [21.079490264892566, 52.12697633873785], [21.06421240234374, 52.143413406069634], [21.052024444580066, 52.154473402050264], [21.043269714355457, 52.15647444111914], [21.032626708984363, 52.16711003359743], [21.01752050781249, 52.16553065086626]]]}';
	}
}
```

### Area model class migration
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAreasTable extends Migration
{	
	public function up()
	{
		Schema::create('areas', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('restaurant_id');
			$table->string('name')->default('');
			$table->string('about')->default('');
			$table->unsignedDecimal('min_order_cost',15,2)->default(0.00); // Delivery min order cost
			$table->unsignedDecimal('cost',15,2)->default(0.00); // Delivery cost
			$table->unsignedDecimal('free_from',15,2)->nullable()->default(0.00); // Delivery free from price
			$table->tinyInteger('on_free_from')->nullable()->default(0); // Enable free from delivery
			$table->integer('time')->nullable()->default(60); // Delivery time
			$table->polygon('polygon')->nullable(true); // $table->json('polygon')->default('{}');
			$table->integer('sorting')->nullable()->default(0);
			$table->tinyInteger('visible')->nullable()->default(1);
			$table->timestamps();
			$table->softDeletes();

			$table->unique(['name','restaurant_id']);
			$table->foreign('restaurant_id')->references('id')->on('restaurants')->onUpdate('cascade')->onDelete('cascade');
		});
	}

	public function down()
	{
		Schema::dropIfExists('areas');
	}
}
```
