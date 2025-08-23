<?php

namespace NoisyWinds\Smartmd\Facades;

use Illuminate\Support\Facades\Facade;
use NoisyWinds\Smartmd\Smartmd as SmartmdService;

/**
 * Smartmd facade for easy access to markdown functionality.
 * 
 * This facade provides static access to the Smartmd service, allowing
 * easy integration with Laravel applications through a clean API.
 * 
 * @package NoisyWinds\Smartmd\Facades
 * @author noisywinds
 * @since 2.0.0
 * 
 * @method static string parse(string $text, array $options = []) Parse markdown text to HTML
 * @method static mixed config(string $key, mixed $default = null) Get configuration value
 * @method static \NoisyWinds\Smartmd\Markdown getMarkdownParser() Get the enhanced markdown parser
 * @method static \Parsedown getParsedown() Get the basic parsedown instance
 * 
 * @see \NoisyWinds\Smartmd\Smartmd
 */
class Smartmd extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return SmartmdService::class;
    }

    /**
     * Get the root object behind the facade.
     *
     * @return SmartmdService
     */
    public static function getFacadeRoot(): SmartmdService
    {
        return parent::getFacadeRoot();
    }
}