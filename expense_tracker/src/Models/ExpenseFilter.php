<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Parameter pencarian daftar pengeluaran (US-5, FR-6, FR-7, FR-8).
 * Nilai tidak valid diabaikan, bukan menyebabkan galat.
 */
final class ExpenseFilter
{
    public const SEARCH_MAX = 100;

    public function __construct(
        private string $search = '',
        private string $category = '',
        private string $month = '',
        private string $sort = 'desc',
    ) {
    }

    /** @param array<string, mixed> $query biasanya $_GET */
    public static function fromQuery(array $query): self
    {
        $search = is_string($query['search'] ?? null) ? trim($query['search']) : '';
        if (!mb_check_encoding($search, 'UTF-8')) {
            $search = '';
        }
        $search = mb_substr($search, 0, self::SEARCH_MAX);

        $category = is_string($query['category'] ?? null) ? mb_strtolower(trim($query['category'])) : '';
        if (preg_match('/^[a-z0-9-]{1,40}$/', $category) !== 1) {
            $category = '';
        }

        $month = is_string($query['month'] ?? null) ? trim($query['month']) : '';
        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month) !== 1) {
            $month = '';
        }

        $sort = ($query['sort'] ?? '') === 'asc' ? 'asc' : 'desc';

        return new self($search, $category, $month, $sort);
    }

    public function getSearch(): string
    {
        return $this->search;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getMonth(): string
    {
        return $this->month;
    }

    public function getSort(): string
    {
        return $this->sort;
    }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'search'   => $this->search,
            'category' => $this->category,
            'month'    => $this->month,
            'sort'     => $this->sort,
        ];
    }
}
