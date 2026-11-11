#!/usr/bin/env bash
# Smoke test HTTP end-to-end memakai server bawaan PHP dan database sementara.
# Jalankan dari folder proyek:  bash tests/http_smoke.sh
set -u

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PORT="${PORT:-8765}"
BASE="http://127.0.0.1:${PORT}"
WORK="$(mktemp -d)"
export EXPENSE_DB_PATH="$WORK/db/test.sqlite"
JAR="$WORK/cookies.txt"
pass=0; fail=0

cleanup() { kill "${SERVER_PID:-0}" 2>/dev/null; rm -rf "$WORK"; }
trap cleanup EXIT

php -S "127.0.0.1:${PORT}" -t "$ROOT" "$ROOT/router.php" >"$WORK/server.log" 2>&1 &
SERVER_PID=$!
for _ in $(seq 1 50); do curl -s -o /dev/null "$BASE/?action=health" && break; sleep 0.1; done

ok()  { pass=$((pass+1)); }
bad() { fail=$((fail+1)); echo "GAGAL: $1"; }
expect() { # nama, nilai_sebenarnya, nilai_diharapkan
  if [ "$2" = "$3" ]; then ok; else bad "$1 (dapat '$2', harapan '$3')"; fi
}
contains()     { if grep -qF -- "$3" <<<"$2"; then ok; else bad "$1 (tidak memuat '$3')"; fi; }
not_contains() { if grep -qF -- "$3" <<<"$2"; then bad "$1 (memuat '$3')"; else ok; fi; }
code() { curl -s -o /dev/null -w '%{http_code}' -b "$JAR" -c "$JAR" "$@"; }

# Health dan routing
expect "health 200" "$(code "$BASE/?action=health")" 200
contains "health body" "$(curl -s "$BASE/?action=health")" '"status":"ok"'
expect "action tidak dikenal 404" "$(code "$BASE/?action=nope")" 404
expect "GET ke store 405" "$(code "$BASE/?action=store")" 405
contains "405 membawa Allow" "$(curl -s -i "$BASE/?action=store")" 'Allow: POST'
expect "POST tanpa sesi/token 403" "$(code -X POST -d 'name=x' "$BASE/?action=store")" 403

# Ambil token CSRF (dikirim sebagai header oleh View)
TOKEN="$(curl -s -D - -o /dev/null -b "$JAR" -c "$JAR" "$BASE/" | tr -d '\r' | awk -F': ' 'tolower($1)=="x-csrf-token"{print $2}')"
[ -n "$TOKEN" ] && ok || bad "token CSRF tidak diterima"

expect "POST token salah 403" "$(code -X POST -d 'csrf_token=salah&name=x' "$BASE/?action=store")" 403

post() { # action, data...
  local action="$1"; shift
  code -X POST --data-urlencode "csrf_token=$TOKEN" "$@" "$BASE/?action=$action"
}

# Create (FR-1) dan validasi (NFR-4)
expect "store valid 303" "$(post store -d 'name=Makan siang' -d 'amount=25000' -d 'category=makanan' -d 'date=2026-11-09')" 303
expect "store nominal 0 422" "$(post store -d 'name=A' -d 'amount=0' -d 'category=makanan' -d 'date=2026-11-09')" 422
expect "store nama kosong 422" "$(post store -d 'name=' -d 'amount=100' -d 'category=makanan' -d 'date=2026-11-09')" 422
expect "store kategori tidak ada 422" "$(post store -d 'name=A' -d 'amount=100' -d 'category=hantu' -d 'date=2026-11-09')" 422
expect "store tipe asing 422" "$(post store -d 'type=Hacker' -d 'name=A' -d 'amount=100' -d 'category=makanan' -d 'date=2026-11-09')" 422
expect "store lainnya tanpa catatan 422" "$(post store -d 'name=A' -d 'amount=100' -d 'category=lainnya' -d 'date=2026-11-09')" 422
expect "store credit 303" "$(post store -d 'type=CreditExpense' -d 'name=Laptop' -d 'amount=5000000' -d 'category=belanja' -d 'date=2026-10-15' -d 'card_name=BCA' -d 'installments=12' -d 'interest_rate=12')" 303
expect "store XSS 303" "$(post store --data-urlencode 'name=<script>alert(1)</script>' -d 'amount=1000' -d 'category=belanja' -d 'date=2026-11-02')" 303

# Read, ringkasan, filter, urut (FR-2, FR-5..8)
LIST="$(curl -s -b "$JAR" -c "$JAR" "$BASE/")"
contains "daftar memuat Makan siang" "$LIST" "Makan siang"
contains "daftar memuat Laptop" "$LIST" "Laptop"
contains "XSS di-escape" "$LIST" "&lt;script&gt;alert(1)"
not_contains "tidak ada script mentah" "$LIST" "<script>alert(1)"
contains "total keseluruhan 5.026.000" "$LIST" "5.026.000"
contains "filter kategori" "$(curl -s "$BASE/?category=makanan")" "Makan siang"
not_contains "filter kategori menyaring" "$(curl -s "$BASE/?category=makanan")" "Laptop"
not_contains "filter bulan menyaring" "$(curl -s "$BASE/?month=2026-10")" "Makan siang"
contains "cari nama" "$(curl -s "$BASE/?search=lapt")" "Laptop"

