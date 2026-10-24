<?php

namespace App\Support;

class CsvSanitizer
{
    /**
     * Karakter berisiko formula injection pada file CSV/Excel:
     * =, +, -, @, tab (\t), atau carriage return (\r).
     */
    protected const DANGEROUS_PREFIXES = ['=', '+', '-', '@', "\t", "\r"];

    /**
     * Netralkan nilai sel teks terhadap CSV Formula Injection.
     * Jika diawali salah satu karakter berisiko, tambahkan awalan petik tunggal (').
     */
    public static function sanitize(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        $firstChar = mb_substr($value, 0, 1);

        if (in_array($firstChar, self::DANGEROUS_PREFIXES, true)) {
            return "'".$value;
        }

        return $value;
    }

    /**
     * Netralkan seluruh kolom dalam satu baris CSV.
     *
     * @param  array<int, mixed>  $row
     * @return array<int, mixed>
     */
    public static function sanitizeRow(array $row): array
    {
        return array_map([self::class, 'sanitize'], $row);
    }
}
