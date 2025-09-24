<?php
namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class FormulariosExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(private Collection $rows) {}

    public function collection() { return $this->rows; }

    public function headings(): array
    {
        return [
            'Organización',
            'Formulario ID','Estado Formulario','Creado',
            'Periodo Año','Periodo Estado',
            'Beneficiario ID','RUT','Nombre Completo','Fecha Nac.','Sexo','Dirección','Tramo ID',
        ];
    }
}
