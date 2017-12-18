<?php

namespace YaMoney\ConfigParser\Type;

/**
 * Класс описания массива байт
 *
 * @package YaMoney\ConfigParser\Type
 */
class ByteArrayType extends AbstractStringType
{
    /**
     * Возвращает код для определения длины строки
     * @param string $variableName Имя параметра в коде
     * @return string PHP код в виде строки для определения длины строки
     */
    protected function getLengthFunction($variableName)
    {
        return 'strlen($'.$variableName.')';
    }
}
