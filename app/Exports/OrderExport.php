<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;


class OrderExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
 
    protected $laundry_id;

    public function __construct($laundry_id = null)
    {
        $this->laundry_id = $laundry_id;
    }

    public function collection()
    {
    $query = Order::with(['OrderItems.Service', 'Laundry','OrderType']) 
        ->select([
            "id",           
            "order_number",   
            "type_order",     
            "pickup_time",   
            "delivery_time",  
            "order_date",     
            "status",         
            "base_cost",      
            "paid",          
            "note",           
            "point",
            "order_type_id",
            "laundry_id"    
        ]);

      if ($this->laundry_id) {
        $query->where('laundry_id', $this->laundry_id);
    }

  
    return $query->get();
}
    public function headings(): array
    {
        return [
            'Order Number',  
            'Type of Order', 
            'Pickup Time',   
            'Delivery Time', 
            'Order Date',    
            'Status',       
            'Base Cost',   
            'Paid',          
            'Note',          
            'Point',   
            'Type order',
            'Lanudry Name',
            'Item Name',  
            'Service Name',    
            'Quantity',      
            'Price',       
        ];
    }

    public function map($order): array
    {
        $rows = [];

        foreach ($order->OrderItems as $item) {
            $rows[] = [
                $order->order_number,
                $order->type_order,
                $order->pickup_time,
                $order->delivery_time,
                $order->order_date,
                $order->status,
                $order->base_cost,
                $order->paid,
                $order->note,
                $order->point,
                $order->OrderType->type,
                $order->Laundry->name_en, 
                $item->LaundryItem->item_type_en ?? 'N/A',
                $item->Service->name_en ?? 'N/A',  
                $item->quantity,   
                $item->price       
            ];
        }

        return $rows;
    }

}

