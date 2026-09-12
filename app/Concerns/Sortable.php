<?php

namespace App\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait Sortable
{
    /**
     * Apply one of a set of named sort callbacks to a query, based on a
     * request query parameter (default `sort`). Falls back to $default
     * when the requested key isn't in $options. Pass a distinct
     * $paramName when a single page needs more than one independent
     * sortable section (e.g. live/upcoming/completed auction lists).
     *
     * @param  array<string, \Closure(Builder):void>  $options
     */
    protected function applySort(Builder $query, Request $request, array $options, string $default, string $paramName = 'sort'): string
    {
        $sort = $request->query($paramName);
        $sort = array_key_exists($sort, $options) ? $sort : $default;

        $options[$sort]($query);

        return $sort;
    }
}
