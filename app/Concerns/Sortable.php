<?php

namespace App\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait Sortable
{
    /**
     * Apply one of a set of named sort callbacks to a query, based on the
     * request's `sort` parameter. Falls back to $default when the
     * requested key isn't in $options.
     *
     * @param  array<string, \Closure(Builder):void>  $options
     */
    protected function applySort(Builder $query, Request $request, array $options, string $default): string
    {
        $sort = $request->input('sort');
        $sort = array_key_exists($sort, $options) ? $sort : $default;

        $options[$sort]($query);

        return $sort;
    }
}
