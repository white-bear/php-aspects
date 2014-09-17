<?php

namespace Cms\Aop\Advice\Decorator;


/**
 * Class CustomDecorator
 * @package Cms\Aop\Advice\Decorator
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
class CustomDecorator extends BaseDecorator
{
	const NAME = '@Custom';
	const ADVICE = 'AroundAdvice';

	/**
	 * Пример: @<Decorator>(<args>)
	 *
	 * @var string
	 */
	static protected $pattern = '~^[ \t]*\*[ \t]*{$name}\((.*)\)$~um';

	/**
	 * @var string
	 */
	static protected $definition = '<advice_class>::<advice_method>({$args})';


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
		foreach ($matches[1] as $args) {
			$cls = 'Cms\Aop\Advice\\' . static::ADVICE;

			$definition = str_replace('{$args}', $args, static::$definition);

			$result []= new $cls($definition);
		}

		return $result;
	}
}
