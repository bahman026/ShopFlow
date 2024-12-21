<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ArToEnMiddleware
{
    protected array $except = [];

    public function handle(Request $request, Closure $next): mixed
    {
        $except = array_merge($this->except, array_slice(func_get_args(), 2));
        $request->merge($this->process($request->except($except)));

        return $next($request);
    }

    protected function process(array $data): array
    {

        array_walk_recursive(
            $data,
            function (mixed &$value) {
                $value = $this->processValue($value);
            }
        );

        return $data;
    }

    protected function processValue(mixed $value): mixed
    {
        if (is_string($value)) {
            $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
            $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

            $string = str_replace($arabic, $english, $value);

            return str_replace($persian, $english, $string);
        }

        return $value;
    }
}
