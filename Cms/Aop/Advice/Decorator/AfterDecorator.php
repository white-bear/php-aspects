<?php

namespace Cms\Aop\Advice\Decorator;


/**
 * Class AfterDecorator
 * @package Cms\Aop\Advice\Decorator
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
class AfterDecorator extends BaseDecorator
{
	const NAME = '@After';
	const ADVICE = 'AfterAdvice';
}
