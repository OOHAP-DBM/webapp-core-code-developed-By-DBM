<?php

namespace Modules\Import\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Import\Entities\InventoryImportBatch;
use Modules\Import\Services\ImportApprovalService;
use Exception;

class ImportApprovalController extends Controller
{
    use AuthorizesRequests;

    /**
     * @var ImportApprovalService
     */
    protected ImportApprovalService $approvalService;

    /**
     * Constructor
     *
     * @param ImportApprovalService $approvalService
     */
    public function __construct(ImportApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Approve import batch and create hoardings
     *
     * @param InventoryImportBatch $batch
     * @return JsonResponse
     */
    public function approve(InventoryImportBatch $batch): JsonResponse
    {
        try {
            // Authorization check
            $this->authorize('approve', $batch);

            \Log::info('Approval request received', [
                'batch_id' => $batch->id,
                'vendor_id' => auth()->id(),
            ]);

            // Process approval through service
            $result = $this->approvalService->approveBatch($batch);

            if (!($result['success'] ?? false)) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to approve import batch',
                    'data' => [
                        'batch_id' => $batch->id,
                        'created_count' => $result['created_count'] ?? 0,
                        'failed_count' => $result['failed_count'] ?? 0,
                        'total_processed' => $result['total_processed'] ?? 0,
                        'status' => $result['status'] ?? $batch->fresh()->status,
                    ],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'] ?? 'Import approved and hoardings created successfully',
                'data' => [
                    'batch_id' => $batch->id,
                    'created_count' => $result['created_count'],
                    'failed_count' => $result['failed_count'],
                    'total_processed' => $result['total_processed'],
                    'status' => $result['status'] ?? $batch->fresh()->status,
                ],
            ], 200);
        } catch (Exception $e) {
            \Log::error('Import approval failed', [
                'batch_id' => $batch->id ?? null,
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            $statusCode = $e->getCode() === 0 ? 422 : $e->getCode();

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve import batch',
                'error' => $e->getMessage(),
            ], $statusCode ?: 500);
        }
    }
}
