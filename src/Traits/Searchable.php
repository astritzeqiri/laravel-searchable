<?php 

namespace AstritZeqiri\LaravelSearchable\Traits;

use AstritZeqiri\LaravelSearchable\Exceptions\SearchException;

trait Searchable
{
    /**
     * Search on multiple database fields on eloquent models.
     * 
     * @param  Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @param  array   $fields
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

        if (! $fields && ! property_exists(static::class, 'searchOn')) {
            throw SearchException::searchOnOrFieldsNotFound();
        }

        $fields = $fields ?: static::$searchOn;
        
        if (! is_array($fields)) {
            $fields = [$fields];
        }

        if (empty($fields)) {
            return $query;
        }

        return (new Search($query))
                        ->className(static::class)
                        ->searchFor($search)
                        ->onFields($fields)
                        ->exactSearch($exact)
                        ->build();
    }
}
