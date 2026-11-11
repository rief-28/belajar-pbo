<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Kelas anak (child class) untuk pengeluaran memakai kartu kredit.
 * Menambah nama kartu, cicilan, bunga, dan jatuh tempo.
 *
 * Bunga dihitung flat atas seluruh nominal: total = nominal x (1 + bunga/100).
 */
final class CreditExpense extends Expense
{
    public const TYPE = 'CreditExpense';

    public const CARD_NAME_MAX = 50;
    public const INSTALLMENTS_MAX = 60;
    public const INTEREST_MAX = 100;

    private string $cardName;
    private int|string $installments;
    private float|string $interestRate;
    private ?string $dueDate;

    public function __construct(
        string $name,
        int|string $amount,
        string $category,
        string $date,
        ?string $note = null,
        ?string $cardName = null,
        int|string $installments = 1,
        float|int|string $interestRate = 0,
        ?string $dueDate = null,
    ) {
        parent::__construct($name, $amount, $category, $date, $note);

        $this->cardName = trim($cardName ?? '');

        // Cicilan kosong dianggap 1 (bayar sekali), bunga kosong dianggap 0.
        $installments = is_string($installments) ? trim($installments) : $installments;
        $this->installments = $installments === '' ? 1 : $installments;

        if (is_string($interestRate)) {
            $interestRate = str_replace(',', '.', trim($interestRate));
            $interestRate = $interestRate === '' ? 0.0 : $interestRate;
        }
        $this->interestRate = is_int($interestRate) ? (float) $interestRate : $interestRate;

        $dueDate = $dueDate === null ? null : trim($dueDate);
        $this->dueDate = $dueDate === '' ? null : $dueDate;
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function getTypeLabel(): string
    {
        return 'Kartu Kredit';
    }

    public function getCardName(): string
    {
        return $this->cardName;
    }

    public function getInstallments(): int
    {
        return self::parseInt($this->installments, 1, self::INSTALLMENTS_MAX) ?? 1;
    }

    public function getInterestRate(): float
    {
        return $this->parseRate($this->interestRate) ?? 0.0;
    }

    public function getDueDate(): ?string
    {
        return $this->dueDate;
    }

    /** Nominal ditambah bunga flat, dibulatkan ke Rupiah terdekat. */
    public function getTotalWithInterest(): int
    {
        return (int) round($this->getAmount() * (1 + $this->getInterestRate() / 100));
    }

    /** Cicilan per bulan, dibulatkan ke atas agar total cicilan tidak kurang. */
    public function getMonthlyInstallment(): int
    {
        $n = $this->getInstallments();

        return intdiv($this->getTotalWithInterest() + $n - 1, $n);
    }

    protected function validateSpecific(): array
    {
        $errors = [];

        if ($this->cardName === '') {
            $errors['card_name'] = 'Nama kartu wajib diisi.';
        } elseif (!mb_check_encoding($this->cardName, 'UTF-8') || self::hasControlChars($this->cardName, false)) {
            $errors['card_name'] = 'Nama kartu mengandung karakter yang tidak valid.';
        } elseif (mb_strlen($this->cardName) > self::CARD_NAME_MAX) {
            $errors['card_name'] = 'Nama kartu maksimal ' . self::CARD_NAME_MAX . ' karakter.';
        }

        if (self::parseInt($this->installments, 1, self::INSTALLMENTS_MAX) === null) {
            $errors['installments'] = 'Cicilan harus berupa angka bulat 1 sampai ' . self::INSTALLMENTS_MAX . '.';
        }

        if ($this->parseRate($this->interestRate) === null) {
            $errors['interest_rate'] = 'Bunga harus berupa angka 0 sampai ' . self::INTEREST_MAX
                . ' dengan maksimal 2 angka desimal.';
        }

        if ($this->dueDate !== null) {
            if (!self::isValidDate($this->dueDate)) {
                $errors['due_date'] = 'Jatuh tempo harus berformat YYYY-MM-DD dan berupa tanggal yang nyata.';
            } elseif (self::isValidDate($this->date) && $this->dueDate < $this->date) {
                $errors['due_date'] = 'Jatuh tempo tidak boleh sebelum tanggal pengeluaran.';
            }
        }

        return $errors;
    }

    protected function specificRow(): array
    {
        return [
            'card_name'     => $this->cardName,
            'installments'  => $this->getInstallments(),
            'interest_rate' => $this->getInterestRate(),
            'due_date'      => $this->dueDate,
        ];
    }

    protected function specificArray(): array
    {
        return [
            'card_name'           => $this->cardName,
            'installments'        => $this->installments,
            'interest_rate'       => $this->interestRate,
            'due_date'            => $this->dueDate,
            'total_with_interest' => $this->getTotalWithInterest(),
            'monthly_installment' => $this->getMonthlyInstallment(),
        ];
    }

    /** @return float|null null bila bukan angka 0..100 dengan maksimal 2 desimal */
    private function parseRate(float|string $rate): ?float
    {
        if (is_string($rate)) {
            if (preg_match('/^\d{1,3}(\.\d{1,2})?$/', $rate) !== 1) {
                return null;
            }
            $rate = (float) $rate;
        }

        if (!is_finite($rate) || $rate < 0 || $rate > self::INTEREST_MAX) {
            return null;
        }

        return round($rate, 2);
    }
}
