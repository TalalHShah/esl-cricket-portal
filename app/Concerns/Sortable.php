<?php

namespace App\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
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
     * Accepts either a plain query builder or an Eloquent relation
     * (e.g. `$team->players()`) — a relation proxies orderBy/orderByRaw
     * etc. through to its underlying query builder, so both work the
     * same way here.
     *
     * @param  array<string, \Closure(Builder):void>  $options
     */
    protected function applySort(Builder|Relation $query, Request $request, array $options, string $default, string $paramName = 'sort'): string
    {
        $sort = $request->query($paramName);
        $sort = array_key_exists($sort, $options) ? $sort : $default;

        $options[$sort]($query);

        return $sort;
    }
}
