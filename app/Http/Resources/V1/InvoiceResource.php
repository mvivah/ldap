<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
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
            'customer' => $this->customer->name,
            'amount' => $this->amount,
            'status' => $this->status,
            'issuedDate' => $this->issued_date,
            'paidDate' => $this->paid_date,
            'dueDate' => $this->due_date,
        ];
    }
}
