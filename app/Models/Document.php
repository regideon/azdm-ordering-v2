<?php

namespace App\Models;

// use Laravel\Scout\Searchable;
// use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
// use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


/**
 * 4) First-time indexing

 * If you want to index only Orders (document_type_id=1), you already handled it in shouldBeSearchable() and makeAllSearchableUsing().
 * php artisan scout:import "App\Models\Document"
  
 * OR
 * Important production tip (with your scale: millions of rows). Use chunking when reimporting:
 * php artisan scout:import "App\Models\Document" --chunk=1000
  
 * 5) Production queue worker (Forge)
 * If SCOUT_QUEUE=true, add a Forge daemon:
 * php artisan queue:work --sleep=1 --tries=3 --timeout=120
 */
class Document extends Model
{
    use SoftDeletes;
    // use SoftDeletes, InteractsWithMedia;
    //  use Searchable;

    protected $guarded = ['id'];

    protected $casts = [
        'attachments' => 'array',
        'attachments_qc' => 'array',
        'attachments_packing' => 'array',
        'attachments_clerk' => 'array',
        'attachments_delivery' => 'array',
        'attachments_orderchecker' => 'array',

        'attachments_spaces_s3' => 'array',
        'attachments_spaces_s3' => 'array',
        'attachments_spaces_s3' => 'array',
        'attachments_spaces_s3' => 'array',
        'attachments_spaces_s3' => 'array',
        'attachments_spaces_s3' => 'array',
        //'document_status_id' => DocumentStatus::class,
    ];
    

    public function items() : HasMany {
        return $this->hasMany(DocumentItem::class);
    }

    public function searchableAs(): string
    {
        return 'documents';
    }

    /**
     * Optional: only index Orders (document_type_id = 1)
     * and only recent records if you want to control Algolia cost.
     */
    public function shouldBeSearchable(): bool
    {
        // Keep only Orders in Algolia:
        if ((int) $this->document_type_id !== 1) {
            return false;
        }

        // Optional cost-control: only last 12 months
        // return $this->created_at?->gte(now()->subMonths(12)) ?? false;

        return true;
    }

    /**
     * IMPORTANT: Use existing "flattened" columns (items_search, customer_name, etc.)
     * so indexing does NOT query DocumentItem rows (super important at 2M records).
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (int) $this->id,

            'document_number' => (string) ($this->document_number ?? ''),
            'tracking_number' => (string) ($this->tracking_number ?? ''),

            'customer_name' => (string) ($this->customer_name ?? ''),
            'customer_nick' => (string) ($this->customer_nick ?? ''),
            'items_search'  => (string) ($this->items_search ?? ''),

            'document_status_id' => (int) ($this->document_status_id ?? 0),
            'payment_sort_id'    => (int) ($this->payment_sort_id ?? 0),

            'grand_amount' => (float) ($this->grand_amount ?? 0),

            'created_at' => optional($this->created_at)->timestamp,
            'issued_at'  => optional($this->issued_at)->timestamp,
        ];
    }

    /**
     * Optional: Make the import query lighter (important for 2M rows)
     */
    protected function makeAllSearchableUsing($query)
    {
        return $query
            ->where('document_type_id', 1)
            ->select([
                'id',
                'document_type_id',
                'document_status_id',
                'payment_sort_id',
                'customer_id',
                'document_number',
                'tracking_number',
                'customer_name',
                'customer_nick',
                'items_search',
                'grand_amount',
                'created_at',
                'issued_at',
            ]);
    }

    public function getImageUrlAttribute()
    {
        return env('DO_SPACES_CDN_ENDPOINT') . '/' . $this->image;
    }

    public function customer() : BelongsTo {
        return $this->belongsTo(Customer::class);
    }

    public function vendor() : BelongsTo {
        return $this->belongsTo(Vendor::class);
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

    public function bankStatementBanks() : BelongsToMany {
        // return $this->belongsToMany(BankStatementBank::class);
        return $this->belongsToMany(
            BankStatementBank::class,
            'bank_statement_bank_document',
            'document_id',
            'bank_statement_bank_id'
        );
    }
    
    public static function setIfDisabled($id) : bool {
        $enablePaymentTransactionToUsers = true;
        if ($enablePaymentTransactionToUsers) {
            return false;

        } else {
            $document = Document::findOrFail($id);
            if ($document->document_status_id >= 5) {
                return true;
            }
            return false;
        }        
    }

    public function createdBy() : BelongsTo {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documentTotal() : HasMany {
        return $this->hasMany(DocumentTotal::class);
    }

    public function computeDocumentTotal($type, $id) : string {
        //return 120.12;
        $amount = 0;
        $documentTotal = DocumentTotal::where('document_id', $id)->where('code', $type)->firstOrFail();        
        $amount = $documentTotal->amount;

        return number_format((float)$amount, 2, '.', ',');
    }

    public function getLastDocumentNumber() {
        $data = Document::latest()->first();
        if ($data) {
            $newDocumentNumber = $data->id + 1;
        } else {
            $newDocumentNumber = 1;
        }
        
        $newIdStrPad = str_pad($newDocumentNumber, 7, "0", STR_PAD_LEFT);
        $newDocumentNumber = env('PREFIX_INVOICE') . $newIdStrPad;
        return $newDocumentNumber;
    }

    //public static function stateUpdatedForGrandAmount($items = null, $discountType = null, $discountValue = null, $taxId = null) : string {
    public function stateUpdatedForGrandAmount($id, $discountType = null, $discountValue = null, $taxId = null) : string {
        // if (!in_array($id, $this->productIds)) {
        //     $this->productIds[] = $id;
        // }
        $this->productIds[] = $id;

        DB::table('_debug')->insert([
            'content' => json_encode($this->productIds),   
        ]);
        $grandAmount = "";
        // if ($items) {
        //     $amount = 0;
        //     foreach ($items as $item) {
        //         $amount += $item['price'];
        //     }
        //     $grandAmount = "$amount";
        // }
        
        return $grandAmount;
    }

    // public function canAccessPanel(Panel $panel): bool
    // {
    //     //return $this->hasRole('Superadmin');
    //     return $this->hasRole(['Superadmin']);
    //     //return str_ends_with($this->email, '@yourdomain.com');
    // }

    public function documentHistoriesNew() : HasMany {
        return $this->hasMany(DocumentHistoryNew::class);
    }

    public function getItemsSummaryAttribute()
    {
        // $formatted = $this->items
        //     ->map(function ($item) {
        //         $name = "{$item->name}";
        //         $desc = $item->description ? " ({$item->description})" : '';
        //         return $name . $desc;
        //     })
        //     ->implode(', ');
        $formatted = $this->items
            ->map(function ($item) {
                $name = "{$item->name}";
                $desc = $item->description ? " ({$item->description})" : '';
                return $name;
            })
            ->implode(', ');

        return new HtmlString($formatted);
    }
}
