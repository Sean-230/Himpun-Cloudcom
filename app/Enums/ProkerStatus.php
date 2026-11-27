<?php

namespace App\Enums;

enum ProkerStatus: string
{
    case Usulan = 'Usulan';
    case PembentukanPanitia = 'Pembentukan panitia';
    case Berjalan = 'Berjalan';
    case Lpj = 'LPJ';
    case Selesai = 'Selesai';

    public function label(): string
    {
        return $this->value;
    }

    public function pillClass(): string
    {
        return match ($this) {
            self::Berjalan => 'bg-[#e6f4ec] text-good',
            self::PembentukanPanitia => 'bg-[#fbf0dc] text-warn',
            self::Lpj => 'bg-[#e8f0f7] text-actual',
            self::Usulan, self::Selesai => 'bg-black/5 text-muted',
        };
    }

    public function group(): string
    {
        return match ($this) {
            self::Usulan => 'usulan',
            self::Selesai => 'selesai',
            default => 'berjalan',
        };
    }

    public function isOpen(): bool
    {
        return $this !== self::Selesai;
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
