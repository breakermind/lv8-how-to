# UUID jako klucz główny zamiast auto-increment w Laravelu (oneToOne, oneToMany)

## Uuid Trait
App\Helpers\Traits\Uuids.php
```php
<?php
namespace App\Helpers\Traits;

use Illuminate\Support\Str;

/**
 * Change id in model class
 *
 * protected $primaryKey = 'uid';
 */
trait Uuids
{
	protected static function boot()
	{
		parent::boot();

		static::creating(function ($model) {
			try {
				// if (empty($model->{$model->getKeyName()})) {
					$model->{$model->getKeyName()} = (string) Str::uuid();
				// }
			} catch (\Exception $e) {
				abort(500, $e->getMessage());
			}
		});
	}

	public function getIncrementing(): bool
	{
		return false;
	}

	public function getKeyType(): string
	{
		return 'string';
	}
}
```

## Models

### Post
App\Models\Post.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Helpers\Traits\Uuids;
use App\Models\Detail;
use App\Models\Comment;

class Post extends Model
{
	use HasFactory, Uuids;

	protected $primaryKey = 'uid';

	protected $guarded = [];	

	public function details()
	{
		// 'foreign_key', 'local_key'
		return $this->hasOne(Detail::class, 'post_uid', 'uid');
	}

	public function comments()
	{
		// 'foreign_key', 'local_key'
		return $this->hasMany(Comment::class, 'post_uid', 'uid');
	}
}
```

### Detail
App\Models\Detail.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Post;

class Detail extends Model
{
	use HasFactory;

	protected $guarded = [];

	public function post()
	{
		// 'foreign_key', 'owner_key'
		return $this->belongsTo(Post::class, 'uid', 'post_uid');
	}
}
```

### Comment 
App\Models\Comment.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Post;

class Comment extends Model
{
	use HasFactory;

	protected $guarded = [];

	public function post()
	{
		// 'foreign_key', 'owner_key'
		return $this->belongsTo(Post::class, 'uid', 'post_uid');
	}
}
```

## Migrations

### Post
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('posts', function (Blueprint $table) {
			$table->uuid('uid')->primary();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('posts');
	}
};
```

### Details
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('details', function (Blueprint $table) {
			$table->id();
			$table->uuid('post_uid')->unique();
			$table->string('title');
			$table->timestamps();

			$table->foreign('post_uid')->references('uid')->on('posts')->onDelete('cascade')->onUpdate('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('details');
	}
};
```

### Comments
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('comments', function (Blueprint $table) {
			$table->id();
			$table->uuid('post_uid');
			$table->string('message');
			$table->unsignedInteger('status')->default(1)->nullable();
			$table->timestamps();

			$table->foreign('post_uid')->references('uid')->on('posts')->onDelete('cascade')->onUpdate('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('comments');
	}
};
```

# Routes
```php
<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Post;
use App\Models\Detail;
use App\Models\Comment;

// Login
Route::get('/post', function () {

	$p = Post::create();

	$p->details()->save(Detail::create([
		'post_uid' => $p->uid,
		'title' => 'Hello ' . uniqid(),
	]));

	$p->comments()->saveMany([
		new Comment(['message' => 'A new comment.']),
		new Comment(['message' => 'Another new comment.']),
		new Comment(['message' => 'Wooow new comment.']),
	]);

	// Detail single
	return Post::with('details')->find($p->uid);

	// Detail single
	return Post::find($p->uid)->with('details')->latest()->first();

	// Comments many
	return Post::with(['comments' => function($query) {
		$query->where('status', 1)->latest()->limit(2);
	}])->find($p->uid);

});
```
