<?php
// app/Helpers/NumberToWords.php

namespace App\Helpers;

class NumberToWords
{
    private static array $ones = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
        'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen',
        'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen',
    ];

    private static array $tens = [
        '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty',
        'Sixty', 'Seventy', 'Eighty', 'Ninety',
    ];

    /**
     * Convert a numeric amount to Indian Rupee words.
     * e.g. 11800.00 → "Rupees Eleven Thousand Eight Hundred"
     */
    public static function convert(float $amount): string
    {
        $amount = round($amount, 2);

        if ($amount == 0) {
            return 'Rupees Zero';
        }

        [$rupees, $paise] = explode('.', number_format($amount, 2, '.', ''));
        $rupees = (int) str_replace(',', '', $rupees);
        $paise  = (int) $paise;

        $words = 'Rupees ' . self::inWords($rupees);

        if ($paise > 0) {
            $words .= ' and ' . self::inWords($paise) . ' Paise';
        }

        return trim($words);
    }

    private static function inWords(int $number): string
    {
        if ($number < 0) {
            return 'Minus ' . self::inWords(abs($number));
        }

        if ($number === 0) {
            return '';
        }

        if ($number < 20) {
            return self::$ones[$number];
        }

        if ($number < 100) {
            return self::$tens[(int)($number / 10)]
                . (($number % 10 !== 0) ? ' ' . self::$ones[$number % 10] : '');
        }

        if ($number < 1000) {
            return self::$ones[(int)($number / 100)] . ' Hundred'
                . (($number % 100 !== 0) ? ' ' . self::inWords($number % 100) : '');
        }

        // Indian numbering: lakh, crore
        if ($number < 100000) {
            return self::inWords((int)($number / 1000)) . ' Thousand'
                . (($number % 1000 !== 0) ? ' ' . self::inWords($number % 1000) : '');
        }

        if ($number < 10000000) {
            return self::inWords((int)($number / 100000)) . ' Lakh'
                . (($number % 100000 !== 0) ? ' ' . self::inWords($number % 100000) : '');
        }

        return self::inWords((int)($number / 10000000)) . ' Crore'
            . (($number % 10000000 !== 0) ? ' ' . self::inWords($number % 10000000) : '');
    }
}