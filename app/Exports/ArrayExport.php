<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ArrayExport implements FromArray, WithHeadings, WithColumnWidths, WithStyles
{
    protected $rows;
    protected $headings;

    public function __construct(array $rows, array $headings = [])
    {
        $this->rows = $rows;
        $this->headings = $headings;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings ?: array_keys($this->rows[0] ?? []);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25, 
            'B' => 30, 
            'C' => 20, 
            'D' => 25, 
            'E' => 30,
            'F' => 15, 
            'G' => 15,
            'H' => 15,
            'I' => 15,
            'J' => 15,
            'K' => 15,
            'L' => 15,
            'M' => 25,
            'N' => 20,
            'O' => 40,
            'P' => 15,
            'Q' => 15,
            'R' => 15,
            'S' => 15,
            'T' => 15,
            'U' => 15,
            'V' => 15,
            'W' => 15,
            'X' => 15,
            'Y' => 15,
            'Z' => 15,
            
        ];
    }

    // Optional: style header row
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]] // Make first row bold
        ];
    }
}
