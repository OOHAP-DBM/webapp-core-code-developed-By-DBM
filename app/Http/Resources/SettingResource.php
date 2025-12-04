<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'key' => $this->key,
            'value' => $this->getTypedValue(),
            'type' => $this->type,
            'description' => $this->description,
            'group' => $this->group,
            'tenant_id' => $this->tenant_id,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
