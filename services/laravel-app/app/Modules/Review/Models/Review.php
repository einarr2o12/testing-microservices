<?php

namespace App\Modules\Review\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Product\Models\Product;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'rating',
        'comment',
        'reviewer_name',
        'reviewer_email',
        'is_approved'
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'boolean'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}