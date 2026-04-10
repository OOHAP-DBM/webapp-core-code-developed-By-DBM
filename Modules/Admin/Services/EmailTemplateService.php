<?php

namespace Modules\Admin\Services;

use App\Models\EmailTemplate;
use App\Models\EmailLayout;
use App\Models\EmailLog;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EmailTemplateService
{
    // -------------------------
    // saare templates — paginated
    // -------------------------
    public function getAllTemplates(): LengthAwarePaginator
    {
        return EmailTemplate::with('layout')
            ->latest()
            ->paginate(10);
    }

    // -------------------------
    // saare layouts — dropdown ke liye
    // -------------------------
    public function getAllLayouts(): Collection
    {
        return EmailLayout::active()->get(['id', 'logo_url', 'primary_color']);
    }

    // -------------------------
    // single template by ID
    // -------------------------
    public function getTemplateById(int $id): EmailTemplate
    {
        return EmailTemplate::with(['layout', 'variables'])->findOrFail($id);
    }

    // -------------------------
    // template_key se dhundo
    // -------------------------
    public function getTemplateByKey(string $key): EmailTemplate
    {
        return EmailTemplate::with('layout')
            ->where('template_key', $key)
            ->firstOrFail();
    }

    // -------------------------
    // naya template banao
    // -------------------------
    public function createTemplate(array $data): EmailTemplate
    {
        // is_active default true agar nahi aaya
        $data['is_active'] = $data['is_active'] ?? true;

        return EmailTemplate::create($data);
    }

    // -------------------------
    // template update karo
    // -------------------------
    public function updateTemplate(EmailTemplate $template, array $data): EmailTemplate
    {
        $data['is_active'] = $data['is_active'] ?? $template->is_active;

        $template->update($data);

        return $template->fresh(['layout', 'variables']);
    }

    // -------------------------
    // template delete karo
    // -------------------------
    public function deleteTemplate(EmailTemplate $template): void
    {
        // pehle check karo koi log toh nahi hai
        if ($template->logs()->exists()) {
            // logs hain toh sirf soft delete ya inactive karo
            $template->update(['is_active' => false]);
            return;
        }

        $template->delete();
    }

    // -------------------------
    // active/inactive toggle
    // -------------------------
    public function toggleStatus(EmailTemplate $template): EmailTemplate
    {
        $template->update([
            'is_active' => !$template->is_active
        ]);

        return $template->fresh();
    }

    // -------------------------
    // preview — variables ko dummy data se replace karo
    // -------------------------
    public function previewTemplate(EmailTemplate $template): array
    {
        // dummy data banao variables se
        $dummyData = [];

        if (!empty($template->variables_schema)) {
            foreach ($template->variables_schema as $variable) {
                $dummyData[$variable] = '{{ ' . $variable . ' }}';
            }
        }

        return $template->render($dummyData);
    }

    // -------------------------
    // actual email render karo real data se
    // -------------------------
    public function renderTemplate(string $templateKey, array $data): array
    {
        $template = $this->getTemplateByKey($templateKey);

        // check karo required variables aaye hain ya nahi
        $this->validateVariables($template, $data);

        return $template->render($data);
    }

    // -------------------------
    // variables validate karo
    // -------------------------
    private function validateVariables(EmailTemplate $template, array $data): void
    {
        $required = $template->variables()
            ->where('is_required', true)
            ->pluck('variable_name')
            ->toArray();

        $missing = array_diff($required, array_keys($data));

        if (!empty($missing)) {
            throw new \InvalidArgumentException(
                'Ye variables missing hain: ' . implode(', ', $missing)
            );
        }
    }

    // -------------------------
    // email log save karo
    // -------------------------
    public function logEmail(
        EmailTemplate $template,
        string $recipientEmail,
        array $rendered,
        string $status = 'sent',
        ?string $errorMessage = null
    ): EmailLog {
        return EmailLog::create([
            'template_id'     => $template->id,
            'recipient_email' => $recipientEmail,
            'subject_final'   => $rendered['subject'],
            'body_final'      => $rendered['body'],
            'status'          => $status,
            'error_message'   => $errorMessage,
            'sent_at'         => $status === 'sent' ? now() : null,
        ]);
    }
}