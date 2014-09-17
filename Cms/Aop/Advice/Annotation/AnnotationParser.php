<?php

namespace Cms\Aop\Advice\Annotation;

use ReflectionClass;


/**
 * Class AnnotationParser
 * @package Cms\Aop\Advice\Annotation
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
class AnnotationParser
{
	const SKIP_PARSE = '@SkipAopInjection';

	static protected $advices = [
		'\Cms\Aop\Advice\AfterAdvice',
		'\Cms\Aop\Advice\AroundAdvice',
		'\Cms\Aop\Advice\BeforeAdvice',
		'\Cms\Aop\Advice\ThrowAdvice',
	];

	static protected $decorators = [];


	/**
	 * Получение списка советов, прикрепленных к указанному классу
	 *
	 * @param  string $class_name
	 *
	 * @return array
	 */
	public function getAdvices($class_name)
	{
		$class = new ReflectionClass($class_name);
		if (! $this->hasAdvices($class)) {
			return [];
		}

		$methods = $class->getMethods();

		$advices = [];
		foreach ($methods as $method) {
			$annotation = $method->getDocComment();
			if (empty($annotation)) {
				continue;
			}

			if (strpos($annotation, self::SKIP_PARSE) !== false) {
				continue;
			}

			foreach ($this->getDecorators() as $name => $decorator_data) {
				if (strpos($annotation, $name) === false) {
					continue;
				}

				/** @var \Cms\Aop\Advice\Decorator\DecoratorInterface $decorator */
				$decorator = $decorator_data['decorator'];
				$advice_type = $decorator_data['advice'];

				if ($decorator->match($annotation)) {
					$method_name = $method->getName();
					if (! isset( $advices[$method_name] )) {
						$advices[$method_name] = [];
					}

					$advices[$method_name][$advice_type] = $decorator->getAdvices($annotation);
				}
			}
		}

		return $advices;
	}

	/**
	 * Быстрая проверка класса на наличие аспектов
	 *
	 * @param  \ReflectionClass $class
	 *
	 * @return bool
	 */
	protected function hasAdvices(ReflectionClass $class)
	{
		$src = file_get_contents($class->getFileName());
		foreach ($this->getDecorators() as $name => $decorator_data) {
			if (strpos($src, $name) === false) {
				continue;
			}

			return true;
		}

		return false;
	}

	/**
	 * Получение набора зарегистрированных декораторов
	 *
	 * @return array
	 */
	protected function getDecorators()
	{
		if (! empty(static::$decorators)) {
			return static::$decorators;
		}

		foreach (static::$advices as $advice_class_name) {
			/** @type \Cms\Aop\Advice\BaseAdvice $advice_class_name */
			$decorators = $advice_class_name::getDecorators();

			foreach ($decorators as $decorator_name) {
				/** @var \Cms\Aop\Advice\Decorator\BaseDecorator $decorator */
				$decorator = new $decorator_name();
				static::$decorators[$decorator::NAME] = [
					'advice' => $advice_class_name::TYPE,
					'decorator' => $decorator,
				];
			}
		}

		return static::$decorators;
	}
}
