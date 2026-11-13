# PHP Enum implementation

This package offers enums in PHP. We don't use a simple "value" representation, so you're always working with the enum object. This allows for proper type hinting, autocompletion and refactoring in IDEs.

## Why?

Using an enum instead of class constants provides the following advantages:

- You can type hint the enum: `function setStatus(InvoiceStatus $status)`
- You can enrich the enum with alias method names (e.g. `pending()`, `paid()`, …)
- You can extend the enum to add new values (make your enum `final` to prevent it)
- You can get a list of all the possible values (see below)

This Enum class is not intended to replace class constants, but only to be used when it makes sense.

## Installation

```bash
composer require xaamin/enum
```

## Declaration

Extend `Xaamin\Enum\Enum` and declare the available entries in the `$enum` property (name as key, value as value). Add `@method` doc blocks to get IDE autocompletion for the static constructors and the `is*` methods.

```php
use Xaamin\Enum\Enum;

/**
 * @method static self pending()
 * @method static self paid()
 * @method static self overdue()
 * @method bool isPending()
 * @method bool isPaid()
 * @method bool isOverdue()
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

### Creating an instance

```php
$status = InvoiceStatus::paid();        // static alias method
$status = InvoiceStatus::make('paid');  // from a name
$status = new InvoiceStatus('paid');

// Names are case insensitive
$status = InvoiceStatus::PAID();

// Unknown names throw an InvalidArgumentException
InvoiceStatus::make('refunded');
// The given name [refunded] is not available in this enum InvoiceStatus
```

Use it as a type hint:

```php
public function setInvoiceStatus(InvoiceStatus $status)
{
    $this->status = $status;
}

$invoice->setInvoiceStatus(InvoiceStatus::paid());
```

### Reading the name and value

```php
$status = InvoiceStatus::paid();

$status->getName();   // 'paid'
$status->getValue();  // 'invoice.paid'
$status->toString();  // 'invoice.paid'
(string) $status;     // 'invoice.paid'
```

### Comparing enums

```php
$status = InvoiceStatus::paid();

$status->equals(InvoiceStatus::paid());   // true
$status->equals('paid');                  // true, accepts a name (case insensitive)
$status->equals(InvoiceStatus::pending()); // false

// Dynamic "is" methods
$status->isPaid();                  // true
$status->isPending();               // false
InvoiceStatus::isPaid($status);     // true, static form requires the enum as argument
```

### Listing the entries

```php
InvoiceStatus::keys();
// ['pending', 'paid', 'overdue']

InvoiceStatus::values();
// ['invoice.pending', 'invoice.paid', 'invoice.overdue']

InvoiceStatus::toArray(); // or InvoiceStatus::all()
// ['pending' => 'invoice.pending', 'paid' => 'invoice.paid', 'overdue' => 'invoice.overdue']
```

### Searching by value

`search()` finds an entry by its value (case insensitive) and returns an enum instance, or `null` when nothing matches.

```php
$status = InvoiceStatus::search('invoice.overdue');

$status->getName(); // 'overdue'

InvoiceStatus::search('invoice.unknown'); // null
```

### Validating a name

```php
InvoiceStatus::isValidName('paid');     // true
InvoiceStatus::isValidName('refunded'); // false
```

## API reference

| Method | Description |
| --- | --- |
| `Enum::make($name)` / `Enum::{name}()` | Creates an instance for the given name |
| `getName()` | Name of the current entry |
| `getValue()` | Value of the current entry |
| `toString()` / `__toString()` | Value of the current entry as string |
| `equals($enum)` | Compares against another instance or a name |
| `is{Name}()` | Checks whether the instance is the given entry |
| `Enum::keys()` | List of names |
| `Enum::values()` | List of values |
| `Enum::toArray()` / `Enum::all()` | Names as keys, values as values |
| `Enum::search($value)` | Instance for the given value or `null` |
| `Enum::isValidName($name)` | Whether the name exists in the enum |

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
