<?php
namespace App\Exports;

use App\Models\StockMovement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;

class StockOutExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return StockMovement::with('product.category')
            ->where('type', 'out')
            ->get()
            ->map(function ($item) {
                return [
                    'ID' => $item->id,
                    'Product' => $item->product->name ?? '-',
                    'Category' => $item->product->category->name ?? '-',
                    'Qty' => $item->quantity,
                    'Size' => $item->product->size ?? '-',
                    'Created At' => $item->created_at->format('Y-m-d H:i:s'),
                ];
            });
    }

    public function headings(): array
    {
        return ['ID', 'Product', 'Category', 'Qty','Size','Created At'];
    }
}
