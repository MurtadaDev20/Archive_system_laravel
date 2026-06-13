<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\DocumentType;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EdmsTaxonomySeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'عقد', 'slug' => 'contract', 'description' => 'عقود واتفاقيات'],
            ['name' => 'تقرير', 'slug' => 'report', 'description' => 'تقارير دورية'],
            ['name' => 'مراسلة', 'slug' => 'correspondence', 'description' => 'خطابات ومراسلات'],
            ['name' => 'فاتورة', 'slug' => 'invoice', 'description' => 'فواتير مالية'],
            ['name' => 'محضر', 'slug' => 'minutes', 'description' => 'محاضر اجتماعات'],
        ];

        foreach ($types as $type) {
            DocumentType::firstOrCreate(['slug' => $type['slug']], $type);
        }

        $categories = [
            ['name' => 'موارد بشرية', 'slug' => 'hr'],
            ['name' => 'مالية', 'slug' => 'finance'],
            ['name' => 'قانونية', 'slug' => 'legal'],
            ['name' => 'تقنية المعلومات', 'slug' => 'it'],
            ['name' => 'عام', 'slug' => 'general'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['slug' => $cat['slug']], $cat);
        }

        $tags = ['عاجل', 'سري', 'داخلي', 'خارجي', 'يتطلب اعتماد'];
        foreach ($tags as $tag) {
            Tag::firstOrCreate(['slug' => Str::slug($tag)], ['name' => $tag, 'color' => 'primary']);
        }
    }
}
