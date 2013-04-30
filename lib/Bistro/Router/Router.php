<?php

namespace Bistro\Router;

use \Bistro\Router\Route;

/**
 * A stand-alone routing engine
 */
class Router
{
	protected $routes = array();
	protected $sub_directory;

	/**
	 * @param string $sub_directory Any subdirectory that the application is sitting in.
	 */
	public function __construct($sub_directory = "")
	{
		$this->setSubDirectory($sub_directory);
	}

	/**
	 * @return string
	 */
	public function getSubDirectory()
	{
		return $this->sub_directory;
	}

	/**
	 * @param string $dir The sub directory for the router
	 * @return $this
	 */
	public function setSubDirectory($dir)
	{
		if ($dir !== "")
		{
			$dir = "/".trim($dir, "/");
		}

		$this->sub_directory = $dir;
		return $this;
	}

	/**
	 * Loops through all of the registered routes and tries to find a match.
	 *
	 * @param  string $method   The http request method
	 * @param  string $url      The url to match
	 * @return array            An array of matches params or an empty array
	 */
	public function match($method, $url)
	{
		$params = array();

		foreach ($this->routes as $name => $route)
		{
			if ($route->isMatch($method, $url))
			{
				$params = $route->getMatchedParams();
				break;
			}
		}

		return $params;
	}

	/**
	 * Adds a route.
	 *
	 * @param  string $name        The route name
	 * @param  string $pattern     The pattern for the route to match
	 * @param  array  $responds_to An array of http verbs the route responds to.
	 * @return \Bistro\Router\Route
	 */
	public function add($name, $pattern, $responds_to = array("GET", "POST", "PUT", "DELETE"))
	{
		$route = new Route($this->sub_directory.$pattern, $responds_to);
		$this->routes[$name] = $route;
		return $route;
	}

	/**
	 * Add a route that responds to a GET request
	 *
	 * @param  string $name    The route name
	 * @param  string $pattern The url pattern
	 * @return \Bistro\Router\Route
	 */
	public function get($name, $pattern)
	{
		return $this->add($name, $pattern, array('GET'));
	}

	/**
	 * Add a route that responds to a POST request
	 *
	 * @param  string $name    The route name
	 * @param  string $pattern The url pattern
	 * @return \Bistro\Router\Route
	 */
	public function post($name, $pattern)
	{
		return $this->add($name, $pattern, array('POST'));
	}

	/**
	 * Add a route that responds to a PUT request
	 *
	 * @param  string $name    The route name
	 * @param  string $pattern The url pattern
	 * @return \Bistro\Router\Route
	 */
	public function put($name, $pattern)
	{
		return $this->add($name, $pattern, array('PUT'));
	}

	/**
	 * Add a route that responds to a DELETE request
	 *
	 * @param  string $name    The route name
	 * @param  string $pattern The url pattern
	 * @return \Bistro\Router\Route
	 */
	public function delete($name, $pattern)
	{
		return $this->add($name, $pattern, array('DELETE'));
	}

	/**
	 * Reverse routing helper.
	 *
	 * @throws \UnexpectedValueException
	 *
	 * @param  string $name   The route name
	 * @param  array  $params Route parameters
	 * @return string         The route url
	 */
	public function url($name, $params = array())
	{
		if ( ! array_key_exists($name, $this->routes))
		{
			throw new \UnexpectedValueException("A route with the name {$name} was not found");
		}

		$route = $this->routes[$name];
		return $route->url($params);
	}

}
