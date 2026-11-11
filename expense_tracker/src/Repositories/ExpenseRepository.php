<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Expense;
use App\Models\ExpenseFactory;
use App\Models\ExpenseFilter;
use PDO;

/**
 * Akses data tabel expenses. Semua query memakai prepared statement.
 */
final class ExpenseRepository
{
    private const CHILD_COLUMNS = [
        'cash_received' => null,
        'card_name'     => null,
        'installments'  => null,
        'interest_rate' => null,
        'due_date'      => null,
    ];

    public function __construct(private PDO $pdo)
    {
    }

    /** @return Expense[] */
    public function findAll(ExpenseFilter $filter): array
    {
        $sql = 'SELECT e.*, c.name AS category_name
                FROM expenses e
                JOIN categories c ON c.slug = e.category';
        $where  = [];
        $params = [];

        if ($filter->getSearch() !== '') {
            $where[] = "e.name LIKE :search ESCAPE '\\'";
            $params[':search'] = '%' . self::escapeLike($filter->getSearch()) . '%';
        }
        if ($filter->getCategory() !== '') {
            $where[] = 'e.category = :category';
            $params[':category'] = $filter->getCategory();
        }
        if ($filter->getMonth() !== '') {
            // Perbandingan string pada tanggal ISO, dapat memakai idx_expenses_date.
            $where[] = 'e.date BETWEEN :from AND :to';
            $params[':from'] = $filter->getMonth() . '-01';
            $params[':to']   = $filter->getMonth() . '-31';
        }
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $direction = $filter->getSort() === 'asc' ? 'ASC' : 'DESC';
        $sql .= " ORDER BY e.date $direction, e.id $direction";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return array_map([ExpenseFactory::class, 'fromRow'], $stmt->fetchAll());
    }

    public function find(int $id): ?Expense
    {
        $stmt = $this->pdo->prepare(
            'SELECT e.*, c.name AS category_name
             FROM expenses e
             JOIN categories c ON c.slug = e.category
             WHERE e.id = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : ExpenseFactory::fromRow($row);
    }

    /** @return int id baris baru */
    public function insert(Expense $expense): int
    {
        $now = gmdate('Y-m-d H:i:s');
        $row = array_merge(self::CHILD_COLUMNS, $expense->toRow());

        $stmt = $this->pdo->prepare(
            'INSERT INTO expenses
                (name, amount, category, type, date, note,
                 cash_received, card_name, installments, interest_rate, due_date,
                 created_at, updated_at)
             VALUES
                (:name, :amount, :category, :type, :date, :note,
                 :cash_received, :card_name, :installments, :interest_rate, :due_date,
                 :created_at, :updated_at)'
        );
        $stmt->execute(self::bind($row) + [':created_at' => $now, ':updated_at' => $now]);

        return (int) $this->pdo->lastInsertId();
    }

    /** @return bool false jika id tidak ada */
    public function update(Expense $expense): bool
    {
        $row = array_merge(self::CHILD_COLUMNS, $expense->toRow());

        $stmt = $this->pdo->prepare(
            'UPDATE expenses SET
                name = :name, amount = :amount, category = :category, type = :type,
                date = :date, note = :note,
                cash_received = :cash_received, card_name = :card_name,
                installments = :installments, interest_rate = :interest_rate,
                due_date = :due_date, updated_at = :updated_at
             WHERE id = :id'
        );
        $stmt->execute(self::bind($row) + [
            ':updated_at' => gmdate('Y-m-d H:i:s'),
            ':id'         => $expense->getId(),
        ]);

        return $stmt->rowCount() > 0;
    }

    /** @return bool false jika id tidak ada */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM expenses WHERE id = :id');
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }

    public function totalOverall(): int
    {
        return (int) $this->pdo->query('SELECT COALESCE(SUM(amount), 0) FROM expenses')->fetchColumn();
    }

    /** @param string $month format YYYY-MM */
    public function totalForMonth(string $month): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE date BETWEEN :from AND :to'
        );
        $stmt->execute([':from' => $month . '-01', ':to' => $month . '-31']);

        return (int) $stmt->fetchColumn();
    }

    /** @return array<int, array{month: string, total: int, count: int}> bulan terbaru dulu */
    public function totalsPerMonth(): array
    {
        $rows = $this->pdo->query(
            'SELECT substr(date, 1, 7) AS month, SUM(amount) AS total, COUNT(*) AS count
             FROM expenses
             GROUP BY month
             ORDER BY month DESC'
        )->fetchAll();

        return array_map(static fn (array $r): array => [
            'month' => (string) $r['month'],
            'total' => (int) $r['total'],
            'count' => (int) $r['count'],
        ], $rows);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed> kunci berawalan ':'
     */
    private static function bind(array $row): array
    {
        $params = [];
        foreach ($row as $key => $value) {
            $params[':' . $key] = $value;
        }
        return $params;
    }

    private static function escapeLike(string $term): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term);
    }
}
