# Model Relations, Pivot Tables
Tworzenie relacji pomiędzy modelami (tabelami) w bazie danych.

#### Przykład koszyka dla restauracji z produktami i dodatkami
https://github.com/breakermind/shopping-cart

### Klasy pivot

#### Dodatek pivot
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Models\Addon;

class CartAddon extends Pivot
{
	public $incrementing = true;
	
	protected $hidden = [
		'created_at',
		'updated_at',
		'deleted_at'
	];

	function addon()
	{
		return $this->belongsTo(Addon::class);
	}
}
```

#### Wariant pivot
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Models\Addon;
use App\Models\Product;
use App\Models\CartAddon;

class CartVariant extends Pivot
{
	public $incrementing = true;
	
	protected $hidden = [
		'created_at',
		'updated_at',
		'deleted_at'
	];

	function product()
	{
		return $this->belongsTo(Product::class);
	}

	function addons()
	{
		return $this->belongsToMany(Addon::class)
			->withPivot('quantity','id')
			->using(CartAddon::class)
			->withTimestamps();
	}
}
```


#### Klasa pivot w modelu
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\CartVariant;

class Cart extends Model
{
	use HasFactory, SoftDeletes;

	public $incrementing = false;

	protected $primaryKey = 'id';

	protected $keyType = 'string';

	protected $guarded = [];

	protected $hidden = [
		'updated_at',
		'deleted_at'
	];

	protected $casts = [
		'created_at' => 'datetime:Y-m-d h:i:s',
	];

	function variants()
	{
		return $this->belongsToMany(Variant::class)
			->wherePivot('cart_id', $this->id)
			->withPivot('id','quantity')
			->using(CartVariant::class)
			->withTimestamps();
	}

	public function newPivot(Model $parent, array $attributes, $table, $exists, $using = null)
	{
		if($parent instanceof Cart) {
			return new CartVariant($attributes);
		}
		return parent::pivot($parent, $attributes, $table, $exists);
	}
}
```
