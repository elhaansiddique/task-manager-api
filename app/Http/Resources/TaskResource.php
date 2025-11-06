<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'assigned_to' => new UserResource($this->assignee),
            'created_by' => new UserResource($this->creator),
            'logs' => TaskLogResource::collection($this->logs),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
