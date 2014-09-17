<?php

namespace Cms\Aop\Advice;

use Cms\Aop\Advice\Definition\DefinitionParser;


/**
 * Class BaseAdvice
 * @package Cms\Aop\Advice
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
abstract class BaseAdvice implements AdviceInterface
{
	const TYPE = 'Base';


	/**
	 * @var string|array|callable
	 */
	protected $callable = '';

	/**
	 * @var array
	 */
	protected $arguments = [];

	/**
	 * @var string
	 */
	protected $call_type = DefinitionParser::ADVICE_CALL_STATIC;


	/**
	 * @return array|string
	 */
	public function getCallable()
	{
		return $this->callable;
	}

	/**
	 * @return array
	 */
	public function getArguments()
	{
		return $this->arguments;
	}

	/**
	 * @return string
	 */
	public function getCallType()
	{
		return $this->call_type;
	}

	/**
	 * @param string $definition
	 */
	protected function parseDefinition($definition)
	{
		$parser = new DefinitionParser();

		$this->callable = $parser->getFunctionName($definition);
		$this->call_type = $parser->getFunctionCallType($definition);
		$this->arguments = $parser->getFunctionArguments($definition);
	}

	/**
	 * @return array
	 */
	static public function getDecorators()
	{
		return [sprintf('\Cms\Aop\Advice\Decorator\\%sDecorator', static::TYPE)];
	}
}
