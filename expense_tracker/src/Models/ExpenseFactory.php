<?php

declare(strict_types=1);

namespace App\Models;

use InvalidArgumentException;

/**
 * Membuat objek Expense yang tepat (CashExpense atau CreditExpense)
 * dari input form atau baris database.
 *
 * Hanya field yang di-whitelist yang dibaca, sehingga input
 * tambahan (id, created_at, dst) dari klien diabaikan.
 */
final class ExpenseFactory
{
    public const TYPES = [CashExpense::TYPE, CreditExpense::TYPE];

    /**
     * @param array<string, mixed> $input biasanya $_POST
     * @throws InvalidArgumentException jika type tidak dikenal
     */
    public static function fromInput(array $input, ?int $id = null, ?string $createdAt = null): Expense
    {
        $type = self::str($input, 'type') ?? CashExpense::TYPE;

        $expense = match ($type) {
            CashExpense::TYPE => new CashExpense(
                self::str($input, 'name') ?? '',
                self::str($input, 'amount') ?? '',
                self::str($input, 'category') ?? '',
                self::str($input, 'date') ?? '',
                self::str($input, 'note'),
                self::str($input, 'cash_received'),
            ),
            CreditExpense::TYPE => new CreditExpense(
                self::str($input, 'name') ?? '',
                self::str($input, 'amount') ?? '',
                self::str($input, 'category') ?? '',
                self::str($input, 'date') ?? '',
                self::str($input, 'note'),
                self::str($input, 'card_name'),
                self::str($input, 'installments') ?? '',
                self::str($input, 'interest_rate') ?? '',
                self::str($input, 'due_date'),
            ),
            default => throw new InvalidArgumentException('Tipe pengeluaran tidak valid.'),
        };

        $expense->setId($id);
        $expense->setTimestamps($createdAt, null);

        return $expense;
    }

    /**
     * @param array<string, mixed> $row baris dari tabel expenses (opsional dengan category_name)
     */
    public static function fromRow(array $row): Expense
    {
        $type = (string) $row['type'];

        $expense = match ($type) {
            CashExpense::TYPE => new CashExpense(
                (string) $row['name'],
                (int) $row['amount'],
                (string) $row['category'],
                (string) $row['date'],
                $row['note'] === null ? null : (string) $row['note'],
                $row['cash_received'] === null ? null : (int) $row['cash_received'],
            ),
            CreditExpense::TYPE => new CreditExpense(
                (string) $row['name'],
                (int) $row['amount'],
                (string) $row['category'],
                (string) $row['date'],
                $row['note'] === null ? null : (string) $row['note'],
                $row['card_name'] === null ? null : (string) $row['card_name'],
                (int) ($row['installments'] ?? 1),
                (float) ($row['interest_rate'] ?? 0),
                $row['due_date'] === null ? null : (string) $row['due_date'],
            ),
            default => throw new InvalidArgumentException('Tipe tersimpan tidak dikenal: ' . $type),
        };

        $expense->setId((int) $row['id']);
        $expense->setCategoryName(isset($row['category_name']) ? (string) $row['category_name'] : null);
        $expense->setTimestamps((string) $row['created_at'], (string) $row['updated_at']);

        return $expense;
    }

    /** @param array<string, mixed> $input */
    private static function str(array $input, string $key): ?string
    {
        $value = $input[$key] ?? null;
        return is_string($value) ? $value : null;
    }
}
