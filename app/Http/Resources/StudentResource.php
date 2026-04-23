<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nisn' => $this->nisn,
            'pob' => $this->pob,
            'dob' => $this->dob,
            'gender' => $this->gender,
            'classroom' => new ClassroomResource($this->whenLoaded('classroom')),
        ];
    }
}
