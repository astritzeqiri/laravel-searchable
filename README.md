# Laravel Meta data

A php trait for searching on laravel eloquent models

## Requirements

- PHP >= 5.4
- Laravel >= 5.0


## Installation

Add laravel-searchable to your composer.json file:

```json
"require": {
    "astritzeqiri/laravel-searchable": "~1.0"
}
```

Get composer to install the package:

```
$ composer require astritzeqiri/laravel-searchable
```

## Usage

### Examples

First you need to go to your model and use the Searchable:

```php
// E.x. User.php
// add this before the class declaration
use AstritZeqiri\LaravelSearchable\Traits\Searchable;

// after the class declaration add this code snippet:
use Searchable;
```

Basic search:

```php
// This gives you a list of users that match the name john
$users = App\User::search('John', ['name'])->get();

// if you want the search to be exact you pass a third attribute
$users = App\User::search('John', ['name'], true)->get();
```

The array of search fields can also be set on the Model itself E.x. User.php:

```php

class User extends Model
{
    // These are the default search fields.
    protected static $searchOn = ['first_name', 'last_name'];
    
}

// Now you can do this.
// That gives you the users with the first_name or last_name Doe
$users = App\User::search('Doe')->get();

// Of course if you give it the second attribute it ignores the model fields.
// Now it only searches on the first_name not the last_name
$users = App\User::search('Doe', ['first_name'])->get();


```


Sometimes you may want to search on some other fields that are not on the user model but in some other table related to the user:

```php

// Ex. You want to search users profile description which is contained in the profiles table,
// you can do that by giving for example profile_description asf field.
$users = App\User::search('Texas', ['profile_description'])->get();

// And then you'll have to declare a scope function on you User model for that field.
// The function has to be called 'scopeSearchOn' and your field on studly_case
// in this example it needs to be 'scopeSearchOnProfileDescription'
class User extends Model
{
    /**
     * Search on the users profile description.
     * 
     * @param  QueryBuilder $query
     * @param  string $search [the string that we are searching for]
     * @param  string $exact [if exact searching has been required]
     * 
     * @return QueryBuilder $query
     */
    public function scopeSearchOnProfileDescription($query, $search, $exact)
    {
        return $query->whereHas('profile', function($query) use($search) {
            return $query->where('description', 'LIKE', $search);
        });
    }
    
}

```

## License
[MIT](http://opensource.org/licenses/MIT)
