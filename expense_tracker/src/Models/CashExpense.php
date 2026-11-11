<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Kelas anak (child class) untuk pengeluaran tunai.
 * Mewarisi semua atribut dan perilaku Expense, lalu menambah uang tunai
 * yang dibayarkan dan perhitungan kembalian.
 */
final class CashExpense extends Expense
{
    public const TYPE = 'CashExpense';

    private int|string|null $cashReceived;

    public function __construct(
        string $name,
        int|string $amount,
        string $category,
        string $date,
        ?string $note = null,
        int|string|null $cashReceived = null,
    ) {
        parent::__construct($name, $amount, $category, $date, $note);

        if (is_string($cashReceived)) {
            $cashReceived = trim($cashReceived);
            if ($cashReceived === '') {
                $cashReceived = null;
            }
        }
        $this->cashReceived = $cashReceived;
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function getTypeLabel(): string
    {
        return 'Tunai';
    }

    /** Uang tunai yang dibayarkan. null bila kosong atau belum valid. */
    public function getCashReceived(): ?int
    {
        if ($this->cashReceived === null) {
            return null;
        }

        return self::parseInt($this->cashReceived, 0, self::AMOUNT_MAX);
    }

    /** Kembalian = tunai dibayarkan - nominal. null bila tunai tidak diisi. */
    public function getChange(): ?int
    {
        $received = $this->getCashReceived();

        return $received === null ? null : max(0, $received - $this->getAmount());
    }

    protected function validateSpecific(): array
    {
        $errors = [];

        if ($this->cashReceived !== null) {
            $received = $this->getCashReceived();
            $amount   = self::parseInt($this->amount, 1, self::AMOUNT_MAX);

            if ($received === null) {
                $errors['cash_received'] = 'Uang tunai harus berupa angka bulat, 0 atau lebih.';
            } elseif ($amount !== null && $received < $amount) {
                $errors['cash_received'] = 'Uang tunai tidak boleh lebih kecil dari nominal.';
            }
        }

        return $errors;
    }

    protected function specificRow(): array
    {
        return ['cash_received' => $this->getCashReceived()];
    }

    protected function specificArray(): array
    {
        return [
            'cash_received' => $this->cashReceived,
            'change'        => $this->getChange(),
        ];
    }
}
