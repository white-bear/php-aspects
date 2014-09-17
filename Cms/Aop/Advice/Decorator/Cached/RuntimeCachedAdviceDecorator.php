<?php

namespace Cms\Aop\Advice\Decorator\Cached;

use Cms\Aop\Advice\Decorator\CustomDecorator;


/**
 * Class RuntimeCachedAdviceDecorator
 * @package Cms\Aop\Advice\Decorator\Cached
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
class RuntimeCachedAdviceDecorator extends CustomDecorator
{
	const NAME = '@RuntimeCached';

	/**
	 * @var string
	 */
	static protected $definition = '\Cms\Cache\RuntimeCachedAdvice->cache({$args})';
}
