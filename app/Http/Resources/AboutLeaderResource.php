<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AboutLeaderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'designation' => $this->designation,
            'bio' => $this->bio,
            'image' => $this->image ? url($this->image) : null,
        ];
    }
}

