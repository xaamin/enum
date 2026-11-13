<?php

use Xaamin\Enum\Enum;
use Xaamin\Enum\Tests\Fixtures\InvoiceStatus;
use Xaamin\Enum\Tests\Fixtures\Priority;

describe('creation', function () {
    it('creates an enum from its name', function () {
        $status = InvoiceStatus::make('paid');

        expect($status)->toBeInstanceOf(InvoiceStatus::class)
            ->and($status->getName())->toBe('paid')
            ->and($status->getValue())->toBe('invoice.paid');
    });

    it('creates an enum from a static method named after the entry', function () {
        $status = InvoiceStatus::overdue();

        expect($status)->toBeInstanceOf(InvoiceStatus::class)
            ->and($status->getName())->toBe('overdue')
            ->and($status->getValue())->toBe('invoice.overdue');
    });

    it('resolves names case-insensitively', function () {
        expect(InvoiceStatus::make('PAID')->getName())->toBe('paid')
            ->and(InvoiceStatus::make('Pending')->getValue())->toBe('invoice.pending');
    });

    it('allows overriding the value of an entry', function () {
        $status = InvoiceStatus::make('paid', 'custom');

        expect($status->getName())->toBe('paid')
            ->and($status->getValue())->toBe('custom');
    });

    it('creates an empty enum when no name is given', function () {
        $status = new InvoiceStatus();

        expect($status->getName())->toBeNull()
            ->and($status->getValue())->toBeNull();
    });

    it('creates an enum from an instance method named after the entry', function () {
        $status = InvoiceStatus::pending()->paid();

        expect($status->getName())->toBe('paid')
            ->and($status->getValue())->toBe('invoice.paid');
    });

    it('rejects unknown names', function () {
        InvoiceStatus::make('cancelled');
    })->throws(
        InvalidArgumentException::class,
        'The given name [cancelled] is not available in this enum ' . InvoiceStatus::class
    );

    it('rejects unknown static methods', function () {
        InvoiceStatus::cancelled();
    })->throws(InvalidArgumentException::class);

    it('rejects names that are not strings', function () {
        InvoiceStatus::make(['paid']);
    })->throws(TypeError::class);

    it('reads the value of entries declared with metadata', function () {
        expect(Priority::make('high')->getValue())->toBe(10)
            ->and(Priority::make('low')->getValue())->toBe(1);
    });
});

describe('validation', function () {
    it('tells whether a name is valid', function () {
        expect(InvoiceStatus::isValidName('paid'))->toBeTrue()
            ->and(InvoiceStatus::isValidName('PAID'))->toBeTrue()
            ->and(InvoiceStatus::isValidName('cancelled'))->toBeFalse();
    });
});

describe('comparison', function () {
    it('compares against names and other enums', function () {
        $status = InvoiceStatus::paid();

        expect($status->equals('paid'))->toBeTrue()
            ->and($status->equals('PAID'))->toBeTrue()
            ->and($status->equals('pending'))->toBeFalse()
            ->and($status->equals(InvoiceStatus::paid()))->toBeTrue()
            ->and($status->equals(InvoiceStatus::overdue()))->toBeFalse();
    });

    it('supports dynamic is methods on instances', function () {
        $status = InvoiceStatus::paid();

        expect($status->isPaid())->toBeTrue()
            ->and($status->isPending())->toBeFalse();
    });

    it('supports dynamic is methods in static context', function () {
        expect(InvoiceStatus::isPaid(InvoiceStatus::paid()))->toBeTrue()
            ->and(InvoiceStatus::isPaid(InvoiceStatus::pending()))->toBeFalse()
            ->and(InvoiceStatus::isPaid('paid'))->toBeTrue();
    });

    it('requires an argument for static is methods', function () {
        InvoiceStatus::isPaid();
    })->throws(
        InvalidArgumentException::class,
        'Calling ' . InvoiceStatus::class . '::isPaid() in static context requires one argument'
    );
});

describe('search', function () {
    it('finds an enum by its value', function () {
        $status = InvoiceStatus::search('invoice.overdue');

        expect($status)->toBeInstanceOf(InvoiceStatus::class)
            ->and($status->getName())->toBe('overdue')
            ->and($status->getValue())->toBe('invoice.overdue');
    });

    it('searches values case-insensitively', function () {
        expect(InvoiceStatus::search('INVOICE.PAID')->getName())->toBe('paid');
    });

    it('returns null when no value matches', function () {
        expect(InvoiceStatus::search('invoice.cancelled'))->toBeNull();
    });

    it('finds non string values', function () {
        expect(Priority::search(10)->getName())->toBe('high');
    });
});

describe('listing', function () {
    it('lists the names', function () {
        expect(InvoiceStatus::keys())->toBe(['pending', 'paid', 'overdue']);
    });

    it('lists the values', function () {
        expect(InvoiceStatus::values())->toBe(['invoice.pending', 'invoice.paid', 'invoice.overdue']);
    });

    it('exports names and values as an array', function () {
        $expected = [
            'pending' => 'invoice.pending',
            'paid' => 'invoice.paid',
            'overdue' => 'invoice.overdue',
        ];

        expect(InvoiceStatus::toArray())->toBe($expected)
            ->and(InvoiceStatus::all())->toBe($expected);
    });

    it('does not list unknown names after a failed lookup', function () {
        expect(InvoiceStatus::isValidName('cancelled'))->toBeFalse()
            ->and(fn () => InvoiceStatus::make('refunded'))->toThrow(InvalidArgumentException::class)
            ->and(InvoiceStatus::keys())->toBe(['pending', 'paid', 'overdue'])
            ->and(InvoiceStatus::values())->toBe(['invoice.pending', 'invoice.paid', 'invoice.overdue'])
            ->and(InvoiceStatus::search('invoice.refunded'))->toBeNull();
    });

    it('keeps the entries of each enum class apart', function () {
        expect(InvoiceStatus::keys())->toBe(['pending', 'paid', 'overdue'])
            ->and(Priority::keys())->toBe(['low', 'high'])
            ->and(Priority::toArray())->toBe(['low' => 1, 'high' => 10]);
    });
});

describe('string conversion', function () {
    it('casts to its value', function () {
        expect((string) InvoiceStatus::paid())->toBe('invoice.paid')
            ->and(InvoiceStatus::paid()->toString())->toBe('invoice.paid');
    });

    it('casts non string values', function () {
        expect((string) Priority::high())->toBe('10');
    });

    it('casts an empty enum to an empty string', function () {
        expect((string) new InvoiceStatus())->toBe('');
    });
});

it('is an enum', function () {
    expect(InvoiceStatus::paid())->toBeInstanceOf(Enum::class);
});
