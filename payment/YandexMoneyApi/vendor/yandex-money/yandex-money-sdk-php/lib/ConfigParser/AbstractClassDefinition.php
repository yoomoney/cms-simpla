<?php

namespace YaMoney\ConfigParser;

/**
 * Базовый класс описания класса и enum'a
 * 
 * @package YaMoney\ConfigParser
 */
abstract class AbstractClassDefinition extends AbstractDefinition
{
    /**
     * @var string Пространство имён класса
     */
    private $namespace;

    /**
     * @var AbstractClassDefinition|null Родительский класс текущего класса
     */
    private $parent;

    /**
     * @var bool Является ли класс абстрактным
     */
    private $abstract = false;

    /**
     * Возвращает имя класса
     * @param bool $full Вернуть ли полное имя класса
     * @return string Имя класса
     */
    public function getClassName($full = false)
    {
        return $full ? $this->getFullClassName() : $this->getName();
    }

    /**
     * Устаналивает пространство имён класса
     * @param string $value Пространство имён класса
     */
    public function setNamespace($value)
    {
        $this->namespace = str_replace('.', '\\', $value);
    }

    /**
     * Возвращает неймспейс текущего класса
     * @return string Неймспейс текущего класса
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Возвращает полное имя класса вместе с неймспейсом
     * @return string Полное имя класса
     */
    public function getFullClassName()
    {
        return $this->namespace . '\\' . $this->getName();
    }

    /**
     * Устанавливает родительский класс
     * @param AbstractClassDefinition $parent Описание родительского класса
     */
    public function setParent(AbstractClassDefinition $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Возвращает описание родительского класса
     * @return AbstractClassDefinition|null Опсиание родлительского класса, если оно есть, или null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Устанавливает флаг того, является ли текущий класс абстрактным
     * @param bool $value Флаг абстрактного класса
     */
    public function setAbstract($value)
    {
        $this->abstract = $value ? true : false;
    }

    /**
     * Проверяет, является ли текущий класс абстрактным
     * @return bool True если класс является абстрактым, false если нет
     */
    public function isAbstract()
    {
        return $this->abstract;
    }

    /**
     * Проверяет, является ли текущий объект описанием класса
     * @return bool True если описание класса, false если нет
     */
    public function isClass()
    {
        return true;
    }
}