# Update (FR-3)
expect "edit id ada (200)" "$(code "$BASE/?action=edit&id=1")" 200
expect "edit id tidak ada 404" "$(code "$BASE/?action=edit&id=9999")" 404
expect "edit id ngawur 404" "$(code "$BASE/?action=edit&id=abc")" 404
expect "edit id SQL injection 404" "$(code "$BASE/?action=edit&id=1%20OR%201=1")" 404
expect "update 303" "$(post "update&id=1" -d 'name=Makan malam' -d 'amount=30000' -d 'category=makanan' -d 'date=2026-11-09')" 303
expect "update invalid 422" "$(post "update&id=1" -d 'name=' -d 'amount=30000' -d 'category=makanan' -d 'date=2026-11-09')" 422
expect "update id tidak ada 404" "$(post "update&id=9999" -d 'name=A' -d 'amount=1' -d 'category=makanan' -d 'date=2026-11-09')" 404
contains "update terlihat di daftar" "$(curl -s "$BASE/")" "Makan malam"

# Delete (FR-4), aman diulang
expect "GET delete 405" "$(code "$BASE/?action=delete&id=1")" 405
expect "delete 303" "$(post "delete&id=1")" 303
expect "delete ulang 303" "$(post "delete&id=1")" 303
not_contains "terhapus dari daftar" "$(curl -s "$BASE/")" "Makan malam"

# Kategori (US-6)
expect "tambah kategori 303" "$(post category_store -d 'name=Hobi')" 303
expect "hapus kategori bawaan tetap 303 (flash galat)" "$(post category_delete -d 'slug=makanan')" 303
expect "store ke kategori baru 303" "$(post store -d 'name=Gitar' -d 'amount=500000' -d 'category=hobi' -d 'date=2026-11-03')" 303
expect "hapus kategori dipakai tetap 303 (flash galat)" "$(post category_delete -d 'slug=hobi')" 303
contains "kategori dipakai tidak terhapus" "$(curl -s "$BASE/?category=hobi")" "Gitar"

# Kebocoran file
DB_BODY="$(curl -s "$BASE/database/test.sqlite")"
not_contains "file database tidak bisa diunduh" "$DB_BODY" "SQLite format"
not_contains "source tidak bocor" "$(curl -s "$BASE/src/Models/Expense.php")" "abstract class"
not_contains "schema tidak bocor" "$(curl -s "$BASE/database/schema.sql")" "CREATE TABLE"
expect "static css dilayani" "$(code "$BASE/public/css/style.css")" 200
contains "css dimuat lewat alamat relatif" "$(curl -s "$BASE/")" 'href="public/css/style.css"'
not_contains "traversal ke source tidak bocor" "$(curl -s --path-as-is "$BASE/public/../src/Models/Expense.php")" "abstract class"
not_contains "traversal ke database tidak bocor" "$(curl -s --path-as-is "$BASE/public/../database/test.sqlite")" "SQLite format"
expect "traversal via %2e%2e tidak bocor" "$(curl -s --path-as-is "$BASE/public/%2e%2e/src/Models/Expense.php" | grep -c 'abstract class')" 0

# Header keamanan dan request id
HDR="$(curl -s -D - -o /dev/null "$BASE/")"
contains "X-Request-Id" "$HDR" "X-Request-Id:"
contains "nosniff" "$HDR" "X-Content-Type-Options: nosniff"

# Performa NFR-1: 500 entry, daftar < 1 detik
php -r '
require $argv[1] . "/bootstrap.php";
$pdo = App\Config\Database::open(getenv("EXPENSE_DB_PATH"));
$r = new App\Repositories\ExpenseRepository($pdo);
$pdo->exec("BEGIN");
for ($i = 0; $i < 500; $i++) {
    $r->insert(new App\Models\CashExpense("Item $i", 1000 + $i, "makanan", sprintf("2026-%02d-%02d", 1 + $i % 11, 1 + $i % 28)));
}
$pdo->exec("COMMIT");
' "$ROOT"
T="$(curl -s -o /dev/null -w '%{time_total}' -b "$JAR" "$BASE/")"
if awk -v t="$T" 'BEGIN{exit !(t < 1.0)}'; then ok; else bad "daftar 500+ entry terlalu lambat: ${T}s"; fi
echo "Waktu muat daftar 500+ entry: ${T}s"

# Log terstruktur
contains "log JSON ada request_id" "$(cat "$WORK/server.log")" '"request_id"'
not_contains "tidak ada Fatal/Warning PHP di log server" "$(cat "$WORK/server.log")" "PHP Fatal"

echo
echo "$pass lolos, $fail gagal"
[ "$fail" -eq 0 ]
