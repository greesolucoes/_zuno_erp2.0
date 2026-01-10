<?php

namespace App\Http\Controllers;

use Closure;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class RouteExplorerController extends Controller
{
    public function index()
    {
        $routes = collect(Route::getRoutes())->map(function ($route) {
            $middleware = collect($route->gatherMiddleware())
                ->map(function ($m) {
                    if (is_string($m)) return $m;
                    if ($m instanceof Closure) return 'Closure';
                    if (is_object($m) && method_exists($m, '__toString')) return (string) $m;
                    if (is_object($m)) return get_class($m);
                    return (string) $m;
                })
                ->values()
                ->all();

            return [
                'uri' => '/' . ltrim($route->uri(), '/'),
                'methods' => $route->methods(),
                'name' => $route->getName(),
                'action' => $route->getActionName() === 'Closure' ? 'Closure' : $route->getActionName(),
                'middleware' => $middleware,
            ];
        })
            ->filter(function (array $r) {
                // Hide internal/framework endpoints and unnamed closures noise
                if ($r['name'] !== null && Str::startsWith($r['name'], 'ignition.')) return false;
                if ($r['uri'] === '/_ignition/execute-solution') return false;
                if ($r['uri'] === '/_ignition/health-check') return false;
                if ($r['uri'] === '/_ignition/scripts/{script}') return false;
                if ($r['uri'] === '/_ignition/styles/{style}') return false;
                if ($r['uri'] === '/up') return false;
                return true;
            })
            ->values();

        $groups = $routes->groupBy(function (array $r) {
            $uri = ltrim($r['uri'], '/');
            if ($uri === '') return 'root';
            return Str::before($uri, '/');
        })->sortKeys();

        return view('route_explorer.index', [
            'groups' => $groups,
            'total' => $routes->count(),
        ]);
    }
}
