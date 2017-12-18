<?php

namespace YaMoney\ConfigParser\Type;

/**
 * Класс описания типа числа с плавающей точкой
 *
 * @package YaMoney\ConfigParser\Type
 */
class FloatType extends AbstractNumericType
{
    /**
     * Возвращает код для каста в нужный тип
     * @return string PHP код для каста данных в нужный тип
     */
    public function getCastDefinition()
    {
        return 'float';
    }

    /**
     * Возвращает имя текущего типа
     * @return string Имя текущего типа
     */
    public function getName()
    {
        return 'float';
    }
}
