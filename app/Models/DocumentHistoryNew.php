<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentHistoryNew extends Model
{
    protected $table = 'document_histories_new';

    protected $guarded = [];

    protected $casts = [
        'attachments' => 'array',
        'attachments_qc' => 'array',
        'attachments_packing' => 'array',
        'attachments_clerk' => 'array',
        'attachments_delivery' => 'array',
        'attachments_orderchecker' => 'array',
    ];  
    
    
    public function customer() : BelongsTo {
        return $this->belongsTo(Customer::class);
    }

    public function vendor() : BelongsTo {
        return $this->belongsTo(Vendor::class);
    }

    public function items() : HasMany {
        return $this->hasMany(DocumentItem::class);
    }

    public function transactions() : HasMany {
        return $this->hasMany(Transaction::class);
    }

    public function documentStatus() : BelongsTo {
        return $this->belongsTo(DocumentStatus::class);
        
        // set statuses based on roles
        if (auth()->user()->hasRole(['Superadmin', 'Admin'])) {
            return $this->belongsTo(DocumentStatus::class);

        } else if (auth()->user()->hasRole(['Encoder'])) {
            if (isset($this->document_status_id)) {
                if ($this->document_status_id == 4) {
                    return $this->belongsTo(DocumentStatus::class)->whereIn('id', [4,20])->orderBy('id', 'asc');
                }
            }
            return $this->belongsTo(DocumentStatus::class)->whereIn('id', [1,4]);

        } else if (auth()->user()->hasRole(['Accounting'])) {
            if (isset($this->document_status_id)) {
                if (in_array($this->document_status_id, [4,5,15,20])) {
                    return $this->belongsTo(DocumentStatus::class)->whereIn('id', [4,5,15,20])->orderBy('id', 'asc');
                }
            }
            return $this->belongsTo(DocumentStatus::class)->whereIn('id', [1,4]);
            // return $this->belongsTo(DocumentStatus::class)->whereIn('id', [5,15,20])->orderBy('id', 'asc');

        } else if (auth()->user()->hasRole(['Order Control Specialist'])) {
            if (isset($this->document_status_id)) {
                if ($this->document_status_id == 5 || $this->document_status_id == 15) {
                    return $this->belongsTo(DocumentStatus::class)->whereIn('id', [6,20])->orderBy('id', 'asc');
                }
            }
            return $this->belongsTo(DocumentStatus::class)->whereIn('id', [1,4]);

        } else if (auth()->user()->hasRole([''])) {
        } else if (auth()->user()->hasRole([''])) {
        }


        return $this->belongsTo(DocumentStatus::class);
    }

    public function documentStatusAll() : BelongsTo {
        return $this->belongsTo(DocumentStatus::class);
    }

    public function tax() : BelongsTo {
        return $this->belongsTo(Tax::class);
    }

    public function documentShipmentType() : BelongsTo {
        return $this->belongsTo(DocumentShipmentType::class);
    }
    
    public function paymentSort() : BelongsTo {
        return $this->belongsTo(PaymentSort::class);
    }
}
