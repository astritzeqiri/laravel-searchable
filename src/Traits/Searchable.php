<?php 

namespace AstritZeqiri\LaravelSearchable\Traits;

use AstritZeqiri\LaravelSearchable\Search;
use AstritZeqiri\LaravelSearchable\Exceptions\SearchException;

trait Searchable
{
    /**
     * Search on multiple database fields on eloquent models.
     * 
     * @param  Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @param  array|string   $fields
     * @param  boolean $exact
     *
     * @throws SearchException [if the search fields are not set on the model or they are not given on the method] 
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search = "", $fields = [], $exact = false)
    {
        if (! $search) {
            return $query;
        }

        $fields = $this->resolveSearchFields($fields);

        if (empty($fields)) {
            throw SearchException::searchOnOrFieldsNotFound();
        }

        return (new Search($query))
                        ->className(static::class)
                        ->searchFor($search)
                        ->onFields($fields)
                        ->exactSearch($exact)
                        ->build();
    }

    /**
     * Resolve the fields that should be searched on.
     * The fields are given as an array or declared
     * on the model as $searchOn property.
     * 
     * @param  array|string  $fields
     * 
     * @return array
     */
    private function resolveSearchFields($fields = [])
    {
        if (is_array($fields)) {
            return $fields;
        }

        if (is_string($fields)) {
            return [$fields];
        }

        if (property_exists(static::class, 'searchOn')) {
            return is_array(static::$searchOn) ? static::$searchOn : [static::$searchOn];
        }

        return [];
    }
}
