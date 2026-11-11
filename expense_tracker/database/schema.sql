-- Skema SQLite untuk Expense Tracker (PRD bagian 6).
-- Idempotent: aman dijalankan ulang. Dijalankan oleh config/Database.php
-- di dalam satu transaksi, jadi file ini tidak boleh berisi BEGIN/COMMIT.
-- Nominal disimpan sebagai bilangan bulat Rupiah (tanpa sen, tanpa multi-currency).

CREATE TABLE IF NOT EXISTS categories (
    slug       TEXT    PRIMARY KEY
                       CHECK (length(slug) BETWEEN 1 AND 40),
    name       TEXT    NOT NULL
                       CHECK (length(trim(name)) BETWEEN 2 AND 30),
    is_default INTEGER NOT NULL DEFAULT 0
                       CHECK (is_default IN (0, 1))
);

-- Kategori bawaan tidak boleh terhapus, apa pun jalur aksesnya.
CREATE TRIGGER IF NOT EXISTS trg_categories_protect_default
BEFORE DELETE ON categories
WHEN OLD.is_default = 1
BEGIN
    SELECT RAISE(ABORT, 'Kategori bawaan tidak bisa dihapus');
END;

INSERT OR IGNORE INTO categories (slug, name, is_default) VALUES
    ('makanan',      'Makanan',      1),
    ('transportasi', 'Transportasi', 1),
    ('belanja',      'Belanja',      1),
    ('lainnya',      'Lainnya',      1);

CREATE TABLE IF NOT EXISTS expenses (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    name          TEXT    NOT NULL
                          CHECK (length(trim(name)) BETWEEN 1 AND 100),
    amount        INTEGER NOT NULL
                          CHECK (amount BETWEEN 1 AND 999999999999),
    category      TEXT    NOT NULL
                          REFERENCES categories (slug)
                          ON DELETE RESTRICT ON UPDATE RESTRICT,
    type          TEXT    NOT NULL
                          CHECK (type IN ('CashExpense', 'CreditExpense')),
    date          TEXT    NOT NULL
                          CHECK (date GLOB '[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]'
                                 AND date(date) IS date),
    note          TEXT
                          CHECK (note IS NULL OR length(note) <= 255),

    -- Kolom khusus CashExpense
    cash_received INTEGER
                          CHECK (cash_received IS NULL OR cash_received >= 0),

    -- Kolom khusus CreditExpense
    card_name     TEXT
                          CHECK (card_name IS NULL OR length(trim(card_name)) BETWEEN 1 AND 50),
    installments  INTEGER
                          CHECK (installments IS NULL OR installments BETWEEN 1 AND 60),
    interest_rate REAL
                          CHECK (interest_rate IS NULL OR interest_rate BETWEEN 0 AND 100),
    due_date      TEXT
                          CHECK (due_date IS NULL
                                 OR (due_date GLOB '[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]'
                                     AND date(due_date) IS due_date)),

    created_at    TEXT    NOT NULL
                          CHECK (created_at GLOB '[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9] [0-9][0-9]:[0-9][0-9]:[0-9][0-9]'),
    updated_at    TEXT    NOT NULL
                          CHECK (updated_at GLOB '[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9] [0-9][0-9]:[0-9][0-9]:[0-9][0-9]'),

    -- Kolom milik satu tipe tidak boleh terisi pada tipe lain.
    CHECK (
        (type = 'CashExpense'
            AND card_name IS NULL AND installments IS NULL
            AND interest_rate IS NULL AND due_date IS NULL)
        OR
        (type = 'CreditExpense'
            AND cash_received IS NULL AND card_name IS NOT NULL
            AND installments IS NOT NULL AND interest_rate IS NOT NULL)
    )
);

CREATE INDEX IF NOT EXISTS idx_expenses_date     ON expenses (date);
CREATE INDEX IF NOT EXISTS idx_expenses_category ON expenses (category);
