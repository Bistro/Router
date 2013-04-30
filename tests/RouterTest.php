<?php

use \Bistro\Router\Router;

/**
 * Most of these tests are for routes and not the router... oh well
 */
class RouterTest extends PHPUnit_Framework_TestCase
{
	public $router;

	public function setUp()
	{
		$this->router = new Router;
	}

	public function testEmptySubDirectory()
	{
		$this->assertSame("", $this->router->getSubDirectory());
	}

	public function testSetSubDirectoryCleansUp()
	{
		$this->router->setSubDirectory("testing/");
		$this->assertSame("/testing", $this->router->getSubDirectory());
	}

	public function testStatic()
	{
		$this->router->add('home', "/")->defaults(array('controller' => "home"));
		$params = $this->router->match("GET", "/");
		$this->assertNotEmpty($params);
	}

	public function testRegex()
	{
		$this->router->add('public', "/:controller");
		$params = $this->router->match("GET", "/testing");
		$this->assertSame('testing', $params['controller']);
	}

	public function testRegexWithAdditionalRegex()
	{
		$this->router->add('regex', '/one|two:controller');
		$params = $this->router->match("GET", "/three");
		$this->assertEmpty($params);
	}

	public function testMultipleNamedParams()
	{
		$this->router->add('regex', '/:controller/:action');
		$params = $this->router->match("GET", "/foo/bar");
		$this->assertSame(array(
			'controller' => 'foo',
			'action' => 'bar'
		), $params);
	}

	public function testOptionalParams()
	{
		$this->router->add('optional', "/:controller/:action?/\d+:id?");

		$params = $this->router->match("GET", "/user/edit");
		$this->assertSame(array(
			'controller' => 'user',
			'action' => 'edit'
		), $params);
	}

	public function testWildcard()
	{
		$this->router->add('wildcard', '/:controller/.*:wildcard');
		$params = $this->router->match("GET", '/wildcard/here/is/a/bunch/of/stuff');
		$this->assertSame(array(
			'controller' => 'wildcard',
			'wildcard' => 'here/is/a/bunch/of/stuff'
		), $params);
	}

	public function testRespondsTo()
	{
		$this->router->get("login", '/login')->defaults(array('controller' => 'login'));
		$this->assertEmpty($this->router->match("POST", '/login'));
	}

	public function testWithStaticAndRegex()
	{
		$this->router->add("admin", '/admin/:controller/:action?');
		$params = $this->router->match("GET", '/admin/dashboard');
		$this->assertSame(array(
			'controller' => 'dashboard'
		), $params);
	}

	public function testWithSubDirectory()
	{
		$defaults = array(
			'controller' => 'dashboard',
			'directory' => 'admin'
		);

		$router = new Router('admin');
		$router->add('dashboard', '/dashboard')->defaults($defaults);

		$params = $router->match("GET", '/admin/dashboard');
		$this->assertSame($defaults, $params);
	}

	public function testReverseRoutingStatic()
	{
		$this->router->add('static', '/welcome/home');
		$this->assertSame('/welcome/home', $this->router->url('static'));
	}

	public function testReverseRoutingRegex()
	{
		$this->router->add('crud', '/:controller/:action?/:id?')
			->defaults(array(
				'controller' => "user",
				'action' => 'view',
				'id' => null
			));

		$url = $this->router->url('crud', array(
			'controller' => 'post',
			'action' => 'create'
		));

		$this->assertSame('/post/create', $url);
	}

	public function testReverseRouteMixed()
	{
		$this->router->add('mixed', '/admin/:controller/:action?');
		$this->assertSame('/admin/user/create', $this->router->url('mixed', array(
			'controller' => 'user',
			'action' => 'create'
		)));
	}

	public function testReverseRoutingWithoutOptionalParams()
	{
		$this->router->add('oops', '/:controller/:action?')
			->defaults(array('action' => 'view'));

		$this->assertSame('/dashboard', $this->router->url('oops', array('controller' => 'dashboard')));
	}

	public function testMethodSpecificRoutes()
	{
		$this->router->add('methods', '/user/:id?')
			->defaults(array('controller' => 'user'))
			->get(array('action' => 'read'))
			->post(array('action' => 'create'))
			->put(array('action' => 'update'))
			->delete(array('action' => 'delete'));

		$this->assertSame(
			array('controller' => 'user', 'action' => 'update', 'id' => '5'),
			$this->router->match("PUT", '/user/5')
		);
	}

	/**
     * @expectedException UnexpectedValueException
     */
	public function testMissingSegementThrowsError()
	{
		$this->router->add('crud', '/:controller/:action?/:id?');
		$url = $this->router->url('crud');
	}

}
