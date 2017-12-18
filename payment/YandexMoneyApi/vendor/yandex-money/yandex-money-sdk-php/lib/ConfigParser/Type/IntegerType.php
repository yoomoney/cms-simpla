<?php

namespace YaMoney\ConfigParser\Type;

/**
 * Класс описания целочисленного типа
 *
 * @package YaMoney\ConfigParser\Type
 */
class IntegerType extends AbstractNumericType
{
    /**
     * Возвращает код для каста в нужный тип
     * @return string PHP код для каста данных в нужный тип
     */
    public function getCastDefinition()
    {
        return 'int';
    }

    /**
     * Возвращает имя текущего типа
     * @return string Имя текущего типа
     */
    public function getName()
    {
        return 'int';
    }
}