<?php

namespace Cms\Aop\JoinPoint;


/**
 * Class JoinPoint
 * @package Cms\Aop\JoinPoint
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
class JoinPoint implements JoinPointInterface
{
	protected $object = null;

	protected $class_name = '';

	protected $method_name = '';

	protected $original_method_name = '';

	protected $arguments = [];

	protected $advice_type = '';

	protected $returned_value = null;

	protected $exception = null;


	/**
	 * @param mixed  $object
	 * @param string $method_name
	 * @param string $original_method_name
	 */
	public function __construct($object, $method_name, $original_method_name)
	{
		$this->object = $object;
		$this->class_name = get_class($object);
		$this->method_name = $method_name;
		$this->original_method_name = $original_method_name;
	}

	/**
	 * @return mixed
	 */
	public function getObject()
	{
		return $this->object;
	}

	/**
	 * @param  string $argument_name
	 *
	 * @return int
	 */
	protected function argumentIndex($argument_name)
	{
		$method = new \ReflectionMethod($this->object, $this->original_method_name);
		$params = $method->getParameters();

		foreach ($params as $i => $param) {
			$name = $param->getName();
			if ($argument_name == $name) {
				return $i;
			}
		}

		return -1;
	}

	/**
	 * @param  string $argument_name
	 *
	 * @return bool
	 */
	public function hasArgument($argument_name)
	{
		return $this->argumentIndex($argument_name) >= 0;
	}

	/**
	 * @param  string $argument_name
	 * @param  mixed  $default
	 *
	 * @return mixed
	 */
	public function getArgument($argument_name, $default=null)
	{
		$method = new \ReflectionMethod($this->object, $this->original_method_name);
		$params = $method->getParameters();

		foreach ($params as $i => $param) {
			$name = $param->getName();
			if ($argument_name != $name) {
				continue;
			}

			if (array_key_exists($i, $this->arguments)) {
				return $this->arguments[$i];
			}

			if ($param->isOptional()) {
				return $param->getDefaultValue();
			}

			return $default;
		}

		return $default;
	}

	/**
	 * @return array
	 */
	public function getArguments()
	{
		return $this->arguments;
	}

	/**
	 * @return array
	 */
	public function getNamedArguments()
	{
		$method = new \ReflectionMethod($this->object, $this->original_method_name);
		$params = $method->getParameters();

		$result = [];
		foreach ($params as $i => $param) {
			$name = $param->getName();
			$result[$name] = null;

			if (array_key_exists($i, $this->arguments)) {
				$result[$name] = $this->arguments[$i];
			}
			elseif ($param->isOptional()) {
				$result[$name] = $param->getDefaultValue();
			}
		}

		return $result;
	}

	/**
	 * @param string $argument_name
	 * @param mixed  $value
	 */
	public function setArgument($argument_name, $value)
	{
		if ($this->hasArgument($argument_name)) {
			$this->arguments[ $this->argumentIndex($argument_name) ] = $value;
		}
	}

	/**
	 * @param array &$args
	 */
	public function setArguments(array &$args=[])
	{
		$this->arguments = $args;
	}

	/**
	 * @return string
	 */
	public function getAdviceType()
	{
		return $this->advice_type;
	}

	/**
	 * @param string $advice_type
	 */
	public function setAdviceType($advice_type)
	{
		$this->advice_type = $advice_type;
	}

	/**
	 * @return mixed
	 */
	public function getReturnedValue()
	{
		return $this->returned_value;
	}

	/**
	 * @param mixed $value
	 */
	public function setReturnedValue($value=null)
	{
		$this->returned_value = $value;
	}

	/**
	 * @return \Exception|null
	 */
	public function getException()
	{
		return $this->exception;
	}

	/**
	 * @param \Exception $exception
	 */
	public function setException($exception)
	{
		$this->exception = $exception;
	}

	/**
	 * @return string
	 */
	public function getClassName()
	{
		return $this->class_name;
	}

	/**
	 * @return string
	 */
	public function getMethodName()
	{
		return $this->method_name;
	}

	public function process()
	{
		$method = new \ReflectionMethod($this->object, $this->original_method_name);
        $method->setAccessible(true);
		$this->returned_value = $method->invokeArgs($this->object, $this->arguments);
	}
}
