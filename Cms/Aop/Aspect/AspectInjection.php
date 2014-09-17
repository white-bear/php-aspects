<?php

namespace Cms\Aop\Aspect;

use Cms\Aop\Advice\Annotation\AnnotationParser;


/**
 * Class AspectInjection
 * @package Cms\Aop\Aspect
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
class AspectInjection
{
	const PUBLIC_PROXY_POSTFIX = '__aop_public';

	static protected $annotation_parser = null;

	static protected $aspect_generator = null;

	static protected $binded_methods = [];


	/**
	 * @return \Cms\Aop\Advice\Annotation\AnnotationParser
	 */
	static public function getAnnotationParser()
	{
		if (static::$annotation_parser === null) {
			static::$annotation_parser = new AnnotationParser();
		}

		return static::$annotation_parser;
	}

	/**
	 * @return \Cms\Aop\Aspect\AspectGenerator
	 */
	static public function getAspectGenerator()
	{
		if (static::$aspect_generator === null) {
			static::$aspect_generator = new AspectGenerator();
		}

		return static::$aspect_generator;
	}

	/**
	 * @param string $class_name
	 * @param string $method_name
	 * @param string $aspect
	 * @param \Cms\Aop\Aspect\AspectGenerator $aspect_generator
	 * @param string $args
	 */
	static public function dynamicBindAspectCallback($class_name, $method_name, $aspect, $aspect_generator, $args)
	{
		$key = $class_name . '|' . $method_name;
		if (in_array($key, static::$binded_methods)) {
			return;
		}

		$keys = [$key];
		$ref_class = new \ReflectionClass($class_name);
		while (($ref_class = $ref_class->getParentClass()) instanceof \ReflectionClass) {
			/** @type \ReflectionClass $ref_class */
			if (! $ref_class->hasMethod($method_name)) {
				continue;
			}

			$key = $ref_class->getName() . '|' . $method_name;
			if (in_array($key, static::$binded_methods)) {
				return;
			}

			$keys []= $key;
		}

		$new_original = $aspect_generator->getMethodName($method_name);
		$public_proxy = $method_name . self::PUBLIC_PROXY_POSTFIX;

		$body = [];
		$body []= sprintf('$reflection_method = new \ReflectionMethod($this, "%s");', $public_proxy);
		$body []= '$method_params = $reflection_method->getParameters();';
		$body []= '$args_list = [];';
		$body []= 'foreach ($method_params as $method_param) {';
		$body []= '$param_name = $method_param->getName();';
		$body []= '$args_list []= &$$param_name;';
		$body []= '}';
        $body []= '$reflection_method->setAccessible(true);';
		$body []= 'return $reflection_method->invokeArgs($this, $args_list);';

		$public_proxy_body = join('', $body);

		runkit_method_add($class_name, $new_original, $args, $public_proxy_body);

		runkit_method_copy($class_name, $public_proxy, $class_name, $method_name);

		runkit_method_redefine($class_name, $method_name, $args, $aspect, RUNKIT_ACC_PUBLIC);

		static::$binded_methods = array_merge(static::$binded_methods, $keys);
	}

	/**
	 * @param string $class_name
	 */
	static public function dynamicBindAspect($class_name)
	{
		if (extension_loaded('runkit')) {
			self::bindAspect($class_name, [__CLASS__, 'dynamicBindAspectCallback']);
		}
	}

	/**
	 * @param string   $class_name
	 * @param callable $callback
	 */
	static public function staticBindAspect($class_name, $callback)
	{
		self::bindAspect($class_name, $callback, $flat_aspect_body=true);
	}

	/**
	 * @param string   $class_name
	 * @param callable $callback
	 * @param bool     $flat_aspect_body
	 */
	static protected function bindAspect($class_name, $callback, $flat_aspect_body=false)
	{
		$annotation_parser = self::getAnnotationParser();
		$aspect_generator = self::getAspectGenerator();

		$advices = $annotation_parser->getAdvices($class_name);
		if (empty($advices)) {
			return;
		}

		$aspect_generator->setFlatBody($flat_aspect_body);
		foreach ($advices as $method_name => $method_advices) {
			if (empty($method_advices)) {
				continue;
			}

			$aspect = $aspect_generator->getAspectBody($method_name, $method_advices);

			$method = new \ReflectionMethod($class_name, $method_name);
			$args = static::getMethodArgsString($method);

			$callback($class_name, $method_name, $aspect, $aspect_generator, $args);
		}
	}
	/**
	 * Получение списка аргументов метода
	 *
	 * @param \ReflectionMethod $method
	 *
	 * @return string
	 */
	static protected function getMethodArgsString($method)
	{
		$args = array();

		$params = $method->getParameters();
		foreach ($params as $param) {
			$name = $param->getName();
			$arg = '$' . $name;

			if ($param->isPassedByReference()) {
				$arg = '&' . $arg;
			}

			if ($param->isOptional()) {
				$default = $param->getDefaultValue();
				$args []= sprintf('%s=%s', $arg, var_export($default, true));
			}
			else {
				$args []= $arg;
			}
		}

		return implode(', ', $args);
	}
}
