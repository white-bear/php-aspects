<?php

namespace Cms\Patterns;


/**
 * Trait SingletonPattern
 * @package Cms\Patterns
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
trait SingletonPattern
{
	static protected $self = null;


	static public function getInstance()
	{
		if (static::$self !== null) {
			return static::$self;
		}

		static::$self = new self();

		return static::$self;
	}

	protected function __construct() {}

	protected function __clone() {}

	protected function __wakeup() {}
}
