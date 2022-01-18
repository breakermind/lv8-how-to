# Controller, Requests, Policy, Resource

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

		$a = Area::where(
			DB::raw("CONCAT_WS(' ','name', 'about')"), 'regexp', str_replace(" ","|",$search)
		)->orderBy("id", 'desc')->simplePaginate($this->perpage())->withQueryString();

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
