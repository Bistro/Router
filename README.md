# Bisro: Router

A stand-alone routing engine for PHP > = 5.3.

## Installation

Packagist all the way!

``` json
"require": {
	"bistro/router": "1.0.*"
}
```

Of course you can always [download a zip](https://github.com/Bistro/Router/archive/master.zip)
of the source on GitHub.

## Creating Routes

``` php
$router = new \Bistro\Router\Router;
$router->add('home', '/')->defaults(array(
	'controller' => 'welcome',
	'action' => 'view'
));
```

## Checking For Matches
``` php
$method = $_SERVER['REQUEST_METHOD'];
$uri = isset($_SEVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/';

$params = $router->match($method, $uri);
// $params = array('controller' => 'welcome', 'action' => 'view');
```

If you get an empty array back from `match()` signifies that there were no matches
to the request. Probably 404 time!

## Named Route Segements

You can supply named segements for your routes with :{name}.

``` php
$router->add('crud', '/:controller/:action/:id?')->defaults(array(
	'id' => null
));
```

You can put a ? at the end of a named segment to make this segment optional.

## Adding Constraints

If you want to add constraints to a named segment you can put a valid regular expression
before the `:`

``` php
$router->add('id_only', '/:controller/user|post:action/\d+:id');
```

In this route the action must be either user or post and the id is an integer.

## Method Based Routes

Only want to pick up certain request methods? Just use the helper methods

``` php
$router->post('login', '/login')->defaults(array('controller' => 'login', 'action' => 'process'));
```

The available helper methods are `get`, `post`, `put` and `delete`.

## Wildcard

Want to pick up everything at the end of a url? Easy!

``` php
$router->add('wildcard', '/:controller/.*:wildcard')
```

## Adding Request Method Defaults

Building an api and want to add in different parameters for each request method?

``` php
$router->add('api', "/:controller/\d+:id?")
	->get(array('action' => 'read'))
	->post(array('action' => 'create'))
	->put(array('action' => 'update'))
	->delete(array('action' => 'delete'));
```

## Reverse Routing

Keep track of your urls in a sane matter with the built in reverse routing functionality.

Seriously... don't hand type urls into your application! This is much easier and
allows for greater flexibility.

``` php
$router->add('reverse', '/blog/:year/:month/:day');

// Reverse Routing magic!
echo $router->url('reverse', array(
	'year' => 2013,
	'month' => 03,
	'day' => 31
));
// Output: /blog/2013/03/31
```

## Sub-Directory Installation?

If you have installed your app in a subdirectory you can add that information into
the router and everything will still work as adverstised.

``` php
$router = new \Bistro\Router\Router('subdirectory');
```

## License

MIT
