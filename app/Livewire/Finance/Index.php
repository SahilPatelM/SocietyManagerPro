<?php

namespace App\Livewire\Finance;

use App\Livewire\Concerns\ShowsToast;
use App\Enums\ExpenseCategory;
use App\Enums\IncomeCategory;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Services\FinanceService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use ShowsToast;
    use WithPagination;

    public ?string $type = null;

    public bool $showForm = false;

    public string $formType = 'income';

    public string $category = 'maintenance';

    public string $amount = '';

    public string $transactionDate = '';

    public string $description = '';

    public function mount(): void
    {
        $this->transactionDate = now()->toDateString();
    }

    public function save(FinanceService $finance, TransactionRepositoryInterface $transactions): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $this->validate([
            'formType' => 'required|in:income,expense',
            'category' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0.01',
            'transactionDate' => 'required|date',
            'description' => 'nullable|string|max:500',
        ]);

        $finance->store([
            'society_id' => auth()->user()->society_id,
            'type' => $this->formType,
            'category' => $this->category,
            'amount' => $this->amount,
            'transaction_date' => $this->transactionDate,
            'description' => $this->description ?: null,
            'created_by' => auth()->id(),
        ]);

        $this->reset(['amount', 'description', 'showForm']);
        $this->transactionDate = now()->toDateString();
        $this->toastSuccess(__('app.finance_saved'));
    }

    public function render(TransactionRepositoryInterface $transactions)
    {
        $incomeCategories = array_column(IncomeCategory::cases(), 'value');
        $expenseCategories = array_column(ExpenseCategory::cases(), 'value');

        return view('livewire.finance.index', [
            'transactions' => $transactions->paginate(auth()->user()->society_id, $this->type),
            'isAdmin' => auth()->user()->isAdmin(),
            'incomeCategories' => $incomeCategories,
            'expenseCategories' => $expenseCategories,
        ])->layout('layouts.mobile', ['title' => __('app.finance')]);
    }
}
