<?php

namespace App\Exports;

use App\Models\FinancialTransaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FinancialReportExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected int $societyId,
        protected string $from,
        protected string $to,
    ) {}

    public function collection()
    {
        return FinancialTransaction::where('society_id', $this->societyId)
            ->whereBetween('transaction_date', [$this->from, $this->to])
            ->orderBy('transaction_date')
            ->get()
            ->map(fn ($t) => [
                $t->transaction_date->format('Y-m-d'),
                $t->type,
                $t->category,
                $t->amount,
                $t->description,
            ]);
    }

    public function headings(): array
    {
        return ['Date', 'Type', 'Category', 'Amount', 'Description'];
    }
}
