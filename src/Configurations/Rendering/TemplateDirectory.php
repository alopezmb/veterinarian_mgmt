<?php declare(strict_types=1);

namespace Felican\Configurations\Rendering;

final class TemplateDirectory
{
    private $templateDirectory;

    public function __construct(string $templateDIR)
    {
        $this->templateDirectory = $templateDIR;
    }

    public function toString(): string
    {
        return $this->templateDirectory;
    }
}