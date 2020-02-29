# PHP Enum implementation

This package offers enums in PHP. We don't use a simple "value" representation, so you're always working with the enum object. This allows for proper autocompletion and refactoring in IDEs.


## Why?

Using an enum instead of class constants provides the following advantages:

- You can enrich the enum with alias methods names (e.g. `draft()`, `published()`, …)
- You can extend the enum to add new values (make your enum `final` to prevent it)
- You can get a list of all the possible values (see below)

This Enum class is not intended to replace class constants, but only to be used when it makes sense.

## Installation

```
composer require xaamin/enum
```

## Declaration

```php
use Xaamin\Enum\Enum;

class InvoiceStatus extends Enum
{
    protected $enum = [
        'pending' => 'invoice.pending',
        'paid' => 'invoice.paid',
        'overdue' => 'invoice.overdue',
    ];
}
```

## Documentation

- `getName()` Returns the name of the current value on Enum
- `getValue()` Returns the current value of the enum
- `equals($enum)` Tests whether 2 enum instances are equal (returns `true` if enum values are equal, `false` otherwise)

Static methods:

- `toArray()` method Returns all possible values as an array (constant name in key, constant value in value)
- `keys()` Returns the names (keys) of all constants in the Enum class
- `values()` Returns instances of the Enum class of all Enum constants (constant name in key, Enum instance in value)
- `search()` Return key for the searched value

### Static methods

```php
class InvoiceStatus extends Enum
{
    protected $enum = [
        'pending' => 'invoice.pending',
        'paid' => 'invoice.paid',
        'overdue' => 'invoice.overdue',
    ];
}

// Static method:
$invoice = InvoiceStatus::pending();
$invoice = InvoiceStatus::paid();
```

You can use phpdoc for autocompletion, this is supported in some IDEs:

```php
/**
 * @method static self pending()
 * @method static self paid()
 * @method static self overdue()
 */
class InvoiceStatus extends Enum
{
    protected $enum = [
        'pending' => 'invoice.pending',
        'paid' => 'invoice.paid',
        'overdue' => 'invoice.overdue',
    ];
}
```

## Usage

This is how an enum can be defined.

```php
/**
 * @method static self pending()
 * @method static self paid()
 * @method static self overdue()
 */
class InvoiceStatus extends Enum
{
    protected $enum = [
        'pending' => 'invoice.pending',
        'paid' => 'invoice.paid',
        'overdue' => 'invoice.overdue',
    ];
}
```

This is how they are used:

```php
public function setInvoiceStatus(InvoiceStatus $invoice)
{
    $this->invoice = $invoice;
}

// ...

$class->setInvoiceStatus(InvoiceStatus::paid());
```

This is how get we can get the enum name from a value:

```php
$invoice = InvoiceStatus::search('invoice.overdue');

// $invoice is 'overdue'
```

### Creating an enum from a value

```php
$invoice = InvoiceStatus::make('paid');
```

### Comparing enums

Enums can be compared using the `equals` method:

```php
$invoice->equals($invoice);
```

You can also use dynamic `is` methods:

```php
// return a boolean
$invoice->isPaid();

// return a boolean
InvoiceStatus::isPaid($invoice);
```

Note that if you want auto completion on these `is` methods, you must add extra doc blocks on your enum classes.

### Search

You can search enum with its textual value:

```php
$invoice = InvoiceStatus::search('invoice.pending');

// $invoice is 'paid'
```