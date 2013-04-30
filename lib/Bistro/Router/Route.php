<?php

namespace Bistro\Router;

/**
 * A route
 */
class Route
{
	public $pattern;
	protected $responds_to;
	protected $compiled = false;
	protected $defaults = array();
	protected $matched_params = array();

	protected $method_defaults = array(
		"GET" => array(),
		"POST" => array(),
		"PUT" => array(),
		"DELETE" => array()
	);

	/**
	 * @param string $pattern     The url pattern
	 * @param array  $responds_to An array holding the request verbs that this route responds to
	 */
	public function __construct($pattern, $responds_to)
	{
		$this->pattern = $pattern;
		$this->responds_to = $responds_to;
	}

	/**
	 * @param  array  $params Default parameters for a route
	 * @return $this
	 */
	public function defaults($params = array())
	{
		$this->defaults = $params;
		return $this;
	}

	/**
	 * @return array  The matched parameters for the route
	 */
	public function getMatchedParams()
	{
		return $this->matched_params;
	}

	/**
	 * @return boolean
	 */
	public function isStatic()
	{
		return \strpos($this->pattern, ":") === false;
	}

	/**
	 * Check to see if this route is a match.
	 *
	 * @param  string  $method The request method
	 * @param  string  $url    The accessed url
	 * @return boolean         [description]
	 */
	public function isMatch($method, $url)
	{
		$match = false;

		if (\in_array($method, $this->responds_to))
		{
			\preg_match($this->compile(), $url, $matches);

			if ( ! empty($matches))
			{
				$this->matched_params = \array_merge(
					$this->defaults,
					$this->method_defaults[$method],
					$this->cleanMatches($matches)
				);

				$match = true;
			}
		}

		return $match;
	}

	/**
	 * Adds extra params for a GET request.
	 *
	 * @param  array $params Additional route params
	 * @return $this
	 */
	public function get($params)
	{
		$this->method_defaults["GET"] = $params;
		return $this;
	}

	/**
	 * Adds extra params for a POST request.
	 *
	 * @param  array $params Additional route params
	 * @return $this
	 */
	public function post($params)
	{
		$this->method_defaults["POST"] = $params;
		return $this;
	}

	/**
	 * Adds extra params for a PUT request.
	 *
	 * @param  array $params Additional route params
	 * @return $this
	 */
	public function put($params)
	{
		$this->method_defaults["PUT"] = $params;
		return $this;
	}

	/**
	 * Adds extra params for a DELETE request.
	 *
	 * @param  array $params Additional route params
	 * @return $this
	 */
	public function delete($params)
	{
		$this->method_defaults["DELETE"] = $params;
		return $this;
	}

	/**
	 * A reverse routing helper.
	 *
	 * @throws \UnexpectedValueException
	 *
	 * @param  array  $params Parameters to set named options to
	 * @return string
	 */
	public function url($params = array())
	{
		if ($this->isStatic())
		{
			return $this->pattern;
		}

		$url = $this->pattern;

		foreach ($this->getSegments($url) as $segment)
		{
			$func = $segment['optional'] === true ? 'replaceOptional' : 'replaceRequired';
			$url = $this->{$func}($url, $segment['name'], $segment['token'], $params);
		}

		return $url;
	}

	/**
	 * Compiles the pattern into one suitable for regex.
	 *
	 * @return string   The regex pattern
	 */
	protected function compile()
	{
		if ($this->compiled !== false)
		{
			return $this->compiled;
		}

		if ($this->isStatic())
		{
			$this->compiled = '~^'.$this->pattern.'$~';
			return $this->compiled;
		}

		$compiled = $this->pattern;
		foreach ($this->getSegments($compiled) as $segment)
		{
			$compiled = \str_replace($segment['token'], $segment['regex'], $compiled);
		}

		$this->compiled = "~^{$compiled}$~";
		return $this->compiled;
	}

	/**
	 * Gets an array of url segments
	 *
	 * @param  string $pattern The route pattern
	 * @return array           An array containing url segments
	 */
	protected function getSegments($pattern)
	{
		$segments = array();
		$parts = \explode("/", ltrim($pattern, "/"));

		foreach ($parts as $segment)
		{
			if (\strpos($segment, ":") !== false)
			{
				$segments[] = $this->parseSegment($segment);
			}
		}

		return $segments;
	}

	/**
	 * Pulls out relevent information on a given segment.
	 *
	 * @param  string $segment The segment
	 * @return array           ['segment' => (string), name' => (string), 'regex' => (string), 'optional' => (boolean)]
	 */
	protected function parseSegment($segment)
	{
		$optional = false;

		list($regex, $name) = \explode(":", $segment);

		if (\substr($name, -1) === "?")
		{
			$name = \substr($name, 0, -1);
			$optional = true;
		}

		if ($regex === "")
		{
			$regex = "[^\/]+";
		}

		$regex = "/(?P<{$name}>{$regex})";

		if ($optional)
		{
			$regex = "(?:{$regex})?";
		}

		return array(
			'segment' => $segment,
			'token' => "/".$segment,
			'name' => $name,
			'regex' => $regex,
			'optional' => $optional
		);
	}

	protected function cleanMatches(array $matches)
	{
		$named = array();
		foreach ($matches as $key => $value)
		{
			if ( ! is_int($key))
			{
				$named[$key] = $value;
			}
		}

		return $named;
	}

	/**
	 * @throws \UnexpectedValueException
	 *
	 * @param  string $url     The url string
	 * @param  string $name    The param name
	 * @param  string $token   The replacement token
	 * @param  array  $params  The parameters used in replacement
	 * @return string          The updated string
	 */
	protected function replaceRequired($url, $name, $token, array $params = array())
	{
		if ( ! isset($params[$name]))
		{
			throw new \UnexpectedValueException("The required route segment, {$name}, is missing.");
		}

		return str_replace($token, "/".$params[$name], $url);
	}

	/**
	 * @param  string $url     The url string
	 * @param  string $name    The param name
	 * @param  string $token   The replacement token
	 * @param  array  $params  The parameters used in replacement
	 * @return string          The updated string
	 */
	protected function replaceOptional($url, $name, $token, array $params = array())
	{
		$value = isset($params[$name]) ? "/".$params[$name] : "";
		return \str_replace($token, $value, $url);
	}

}
