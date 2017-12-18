<?php

namespace YaMoney\ConfigParser;

/**
 * Базовый класс описания сущностей из файла конфига сваггера
 *
 * @package YaMoney\ConfigParser
 */
abstract class AbstractDefinition
{
    /**
     * @var string Имя сущности
     */
    private $name;

    /**
     * @var string|null Имя сущности в удобочитаемов виде
     */
    private $title;

    /**
     * @var string|null Описание сущности
     */
    private $desc;

    /**
     * @var string В какой части запроса/ответа сущность будет передаваться/приниматься
     */
    private $in;

    /**
     * Конструктор, принимает имя сущности
     * @param string $name Имя сущности
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Возвращает имя сущности
     * @return string Имя сущности
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Устанавливает название сущности
     * @param string $value Название сущности в удобо читаемом виде
     */
    public function setTitle($value)
    {
        $this->title = $value;
    }

    /**
     * Возвращает название сущности
     * @return string Название сущности в удобо читаемом виде
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Проверяет, установлено ли название сущности
     * @return bool True если название сущности установлено, false если нет
     */
    public function hasTitle()
    {
        return !empty($this->title);
    }

    /**
     * Устанавливает описание сущности
     * @param string $value Описание сущности
     */
    public function setDescription($value)
    {
        $this->desc = $value;
    }

    /**
     * Возвращает описание сущности
     * @return string Описание сущности
     */
    public function getDescription()
    {
        return $this->desc;
    }

    /**
     * Проверяет, установлено ли описание сущности
     * @return bool True если  описание сущности установлено, false если нет
     */
    public function hasDescription()
    {
        return !empty($this->desc);
    }

    /**
     * Возвращает информацию о том, в какой части запроса или отведа данные отправляются или получаются
     * @return string|null header|query|body - откуда данные поступаюит или куда отправляются
     */
    public function getIn()
    {
        return $this->in;
    }

    /**
     * Устанавливает информацию о том куда или откуда данные отправляются или поступают
     * @param string $value header|query|body - откуда данные поступаюит или куда отправляются
     * @return AbstractDefinition Инстанс текущего объекта
     */
    public function setIn($value)
    {
        $this->in = $value;
        return $this;
    }

    /**
     * Проверяет, является ли описание описанием enum'a
     * @return bool True если enum, false если нет
     */
    abstract public function isEnum();

    /**
     * Проверяет, является ли текущий объект описанием класса
     * @return bool True если описание класса, false если нет
     */
    abstract public function isClass();

    /**
     * Проверяет, является ли текущий объект описанием скалярного типа
     * @return bool True если описание скалярного типа, false если нет
     */
    public function isScalar()
    {
        return !$this->isClass();
    }
}