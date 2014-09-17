<?php

namespace Cms\Aop\Advice;


/**
 * Class AroundAdvice
 * @package Cms\Aop\Advice
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
class AroundAdvice extends BaseAdvice
{
	const TYPE = 'Around';


	/**
	 * @param string $definition
	 */
	public function __construct($definition)
	{
		$this->parseDefinition($definition);
	}

	/**
	 * @return array
	 */
	static public function getDecorators()
	{
		return array_merge(
			[
				'\Cms\Aop\Advice\Decorator\Cached\LazyCachedDecorator',
				'\Cms\Aop\Advice\Decorator\Cached\MethodResultCachedDecorator',
				'\Cms\Aop\Advice\Decorator\Cached\RuntimeCachedAdviceDecorator',
				'\Cms\Aop\Advice\Decorator\Profiled\ProfiledDecorator',
			],
			parent::getDecorators()
		);
	}
}
