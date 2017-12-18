<?php

namespace YaMoney\ConfigParser;

/**
 * Класс описания анонимного класса
 *
 * @package YaMoney\ConfigParser
 */
class InternalClassDefinition extends ClassDefinition
{
    /**
     * @param string $parentClassName Имя класса в котором объявлен класс
     * @param string $propertyName Имя свойства для которого создаётся анонимный класс
     */
    public function __construct($parentClassName, $propertyName = '')
    {
        parent::__construct($parentClassName . ucfirst($propertyName));
    }
}
