<?php

namespace NoisyWinds\Smartmd;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Config\Repository as ConfigContract;
use InvalidArgumentException;
use Parsedown;

/**
 * Core Smartmd class for markdown processing and editor management.
 * 
 * This class provides the main interface for interacting with the Smartmd
 * markdown editor, including configuration management and markdown processing.
 * 
 * @package NoisyWinds\Smartmd
 * @author noisywinds
 * @since 2.0.0
 */
class Smartmd
{
    /**
     * Configuration repository instance.
     */
    protected ConfigContract $config;

    /**
     * Parsedown markdown parser instance.
     */
    protected Parsedown $markdown;

    /**
     * Smartmd markdown parser instance.
     */
    protected Markdown $smartmdParser;

    /**
     * Default configuration values.
     *
     * @var array<string, mixed>
     */
    protected array $defaultConfig = [
        'editor' => [
            'auto_save' => true,
            'auto_save_delay' => 3000,
            'theme' => 'default',
            'line_numbers' => true,
            'line_wrapping' => true,
        ],
        'image' => [
            'max_size' => 4096, // KB
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'quality' => 80,
            'max_width' => 1200,
        ],
        'security' => [
            'sanitize_html' => true,
            'allowed_html_tags' => ['p', 'br', 'strong', 'em', 'code', 'pre', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
        ],
    ];

    /**
     * Create a new Smartmd instance.
     *
     * @param ConfigContract $config Laravel configuration repository
     * @throws InvalidArgumentException When configuration is invalid
     */
    public function __construct(ConfigContract $config)
    {
        $this->config = $config;
        $this->markdown = new Parsedown();
        $this->smartmdParser = new Markdown();
        
        $this->validateConfiguration();
        $this->configureMarkdownParser();
    }

    /**
     * Get configuration value with optional default.
     *
     * @param string $key Configuration key using dot notation
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    public function config(string $key, mixed $default = null): mixed
    {
        return $this->config->get("smartmd.{$key}", 
            $this->getDefaultConfig($key, $default)
        );
    }

    /**
     * Parse markdown text to HTML.
     *
     * @param string $text Markdown text to parse
     * @param array<string, mixed> $options Parsing options
     * @return string Parsed HTML
     */
    public function parse(string $text, array $options = []): string
    {
        $parser = $options['use_enhanced'] ?? true 
            ? $this->smartmdParser 
            : $this->markdown;

        return $parser->text($text);
    }

    /**
     * Get the enhanced markdown parser instance.
     *
     * @return Markdown
     */
    public function getMarkdownParser(): Markdown
    {
        return $this->smartmdParser;
    }

    /**
     * Get the basic parsedown instance.
     *
     * @return Parsedown
     */
    public function getParsedown(): Parsedown
    {
        return $this->markdown;
    }

    /**
     * Validate the current configuration.
     *
     * @throws InvalidArgumentException When configuration is invalid
     */
    protected function validateConfiguration(): void
    {
        $imageRoot = $this->config('image.root');
        if ($imageRoot && !is_string($imageRoot)) {
            throw new InvalidArgumentException('Image root path must be a string');
        }

        $imageUrl = $this->config('image.url');
        if ($imageUrl && !is_string($imageUrl)) {
            throw new InvalidArgumentException('Image URL must be a string');
        }

        $maxSize = $this->config('image.max_size', 4096);
        if (!is_int($maxSize) || $maxSize <= 0) {
            throw new InvalidArgumentException('Image max size must be a positive integer');
        }
    }

    /**
     * Configure the markdown parser with security settings.
     */
    protected function configureMarkdownParser(): void
    {
        $this->markdown->setSafeMode($this->config('security.sanitize_html', true));
        $this->smartmdParser->setSafeMode($this->config('security.sanitize_html', true));
    }

    /**
     * Get default configuration value using dot notation.
     *
     * @param string $key
     * @param mixed $fallback
     * @return mixed
     */
    protected function getDefaultConfig(string $key, mixed $fallback = null): mixed
    {
        $keys = explode('.', $key);
        $value = $this->defaultConfig;

        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $fallback;
            }
            $value = $value[$segment];
        }

        return $value;
    }
}
