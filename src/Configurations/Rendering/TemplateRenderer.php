<?php declare(strict_types=1);

namespace Felican\Configurations\Rendering;

interface TemplateRenderer
{
    public function render(string $template, array $data = []): string;
}