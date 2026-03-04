<?php

namespace Logicoforms\Forms\Tests\Unit;

use Logicoforms\Forms\Services\FormTemplateService;
use Logicoforms\Forms\Tests\TestCase;

class FormTemplateServiceTest extends TestCase
{
    public function test_all_returns_templates_with_expected_keys(): void
    {
        $templates = FormTemplateService::all();

        $this->assertIsArray($templates);
        $this->assertNotEmpty($templates);

        $first = $templates[0];
        $this->assertArrayHasKey('slug', $first);
        $this->assertArrayHasKey('title', $first);
        $this->assertArrayHasKey('description', $first);
        $this->assertArrayHasKey('category', $first);
        $this->assertArrayHasKey('questions', $first);
    }

    public function test_categories_returns_unique_list(): void
    {
        $categories = FormTemplateService::categories();

        $this->assertIsArray($categories);
        $this->assertNotEmpty($categories);
        $this->assertEquals(array_unique($categories), $categories);
    }

    public function test_find_returns_template_by_slug(): void
    {
        $templates = FormTemplateService::all();
        $slug = $templates[0]['slug'];

        $found = FormTemplateService::find($slug);
        $this->assertNotNull($found);
        $this->assertEquals($slug, $found['slug']);
    }

    public function test_find_returns_null_for_unknown_slug(): void
    {
        $result = FormTemplateService::find('nonexistent-slug-12345');
        $this->assertNull($result);
    }
}
