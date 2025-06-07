<?php

namespace App\Exports;

use App\Models\StockMovement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StockInExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
   public function collection()
    {
        return StockMovement::with('product.category')
            ->where('type', 'in')
            ->get()
            ->map(function ($item) {
                return [
                    'ID' => $item->id,
                    'Product' => $item->product->name ?? '-',
                    'Category' => $item->product->category->name ?? '-',
                    'Size' => $item->product->size ?? '-',
                    'Qty' => $item->quantity,
                    'Created At' => $item->created_at->format('Y-m-d H:i:s'),
                ];
            });
    }

    public function headings(): array
    {
        return ['ID', 'Product', 'Category', 'Qty','Size','Created At'];
    }
}
