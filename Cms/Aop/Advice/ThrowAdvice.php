<?php

namespace Cms\Aop\Advice;


/**
 * Class ThrowAdvice
 * @package Cms\Aop\Advice
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
class ThrowAdvice extends BaseAdvice
{
	const TYPE = 'Throw';


	/**
	 * @var string
	 */
	protected $exception = '';

	/**
	 * @var bool
	 */
	protected $stop_propagation = false;

	/**
	 * @param string $exception
	 * @param string $definition
	 * @param bool   $stop_propagation
	 */
	public function __construct($exception, $definition, $stop_propagation)
	{
		$this->exception = $exception;
		$this->parseDefinition($definition);
		$this->stop_propagation = $stop_propagation;
	}

	/**
	 * @return string
	 */
	public function getException()
	{
		return $this->exception;
	}

	/**
	 * @return bool
	 */
	public function stopPropagation()
	{
		return $this->stop_propagation;
	}
}
