<?php

/**
 * This class solves the problem of product big integers written by strings.
 * But it also has some restrictions.
 *
 * First of all, its implied that integers in strings are valid (no leading zeros, no trash symbols, etc.).
 * Secondly, PHP has max integer value in PHP_INT_MAX constant.
 * This solution use array which contains digit sums.
 * Array cant have integer index more than max integer.
 * So the total length of multiplier strings should be less than PHP_INT_MAX or equal.
 * Also, any using in calculations sum will be less than PHP_INT_MAX div 9*9.
 * So min multiplier string length should be less than PHP_INT_MAX div 9*9 or equal.
 * This can be summarized as follows: ((l1 + l2) <= PHP_INT_MAX) and (min(l1, l2) <= PHP_INT_MAX div 81).
 * Where l1, l2 - lengths of multiplier strings.
 */
class CalcProduction
{
    /**
     * Symbol for marking and detecting int as negative.
     */
    const MINUS_SYMBOL = '-';
    /**
     * Symbol for marking int as positive.
     */
    const PLUS_SYMBOL = '';
    private string $a;
    private string $b;
    private int $aDigits;
    private int $bDigits;

    private bool $productIsNegative;
    private array $digitsSums;
    private string $product;

    /**
     * Calculate product of multipliers.
     * @param string $a First multiplier.
     * @param string $b Second multiplier.
     * @return string Product of multipliers.
     */
    public function calc(string $a, string $b): string
    {
        $this->a = $a;
        $this->b = $b;

        if ($this->zeroMultiplierExists()) {
            $this->prepareZeroResult();

            return $this->createResult();
        }

        $this->init();

        $this->identifyProductSign();
        $this->calcMultipliersDigits();

        $this->calcLowerDigitsSums();
        $this->calcUpperDigits();

        return $this->createResult();
    }

    private function createResult(): string
    {
        return ($this->productIsNegative)
            ? static::MINUS_SYMBOL . strrev($this->product)
            : static::PLUS_SYMBOL . strrev($this->product);
    }

    private function prepareZeroResult(): void
    {
        $this->productIsNegative = false;
        $this->product = '0';
    }

    private function calcUpperDigits(): void
    {
        $maxDigits = max($this->aDigits, $this->bDigits);
        for ($pos = $this->aDigits; $pos < 2 * $maxDigits; $pos++) {
            if (!isset($this->digitsSums[$pos])) break;

            $this->popUpDigit($pos);
        }
    }

    private function calcLowerDigitsSums(): void
    {
        $aLen = strlen($this->a);
        $bLen = strlen($this->b);
        for ($i = 0; $i < $this->aDigits; $i++) {
            for ($j = 0; $j < $this->bDigits; $j++) {
                if (!isset($this->digitsSums[$i + $j])) $this->digitsSums[$i + $j] = 0;
                $this->digitsSums[$i + $j] += $this->a[$aLen - $i - 1] * $this->b[$bLen - $j - 1];
            }
            $this->popUpDigit($i);
        }
    }

    private function popUpDigit(int $pos): void
    {
        $posDigit = $this->digitsSums[$pos] % 10;
        $this->product .= $posDigit;

        if ($this->digitsSums[$pos] < 10) return;
        $this->digitsSums[$pos + 1] += intdiv($this->digitsSums[$pos], 10);
        $this->digitsSums[$pos] = $posDigit;
    }

    private function calcMultipliersDigits(): void
    {
        $this->aDigits = $this->calcDigits($this->a);
        $this->bDigits = $this->calcDigits($this->b);
    }

    private function identifyProductSign(): void
    {
        $this->productIsNegative = ($this->a[0] == static::MINUS_SYMBOL xor $this->b[0] == static::MINUS_SYMBOL);
    }

    private function calcDigits(string $num): int
    {
        $digits = strlen($num);
        if ($num[0] == static::MINUS_SYMBOL) {
            $digits--;
        }

        return $digits;
    }

    private function zeroMultiplierExists(): bool
    {
        return $this->a == '0' or $this->b == '0';
    }

    private function init(): void
    {
        $this->product = '';
        $this->digitsSums = [];
    }
}

/**
 * Example of use next.
 */
$production = new CalcProduction();

$a = '-111111111111111111111111111111111111111111111111111111111111111111111';
$b = '9999999999999999999999999999999999999999999999999999999999999999999999';

echo $production->calc($a, $b);
