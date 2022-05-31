<?php

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

if (! function_exists('moduleRoute')) {
    /**
     * @param string $moduleName
     * @param string $prefix
     * @param string $action
     * @param array $parameters
     * @param bool $absolute
     * @return string
     */
    function moduleRoute($moduleName, $prefix, $action = '', $parameters = [], $absolute = true)
    {
        // Fix module name case
        $moduleName = Str::camel($moduleName);

        // Nested module, pass in current parameters for deeply nested modules
        if (Str::contains($moduleName, '.')) {
            $parameters = array_merge(Route::current()->parameters(), $parameters);
        }

        // Create base route name
        $routeName = 'twill.' . ($prefix !== '' && $prefix !== '0' ? $prefix . '.' : '');

        // Prefix it with module name only if prefix doesn't contains it already
        if (
            config('twill.allow_duplicates_on_route_names', true) ||
            ($prefix !== $moduleName &&
                ! Str::endsWith($prefix, '.' . $moduleName))
        ) {
            $routeName .= "{$moduleName}";
        }

        //  Add the action name
        $routeName .= $action !== '' && $action !== '0' ? ".{$action}" : '';

        // Build the route
        return route($routeName, $parameters, $absolute);
    }
}

if (! function_exists('getNavigationUrl')) {
    /**
     * @param array $element
     * @param string $key
     * @param string|null $prefix
     * @return string
     */
    function getNavigationUrl($element, $key, $prefix = null)
    {
        $isModule = $element['module'] ?? false;
        $isSingleton = $element['singleton'] ?? false;

        if ($isModule) {
            $action = $element['route'] ?? 'index';

            return moduleRoute($key, $prefix, $action);
        } elseif ($isSingleton) {
            return moduleRoute($key, $prefix);
        } elseif ($element['raw'] ?? false) {
            return empty($element['route']) ? '#' : $element['route'];
        }

        return empty($element['route']) ? '#' : route($element['route'], $element['params'] ?? []);
    }
}

if (! function_exists('isActiveNavigation')) {
    function isActiveNavigation(array $navigationElement, string $navigationKey, string $activeNavigationKey): bool
    {
        $keysAreMatching = $navigationKey === $activeNavigationKey;

        if ($keysAreMatching) {
            return true;
        }

        return ($navigationElement['raw'] ?? false) && Str::endsWith(Request::url(), $navigationElement['route']);
    }
}
