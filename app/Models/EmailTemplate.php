<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailTemplate extends Model
{
    protected $fillable = [
        'layout_id',
        'template_key',
        'name',
        'subject',
        'body_html',
        'variables_schema',
        'is_active',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'variables_schema' => 'array', // JSON auto decode/encode hoga
    ];

    // ye template kis layout se belong karta hai
    public function layout(): BelongsTo
    {
        return $this->belongsTo(EmailLayout::class, 'layout_id');
    }

    // is template ke saare variables
    public function variables(): HasMany
    {
        return $this->hasMany(EmailTemplateVariable::class, 'template_id');
    }

    // is template ke saare logs
    public function logs(): HasMany
    {
        return $this->hasMany(EmailLog::class, 'template_id');
    }

    // sirf active templates
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // key se template dhundo  e.g. EmailTemplate::findByKey('welcome_user')
    public function scopeFindByKey($query, string $key)
    {
        return $query->where('template_key', $key)->firstOrFail();
    }

    // subject aur body me variables replace karo
    // e.g. $template->render(['name' => 'Rahul', 'email' => 'r@r.com'])
    public function render(array $data): array
    {
        $subject = $this->subject;
        $body    = $this->body_html;

        foreach ($data as $key => $value) {
            $subject = str_replace('{{' . $key . '}}', $value, $subject);
            $body    = str_replace('{{' . $key . '}}', $value, $body);
        }

        // layout ke saath wrap karo
        $layout     = $this->layout;
        $headerHtml = $layout->header_html;
        $footerHtml = $layout->footer_html;

        // layout-level placeholders replace karo
        $layoutReplacements = [
            '{{logo_url}}'      => $layout->logo_url ? asset($layout->logo_url) : '',
            '{{primary_color}}' => $layout->primary_color ?? '#22c55e',
        ];

        $headerHtml = str_replace(
            array_keys($layoutReplacements),
            array_values($layoutReplacements),
            $headerHtml
        );

        $footerHtml = str_replace(
            array_keys($layoutReplacements),
            array_values($layoutReplacements),
            $footerHtml
        );

        $finalBody = $headerHtml . $body . $footerHtml;

        return [
            'subject' => $subject,
            'body'    => $finalBody,
        ];
    }
}