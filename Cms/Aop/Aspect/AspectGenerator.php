<?php

namespace Cms\Aop\Aspect;

use Cms\Aop\Advice\Definition\DefinitionParser;
use Cms\Aop\Advice\AfterAdvice,
	Cms\Aop\Advice\AroundAdvice,
	Cms\Aop\Advice\BeforeAdvice,
	Cms\Aop\Advice\ThrowAdvice;


/**
 * Class AspectGenerator
 * @package Cms\Aop\Aspect
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
class AspectGenerator
{
	const POSTFIX = '__aop_original';

	const
		ARGUMENT_CALL    = '@call:',
		ARGUMENT_GET_ARG = '@arg:';

	protected $flat_body = false;


	/**
	 * @param bool $flat_body
	 */
	public function setFlatBody($flat_body)
	{
		$this->flat_body = $flat_body;
	}

	/**
	 * @return bool
	 */
	public function isBodyFlat()
	{
		return $this->flat_body;
	}

	/**
	 * @param  string $method
	 *
	 * @return string
	 */
	public function getMethodName($method)
	{
		return $method . self::POSTFIX;
	}

	/**
	 * @param  string $method
	 * @param  array $advices
	 *
	 * @return string
	 */
	public function getAspectBody($method, array $advices=[])
	{
		$try_catch = ! empty( $advices[ ThrowAdvice::TYPE ] );

		$body = [];

		/**
		 * Инициализация точки применения советов
		 */
		$body []= '$advice_factory = \Cms\Aop\Advice\AdviceFactory::getInstance();';
		$body []= sprintf('$original_method = "%s";', $this->getMethodName($method));
		$body []= sprintf('$join_point = new \Cms\Aop\JoinPoint\JoinPoint($this, "%s", $original_method);', $method);

		$body []= '$reflection_method = new \ReflectionMethod($this, $original_method);';
		$body []= '$method_params = $reflection_method->getParameters();';
		$body []= '$args_list = [];';
		$body []= 'foreach ($method_params as $method_param) {';
		$body []= '$param_name = $method_param->getName();';
		$body []= '$args_list []= &$$param_name;';
		$body []= '}';
		$body []= '$join_point->setArguments($args_list);';

		if ($try_catch) {
			$body []= 'try {';
		}

		/**
		 * Предобработчики
		 */
		if (! empty( $advices[ BeforeAdvice::TYPE ] )) {
			$body []= sprintf('$join_point->setAdviceType("%s");', BeforeAdvice::TYPE);

			foreach ($advices[ BeforeAdvice::TYPE ] as $advice) {
				$body []= $this->adviceAsString($advice);
			}
		}

		/**
		 * Подмена основной функции
		 */
		if (! empty( $advices[ AroundAdvice::TYPE ] )) {
			$body []= sprintf('$join_point->setAdviceType("%s");', AroundAdvice::TYPE);

			foreach ($advices[ AroundAdvice::TYPE ] as $advice) {
				$body []= $this->adviceAsString($advice);
			}
		}
		else {
			$body []= '$join_point->process();';
		}

		/**
		 * Постобработчики
		 */
		if (! empty( $advices[ AfterAdvice::TYPE ] )) {
			$body []= sprintf('$join_point->setAdviceType("%s");', AfterAdvice::TYPE);

			foreach ($advices[ AfterAdvice::TYPE ] as $advice) {
				$body []= $this->adviceAsString($advice);
			}
		}

		if ($try_catch) {
			$body []= '}';
		}

		/**
		 * Обработчики исключений
		 */
		if (! empty( $advices[ ThrowAdvice::TYPE ] )) {
			$grouped = [];

			/**
			 * @var \Cms\Aop\Advice\ThrowAdvice $advice
			 */
			foreach ($advices[ ThrowAdvice::TYPE ] as $advice) {
				if (! isset( $grouped[ $advice->getException() ] )) {
					$grouped[ $advice->getException() ] = [];
				}

				$grouped[ $advice->getException() ] []= $advice;
			}

			foreach ($grouped as $exception => $advices_group) {
				$body []= sprintf('catch (%s $e) {', $exception);
				$body []= sprintf('$join_point->setAdviceType("%s");', ThrowAdvice::TYPE);
				$body []= '$join_point->setException($e);';

				$stop_propagation = false;
				foreach ($advices_group as $advice) {
					$body []= $this->adviceAsString($advice);

					if ($advice->stopPropagation()) {
						$stop_propagation = true;
					}
				}

				if (! $stop_propagation) {
					$body []= 'throw $e;';
				}

				$body []= '}';
			}
		}

		$body []= 'return $join_point->getReturnedValue();';

		$glue = $this->flat_body ? ' ' : "\n";

		return join($glue, $body);
	}

	/**
	 * @param  \Cms\Aop\Advice\AdviceInterface $advice
	 * @param  string                          $join_point_name
	 * @param  string                          $advice_factory_name
	 *
	 * @return string
	 */
	protected function adviceAsString($advice, $join_point_name='$join_point', $advice_factory_name='$advice_factory')
	{
		$args = $advice->getArguments();
		$args = $this->getArgumentsAsString($args, $join_point_name);

		if ($this->flat_body) {
			$args = str_replace(",\n", ',', $args);
		}

		return sprintf('call_user_func_array(%s, %s);', $this->callableAsString($advice, $advice_factory_name), $args);
	}

	/**
	 * @param  \Cms\Aop\Advice\AdviceInterface $advice
	 * @param  string                          $advice_factory_name
	 *
	 * @return string
	 */
	protected function callableAsString($advice, $advice_factory_name)
	{
		$callable = $advice->getCallable();
		if (is_string($callable)) {
			return sprintf("'%s'", $callable);
		}

		$call_type = $advice->getCallType();

		if ($call_type === DefinitionParser::ADVICE_CALL_STATIC) {
			return sprintf("['%s', '%s']", $callable[0], $callable[1]);
		}

		$instance = sprintf("%s->getAdvice('%s')", $advice_factory_name, $callable[0]);

		return sprintf("[%s, '%s']", $instance, $callable[1]);
	}

	/**
	 * @param  array $args
	 * @param  string $join_point_name
	 *
	 * @return string
	 */
	protected function getArgumentsAsString(array $args, $join_point_name)
	{
		$result = [$join_point_name];

		foreach ($args as $value) {
			$result []= $this->argumentAsString($value, $join_point_name);
		}

		return sprintf("[%s]", join(', ', $result));
	}

	protected function argumentAsString($arg, $join_point_name)
	{
		if (is_array($arg)) {
			$result = [];
			foreach ($arg as $key => $val) {
				$result []= sprintf('%s => %s',
					$this->argumentAsString($key, $join_point_name),
					$this->argumentAsString($val, $join_point_name)
				);
			}

			return sprintf("[%s]", join(', ', $result));
		}

		if (! is_string($arg)) {
			return var_export($arg, true);
		}

		if (strpos($arg, self::ARGUMENT_CALL) === 0) {
			$method = str_replace(self::ARGUMENT_CALL, '', $arg);

			return "{$join_point_name}->getObject()->{$method}";
		}

		if (strpos($arg, self::ARGUMENT_GET_ARG) === 0) {
			$argument = str_replace(self::ARGUMENT_GET_ARG, '', $arg);

			return "{$join_point_name}->getArgument('{$argument}')";
		}

		return var_export($arg, true);
	}
}
