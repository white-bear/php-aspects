<?php

namespace Cms\Aop\Advice;


/**
 * Interface AdviceInterface
 * @package Cms\Aop\Advice
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
interface AdviceInterface
{
	/**
	 * @return array|string
	 */
	public function getCallable();

	/**
	 * @return array
	 */
	public function getArguments();

	/**
	 * @return string
	 */
	public function getCallType();

	/**
	 * @return array
	 */
	static public function getDecorators();
}
