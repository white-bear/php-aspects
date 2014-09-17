<?php

namespace Cms\Aop\Advice\Decorator;


/**
 * Class ThrowDecorator
 * @package Cms\Aop\Advice\Decorator
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
class ThrowDecorator extends BaseDecorator
{
	const NAME = '@OnThrow';
	const ADVICE = 'ThrowAdvice';

	/**
	 * Примеры
	 * @OnThrow \Exception: Test::catch
	 * @OnThrow \Exception: Test::catch, stopPropagation:true
	 * @OnThrow \Exception: Test::catch, stopPropagation:false
	 *
	 * @var string
	 */
	static protected $pattern = '~^[ \t]*\*[ \t]*{$name}[ \t]+(\S+)\:[ \t]+(\S.+?)(, stopPropagation\:(true|false))?$~um';


	/**
	 * @param  string $annotation
	 *
	 * @return array
	 */
	public function getAdvices($annotation)
	{
		$pattern = str_replace('{$name}', static::NAME, static::$pattern);

		preg_match_all($pattern, $annotation, $matches);

		if (empty($matches) || empty($matches[2])) {
			return [];
		}

		$result = [];
		foreach ($matches[2] as $i => $definition) {
			$cls = 'Cms\Aop\Advice\\' . static::ADVICE;
			$exception_name = $matches[1][$i];

			$stop_propagation = false;
			if (! empty($matches[3][$i]) && $matches[4][$i] == 'true') {
				$stop_propagation = true;
			}

			$result []= new $cls($exception_name, $definition, $stop_propagation);
		}

		return $result;
	}
}
