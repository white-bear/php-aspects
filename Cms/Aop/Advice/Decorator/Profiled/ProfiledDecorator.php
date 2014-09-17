<?php

namespace Cms\Aop\Advice\Decorator\Profiled;

use Cms\Aop\Advice\Decorator\CustomDecorator;


/**
 * Class ProfiledDecorator
 * @package Cms\Aop\Advice\Decorator\Profiled
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
class ProfiledDecorator extends CustomDecorator
{
	const NAME = '@Profiled';

	/**
	 * @var string
	 */
	static protected $definition = '\Cms\Profiler\ProfiledAdvice->profile({$args})';
}
