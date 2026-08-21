<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\FinancialTransaction;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Services\FinanceService;
use App\Services\Report\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FinanceController extends Controller
{
    public function __construct(
        protected TransactionRepositoryInterface $transactions,
        protected FinanceService $finance,
        protected ReportService $reports,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return TransactionResource::collection(
            $this->transactions->paginate(
                $request->user()->society_id,
                $request->get('type')
            )
        );
    }

    public function store(StoreTransactionRequest $request): TransactionResource
    {
        $data = array_merge($request->validated(), [
            'society_id' => $request->user()->society_id,
            'created_by' => $request->user()->id,
        ]);

        return new TransactionResource(
            $this->finance->store($data, $request->file('attachments', []))
        );
    }

    public function update(StoreTransactionRequest $request, FinancialTransaction $transaction): TransactionResource
    {
        return new TransactionResource(
            $this->finance->update($transaction, $request->validated(), $request->file('attachments', []))
        );
    }

    public function destroy(FinancialTransaction $transaction): \Illuminate\Http\JsonResponse
    {
        $this->finance->delete($transaction);

        return response()->json(['message' => 'Deleted']);
    }

    public function balanceSheet(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json(
            $this->reports->balanceSheet(
                $request->user()->society_id,
                $request->get('from', now()->startOfMonth()->toDateString()),
                $request->get('to', now()->toDateString())
            )
        );
    }

    public function exportPdf(Request $request)
    {
        return $this->reports->exportPdf(
            $request->user()->society_id,
            $request->get('from'),
            $request->get('to'),
            $request->get('type', 'balance_sheet')
        );
    }

    public function exportExcel(Request $request)
    {
        return $this->reports->exportExcel(
            $request->user()->society_id,
            $request->get('from'),
            $request->get('to')
        );
    }
}
