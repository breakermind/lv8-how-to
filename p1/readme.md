# Exceptions Handler
Przechwytywanie i logowanie błędów w aplikacji.

### File
```sh
app/Exceptions/Handler.php
```

### Json Handler
```php
<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{	
	// A list of the exception types that are not reported.	 
	protected $dontReport = [
		// InvalidOrderException::class,
	];

	// A list of the inputs that are never flashed for validation exceptions.
	protected $dontFlash = [
		'current_password',
		'password',
		'password_confirmation',
	];

	// Register the exception handling callbacks for the application.
	public function register()
	{
		$this->reportable(function (Throwable $e) {
			// Stop logging to lavavel logs
			// return false;
		});
		
		$this->renderable(function (Throwable $e, $request) {
			// For urls
			if (
				$request->is('web/*') ||
				$request->is('api/*') ||
				$request->wantsJson()
			) {
				// Get exception
				$msg = empty($e->getMessage()) ? 'Not Found' : $e->getMessage();
				$code = empty($e->getCode()) ? 404 : $e->getCode();
				
				// Json response
				return response()->json([
					'message' => $msg,
					'code' => $code,
					'ex' => [
						'name' => $this->getClassName(get_class($e)),
						'namespace' => get_class($e),
					]
				], $code);
			}
		});		
	}

	// Get exception class name without namespace.
	static function getClassName($e) {
		$path = explode('\\', $e);
		return array_pop($path);
	}
}
```
