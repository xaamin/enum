<?php
namespace Xaamin\Enum;

use BadMethodCallException;
use InvalidArgumentException;

class Enum
{
    protected static $cached = [];
    protected static $reflection = [];
    protected $enum = [];

    protected $name;
    protected $value;

    public function __construct($name = null, $value = null)
    {
        $this->fill($this->enum);

        $this->value = $value;

        if ($name !== null) {
            $entry = $this->getValueFromCache($name);

            $this->name = $entry['name'];
            $this->value = $value === null ? $entry['value'] : $value;
        }
    }

    public static function make($name = null, $value = null)
    {
        return new static($name, $value);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }

    /**
     * Check if actual enum matches the expected one
     *
     * @param Enum|string $name
     * @return boolean
     */

    public function equals($name)
    {
        return $this->isEqual($name);
    }

    /**
     * Check is the enums is the expected one
     *
     * @param Enum|string $name
     * @return boolean
     */
    protected function isEqual($name)
    {
        if ($name instanceof Enum) {
            $name = $name->getName();
        }

        return strtoupper($name) === strtoupper($this->getName());
    }

    public static function search($value)
    {
        $key = null;
        $cached = static::resolve();

        foreach ($cached as $entry) {
            $internal = $entry['value'];

            if (strtoupper($internal) === strtoupper($value)) {
                $key = $entry;

                break;
            }
        }

        return $key ? new static($entry['name'], $entry['value']) : null;
    }

    public static function keys()
    {
        $callback = function ($value) {
            return $value['name'];
        };

        return array_map($callback, array_values(static::resolve()));
    }

    public static function values()
    {
        $callback = function ($value) {
            return $value['value'];
        };

        return array_map($callback, array_values(static::resolve()));
    }

    public static function all()
    {
        return static::toArray();
    }

    public static function toArray()
    {
        $cached = static::resolve();
        $array = [];

        foreach (array_values($cached) as $value) {
            $array[$value['name']] = $value['value'];
        };

        return $array;
    }

    public function toString()
    {
        return $this->getValue();
    }

    protected function fill(array $enum = [])
    {
        if (!empty(static::$cached[static::class])) {
            return;
        }

        $this->resolve();

        foreach ($enum as $key => $value) {
            if (is_array($value)) {
                $this->setValueForName($key, $value['value'], isset($value['meta']) ? $value['meta'] : null);
            } else {
                $this->setValueForName($key, $value);
            }
        }
    }

    protected static function resolve($name = null)
    {
        $name = strtoupper($name);

        if (!isset(static::$cached[static::class])) {
            static::$cached[static::class] = [];

            (new static)->fill();
        }

        if (!$name) {
            return static::$cached[static::class];
        }

        if (!isset(static::$cached[static::class][$name])) {
            static::$cached[static::class][$name] = [];
        }

        return static::$cached[static::class][$name];
    }

    protected function getValueFromCache($name)
    {
        $entry = $this->resolve($name);

        if (empty($entry)) {
            $message = 'The name for an enum must be a string but ' . gettype($name) . ' given';

            if (is_string($name)) {
                $message = "The given name [$name] is not available in this enum " . static::class;
            }

            throw new InvalidArgumentException($message);
        }

        return $entry;
    }

    protected function makeValueForName($name, $value)
    {
        return [
            'name' => $name,
            'value' => $value,
        ];
    }

    protected function setValueForName($name, $value)
    {
        $alias = strtoupper($name);

        static::$cached[static::class][$alias] = $this->makeValueForName($name, $value);
    }

    protected static function startsWith(string $haystack, string $needle)
    {
        return strlen($haystack) > 2 && strpos($haystack, $needle) === 0;
    }

    public function __call(string $name, array $arguments)
    {
        if ($this->startsWith($name, 'is')) {
            $name = !isset($arguments[0]) ? substr($name, 2) : $arguments[0];

            return $this->isEqual($name);
        }

        if (!empty($arguments)) {
            return new static($name, $arguments[0], isset($arguments[1]) ? $arguments[1] : null);
        } else {
            $entry = $this->getValueFromCache($name);

            return new static($entry['name'], $entry['value']);
        }

        throw new BadMethodCallException('Call to undefined method '. static::class .'->'. $name .'()');
    }

    public static function __callStatic($name, $arguments)
    {
        $method = $name;
        $startsWithIs = static::startsWith($name, 'is');

        if ($startsWithIs && !isset($arguments[0])) {
            throw new InvalidArgumentException('Calling '. static::class .'::'. $name .'() in static context requires one argument');
        }

        $name = $startsWithIs ? substr($name, 2) : $name;

        $instance = new static($name);

        return call_user_func_array([$instance, $method], $arguments);
    }

    public function __toString()
    {
        return $this->getValue();
    }
}
