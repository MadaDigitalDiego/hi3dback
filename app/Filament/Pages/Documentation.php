<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Documentation extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static string $view = 'filament.pages.documentation';
    
    protected static ?string $navigationLabel = 'Documentation';
    
    protected static ?string $title = 'Documentation du Back-Office';
    
    protected static ?string $navigationGroup = 'Aide';
    
    protected static ?int $navigationSort = 1;
}
