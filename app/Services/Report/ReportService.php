<?php

namespace App\Services\Report;

use App\Repositories\Contracts\TransactionRepositoryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FinancialReportExport;

class ReportService
{
    public function __construct(
        protected TransactionRepositoryInterface $transactions,
    ) {}

    public function balanceSheet(int $societyId, string $from, string $to): array
    {
        $income = $this->transactions->sumByType($societyId, 'income', $from, $to);
        $expense = $this->transactions->sumByType($societyId, 'expense', $from, $to);

        return [
            'period' => ['from' => $from, 'to' => $to],
            'total_income' => $income,
            'total_expense' => $expense,
            'net_balance' => $income - $expense,
        ];
    }

    public function exportPdf(int $societyId, string $from, string $to, string $reportType)
    {
        $data = $this->balanceSheet($societyId, $from, $to);
        $data['report_type'] = $reportType;
        $data['society_name'] = 'Society Manager Pro';

        return Pdf::loadView('reports.financial', $data)->download("report-{$from}-{$to}.pdf");
    }

    public function exportExcel(int $societyId, string $from, string $to)
    {
        return Excel::download(
            new FinancialReportExport($societyId, $from, $to),
            "report-{$from}-{$to}.xlsx"
        );
    }
}
