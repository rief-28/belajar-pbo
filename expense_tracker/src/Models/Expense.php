<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;

/**
 * Kelas induk (parent class) semua pengeluaran.
 *
 * Atribut dilindungi (protected) agar hanya kelas ini dan turunannya yang
 * bisa mengubahnya. Dunia luar memakai getter. Nilai mentah dari form
 * disimpan apa adanya dan diperiksa lewat validate(), sehingga objek yang
 * tidak valid tetap bisa ditampilkan ulang di form beserta pesan galatnya.
 *
 * Turunan wajib mengisi: getType(), getTypeLabel(), validateSpecific(),
 * specificRow() dan specificArray() (pola template method).
 */
abstract class Expense
{
    public const NAME_MAX = 100;
    public const NOTE_MAX = 255;
    public const AMOUNT_MAX = 999_999_999_999;

    /** Kategori yang mewajibkan catatan (PRD 2.4: "Lainnya (user isi keterangan)"). */
    public const NOTE_REQUIRED_CATEGORY = 'lainnya';

    protected ?int $id = null;
    protected string $name;
    protected int|string $amount;
    protected string $category;
    protected string $date;
    protected ?string $note;
    protected ?string $categoryName = null;
    protected ?string $createdAt = null;
    protected ?string $updatedAt = null;

    public function __construct(
        string $name,
        int|string $amount,
        string $category,
        string $date,
        ?string $note = null,
    ) {
        $this->name     = trim($name);
        $this->amount   = is_string($amount) ? trim($amount) : $amount;
        $this->category = mb_strtolower(trim($category));
        $this->date     = trim($date);

        $note = $note === null ? null : trim($note);
        $this->note = $note === '' ? null : $note;
    }

    abstract public function getType(): string;

    abstract public function getTypeLabel(): string;

    /** @return array<string, string> galat khusus tipe, kunci = nama field */
    abstract protected function validateSpecific(): array;

    /** @return array<string, int|float|string|null> kolom khusus tipe untuk tabel expenses */
    abstract protected function specificRow(): array;

    /** @return array<string, mixed> field khusus tipe untuk tampilan */
    abstract protected function specificArray(): array;

    // ---- Getter dan setter -------------------------------------------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /** Nominal sebagai int. 0 bila belum valid (cek validate() dulu). */
    public function getAmount(): int
    {
        return self::parseInt($this->amount, 1, self::AMOUNT_MAX) ?? 0;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function getCategoryName(): ?string
    {
        return $this->categoryName;
    }

    public function setCategoryName(?string $categoryName): void
    {
        $this->categoryName = $categoryName;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function setTimestamps(?string $createdAt, ?string $updatedAt): void
    {
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    // ---- Perilaku ----------------------------------------------------------

    /**
     * Memeriksa aturan PRD (NFR-4 dan 2.4). Keberadaan kategori di database
     * diperiksa oleh controller, bukan di sini.
     *
     * @return array<string, string> kosong jika valid; kunci = nama field
     */
    public function validate(): array
    {
        $errors = [];

        if ($this->name === '') {
            $errors['name'] = 'Nama pengeluaran wajib diisi.';
        } elseif (!mb_check_encoding($this->name, 'UTF-8') || self::hasControlChars($this->name, false)) {
            $errors['name'] = 'Nama pengeluaran mengandung karakter yang tidak valid.';
        } elseif (mb_strlen($this->name) > self::NAME_MAX) {
            $errors['name'] = 'Nama pengeluaran maksimal ' . self::NAME_MAX . ' karakter.';
        }

        if (self::parseInt($this->amount, 1, self::AMOUNT_MAX) === null) {
            $errors['amount'] = 'Nominal harus berupa angka bulat lebih dari 0 (maksimal '
                . number_format(self::AMOUNT_MAX, 0, ',', '.') . ').';
        }

        if ($this->category === '') {
            $errors['category'] = 'Kategori wajib dipilih.';
        } elseif (preg_match('/^[a-z0-9-]{1,40}$/', $this->category) !== 1) {
            $errors['category'] = 'Kategori tidak valid.';
        }

        if ($this->date === '') {
            $errors['date'] = 'Tanggal wajib diisi.';
        } elseif (!self::isValidDate($this->date)) {
            $errors['date'] = 'Tanggal harus berformat YYYY-MM-DD dan berupa tanggal yang nyata.';
        }

        if ($this->note !== null) {
            if (!mb_check_encoding($this->note, 'UTF-8') || self::hasControlChars($this->note, true)) {
                $errors['note'] = 'Catatan mengandung karakter yang tidak valid.';
            } elseif (mb_strlen($this->note) > self::NOTE_MAX) {
                $errors['note'] = 'Catatan maksimal ' . self::NOTE_MAX . ' karakter.';
            }
        } elseif ($this->category === self::NOTE_REQUIRED_CATEGORY) {
            $errors['note'] = 'Kategori "Lainnya" wajib diisi keterangan pada kolom catatan.';
        }

        return $errors + $this->validateSpecific();
    }

    /**
     * Kolom untuk INSERT/UPDATE tabel expenses (tanpa id dan timestamp).
     * Panggil hanya setelah validate() kosong.
     *
     * @return array<string, int|float|string|null>
     */
    public function toRow(): array
    {
        return array_merge([
            'name'     => $this->name,
            'amount'   => $this->getAmount(),
            'category' => $this->category,
            'type'     => $this->getType(),
            'date'     => $this->date,
            'note'     => $this->note,
        ], $this->specificRow());
    }

    /**
     * Bentuk array untuk view dan form. Nilai mentah dipertahankan
     * agar form bisa menampilkan ulang input yang salah.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge([
            'id'            => $this->id,
            'name'          => $this->name,
            'amount'        => $this->amount,
            'category'      => $this->category,
            'category_name' => $this->categoryName ?? ucfirst($this->category),
            'type'          => $this->getType(),
            'type_label'    => $this->getTypeLabel(),
            'date'          => $this->date,
            'note'          => $this->note,
            'created_at'    => $this->createdAt,
            'updated_at'    => $this->updatedAt,
        ], $this->specificArray());
    }

    // ---- Alat bantu untuk turunan -----------------------------------------

    /**
     * Mengubah int atau string digit (maks 12 digit) menjadi int dalam rentang.
     * String lain ("1e3", "-5", "12.5", " ") ditolak, bukan dibulatkan diam-diam.
     */
    protected static function parseInt(int|string $value, int $min, int $max): ?int
    {
        if (is_string($value)) {
            if (preg_match('/^\d{1,12}$/', $value) !== 1) {
                return null;
            }
            $value = (int) $value;
        }

        return ($value >= $min && $value <= $max) ? $value : null;
    }

    /** Tanggal ISO YYYY-MM-DD yang benar-benar ada (2026-02-30 ditolak). */
    protected static function isValidDate(string $date): bool
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }

    protected static function hasControlChars(string $text, bool $allowNewlines): bool
    {
        $pattern = $allowNewlines
            ? '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/'
            : '/[\x00-\x1F\x7F]/';

        return preg_match($pattern, $text) === 1;
    }
}
