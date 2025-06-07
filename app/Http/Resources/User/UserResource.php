<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'    => encrypt($this->id), // Enkripsi ID di sini
            'name'  => $this->name,
            'email' => $this->email,
            'profile_url' => $this->profile_photo_url
            // Tambah field lain sesuai kebutuhan
        ];
    }
}
