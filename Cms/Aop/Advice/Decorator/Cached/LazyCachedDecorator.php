<?php

namespace Cms\Aop\Advice\Decorator\Cached;

use Cms\Aop\Advice\Decorator\CustomDecorator;


/**
 * Class LazyCachedDecorator
 * @package Cms\Aop\Advice\Decorator\Cached
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
class LazyCachedDecorator extends CustomDecorator
{
	const NAME = '@LazyCached';

	/**
	 * @var string
	 */
	static protected $definition = '\Cms\Cache\LazyCachedAdvice->cache({$args})';
}
