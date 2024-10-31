<?php

namespace NativeRent\Core\Routing;

/**
 * Route item.
 */
class Route {
	/** @var string */
	public $method;

	/** @var string */
	public $path;

	/** @var array<class-string, string> */
	public $action;

	/** @var string|null */
	public $name;

	/**
	 * Constructor.
	 *
	 * @param string                      $method Request method: post, get.
	 * @param string                      $path   Route path.
	 * @param array<class-string, string> $action Action method.
	 * @param string|null                 $name   Route name.
	 */
	public function __construct( $method, $path, $action, $name = null ) {
		$this->method = $method;
		$this->path   = $path;
		$this->action = $action;
		$this->name   = $name;
	}
}
