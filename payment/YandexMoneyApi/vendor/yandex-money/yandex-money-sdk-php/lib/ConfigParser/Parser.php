<?php

namespace YaMoney\ConfigParser;

/**
 * Класс парсера конфига API
 *
 * @package YaMoney\ConfigParser
 */
class Parser
{
    /**
     * @var AbstractDefinition[] Массив загруженных описаний классов/именованных скалярных типов
     */
    private $allDefinitions;

    /**
     * @var AbstractDefinition[] Массив загруженных описаний классов/именованных скалярных типов
     */
    private $refs;

    /**
     * @var TypeFactory Фабрика типов объектов
     */
    private $typeFactory;

    /**
     * @var RouteParser Парсер роутов в конфиге API
     */
    private $routeParser;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->allDefinitions = array();
        $this->typeFactory = new TypeFactory();
        $this->routeParser = new RouteParser($this, $this->typeFactory);
    }

    /**
     * Парсит конфиг API и возвращает массив загруженных из конфига объектов
     * @param array $config Конфиг API
     * @param string $namespace Имя пространства имен, в котором генерируются класса
     * @return AbstractDefinition[] Массив загруженных из конфига описаний классов/типов
     */
    public function parse(array $config, $namespace)
    {
        $this->refs = array();

        $this->registerDefinitionsList($config, 'definitions', $namespace);
        $this->registerDefinitionsList($config, 'parameters', $namespace);
        //$this->registerDefinitionsList($config, 'responses');
        // $this->parsePaths($config);

        $this->parseDefinitionList($config, 'definitions');
        $this->parseDefinitionList($config, 'parameters');
        //$this->parseDefinitionList($config, 'responses');
        
        foreach ($config['paths'] as $route => $options) {
            $this->routeParser->parseRoute($config['paths'], $route);
        }

        return $this->allDefinitions;
    }

    /**
     * Регистрирует все объявленные объекты в указанном подмассиве конфига
     * @param array $list Конфиг
     * @param string $ref Имя ключа в конфиге
     * @param string $namespace Используемое пространство имён классов
     */
    protected function registerDefinitionsList($list, $ref, $namespace)
    {
        foreach ($list[$ref] as $className => $options) {
            if (!empty($options['enum'])) {
                $this->registerEnum($className, $ref, $namespace);
            } elseif (isset($options['type']) || isset($options['allOf'])) {
                if (isset($options['allOf']) || $options['type'] === 'object') {
                    $this->registerClass($className, $ref, $namespace);
                } else {
                    $this->registerScalarDefinition($className, $ref);
                }
            }
        }
    }

    /**
     * Парсит описания объектов и забивает их настройками
     * @param array $list Конфиг API
     * @param string $ref Имя ключа конфига, который разбирается
     */
    protected function parseDefinitionList($list, $ref)
    {
        foreach ($list[$ref] as $className => $options) {
            $def = $this->allDefinitions[$className];
            if ($def->isEnum()) {
                $this->parseEnum($def, $options);
            } elseif ($def->isClass()) {
                $this->parseClass($def, $options);
            } else {
                $this->parseScalarDefinition($def, $options);
            }
        }
    }

    /**
     * Находит описание класса/типа по его $ref ссылке
     * @param string $link $ref ссылка на объект
     * @return null|AbstractDefinition Найденный объект с описанием или null если он не найден
     */
    public function resolveRef($link)
    {
        if ($link[0] !== '#' || $link[1] !== '/') {
            return null;
        }
        $index = substr($link, 2);
        return array_key_exists($index, $this->refs) ? $this->refs[$index] : null;
    }

    /**
     * Регистрирует и возвращает enum
     * @param string $className Имя enum'a
     * @param string $parentRef $ref ссылка до enum'a
     * @param string $namespace Пространство имён, в котором enum объявляется
     * @return EnumDefinition Объект описания enum'a
     */
    public function registerEnum($className, $parentRef, $namespace)
    {
        $enum = new EnumDefinition($className);
        $enum->setNamespace($namespace);
        $this->typeFactory->registerEnum($enum);
        $this->allDefinitions[$className] = $enum;
        $this->refs[$parentRef . '/' . $className] = $enum;

        return $enum;
    }

    /**
     * Регистрирует и возвращает класс
     * @param string $className Имя класса
     * @param string $parentRef $ref ссылка до класса
     * @param string $namespace Пространство имён, в котором класс объявляется
     * @return ClassDefinition Объект описания класса
     */
    public function registerClass($className, $parentRef, $namespace = null)
    {
        $class = new ClassDefinition($className);
        if ($namespace !== null) {
            $class->setNamespace($namespace);
        }
        $this->typeFactory->registerClass($class);
        $this->allDefinitions[$className] = $class;
        $this->refs[$parentRef . '/' . $className] = $class;

        return $class;
    }

    /**
     * Регистрирует описание скалярного типа
     * @param string $className Имя скаляра
     * @param string $parentRef $ref ссылка до скаляра
     * @return ScalarParameterDefinition Объект описания скалярного типа
     */
    public function registerScalarDefinition($className, $parentRef)
    {
        $def = new ScalarParameterDefinition(ucfirst($className));
        $this->typeFactory->registerScalar($def);
        $this->allDefinitions[$className] = $def;
        $this->refs[$parentRef . '/' . $className] = $def;

        return $def;
    }

    /**
     * Парсит enum, набивает его свойствами
     * @param EnumDefinition $enum Объект описания enum'a
     * @param array $options Свойства enum'a взятые из конфига API
     */
    public function parseEnum(EnumDefinition $enum, $options)
    {
        if (!empty($options['title'])) {
            $enum->setTitle($options['title']);
        }
        if (!empty($options['description'])) {
            $enum->setDescription($options['description']);
        }
        if (!empty($options['in'])) {
            $enum->setIn($options['in']);
        }
        $enum->setValues($options['enum']);
    }

    /**
     * Устанавливает настройки класса
     * @param ClassDefinition $class Инстанс описания класса
     * @param array $options Настройки класса, полученные из конфига API
     */
    public function parseClass(ClassDefinition $class, $options)
    {
        if (!empty($options['title'])) {
            $class->setTitle($options['title']);
        }
        if (!empty($options['description'])) {
            $class->setDescription($options['description']);
        }
        if (!empty($options['in'])) {
            $class->setIn($options['in']);
        }

        if (!empty($options['discriminator'])) {
            $class->setAbstract(true);
        }

        if (!empty($options['properties'])) {
            if (!empty($options['required'])) {
                foreach ($options['required'] as $propertyName) {
                    if (isset($options['properties'][$propertyName])) {
                        $options['properties'][$propertyName][PropertyOptions::REQUIRED] = true;
                    }
                }
            }
            foreach ($options['properties'] as $propertyName => $option) {
                $this->addClassProperty($class, $propertyName, $option);
            }
        } elseif (!empty($options['allOf'])) {
            foreach ($options['allOf'] as $allOf) {
                if (isset($allOf['$ref'])) {
                    $class->setParent($this->resolveRef($allOf['$ref']));
                } elseif (isset($allOf['type']) && $allOf['type'] === 'object') {
                    foreach ($allOf['properties'] as $propertyName => $option) {
                        if (!$class->getParent()->hasProperty($propertyName)) {
                            $this->addClassProperty($class, $propertyName, $option);
                        }
                    }
                } else {
                    throw new \RuntimeException('Invalid allOf array: ' . json_encode($options));
                }
            }
        }
    }

    /**
     * Добавляет в описание класса описание его свойства
     * @param ClassDefinition $class Объект с описанием класса
     * @param string $propertyName Имя добавляемого свойства
     * @param array $option Массив настроек свойства
     */
    protected function addClassProperty(ClassDefinition $class, $propertyName, $option)
    {
        if (isset($option['allOf'])) {
            //$option = $option['allOf'];
            foreach ($option['allOf'] as $item) {
                foreach ($item as $key => $value) {
                    $option[$key] = $value;
                }
            }
            unset($option['allOf']);
        }

        $property = new PropertyDefinition($propertyName);
        if (!isset($option['$ref'])) {
            if (isset($option['type'])) {
                if ($option['type'] === 'object') {
                    $className = $class->getClassName() . ucfirst($propertyName);
                    $childClass = $this->registerClass(
                        $className, 'definitions/' . $class->getClassName(), $class->getNamespace()
                    );
                    $this->parseClass($childClass, $option);
                    $type = $this->typeFactory->factoryType($className, $option);
                } else {
                    $type = $this->typeFactory->factoryType($option['type'], $option);
                }
                $property->setOptions($option);
                $property->setType($type);
                if (!empty($option['title'])) {
                    $property->setTitle($option['title']);
                }
                if (!empty($option['description'])) {
                    $property->setDescription($option['description']);
                }
                $class->addProperty($property);
            } else {
                var_dump($option);
            }
        } else {
            $typeClass = $this->resolveRef($option['$ref']);
            $type = $this->typeFactory->factoryType($typeClass->getName(), array());
            $property->setType($type);
            $property->setOptions($option);
            if (!empty($option['title'])) {
                $property->setTitle($option['title']);
            }
            if (!empty($option['description'])) {
                $property->setDescription($option['description']);
            }
            $class->addProperty($property);
        }
    }

    /**
     * Парсит именованное описание скалярного типа, устанавливает его настройки
     * @param ScalarParameterDefinition $def Объект с описанием именованного скалярного типа
     * @param array $option Настройки объекта, полученные из конфига API
     */
    private function parseScalarDefinition(ScalarParameterDefinition $def, $option)
    {
        if (isset($option['type'])) {
            $type = $this->typeFactory->factoryType($option['type'], $option);
            $def->setType($type);
            $def->setOptions($option);
            if (!empty($option['title'])) {
                $def->setTitle($option['title']);
            }
            if (!empty($option['description'])) {
                $def->setDescription($option['description']);
            }
            if (!empty($option['in'])) {
                $def->setIn($option['in']);
            }
        } else {
            throw new \RuntimeException('Unknown scalar parameter: ' . json_encode($option));
        }
    }

    private function parsePaths($config)
    {
        foreach ($config['paths'] as $path => $pathOptions) {
            foreach ($pathOptions as $method => $methodOptions) {
                $namespace = $this->getRequestNamespace($path, $method, count($pathOptions));
                $className = $this->getRequestClassName($path, $method, count($pathOptions));
                foreach ($methodOptions['parameters'] as $parameterOptions) {
                    if (isset($parameterOptions['$ref'])) {
                        $type = $this->resolveRef($parameterOptions['$ref']);
                    } else {
                        if (isset($parameterOptions['type'])) {
                            $type = $this->typeFactory->factoryType($parameterOptions['type'], $parameterOptions);
                        } elseif (isset($parameterOptions['schema'])) {
                            $options = $parameterOptions['schema'];

                            var_dump($parameterOptions);
                        }
                    }
                }
                var_dump($namespace . '\\' . $className);
            }
        }
    }

    private function getRequestNamespace($path, $method, $count)
    {
        $ns = 'YaMoney\\Request';
        $parts = explode('/', substr($path, 1));
        if ($count === 1 && $method === 'get') {
            for ($i = 0; $i < count($parts) - 1; $i++) {
                $value = str_replace(array('{', '}'), array('', ''), $parts[$i]);
                if (substr($value, -3) === '_id') {
                    $value = substr($value, 0, -3);
                }
                $ns .= '\\' . ucfirst(
                        preg_replace_callback('/\_(\w)/', function ($m) {
                            return strtoupper($m[1]);
                        }, $value)
                    );
            }
        } else {
            for ($i = 0; $i < count($parts); $i++) {
                $value = str_replace(array('{', '}'), array('', ''), $parts[$i]);
                if (substr($value, -3) === '_id') {
                    $value = substr($value, 0, -3);
                }
                $ns .= '\\' . ucfirst(
                        preg_replace_callback('/\_(\w)/', function ($m) {
                            return strtoupper($m[1]);
                        }, $value)
                    );
            }
        }
        return $ns;
    }

    private function getRequestClassName($path, $method, $count)
    {
        $parts = explode('/', substr($path, 1));
        $last = str_replace(array('{', '}'), array('', ''), end($parts));
        if (substr($last, -3) === '_id') {
            $last = substr($last, 0, -3);
        }
        $className = ucfirst(
            preg_replace_callback('/\_(\w)/', function($m) {
                return strtoupper($m[1]);
            }, $last)
        );
        if ($method === 'post') {
            $className = 'Create' . $className;
        }
        return $className . 'Request';
    }
}
