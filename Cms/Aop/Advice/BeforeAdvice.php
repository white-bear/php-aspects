<?php

namespace Cms\Aop\Advice;


/**
 * Class BeforeAdvice
 * @package Cms\Aop\Advice
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
class BeforeAdvice extends BaseAdvice
{
	const TYPE = 'Before';


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
			['\Cms\Aop\Advice\Decorator\Acl\CheckCurrentUserRight',],
			parent::getDecorators()
		);
	}
}
