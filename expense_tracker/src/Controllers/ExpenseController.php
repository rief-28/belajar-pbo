<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\HttpException;
use App\Models\CashExpense;
use App\Models\Expense;
use App\Models\ExpenseFactory;
use App\Models\ExpenseFilter;
use App\Repositories\CategoryRepository;
use App\Repositories\ExpenseRepository;
use App\Support\Logger;
use App\Support\Session;
use App\Support\View;
use InvalidArgumentException;
use PDOException;

/**
 * Menghubungkan request ke repository dan view.
 *
 * Aturan umum:
 * - Input salah: render ulang form dengan status 422 dan pesan per field.
 * - Berhasil mengubah data: simpan flash lalu redirect 303 (Post/Redirect/Get).
 * - Data tidak ada: HttpException 404, ditangani router.
 */
final class ExpenseController
{
    public function __construct(
        private ExpenseRepository $expenses,
        private CategoryRepository $categories,
    ) {
    }

    // ---- Expense -----------------------------------------------------------

    /** FR-2, FR-5, FR-6, FR-7, FR-8. Daftar, ringkasan total, filter, cari, urut. */
    public function index(array $query): void
    {
        $filter   = ExpenseFilter::fromQuery($query);
        $list     = $this->expenses->findAll($filter);
        $rows     = array_map(static fn (Expense $e): array => $e->toArray(), $list);
        $filtered = array_sum(array_map(static fn (Expense $e): int => $e->getAmount(), $list));

        View::render('expense/index', [
            'expenses'        => $rows,
            'filter'          => $filter->toArray(),
            'filteredTotal'   => $filtered,
            'totalOverall'    => $this->expenses->totalOverall(),
            'totalThisMonth'  => $this->expenses->totalForMonth(date('Y-m')),
            'monthlyTotals'   => $this->expenses->totalsPerMonth(),
            'categories'      => $this->categories->all(),
        ]);
    }

    /** Form tambah. */
    public function create(): void
    {
        $this->renderForm('expense/create', $this->blankExpense()->toArray(), []);
    }

    /** FR-1. Simpan pengeluaran baru. */
    public function store(array $post): void
    {
        try {
            $expense = ExpenseFactory::fromInput($post);
        } catch (InvalidArgumentException $e) {
            $fallback = ExpenseFactory::fromInput(['type' => CashExpense::TYPE] + $post);
            $this->renderForm('expense/create', $fallback->toArray(), ['type' => $e->getMessage()], 422);
            return;
        }

        $errors = $this->validate($expense);
        if ($errors === []) {
            try {
                $id = $this->expenses->insert($expense);
            } catch (PDOException $e) {
                // Kategori terhapus di antara pemeriksaan dan INSERT.
                $errors = $this->constraintToErrors($e);
            }
        }
        if ($errors !== []) {
            $this->renderForm('expense/create', $expense->toArray(), $errors, 422);
            return;
        }

        Logger::log('info', 'expense.created', ['expense_id' => $id, 'type' => $expense->getType()]);
        Session::flash('success', 'Pengeluaran berhasil disimpan.');
        $this->redirect('index.php');
    }

    /** Form edit berisi data lama (FR-3). */
    public function edit(int $id): void
    {
        $expense = $this->findOrFail($id);
        $this->renderForm('expense/edit', $expense->toArray(), []);
    }

    /** FR-3. Simpan perubahan. Tipe boleh berganti (tunai ke kartu kredit atau sebaliknya). */
    public function update(int $id, array $post): void
    {
        $existing = $this->findOrFail($id);

        try {
            $expense = ExpenseFactory::fromInput($post, $id, $existing->getCreatedAt());
        } catch (InvalidArgumentException $e) {
            $this->renderForm('expense/edit', $existing->toArray(), ['type' => $e->getMessage()], 422);
            return;
        }

        $errors = $this->validate($expense);
        if ($errors === []) {
            try {
                if (!$this->expenses->update($expense)) {
                    // Terhapus oleh permintaan lain setelah findOrFail().
                    throw new HttpException(404, 'Pengeluaran tidak ditemukan.');
                }
            } catch (PDOException $e) {
                $errors = $this->constraintToErrors($e);
            }
        }
        if ($errors !== []) {
            $this->renderForm('expense/edit', $expense->toArray(), $errors, 422);
            return;
        }

        Logger::log('info', 'expense.updated', ['expense_id' => $id, 'type' => $expense->getType()]);
        Session::flash('success', 'Pengeluaran berhasil diperbarui.');
        $this->redirect('index.php');
    }

    /** FR-4. Hapus. Menghapus data yang sudah hilang bukan galat (aman diulang). */
    public function delete(int $id): void
    {
        if ($this->expenses->delete($id)) {
            Logger::log('info', 'expense.deleted', ['expense_id' => $id]);
            Session::flash('success', 'Pengeluaran berhasil dihapus.');
        } else {
            Session::flash('info', 'Pengeluaran sudah tidak ada.');
        }
        $this->redirect('index.php');
    }

    // ---- Category (US-6) ---------------------------------------------------

    public function storeCategory(array $post): void
    {
        $name = is_string($post['name'] ?? null) ? $post['name'] : '';

        try {
            $slug = $this->categories->add($name);
            Logger::log('info', 'category.created', ['slug' => $slug]);
            Session::flash('success', 'Kategori berhasil ditambahkan.');
        } catch (HttpException $e) {
            Session::flash('danger', $e->getMessage());
        }
        $this->redirect('index.php');
    }

    public function deleteCategory(array $post): void
    {
        $slug = is_string($post['slug'] ?? null) ? mb_strtolower(trim($post['slug'])) : '';

        try {
            $this->categories->delete($slug);
            Logger::log('info', 'category.deleted', ['slug' => $slug]);
            Session::flash('success', 'Kategori berhasil dihapus.');
        } catch (HttpException $e) {
            Session::flash('danger', $e->getMessage());
        }
        $this->redirect('index.php');
    }

    // ---- Internal ----------------------------------------------------------

    private function findOrFail(int $id): Expense
    {
        $expense = $this->expenses->find($id);
        if ($expense === null) {
            throw new HttpException(404, 'Pengeluaran tidak ditemukan.');
        }
        return $expense;
    }

    /** @return array<string, string> aturan model ditambah pemeriksaan kategori di database */
    private function validate(Expense $expense): array
    {
        $errors = $expense->validate();

        if (!isset($errors['category'])
            && !in_array($expense->getCategory(), $this->categories->slugs(), true)) {
            $errors['category'] = 'Kategori tidak tersedia.';
        }

        return $errors;
    }

    /**
     * @return array<string, string>
     * @throws PDOException bila bukan pelanggaran constraint
     */
    private function constraintToErrors(PDOException $e): array
    {
        if ($e->getCode() === '23000') {
            Logger::log('warning', 'expense.constraint_violation', ['error' => $e->getMessage()]);
            return ['category' => 'Data ditolak database. Periksa kategori dan isian lain, lalu coba lagi.'];
        }
        throw $e;
    }

    private function blankExpense(): Expense
    {
        return new CashExpense('', '', '', date('Y-m-d'));
    }

    /**
     * @param array<string, mixed>  $expense data form (hasil toArray())
     * @param array<string, string> $errors
     */
    private function renderForm(string $view, array $expense, array $errors, int $status = 200): void
    {
        View::render($view, [
            'expense'    => $expense,
            'errors'     => $errors,
            'categories' => $this->categories->all(),
            'types'      => ExpenseFactory::TYPES,
        ], $status);
    }

    private function redirect(string $location): void
    {
        http_response_code(303);
        header('Location: ' . $location);
    }
}
