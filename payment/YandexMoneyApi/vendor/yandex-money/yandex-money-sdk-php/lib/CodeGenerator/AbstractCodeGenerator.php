<?php

namespace YaMoney\CodeGenerator;

/**
 * Базовый класс генератора кода
 *
 * @package YaMoney\CodeGenerator
 */
abstract class AbstractCodeGenerator
{
    /**
     * Возвращает смещение строк в виде пробелов
     * @param int $depth Смещение строк в виде числа (количество отступов/табов)
     * @return string Смещение в виде строки
     */
    protected function getPadding($depth)
    {
        if ($depth === 0) {
            return '';
        }
        return str_pad(' ', $depth * 4);
    }
}