<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_number' => $this->student_number,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'course_id' => $this->course_id,
            'course' => $this->whenLoaded('course', fn () => [
                'id' => $this->course->id,
                'course_code' => $this->course->course_code,
                'name' => $this->course->name,
            ]),
            'year_level_id' => $this->year_level_id,
            'year_level' => $this->whenLoaded('yearLevel', fn () => [
                'id' => $this->yearLevel->id,
                'year_level' => $this->yearLevel->year_level,
                'name' => $this->yearLevel->name,
            ]),
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'address' => $this->address,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
