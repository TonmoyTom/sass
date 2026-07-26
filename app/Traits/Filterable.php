<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

trait Filterable
{
    protected static function bootFilterable(): void
    {
        static::saved(fn () => static::flushTableCache());
        static::deleted(fn () => static::flushTableCache());
    }

    /**
     * current tenant context onujayi cache tag return kore.
     * tenant active thakle 'table:{tenantId}:{tableName}', nahole 'table:central:{tableName}'
     */
    protected static function cacheTag(): string
    {
        $scope = function_exists('tenant') && tenant() ? tenant('id') : 'central';

        return sprintf('table:%s:%s', $scope, (new static)->getTable());
    }

    public function scopeFilterAndCache(
        Builder $query,
        Request $request,
        array $searchable = [],
        array $filterable = [],
        array $sortable = [],
        int $ttlSeconds = 300,
        int $perPage = 15,
        ?\Closure $transform = null
    ) {
        $cacheKey = $this->buildCacheKey($request, $perPage);
        $tag = static::cacheTag();

        $cached = Cache::store('redis')->tags([$tag])->remember($cacheKey, $ttlSeconds, function () use (
            $query, $request, $searchable, $filterable, $sortable, $perPage, $transform
        ) {
            // 1. search — plain column ba relation.column (dot notation) dutoi support
            if ($request->filled('search') && $searchable) {
                $term = $request->string('search')->trim()->toString();

                $query->where(function ($q) use ($searchable, $term) {
                    foreach ($searchable as $col) {
                        if (str_contains($col, '.')) {
                            // "seller.user.name" → relation "seller.user", column "name"
                            $lastDot = strrpos($col, '.');
                            $relation = substr($col, 0, $lastDot);
                            $relCol = substr($col, $lastDot + 1);

                            $q->orWhereHas($relation, function ($rq) use ($relCol, $term) {
                                $rq->where($relCol, 'like', "%{$term}%");
                            });
                        } else {
                            $q->orWhere($col, 'like', "%{$term}%");
                        }
                    }
                });
            }

            // 2. exact filters
            foreach ($filterable as $col) {
                if ($request->filled($col)) {
                    $query->where($col, $request->input($col));
                }
            }

            // 3. date range
            if ($request->filled('date_from')) {
                $query->whereDate($this->getTable().'.created_at', '>=', $request->input('date_from'));
            }
            if ($request->filled('date_to')) {
                $query->whereDate($this->getTable().'.created_at', '<=', $request->input('date_to'));
            }

            // 4. sort
            $sortBy = $request->input('sort_by');
            $sortDir = $request->input('sort_dir', 'desc') === 'asc' ? 'asc' : 'desc';

            if ($sortBy && in_array($sortBy, $sortable, true)) {
                $query->orderBy($sortBy, $sortDir);
            } else {
                $query->latest();
            }

            $paginator = $query->paginate($perPage)->withQueryString();

            $items = $transform
                ? $paginator->getCollection()->map($transform)->values()->all()
                : $paginator->items();

            return [
                'data' => $items,
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'path' => $paginator->path(),
                'query' => $request->query(),
            ];
        });

        return new LengthAwarePaginator(
            $cached['data'],
            $cached['total'],
            $cached['per_page'],
            $cached['current_page'],
            ['path' => $cached['path'], 'query' => $cached['query']]
        );
    }

    protected function buildCacheKey(Request $request, int $perPage): string
    {
        $params = $request->only([
            'search', 'sort_by', 'sort_dir', 'page', 'date_from', 'date_to',
        ]) + $request->except(['page']);

        ksort($params);

        return sprintf('table:%s:%s:%d', $this->getTable(), md5(json_encode($params)), $perPage);
    }

    public static function flushTableCache(): void
    {
        Cache::store('redis')->tags([static::cacheTag()])->flush();
    }
}
