<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ClRut implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        [$num, $dv] = $this->split($value);

        if ($num === null || $dv === null) {
            $fail('RUT inválido.');
            return;
        }

        if (!$this->isValid($num, $dv)) {
            $fail('RUT inválido.');
        }
    }

    private function split($rut): array
    {
        if (!is_string($rut) && !is_numeric($rut)) {
            return [null, null];
        }

        $clean = strtoupper(preg_replace('/[^0-9Kk-]/', '', (string)$rut));
        $clean = str_replace('.', '', $clean);

        if (str_contains($clean, '-')) {
            [$num, $dv] = explode('-', $clean, 2);
        } else {
            $num = substr($clean, 0, -1);
            $dv  = substr($clean, -1);
        }

        if ($num === '' || $dv === '' || !preg_match('/^\d+$/', $num) || !preg_match('/^[0-9K]$/', $dv)) {
            return [null, null];
        }

        if (strlen($num) < 6 || strlen($num) > 9) {
            return [null, null];
        }

        return [$num, strtoupper($dv)];
    }

    private function isValid(string $num, string $dv): bool
    {
        $sum = 0;
        $mul = 2;

        for ($i = strlen($num) - 1; $i >= 0; $i--) {
            $sum += intval($num[$i]) * $mul;
            $mul++;
            if ($mul > 7) $mul = 2;
        }

        $resto = $sum % 11;
        $calc  = 11 - $resto;

        $dvCalc = match ($calc) {
            11 => '0',
            10 => 'K',
            default => (string)$calc,
        };

        return $dvCalc === $dv;
    }
}
