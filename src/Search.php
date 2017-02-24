<?php 

namespace AstritZeqiri\LaravelSearchable;

use Illuminate\Database\Eloquent\Builder;
use AstritZeqiri\LaravelSearchable\Exceptions\SearchException;

class Search
{
    /**
     * The class that is needed for when a custom search columns.
     * 
     * @var string
     */
    private $className;
    
    /**
     * The initial query builder.
     * 
     * @var Illuminate\Database\Eloquent\Builder
     */
    private $builder;
    
    /**
     * The fields that we have to search on.
     * 
     * @var array
     */
    private $fields;
    
    /**
     * The search string.
     * 
     * @var string
     */
    private $search;

    /**
     * Wheter the search should be exact or not.
     * 
     * @var boolean
     */
    private $exact;
        
    /**
     * The comparator ['=' or 'LIKE'] depends on the exact attribute.
     * 
     * @var string
     */
    private $comparator;

    /**
     * Make the searcher.
     * 
     * @param Illuminate\Database\Eloquent\Builder|null $builder
     * @param string       $className
     * @param array        $fields
     * @param string       $search
     * @param boolean      $exact
     */
    public function __construct(
        Builder $builder = null,
        $className = '',
        $fields = [],
        $search = '',
        $exact = false
    ) {
        $this->className = $className;
        $this->builder = $builder;
        $this->fields = $fields;
        $this->search = $search;
        $this->exact = $exact;

        $this->comparator = $this->exact ? "=" : "LIKE";
    }

    /**
     * Build the query and return it. 
     * 
     * @return Illuminate\Database\Eloquent\Builder $this->builder
     */
    public function build()
    {
        $this->checkForNoBuilderGiven();
        
        $this->buildQuery();

        return $this->builder;
    }

    /**
     * Build the query.
     *
     * @throws SearcherException [if the builder has not been provided]
     * 
     * @return void
     */
    public function buildQuery()
    {
        $this->builder->where(function ($query) {
            $boolean = 'and';
            foreach ($this->fields as $field) {
                $this->makeQueryOnField($query, $field, $boolean);

                $boolean = 'or';
            }
        });
    }

    /**
     * Make the query for a single field.
     * 
     * @param  Illuminate\Database\Eloquent\Builder $query
     * @param  string|Closure $field
     * @param  string $boolean       [tells if the function has to be E.x. 'where' if false 'orWhere' if true]
     * 
     * @return void
     */
    private function makeQueryOnField($query, $field, $boolean = 'or')
    {
        if (is_string($field) && $this->hasScopeForSearchOn($field)) {
            $method = $this->getScopeMethodNameFromField($field, false);

            return $this->makeQueryOnField($query, function($query) use ($method) {
                $query->$method(
                    $this->search,
                    $this->exact
                );
            }, $boolean);
        }

        $query->where($field, $this->comparator, $this->getSearchString(), $boolean);
    }

    /**
     * Check if the class has a scopeMethod with the name of the field 
     * 
     * E.x. 
     * $field = 'name'
     * The method has to be: scopeSearchOnName
     *
     * @param  string $field
     * @return boolean
     */
    private function hasScopeForSearchOn($field = '')
    {
        return 
        class_exists($this->className) && 
        method_exists(
            $this->className,
            $this->getScopeMethodNameFromField($field)
        );
    }

    /**
     * Get the scope method name from the given field.
     * 
     * @param  string $field
     * @param  boolean $prependScope [if this is true it appends the scope to the method name]
     * 
     * @return string
     */
    private function getScopeMethodNameFromField($field = '', $prependScope = true)
    {
        $field = studly_case($field);

        return $prependScope ? 'scopeSearchOn' . $field : 'searchOn' . $field;
    }

    /**
     * Check if we need to add percentages to the string if the user wanted exact search or not
     * 
     * @return string
     */
    public function getSearchString()
    {
        return $this->appendSign() . $this->search . $this->prependSign();
    }

    /**
     * If the user requested exact searching then return empty else return % 
     * 
     * @return string
     */
    private function appendSign()
    {
        return $this->exact ? "" : "%";
    }

    /**
     * If the user requested exact searching then return empty else return % 
     * 
     * @return string
     */
    private function prependSign()
    {
        return $this->exact ? "" : "%";
    }

    /**
     * Set the classname.
     * 
     * @param  string $className
     * 
     * @return $this
     */
    public function className($className)
    {
        $this->className = $className;

        return $this;
    }

    /**
     * Set the query builder.
     * 
     * @param  Illuminate\Database\Eloquent\Builder $query
     * 
     * @return $this
     */
    public function query($query)
    {
        $this->builder = $query;

        return $this;
    }

    /**
     * Set the fields.
     * 
     * @param  array $fields
     * 
     * @return $this
     */
    public function onFields(array $fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Set the search string.
     * 
     * @param  string $search
     * 
     * @return $this
     */
    public function searchFor($search)
    {
        $this->search = trim($search);

        return $this;
    }

    /**
     * Set the search to exact.
     * 
     * @param  boolean $exact
     * 
     * @return $this
     */
    public function exactSearch($exact = true)
    {
        $this->exact = $exact;

        return $this;
    }

    /**
     * If there is not a given builder it thrown an exception.
     * 
     * @throws SearcherException [if the builder has not been provided]
     * @return void
     */
    private function checkForNoBuilderGiven()
    {
        if (! $this->builder) {
            throw SearcherException::builderNotFound();
        }
    }
}
