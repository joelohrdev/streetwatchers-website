<?php

namespace App\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Builds a URL slug from a name, adding a number when the slug is taken or reserved.
 */
trait GeneratesUniqueSlugs
{
    /**
     * Checks the table directly so soft-deleted rows, which still hold their slug, count as taken.
     *
     * @param  class-string<Model>  $model
     * @param  list<string>  $reserved  Slugs that would collide with fixed routes, such as "create".
     */
    protected function uniqueSlug(string $name, string $model, array $reserved = []): string
    {
        $table = (new $model)->getTable();
        $base = Str::slug($name) ?: 'untitled';
        $slug = $base;
        $suffix = 2;

        while (in_array($slug, $reserved, true) || DB::table($table)->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
