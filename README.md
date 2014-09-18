php-aspects
===========

Реализация АОП (Аспектно-ориентированное программирование).


Возможности
-----------

Реализованы следующие виды декораторов:
- декоратор вызывается перед исполнением функции
- декоратор вызывается после исполнения функции
- декоратор вызывается в случае выброса определенного исключения
- декоратор вызывается перед исполнением функции, функция вызывается из декоратора, и после ее исполнения управление снова передается декоратору


Использование
-------------

Удобнее всего декораторы использовать для кеширования, профилирования и логирования. Код для данного функционала зачастую прост и однообразен. Вынося этот код в одно место (совет), мы уменьшаем потенциальное количество ошибок в шаблонном коде. Кроме того, это позволяет следовать принципу DRY (Don't repeat yourself).
Рассмотрим пример использования декораторов для простого кеширования результата функции в статической переменной


        class Test
        {
            /**
             * @RuntimeCached()
             */
            public function totalRows()
            {
                return $this->db->count('users');
            }
        }


После генерации аспектов, данный класс будет выглядеть следующим образом:


        class Test
        {
            /**
             * @RuntimeCached()
             *
             * @SkipAopInjection
             */
            public function totalRows()
            {
                $advice_factory = \Cms\Aop\Advice\AdviceFactory::getInstance();
                $original_method = "totalRows__aop_original";
                $join_point = new \Cms\Aop\JoinPoint\JoinPoint($this, "totalRows", $original_method);

                $reflection_method = new \ReflectionMethod($this, $original_method);
                $method_params = $reflection_method->getParameters();

                $args_list = [];
                foreach ($method_params as $method_param) {
                    $param_name = $method_param->getName();
                    $args_list []= &$$param_name;
                }

                $join_point->setArguments($args_list);
                $join_point->setAdviceType("Around");
                call_user_func([$advice_factory->getAdvice('\Cms\Cache\RuntimeCachedAdvice'), 'cache'], $join_point);

                return $join_point->getReturnedValue();
            }

            public function totalRows__aop_original()
            {
                return $this->_table->totalRows($this);
            }
        }


Для продакшн версии весь данный код сворачивается в 1 строку, чтобы не изменять позиции остальных строк, иначе поиск ошибок был бы затруднителен.


Подключение
-------------

В тестовом окружении применяется динамическое переопределение методов класса, для этого к автозагрузчику классов привязывается выполнение инъекции функций-советов:


        $loader->bindAfterLoad(function ($classname) { Cms\Aop\Aspect\AspectInjection::dynamicBindAspect($classname); });


В продакшн окружении используется генератор аспектов, который соответствующим образом меняет исходный код файлов перед выкладыванием их на площадку.
Генерация производится с помощью консольного скрипта `generator.php` в корне проекта.


Требования
----------

- PHP версии 5.4+
- https://github.com/zenovich/runkit

Требуется наложить патч на `runkit` перед его сборкой.
Патч добавляет копирование PHPDOC для методов класса в функции runkit_method_copy

    `sed -i "s~^.*func.common.function_name = estrndup(methodname, methodname_len);.*$~func.common.function_name = estrndup(methodname, methodname_len);\n\n#ifdef ZEND_ENGINE_2\nif (add_or_update == HASH_UPDATE) {\nfunc.op_array.doc_comment = estrndup(orig_fe->op_array.doc_comment, orig_fe->op_array.doc_comment_len);\nfunc.op_array.doc_comment_len = orig_fe->op_array.doc_comment_len;\n}\n#endif\n~" runkit_methods.c`
