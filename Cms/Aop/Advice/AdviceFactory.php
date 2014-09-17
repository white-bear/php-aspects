<?php

namespace Cms\Aop\Advice;

use Cms\Patterns\SingletonPattern;


/**
 * Class AdviceFactory
 * @package Cms\Aop\Advice
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
class AdviceFactory
{
	static protected $instances = [];

	use SingletonPattern;


	public function getAdvice($class_name)
	{
		if (isset( static::$instances[$class_name] )) {
			return static::$instances[$class_name];
		}

		$instance = new $class_name();

		static::$instances[$class_name] = $instance;

		return static::$instances[$class_name];
	}
}
