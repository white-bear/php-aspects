<?php

namespace Cms\Aop\Advice;


/**
 * Class AfterAdvice
 * @package Cms\Aop\Advice
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
class AfterAdvice extends BaseAdvice
{
	const TYPE = 'After';


	/**
	 * @param string $definition
	 */
	public function __construct($definition)
	{
		$this->parseDefinition($definition);
	}
}
