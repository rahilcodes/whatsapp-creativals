<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessMemory extends Model
{
    protected $table = 'business_memories';

    protected $fillable = ['category', 'key', 'value', 'active'];

    protected $casts = ['active' => 'boolean'];

    // ── Get all active entries formatted for AI prompt ────────
    public static function forPrompt(): string
    {
        $entries = static::where('active', true)->orderBy('category')->get();
        if ($entries->isEmpty()) {
            return 'No business information configured yet.';
        }

        $grouped = $entries->groupBy('category');
        $lines = [];
        foreach ($grouped as $category => $items) {
            $lines[] = "\n## " . ucfirst($category);
            foreach ($items as $item) {
                $lines[] = "- **{$item->key}**: {$item->value}";
            }
        }
        return implode("\n", $lines);
    }

    public static function categories(): array
    {
        $defaults = ['services', 'pricing', 'menu', 'faqs', 'hours', 'contact'];
        $dbCats = static::select('category')->distinct()->pluck('category')->toArray();
        return array_values(array_unique(array_merge($defaults, $dbCats)));
    }
}
