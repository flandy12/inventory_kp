<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'category'    => $this->category->name,
            'price'       => $this->price,
            'stock'       => $this->stock,
            'size'        => $this->size,
            'color'       => $this->color,
            'description' => $this->description,
            'image'   => $this->image ? asset('storage/' . $this->image) : null,
        ];
    }
}
