<?php

namespace YaMoney\ConfigParser;

class RouteParser
{
    private $parser;
    private $typeFactory;
    
    public function __construct(Parser $parser, TypeFactory $factory)
    {
        $this->parser = $parser;
        $this->typeFactory = $factory;
    }

    public function parseRoute($config, $route)
    {
        if (!array_key_exists($route, $config)) {
            throw new \RuntimeException('Route "'.$route.'" not exists');
        }
        foreach ($config[$route] as $method => $options) {
            list($namespace, $className) = $this->resolveRequestClassName($route, $method, count($config[$route]));
            $class = $this->parser->registerClass($className, 'paths', $namespace);
            $this->parseConcreteRoute($class, $options);
        }
    }

    private function parseConcreteRoute(ClassDefinition $class, $options)
    {
        $requestType = null;
        if (!empty($options['parameters'])) {
            $requestType = 'header';
            foreach ($options['parameters'] as $param) {
                if (empty($param['$ref'])) {
                    if (empty($param['in'])) {
                        throw new \RuntimeException('Unknown parameter ');
                    }
                    $in = $param['in'];
                } else {
                    $def = $this->parser->resolveRef($param['$ref']);
                    $in = $def->getIn();
                }
                if ($requestType === 'header') {
                    $requestType = $in;
                } elseif ($requestType === 'path' && $in !== 'header') {
                    $requestType = $in;
                } elseif ($in === 'body') {
                    $requestType = 'body';
                }
            }
            $class->setIn($requestType);
            foreach ($options['parameters'] as $param) {
                if ($requestType !== 'body') {
                    if (empty($param['$ref'])) {
                        if ($param['in'] === $requestType) {
                            $this->addClassProperty($class, $param);
                        }
                    } else {
                        $def = $this->parser->resolveRef($param['$ref']);
                        if ($def->getIn() === $requestType) {
                            $this->addClassProperty($class, $param);
                        }
                    }
                } else {
                    if (empty($param['$ref'])) {
                        if ($param['in'] === $requestType) {
                            if (isset($param['schema'])) {
                                $schema = $param['schema']['properties'];
                                foreach ($schema as $propertyName => $option) {
                                    $option['name'] = $propertyName;
                                    $this->addClassProperty($class, $option);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Добавляет в описание класса описание его свойства
     * @param ClassDefinition $class Объект с описанием класса
     * @param array $option Массив настроек свойства
     */
    private function addClassProperty(ClassDefinition $class, $option)
    {
        if (!empty($option['$ref'])) {
            $def = $this->parser->resolveRef($option['$ref']);
            $propertyName = lcfirst($def->getName());
        } else {
            $propertyName = lcfirst($option['name']);
        }

        $property = new PropertyDefinition($propertyName);
        if (!isset($option['$ref'])) {
            if (isset($option['type'])) {
                if ($option['type'] === 'object') {
                    $className = $class->getClassName() . ucfirst($propertyName);
                    $childClass = $this->parser->registerClass(
                        $className, 'path/inline/' . $className, $class->getNamespace()
                    );
                    $this->parser->parseClass($childClass, $option);
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
            $typeClass = $this->parser->resolveRef($option['$ref']);
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

    private function resolveRequestClassName($path, $method, $count)
    {
        $parts = explode('/', substr($path, 1));
        $ns = array(
            'Request',
        );
        for ($i = 0; $i < count($parts) - 1; $i++) {
            $ns[] = $this->normalizeClassNamePart($parts[$i]);
        }
        if ($count > 1) {
            $ns[] = $this->normalizeClassNamePart($parts[$i]);
        }
        $className = '';
        if ($method === 'post') {
            $className .= 'Create';
        }
        $className .= $this->normalizeClassNamePart($parts[$i]) . 'Request';

        return array(
            implode('\\', $ns),
            $className
        );
    }

    private function normalizeClassNamePart($value)
    {
        $value = str_replace(array('{', '}'), array('', ''), $value);
        if (substr($value, -3) === '_id') {
            $value = substr($value, 0, -3);
        }
        return ucfirst(
            preg_replace_callback('/\_(\w)/', function ($matches) {
                return ucfirst($matches[1]);
            }, $value)
        );
    }
}