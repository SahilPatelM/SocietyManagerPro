<?php

namespace App\Services;

use App\Models\FinancialTransaction;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Services\Audit\AuditLogService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FinanceService
{
    public function __construct(
        protected TransactionRepositoryInterface $repository,
        protected AuditLogService $audit,
    ) {}

    public function store(array $data, ?array $files = null): FinancialTransaction
    {
        $transaction = $this->repository->create($data);

        if ($files) {
            $this->storeAttachments($transaction, $files);
        }

        $this->audit->log('transaction.created', $transaction);

        return $transaction->load('attachments');
    }

    public function update(FinancialTransaction $transaction, array $data, ?array $files = null): FinancialTransaction
    {
        $old = $transaction->toArray();
        $updated = $this->repository->update($transaction, $data);

        if ($files) {
            $this->storeAttachments($updated, $files);
        }

        $this->audit->log('transaction.updated', $updated, $old);

        return $updated;
    }

    public function delete(FinancialTransaction $transaction): bool
    {
        $this->audit->log('transaction.deleted', $transaction);

        return $this->repository->delete($transaction);
    }

    protected function storeAttachments(FinancialTransaction $transaction, array $files): void
    {
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $path = $file->store("societies/{$transaction->society_id}/transactions", 'public');
                $transaction->attachments()->create([
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                ]);
            }
        }
    }
}
