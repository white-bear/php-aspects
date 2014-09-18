<?php

namespace Cms\Cache;

use Cms\Aop\JoinPoint\JoinPointInterface;


/**
 * Class RuntimeCachedAdvice
 * @package Cms\Cache
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
class RuntimeCachedAdvice
{
	static protected $storage = [];


	/**
	 * @param \Cms\Aop\JoinPoint\JoinPointInterface $join_point
	 * @param array                                 $args
	 */
	public function cache(JoinPointInterface $join_point, array $args=[])
	{
		$key = $this->getKey($join_point, $args);
		if (array_key_exists($key, static::$storage)) {
			$join_point->setReturnedValue(static::$storage[$key]);

			return;
		}

		$join_point->process();

		static::$storage[$key] = $join_point->getReturnedValue();
	}

	/**
	 * @param \Cms\Aop\JoinPoint\JoinPointInterface $join_point
	 * @param array                                 $args
	 *
	 * @return string
	 */
	protected function getKey(JoinPointInterface $join_point, array $args=[])
	{
		$key = $join_point->getMethodName();
		if (empty($args)) {
			return $key;
		}

		$args_list = $join_point->getNamedArguments();
		foreach ($args as $arg) {
			if (! array_key_exists($arg, $args_list)) {
				continue;
			}

			$key_part = serialize($args_list[$arg]);
			if (strlen($key_part) > 32) {
				$key_part = md5($key_part);
			}

			$key .= '|' . $key_part;
		}

		return $key;
	}
}
