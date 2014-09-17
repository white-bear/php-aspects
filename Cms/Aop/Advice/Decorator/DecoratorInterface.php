<?php

namespace Cms\Aop\Advice\Decorator;


/**
 * Interface DecoratorInterface
 * @package Cms\Aop\Advice\Decorator
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
interface DecoratorInterface
{
	/**
	 * @param  string $annotation
	 *
	 * @return bool
	 */
	public function match($annotation);

	/**
	 * @param  string $annotation
	 *
	 * @return array
	 */
	public function getAdvices($annotation);
}
