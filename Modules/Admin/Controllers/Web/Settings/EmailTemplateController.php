<?php

namespace Modules\Admin\Controllers\Web\Settings;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\EmailLayout;
use Modules\Admin\Services\EmailTemplateService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmailTemplateController extends Controller
{
    public function __construct(
        protected EmailTemplateService $service
    ) {}

    // -------------------------
    // LIST — saare templates
    // -------------------------
    public function index(): View
    {
        $templates = $this->service->getAllTemplates();
        $layouts   = $this->service->getAllLayouts();

        return view('admin.settings.email-templates.index', compact('templates', 'layouts'));
    }

    // -------------------------
    // CREATE — form dikhao
    // -------------------------
    public function create(): View
    {
        $layouts = $this->service->getAllLayouts();

        return view('admin.settings.email-templates.create', compact('layouts'));
    }

    // -------------------------
    // STORE — naya template save karo
    // -------------------------
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'layout_id'       => 'required|exists:email_layouts,id',
            'template_key'    => 'required|string|unique:email_templates,template_key|max:100',
            'name'            => 'required|string|max:255',
            'subject'         => 'required|string|max:255',
            'body_html'       => 'required|string',
            'variables_schema'=> 'nullable|array',
            'is_active'       => 'nullable|boolean',
        ]);

        $this->service->createTemplate($validated);

        return redirect()
            ->route('mail.configuration.index')
            ->with('success', 'Email template successfully create ho gaya!');
    }

    // -------------------------
    // SHOW — single template dekho (preview)
    // -------------------------
    public function show(EmailTemplate $emailTemplate): View
    {
        $emailTemplate->load('layout', 'variables');

        return view('admin.settings.email-templates.show', compact('emailTemplate'));
    }

    // -------------------------
    // EDIT — edit form dikhao
    // -------------------------
    public function edit(EmailTemplate $emailTemplate): View
    {
        $layouts = $this->service->getAllLayouts();
        $emailTemplate->load('variables');

        return view('admin.settings.email-templates.edit', compact('emailTemplate', 'layouts'));
    }

    // -------------------------
    // UPDATE — template update karo
    // -------------------------
    public function update(Request $request, EmailTemplate $emailTemplate): RedirectResponse
    {
        $validated = $request->validate([
            'layout_id'        => 'required|exists:email_layouts,id',
            'name'             => 'required|string|max:255',
            'subject'          => 'required|string|max:255',
            'body_html'        => 'required|string',
            'variables_schema' => 'nullable|array',
            'is_active'        => 'nullable|boolean',
        ]);

        $this->service->updateTemplate($emailTemplate, $validated);

        return redirect()
            ->route('mail.configuration.index')
            ->with('success', 'Email template successfully update ho gaya!');
    }

    // -------------------------
    // DESTROY — template delete karo
    // -------------------------
    public function destroy(EmailTemplate $emailTemplate): RedirectResponse
    {
        $this->service->deleteTemplate($emailTemplate);

        return redirect()
            ->route('mail.configuration.index')
            ->with('success', 'Email template successfully delete ho gaya!');
    }

    // -------------------------
    // TOGGLE STATUS — active/inactive karo
    // -------------------------
    public function toggleStatus(EmailTemplate $emailTemplate): RedirectResponse
    {
        $this->service->toggleStatus($emailTemplate);

        return redirect()
            ->route('mail.configuration.index')
            ->with('success', 'Template status update ho gaya!');
    }

    // -------------------------
    // PREVIEW — rendered email dekho
    // -------------------------
    public function preview(EmailTemplate $emailTemplate): View
    {
        $rendered = $this->service->previewTemplate($emailTemplate);

        return view('admin.settings.email-templates.preview', compact('emailTemplate', 'rendered'));
    }
}