<?php

namespace Cms\Aop\Advice\Definition;

use LogicException;


/**
 * Class DefinitionParser
 * @package Cms\Aop\Advice\Definition
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
class DefinitionParser
{
	const
		ADVICE_CALL_STATIC  = 'static',
		ADVICE_CALL_DYNAMIC = 'dynamic';

	/**
	 * @param  string $definition
	 *
	 * @return array
	 *
	 * @throws \LogicException
	 */
	public function getFunctionName($definition)
	{
		list($name) = explode('(', $definition);
		$name = trim($name);
		if (empty($name)) {
			$msg = sprintf('Advice must use function name to call, got: "%s"', $definition);

			throw new LogicException($msg);
		}

		if (strpos($name, '::') === false && strpos($name, '->') === false) {
			return $name;
		}

		if (! preg_match('~^(.+?)(::|->)(.*)$~u', $name, $matches)) {
			$msg = sprintf('Incorrect syntax for advice: "%s"', $name);

			throw new LogicException($msg);
		}

		return [
			$matches[1],
			$matches[3],
		];
	}

	/**
	 * @param  string $definition
	 *
	 * @return string
	 */
	public function getFunctionCallType($definition)
	{
		list($name) = explode('(', $definition);

		if (strpos($name, '->') !== false) {
			return self::ADVICE_CALL_DYNAMIC;
		}

		return self::ADVICE_CALL_STATIC;
	}

	/**
	 * @param  string $definition
	 *
	 * @return array
	 *
	 * @throws \LogicException
	 */
	public function getFunctionArguments($definition)
	{
		if (strpos($definition, '(') === false || strpos($definition, ')') === false) {
			return [];
		}

		if (! preg_match('~^[^\(]+\((.+)\)\s*$~u', $definition, $matches)) {
			return [];
		}

		$data = trim($matches[1]);
		$args = json_decode('[' . $data . ']', $assoc=true);

		$last_error = json_last_error();
		if ($last_error !== JSON_ERROR_NONE) {
			$msg = sprintf('Incorrect arguments for advice, error: "%s", data: "%s"', $last_error, $data);

			throw new LogicException($msg);
		}

		return $args;
	}
}
