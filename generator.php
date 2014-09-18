<?php

use Cms\Aop\Advice\Annotation\AnnotationParser as AopAnnotationParser,
	Cms\Aop\Aspect\AspectInjection;


function main()
{
	/**
	 * @param string $class_name
	 * @param string $method_name
	 * @param string $aspect
	 * @param \Cms\Aop\Aspect\AspectGenerator $aspect_generator
	 */
	$callback = function ($class_name, $method_name, $aspect, $aspect_generator) {
		$class_name = trim($class_name, '\\');
		$file_name = __DIR__ . DIRECTORY_SEPARATOR .
			str_replace('\\', DIRECTORY_SEPARATOR, $class_name) . '.php';

		$src = file_get_contents($file_name);

		$pattern = sprintf('~(/\*\*(((?<!\*/).)+?)\*/\s+?)([^\n]+?function(\s+%s\s*\()[^\{]+)~us', $method_name);
		$src = preg_replace_callback($pattern, function ($matches) use ($aspect, $aspect_generator, $class_name, $method_name) {
			$skip_parse_keyword = AopAnnotationParser::SKIP_PARSE;
			if (strpos($matches[1], $skip_parse_keyword) !== false) {
				return $matches[0];
			}

			$aspect_declaration = rtrim(trim($matches[4]), '{');
			$aspect_declaration = str_replace('protected', 'public', $aspect_declaration);
			$aspect_declaration = str_replace('private', 'public', $aspect_declaration);

			$new_method_name = $aspect_generator->getMethodName($method_name);
			$declaration = str_replace($matches[5], ' ' . $new_method_name . '(', $matches[4]);
			$declaration = str_replace('protected', 'public', $declaration);
			$declaration = str_replace('private', 'public', $declaration);

			$doc_comment = str_replace('*/', "* {$skip_parse_keyword} */", $matches[1]);

			echo "Updated '{$method_name}' in '{$class_name}'." . PHP_EOL;

			return sprintf('%s%s { %s } %s', $doc_comment, $aspect_declaration, $aspect, $declaration);
		}, $src, $limit=1);

		file_put_contents($file_name, $src);
	};

	$directory = new RecursiveDirectoryIterator(__DIR__ . DIRECTORY_SEPARATOR . 'Cms');
	$iterator = new RecursiveIteratorIterator($directory);
	$regex = new RegexIterator($iterator, '~^.+/(Cms/.+)\.php$~', RecursiveRegexIterator::GET_MATCH);
	foreach ($regex as $file) {
		$file = str_replace('/', '\\', $file[1]);

		if (pcntl_fork()) {
			pcntl_wait($status);
		}
		else {
			try {
				AspectInjection::staticBindAspect($file, $callback);
			}
			catch (\Exception $e) {
				echo "Skip '{$file}' - error injecting code:" . $e->getMessage() . PHP_EOL;
			}

			posix_kill(posix_getpid(), SIGTERM);
		}
	}

	echo "aspects generated" . PHP_EOL;
}

main();
