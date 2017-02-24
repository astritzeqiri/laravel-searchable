<?php 

namespace AstritZeqiri\LaravelSearchable\Exceptions;

class SearchException extends \Exception
{
	public static function builderNotFound()
	{
		return new static('Please provide the query builder');
	}

	public static function searchOnOrFieldsNotFound()
	{
		return new static('Please provide the search fields or set a $searchOn static attribute on your model.');
	}
}
