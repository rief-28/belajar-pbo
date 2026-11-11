<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Exceptions\HttpException;
use PDO;
use PDOException;

/**
 * Akses data tabel categories (US-6).
 * Empat kategori bawaan (is_default = 1) tidak bisa dihapus.
 */
final class CategoryRepository
{
    public const NAME_MIN = 2;
    public const NAME_MAX = 30;

    public function __construct(private PDO $pdo)
    {
    }

    /** @return array<int, array{slug: string, name: string, is_default: bool, expense_count: int}> */
    public function all(): array
    {
        $rows = $this->pdo->query(
            'SELECT c.slug, c.name, c.is_default, COUNT(e.id) AS expense_count
             FROM categories c
             LEFT JOIN expenses e ON e.category = c.slug
             GROUP BY c.slug
             ORDER BY c.rowid'
        )->fetchAll();

        return array_map(static fn (array $r): array => [
            'slug'          => (string) $r['slug'],
            'name'          => (string) $r['name'],
            'is_default'    => (int) $r['is_default'] === 1,
            'expense_count' => (int) $r['expense_count'],
        ], $rows);
    }

    /** @return string[] */
    public function slugs(): array
    {
        return $this->pdo->query('SELECT slug FROM categories ORDER BY rowid')->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @return string slug kategori baru
     * @throws HttpException 422 jika nama tidak valid, 409 jika sudah ada
     */
    public function add(string $name): string
    {
        $name = trim($name);
        if (!mb_check_encoding($name, 'UTF-8')
            || mb_strlen($name) < self::NAME_MIN
            || mb_strlen($name) > self::NAME_MAX) {
            throw new HttpException(422, sprintf(
                'Nama kategori harus %d sampai %d karakter.',
                self::NAME_MIN,
                self::NAME_MAX
            ));
        }

        $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', mb_strtolower($name)), '-');
        if ($slug === '') {
            throw new HttpException(422, 'Nama kategori harus mengandung huruf atau angka.');
        }

        try {
            $stmt = $this->pdo->prepare('INSERT INTO categories (slug, name, is_default) VALUES (:slug, :name, 0)');
            $stmt->execute([':slug' => $slug, ':name' => $name]);
        } catch (PDOException $e) {
            // 23000 = pelanggaran constraint (PRIMARY KEY dobel).
            if ($e->getCode() === '23000') {
                throw new HttpException(409, 'Kategori "' . $name . '" sudah ada.');
            }
            throw $e;
        }

        return $slug;
    }

    /**
     * Hapus dalam satu statement atomik, jadi tidak ada celah antara
     * "cek dipakai" dan "hapus" bila ada pengeluaran baru yang masuk.
     *
     * @throws HttpException 404 / 403 / 409
     */
    public function delete(string $slug): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM categories
             WHERE slug = :slug
               AND is_default = 0
               AND NOT EXISTS (SELECT 1 FROM expenses WHERE category = :slug2)'
        );
        $stmt->execute([':slug' => $slug, ':slug2' => $slug]);
        if ($stmt->rowCount() > 0) {
            return;
        }

        $stmt = $this->pdo->prepare(
            'SELECT c.is_default, (SELECT COUNT(*) FROM expenses WHERE category = c.slug) AS used
             FROM categories c WHERE c.slug = :slug'
        );
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch();

        if ($row === false) {
            throw new HttpException(404, 'Kategori tidak ditemukan.');
        }
        if ((int) $row['is_default'] === 1) {
            throw new HttpException(403, 'Kategori bawaan tidak bisa dihapus.');
        }
        throw new HttpException(409, sprintf(
            'Kategori masih dipakai %d pengeluaran. Ubah atau hapus pengeluaran itu dulu.',
            (int) $row['used']
        ));
    }
}
