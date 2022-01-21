# Translations
Tłumaczenia z bazy danych na wiele języków.

### Utwórz model i wszystkie klasy
```sh
php artisam make:model Trans --all
```

### Migracje tabel bazy danych
database/migrations/90000_01_21_120809_create_trans_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransTable extends Migration
{
    public function up()
    {
        Schema::create('trans', function (Blueprint $table) {
		$table->id();
		$table->longText('slug');
		$table->longText('text');
		$table->string('locale', 2)->nullable()->default('en');
		$table->timestamps();
        });
    }
	
    public function down()
    {
        Schema::dropIfExists('trans');
    }
}
```

### Klasa modelu
app/Models/Trans.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Trans extends Model
{
    use HasFactory;

	protected $guarded = [];

	public function setSlugAttribute($value)
	{
		$this->attributes['slug'] = strip_tags($value);
	}

	public function getSlugAttribute()
	{
		return strip_tags($this->attributes['slug']);
	}

	public function setTextAttribute($value)
	{
		$this->attributes['text'] = strip_tags($value);
	}

	public function getTextAttribute()
	{
		return strip_tags($this->attributes['text']);
	}

	/**
	 * Save translations to resources/lang directory with {locale}.json extension.
	 *
	 * @return void
	 */
	static function createTranslationFiles()
	{
		$list = self::select('locale', DB::raw('count(*) as total'))->groupBy('locale')->get();
		foreach ($list as $i) {
			$data = [];
			$all = self::select('slug', 'text')->where('locale', $i->locale)->get();
			foreach ($all as $t) {
				$data[$t->slug] = $t->text;
			}
			$json = json_encode($data, JSON_PRETTY_PRINT);
			$path = resource_path('lang/') . strtolower($i->locale) . '.json';
			file_put_contents($path, $json);
		}
	}
	
	static function loadSessionLang()
	{
		if(!empty(session('lang'))) {
			App::setLocale(session('lang'));
		}
	}

	static function setSessionLang($lang = '')
	{
		session(['lang' => $lang]);
		App::setLocale($lang);
	}
}
```

### Populacja tabeli w bazie danych
database/seeders/TransSeeder.php
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trans;

class TransSeeder extends Seeder
{
    public function run()
    {
        Trans::create([
			'slug' => 'Fresh newsletter!',
			'text' => 'Fresh newsletter!',
			'locale' => 'en'
		]);

		Trans::create([
			'slug' => 'Fresh newsletter!',
			'text' => 'Świeży newsletter!',
			'locale' => 'pl'
		]);

		Trans::create([
			'slug' => 'Lang',
			'text' => 'English',
			'locale' => 'en'
		]);

		Trans::create([
			'slug' => 'Lang',
			'text' => 'Polski',
			'locale' => 'pl'
		]);
    }
}
```

### Generowanie plików tłumaczeń
resources/lang/{locale}.json
```php
use App\Models\Trans;

// Create translations .json files
Trans::createTranslationFiles();
```

### Pobierz lub zmień język tłumaczeń
```php
use App\Models\Trans;

// Get session lang
Trans::loadSessionLang();

// Set session lang
Trans::setSessionLang('pl');
```

### Użyj w widoku
resource/views/welcome.blade.php
```blade
<h1> @lang('Fresh newsletter!') </h1>

<h1> {{ __('Fresh newsletter!') }} </h1>
```
