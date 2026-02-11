<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\ViewField;
use Illuminate\Support\HtmlString;

class InfoField extends ViewField
{
    protected string $view = 'filament.forms.components.info-field';

    protected string|\Closure|HtmlString|null $text = null;

    protected string $tone = 'gray'; // gray|primary|success|warning|danger
    protected bool $isHtml = true;   // default true since you use HtmlString a lot

    public function text(string|\Closure|HtmlString $text): static
    {
        $this->text = $text;
        return $this;
    }

    public function tone(string $tone): static
    {
        $this->tone = $tone;
        return $this;
    }

    /**
     * If you want plain text (escaped) instead of HTML rendering.
     */
    public function plain(): static
    {
        $this->isHtml = false;
        return $this;
    }

    public function html(): static
    {
        $this->isHtml = true;
        return $this;
    }

    public function getText(): mixed
    {
        // ✅ supports closures like fn (Get $get), fn (?Document $record), etc.
        return $this->evaluate($this->text);
    }

    public function isHtml(): bool
    {
        return $this->isHtml;
    }

    public function getTone(): string
    {
        return $this->tone;
    }
}
