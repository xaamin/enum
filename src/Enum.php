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

        $this->name = $name;
        $this->value = $value;

        if ($name !== null && $value === null) {
            $entry = $this->getValueFromCache($name);

            $this->value = $entry['value'];
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
     * Check is 2 enums are the same
     *
     * @param Enum|string $name
     * @param string|null $value
     * @return boolean
     */
    protected function isEqual($name, $value = null)
    {
        if ($name instanceof Enum) {
            $value = $name->getName();
            $name = $this->getName();
        } else if ($value === null) {
            $value = $name;
            $name = $this->getName();
        }

        if ($value instanceof Enum) {
            $value = $value->getName();
        }

        $entry = (new static())->getValueFromCache($name);

        $internal = $entry['name'];

        return strtoupper($internal) === strtoupper($value);
    }

    public static function search($value)
    {
        $entry = (new static())->searchForValue($value);

        return $entry;
    }

    public static function keys()
    {
        $callback = function ($value) {
            return $value['name'];
        };

        return array_map($callback, array_values((new static())->resolve()));
    }

    public static function values()
    {
        $callback = function ($value) {
            return $value['value'];
        };

        return array_map($callback, array_values((new static())->resolve()));
    }

    public static function all()
    {
        return static::toArray();
    }

    public static function toArray()
    {
        $cached = (new static())->resolve();
        $array = [];

        foreach (array_values($cached) as $value) {
            $array[$value['name']] = $value['value'];
        };

        return $array;
    }

    public function __toString()
    {
        return $this->getValue();
    }

    protected function fill(array $enum)
    {
        if (isset(static::$cached[static::class])) {
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

    protected function resolve($name = null)
    {
        $name = strtoupper($name);

        if (!isset(static::$cached[static::class])) {
            static::$cached[static::class] = [];
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

    protected function searchForValue($value)
    {
        $key = null;
        $cached = static::$cached[static::class];

        foreach ($cached as $entry) {
            $internal = $entry['value'];

            if (strtoupper($internal) === strtoupper($value)) {
                $key = $entry;

                break;
            }
        }

        return $key ? new static($entry['name'], $entry['value']) : null;
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
            return $this->isEqual(substr($name, 2), isset($arguments[0]) ? $arguments[0] : null);
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
        if (static::startsWith($name, 'is') && !isset($arguments[0])) {
            throw new InvalidArgumentException('Calling '. static::class .'::'. $name .'() in static context requires one argument');
        }

        $instance = null;

        if (empty($arguments)) {
            $instance = new static();
        } else {
            $instance = new static($arguments[0], isset($arguments[1]) ? $arguments[1] : null);
        }

        return call_user_func_array([$instance, $name], $arguments);
    }
}
