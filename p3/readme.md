# Controller, Requests, Policy, Resource

### Utwórz klasę resource
```sh
php artisan make:resource AreaResource
```

### Controller
app/Http/Controllers/AreaController.php
```php
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\AreaResource;
use App\Http\Requests\StoreAreaRequest;
use App\Http\Requests\UpdateAreaRequest;
use App\Models\Area;

class AreaController extends Controller
{
	public function __construct()
	{
		// Authorize with policy (class, url_param)
		$this->authorizeResource(Area::class, 'area');
	}

	function perpage()
	{
		return config('app.area.perpage') ?? 12;
	}

	public function index()
	{
		$search = "" . app()->request->input('search');
		
		// $a = Area::whereRaw("CONCAT_WS(' ','name', 'about') regex ?", [str_replace(" ","|",$search)]);
		
		$a = Area::where(
			DB::raw("CONCAT_WS(' ','name', 'about')"),
			'regexp',
			str_replace(" ","|",$search)
		)
		->orderBy("id", 'desc')
		->simplePaginate($this->perpage())
		->withQueryString();

		return ['areas' => $a]; // $a->items()
	}

	public function create()
	{		
		return [];
	}

	public function store(StoreAreaRequest $request)
	{
		// Authorize here or from controller (sample)
		// $this->authorize('create', Area::class);
		
		try {
			$v = $request->validated();
			$v['deleted_at'] = NULL;
			Area::withTrashed()->updateOrCreate([
				'restaurant_id' => $v['restaurant_id'],
				'name' => $v['name']
			], $v);
		} catch(\Exception $e) {
			// Log::error($e->getMessage());
			$error = 'Area has not been created.';
			if (!empty($e->errorInfo)) {
				if ($e->errorInfo[1] == 1062) {
					$error = 'Area with this name exists!';
				}
			}
			return response()->json(['message' => $error], 422);
		}
		return response()->json(['message' => 'The area has been created.']);
	}

	public function show(Area $area)
	{
		return response()->json([
			'message' => 'Delivery area.',			
			'area' => new AreaResource($area),
			// 'area' => $area,
		]);
	}

	public function edit()
	{
		return [];
	}

	public function update(UpdateAreaRequest $request, Area $area)
	{
		try {
			$v = $request->validated();
			$area->forceFill($v);
			$area->save();
		} catch(\Exception $e) {
			// Log::error($e->getMessage());
			$error = 'Area has not been updated.';
			if (!empty($e->errorInfo)) {
				if ($e->errorInfo[1] == 1062) {
					$error = 'Area with this name exists.';
				}
			}
			return response()->json(['message' => $error], 422);
		}
		return response()->json(['message' => 'The area has been updated.']);
	}

	public function destroy(Area $area)
	{
		$area->delete();
	}
}
```

### Policy
app/Polices/AreaPolicy.php
```php
<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\User;
use App\Models\Area;

class AreaPolicy
{
	use HandlesAuthorization;
	
	// Allow only logged admin or worker
	public function before(User $user, $ability)
	{
		// Authenticated roles only: admin and/or worker
		if ($user->role == 'admin') {
			return true;
		}
	}

	// Allow all
	public function viewAny(?User $user)
	{
		return true;
	}

	// Allow all
	public function view(?User $user, Area $area)
	{
		return true;
	}
	
	// Deny all users
	public function create(User $user)
	{
		return false;
	}

	public function update(User $user, Area $area)
	{
		return false;
	}

	public function delete(User $user, Area $area)
	{
		return false;
	}

	public function restore(User $user, Area $area)
	{
		return false;
	}

	public function forceDelete(User $user, Area $area)
	{
		return false;
	}
}
```

### Request Store
app/Http/Requests/StoreAreaRequest.php
```php
<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAreaRequest extends FormRequest
{
	protected $stopOnFirstFailure = true;

	public function authorize()
	{	
		// Allow all
		return true;
	}

	public function rules()
	{
		return [
			'restaurant_id' => 'required',
			'name' => [
				'required',
				Rule::unique('areas', 'restaurant_id')->whereNull('deleted_at')
			],
			'about' => 'required',
			'polygon' => 'required|json',
			'cost' => 'required|numeric|gte:0|regex:/^-?[0-9]+(?:.[0-9]{1,2})?$/',
			'min_order_cost' => 'required|numeric|gte:0|regex:/^-?[0-9]+(?:.[0-9]{1,2})?$/',
			'free_from' => 'sometimes|numeric|gte:0|regex:/^-?[0-9]+(?:.[0-9]{1,2})?$/',
			'on_free_from' => 'sometimes|boolean',
			'time' => 'sometimes|numeric',
			'sorting' => 'sometimes|boolean',
			'visible' => 'sometimes|boolean',
			
			// 'date_of_birth' => 'required|date_format:Y-m-d',
			// 'school_id' => 'required|exists:schools,id',
			// 'file' => 'image|mimes:jpg,jpeg,png',
               		// 'contact_no' => 'regex:/^[-0-9\+]+$/',
		];
	}

	public function failedValidation(Validator $validator)
	{
		throw new \Exception($validator->errors()->first(), 422);
	}

	function prepareForValidation()
	{
		$this->merge(
			collect(request()->json()->all())->only([
				'restaurant_id', 'polygon', 'name', 'about', 'min_order_cost', 'cost', 'on_free_from', 'free_from','time', 'visible', 'sorting'
			])->toArray()
		);
	}
}
```

### Request Update
app/Http/Requests/UpdateAreaRequest.php
```php
<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAreaRequest extends FormRequest
{
	protected $stopOnFirstFailure = true;

	public function authorize()
	{
		// Allow all
		return true; 
	}

	public function rules()
	{	
		// Get url param
		$area = $this->route('area');

		return [
			'name' => [
				'sometimes', Rule::unique('areas', 'restaurant_id')->ignore($area)->whereNull('deleted_at'),
			],
			'about' => 'sometimes|max:255',
			'polygon' => 'sometimes|json',
			'min_order_cost' => 'sometimes|numeric|gte:0|regex:/^-?[0-9]+(?:.[0-9]{1,2})?$/',
			'cost' => 'sometimes|numeric|gte:0|regex:/^-?[0-9]+(?:.[0-9]{1,2})?$/',
			'free_from' => 'sometimes|numeric|gte:0|regex:/^-?[0-9]+(?:.[0-9]{1,2})?$/',
			'on_free_from' => 'sometimes|boolean',
			'time' => 'sometimes|numeric|gte:0',
			'sorting' => 'sometimes|boolean',
			'visible' => 'sometimes|boolean',
		];
	}

	public function failedValidation(Validator $validator)
	{
		throw new \Exception($validator->errors()->first(), 422);
	}

	function prepareForValidation()
	{
		$this->merge(
			collect(request()->json()->all())->only([
				'polygon', 'name', 'about', 'min_order_cost', 'cost', 'on_free_from', 'free_from', 'time', 'visible', 'sorting'
			])->toArray()
		);
	}
}
```

### Resource formatter
app/Http/Resources/AreaResource.php
```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AreaResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
	 */
	public function toArray($request)
	{
		return parent::toArray($request);
	}
}
```
