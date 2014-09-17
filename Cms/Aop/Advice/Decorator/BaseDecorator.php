<?php

namespace Cms\Aop\Advice\Decorator;


/**
 * Class BaseDecorator
 * @package Cms\Aop\Advice\Decorator
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
abstract class BaseDecorator implements DecoratorInterface
{
	const NAME = '@decorator';
	const ADVICE = 'BaseAdvice';

	/**
	 * Пример: @decorator: Test::method()
	 *
	 * @var string
	 */
	static protected $pattern = '~^[ \t]*\*[ \t]*{$name}\:[ \t]+(\S.+)$~um';


	/**
	 * @param  string $annotation
	 *
	 * @return bool
	 */
	public function match($annotation)
	{
		if (strpos($annotation, static::NAME) === false) {
			return false;
		}

		$pattern = str_replace('{$name}', static::NAME, static::$pattern);

		$result = preg_match($pattern, $annotation);

		return $result && $result > 0;
	}

	/**
	 * @param  string $annotation
	 *
	 * @return array
	 */
	public function getAdvices($annotation)
	{
		$pattern = str_replace('{$name}', static::NAME, static::$pattern);

		preg_match_all($pattern, $annotation, $matches);

		if (empty($matches) || empty($matches[1])) {
			return [];
		}

		$result = [];
		foreach ($matches[1] as $definition) {
			$cls = 'Cms\Aop\Advice\\' . static::ADVICE;
			$result []= new $cls($definition);
		}

		return $result;
	}
}
