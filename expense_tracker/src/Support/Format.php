<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Pembantu tampilan: escape HTML, format Rupiah dan tanggal Indonesia,
 * serta warna dan ikon kategori. Tidak menyentuh database.
 */
final class Format
{
    private const MONTHS = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    private const MONTHS_SHORT = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
        7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    /** Warna per kategori: c = ikon/aksen, bg = latar lembut, tx = teks (kontras AA di atas bg). */
    private const PALETTE = [
        'orange' => ['c' => '#F79009', 'bg' => '#FEF0C7', 'tx' => '#B54708'],
        'blue'   => ['c' => '#2E90FA', 'bg' => '#D1E9FF', 'tx' => '#175CD3'],
        'purple' => ['c' => '#9E77ED', 'bg' => '#EBE9FE', 'tx' => '#6938EF'],
        'gray'   => ['c' => '#667085', 'bg' => '#EAECF0', 'tx' => '#475467'],
        'red'    => ['c' => '#E5484D', 'bg' => '#FEE4E2', 'tx' => '#B42318'],
        'pink'   => ['c' => '#EE46BC', 'bg' => '#FCE7F6', 'tx' => '#C11574'],
        'green'  => ['c' => '#0E9F6E', 'bg' => '#D6F5E9', 'tx' => '#067647'],
    ];

    private const FIXED_COLORS = [
        'makanan'      => 'orange',
        'transportasi' => 'blue',
        'belanja'      => 'purple',
        'lainnya'      => 'gray',
    ];

    private const CUSTOM_COLORS = ['red', 'pink', 'green', 'blue', 'orange', 'purple'];

    /** Path SVG (gaya garis 24x24). */
    private const ICONS = [
        'utensils' => '<path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/>',
        'car'      => '<path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/>',
        'bag'      => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>',
        'tag'      => '<path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/>',
        'plus'     => '<path d="M12 5v14"/><path d="M5 12h14"/>',
        'search'   => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
        'pencil'   => '<path d="M21.2 5.6a2.1 2.1 0 0 0-3-3L3.5 17.3 2 22l4.7-1.5Z"/><path d="m15 5 4 4"/>',
        'trash'    => '<path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M10 11v6"/><path d="M14 11v6"/>',
        'x'        => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
        'wallet'   => '<path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1"/><path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4"/>',
        'calendar' => '<path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/>',
        'inbox'    => '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.5 5.1 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.5-6.9A2 2 0 0 0 16.7 4H7.3a2 2 0 0 0-1.8 1.1Z"/>',
    ];

    private const CATEGORY_ICONS = [
        'makanan'      => 'utensils',
        'transportasi' => 'car',
        'belanja'      => 'bag',
    ];

    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /** 45000 -> "Rp 45.000" */
    public static function rupiah(int|float|string $amount): string
    {
        return 'Rp ' . self::number($amount);
    }

    public static function number(int|float|string $amount): string
    {
        return number_format((float) $amount, 0, ',', '.');
    }

    /** "2026-11-09" -> "9 Nov 2026". Nilai tak dikenal dikembalikan apa adanya. */
    public static function date(string $iso): string
    {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $iso, $m) !== 1) {
            return $iso;
        }
        return (int) $m[3] . ' ' . (self::MONTHS_SHORT[(int) $m[2]] ?? $m[2]) . ' ' . $m[1];
    }

    /** "2026-11" -> "November 2026" */
    public static function month(string $ym): string
    {
        if (preg_match('/^(\d{4})-(\d{2})$/', $ym, $m) !== 1) {
            return $ym;
        }
        return (self::MONTHS[(int) $m[2]] ?? $m[2]) . ' ' . $m[1];
    }

    /** @return array{c: string, bg: string, tx: string} */
    public static function categoryColors(string $slug): array
    {
        $name = self::FIXED_COLORS[$slug] ?? self::CUSTOM_COLORS[crc32($slug) % count(self::CUSTOM_COLORS)];
        return self::PALETTE[$name];
    }

    /** Atribut style berisi variabel CSS warna kategori (aman untuk dicetak apa adanya). */
    public static function categoryStyle(string $slug): string
    {
        $c = self::categoryColors($slug);
        return sprintf('--c:%s;--bg:%s;--tx:%s', $c['c'], $c['bg'], $c['tx']);
    }

    public static function categoryIcon(string $slug): string
    {
        return self::icon(self::CATEGORY_ICONS[$slug] ?? 'tag');
    }

    /** SVG inline. Dekoratif, jadi disembunyikan dari pembaca layar. */
    public static function icon(string $name): string
    {
        $paths = self::ICONS[$name] ?? self::ICONS['tag'];
        return '<svg class="icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" '
            . 'stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
            . $paths . '</svg>';
    }
}
