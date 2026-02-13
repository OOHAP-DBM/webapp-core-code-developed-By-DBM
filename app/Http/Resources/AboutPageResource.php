<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AboutPageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'hero_title' => $this->hero_title,
            'hero_description' => $this->hero_description,
            'section_title' => $this->section_title,
            'section_content' => $this->section_content,
            'hero_image' => $this->hero_image ? url($this->hero_image) : null,
            'section_image' => $this->section_image ? url($this->section_image) : null,
        ];
    }
}
