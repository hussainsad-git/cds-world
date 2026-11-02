<?php

namespace App\Models\Concerns;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasTaxonomy
{
    public function categories(): MorphToMany
    {
        return $this->morphToMany(Category::class, 'categoryable');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
