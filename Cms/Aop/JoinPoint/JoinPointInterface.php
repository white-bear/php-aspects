<?php

namespace Cms\Aop\JoinPoint;


/**
 * Interface JoinPointInterface
 * @package Cms\Aop\JoinPoint
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
interface JoinPointInterface
{
	/**
	 * @return mixed
	 */
	public function getObject();

	/**
	 * @param  string $argument_name
	 *
	 * @return bool
	 */
	public function hasArgument($argument_name);

	/**
	 * @param  string $argument_name
	 * @param  mixed $default
	 *
	 * @return mixed
	 */
	public function getArgument($argument_name, $default=null);

	/**
	 * @return array
	 */
	public function getArguments();

	/**
	 * @return array
	 */
	public function getNamedArguments();

	/**
	 * @param string $argument_name
	 * @param mixed  $value
	 */
	public function setArgument($argument_name, $value);

	/**
	 * @param array &$args
	 */
	public function setArguments(array &$args=[]);

	/**
	 * @param string $advice_type
	 */
	public function setAdviceType($advice_type);

	/**
	 * @return string
	 */
	public function getAdviceType();

	/**
	 * @return mixed
	 */
	public function getReturnedValue();

	/**
	 * @param mixed $value
	 */
	public function setReturnedValue($value=null);

	/**
	 * @return \Exception|null
	 */
	public function getException();

	/**
	 * @param \Exception $exception
	 */
	public function setException($exception);

	/**
	 * @return string
	 */
	public function getClassName();

	/**
	 * @return string
	 */
	public function getMethodName();

	public function process();
}
