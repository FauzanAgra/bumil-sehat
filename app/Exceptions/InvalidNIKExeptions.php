<?php

namespace App\Exceptions;

use InvalidArgumentException;

class InvalidNIKException extends InvalidArgumentException
{
    public static function invalidFormat(): static
    {
        return new static('Format NIK tidak valid. NIK harus terdiri dari 16 digit angka.');
    }

    public static function invalidRegionCode(string $code): static
    {
        return new static("Kode wilayah NIK '{$code}' tidak valid.");
    }

    public static function invalidBirthDate(string $raw): static
    {
        return new static("Tanggal lahir '{$raw}' yang terenkode di NIK tidak valid.");
    }
}
