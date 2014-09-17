<?php

namespace Cms\Aop\Advice\Decorator;


/**
 * Class BeforeDecorator
 * @package Cms\Aop\Advice\Decorator
 * @author  Alex Shilkin <shilkin.alexander@gmail.com>
 */
class BeforeDecorator extends BaseDecorator
{
	const NAME = '@Before';
	const ADVICE = 'BeforeAdvice';
}
