<?php

namespace Cms\Aop\Advice\Decorator\Cached;

use Cms\Aop\Advice\Decorator\CustomDecorator;


/**
 * Class MethodResultCachedDecorator
 * @package Cms\Aop\Advice\Decorator\Cached
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
class MethodResultCachedDecorator extends CustomDecorator
{
	const NAME = '@MethodResultCached';

	/**
	 * @var string
	 */
	static protected $definition = '\Cms\Cache\MethodResultCachedAdvice->cache({$args})';
}
