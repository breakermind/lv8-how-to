# Model, Factory, Seeder, Migration
Tworzenie modelu klasy w laravel.

### Model, controller, requests, policy, facroty, seeder, migration
```sh
# Utwórz wszystkie klasy
php artisan make:model Area --all
php artisan make:model Area -a
```

### Model, migration
```sh
# Utwórz model i migrację
php artisan make:model Area --migration
php artisan make:model Area -m
```

### Klasa modelu Area
app/Models/Area.php
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
	
	protected $dateFormat = 'Y-m-d H:i:s';
	
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
	
	static function getAreaFromLocation($lng, $lat) {
		return Area::selectRaw('*')->whereRaw('ST_CONTAINS(polygon, POINT(:lng,:lat))', [ 'lng' => $lng, 'lat' => $lat ])->first();
	}

	function geoJsonPolySample()
	{
		return '{"type": "Polygon", "coordinates": [[[21.01752050781249, 52.16553065086626], [21.018035491943348, 52.12265533376558], [21.079490264892566, 52.12697633873785], [21.06421240234374, 52.143413406069634], [21.052024444580066, 52.154473402050264], [21.043269714355457, 52.15647444111914], [21.032626708984363, 52.16711003359743], [21.01752050781249, 52.16553065086626]]]}';
	}
	
	protected function serializeDate(\DateTimeInterface $date)
	{
		return $date->format($this->dateFormat);
	}
}
```

### Klasa AreaFactory
database/factories/AreaFactory.php
```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Area;

class AreaFactory extends Factory
{	
	// Model for factory
	protected $model = Area::class;

	// Define the model's default state.
	public function definition()
	{
		return [
			'restaurant_id' => null,
			'name' => 'Area '. uniqid(),
			'about' => $this->faker->sentence(),
			'min_order_cost' => $this->faker->randomFloat(),
			'cost' => $this->faker->randomFloat(),			
			'polygon' => '{"type": "Polygon", "coordinates": [[[21.01752050781249, 52.16553065086626], [21.018035491943348, 52.12265533376558], [21.079490264892566, 52.12697633873785], [21.06421240234374, 52.143413406069634], [21.052024444580066, 52.154473402050264], [21.043269714355457, 52.15647444111914], [21.032626708984363, 52.16711003359743], [21.01752050781249, 52.16553065086626]]]}',
		];
	}
		
	// Indicate that the area is visible.
	public function hidden()
	{
		return $this->state(function (array $attributes) {
			return [
				'visible' => 0,
			];
		});
	}
}
```

### Klasa AreaSeeder
database/seeders/AreaSeeder.php
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Restaurant;
use App\Models\Area;

class AreaSeeder extends Seeder
{
	public function run()
	{
		// Single area
		$this->run_single();
		
		// With relations
		// $this->run_relations();
	}
	
	public function run_single()
	{
		$a = Area::factory()->count(3)->create([
			// Change restaurant id
			'restaurant_id' => null
		]);
	}
	
	public function run_relations()
	{
		// Get all restaurants
		$r = Restaurant::all();
		
		 // Add restaurant areas
		$r->each(function($o) {
			// Areas
			$a = Area::factory()->count(rand(1,3))->make([
				// Change restaurant id
				'restaurant_id' => $o->id
			]);
			
			// Bind to restaurant
			$o->areas()->saveMany($a);
		});
	}
}
```

### Klasa migracji bazy danych dla modelu Area
database/migrations/9000_01_01_100001_create_areas_table.php
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
			$table->unsignedBigInteger('restaurant_id')->nullable(true);
			$table->string('name')->default('');
			$table->string('about')->default('');
			$table->unsignedDecimal('min_order_cost',15,2)->default(0.00);
			$table->unsignedDecimal('cost',15,2)->default(0.00);
			$table->unsignedDecimal('free_from',15,2)->nullable()->default(0.00);
			$table->tinyInteger('on_free_from')->nullable()->default(0);
			$table->integer('time')->nullable()->default(60);
			$table->polygon('polygon')->nullable(true); // $table->json('polygon_json')->default('{}');			
			$table->integer('sorting')->nullable()->default(0);
			$table->tinyInteger('visible')->nullable()->default(1);
			$table->timestamps();
			$table->softDeletes();

			$table->unique(['name','restaurant_id']);
			$table->foreign('restaurant_id')->references('id')->on('restaurants')->onUpdate('cascade')->onDelete('cascade');
			
			// 8.x
			// $table->foreignId('restaurant_id')->constrained('restaurants', 'id')->onUpdate('cascade')->onDelete('cascade');
		});
	}

	public function down()
	{
		Schema::dropIfExists('areas');
	}
}
```

### Utwórz tabelki w bazie danych
```sh
# Single file
php artisan migrate:fresh --path=/database/migrations/9000_01_01_100001_create_areas_table.php

# All files
php artisan migrate
php artisan migrate:fresh

# Refresh
php artisan migrate:refresh

# Help
php artisan help migrate
```
