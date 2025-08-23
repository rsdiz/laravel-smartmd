<?php
/**
 * Smartmd package configuration file.
 * 
 * This configuration file defines settings for the Laravel Smartmd package,
 * including image handling, editor behavior, security settings, and more.
 * 
 * @package NoisyWinds\Smartmd
 * @author noisywinds
 * @since 2.0.0
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Image Upload Configuration
    |--------------------------------------------------------------------------
    |
    | These settings control how images are uploaded, processed, and stored
    | in your application. You can configure file paths, quality settings,
    | and validation rules for uploaded images.
    |
    */
    'image' => [
        /*
         * Storage root path for uploaded images.
         * This should be an absolute path where images will be stored.
         */
        'root' => env('SMARTMD_IMAGE_ROOT', storage_path('app/public/smartmd')),

        /*
         * Public URL base for accessing uploaded images.
         * This should be the public URL that corresponds to the root path.
         */
        'url' => env('SMARTMD_IMAGE_URL', env('APP_URL', 'http://localhost') . '/storage/smartmd'),

        /*
         * Maximum file size for uploaded images in kilobytes.
         */
        'max_size' => env('SMARTMD_MAX_FILE_SIZE', 4096),

        /*
         * Maximum width for images in pixels.
         * Images wider than this will be automatically resized.
         */
        'max_width' => env('SMARTMD_MAX_WIDTH', 1200),

        /*
         * Maximum height for images in pixels.
         * Images taller than this will be automatically resized.
         */
        'max_height' => env('SMARTMD_MAX_HEIGHT', 1200),

        /*
         * Image compression quality (1-100).
         * Higher values mean better quality but larger file sizes.
         */
        'quality' => env('SMARTMD_IMAGE_QUALITY', 80),

        /*
         * Allowed image file extensions.
         * Only files with these extensions will be accepted.
         */
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],

        /*
         * Allowed MIME types for uploaded images.
         */
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml',
        ],

        /*
         * Generate thumbnails for uploaded images.
         */
        'generate_thumbnails' => env('SMARTMD_GENERATE_THUMBNAILS', false),

        /*
         * Thumbnail sizes to generate (when enabled).
         */
        'thumbnail_sizes' => [
            'small' => ['width' => 150, 'height' => 150],
            'medium' => ['width' => 300, 'height' => 300],
            'large' => ['width' => 600, 'height' => 600],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Editor Configuration
    |--------------------------------------------------------------------------
    |
    | These settings control the behavior and appearance of the markdown
    | editor interface, including auto-save, themes, and user experience.
    |
    */
    'editor' => [
        /*
         * Enable automatic saving of editor content.
         */
        'auto_save' => env('SMARTMD_AUTO_SAVE', true),

        /*
         * Auto-save delay in milliseconds.
         */
        'auto_save_delay' => env('SMARTMD_AUTO_SAVE_DELAY', 3000),

        /*
         * Default editor theme.
         */
        'theme' => env('SMARTMD_THEME', 'default'),

        /*
         * Show line numbers in the editor.
         */
        'line_numbers' => env('SMARTMD_LINE_NUMBERS', true),

        /*
         * Enable line wrapping in the editor.
         */
        'line_wrapping' => env('SMARTMD_LINE_WRAPPING', true),

        /*
         * Enable spell checking in the editor.
         */
        'spell_check' => env('SMARTMD_SPELL_CHECK', false),

        /*
         * Editor toolbar configuration.
         */
        'toolbar' => [
            'show' => env('SMARTMD_SHOW_TOOLBAR', true),
            'items' => [
                'bold', 'italic', 'strikethrough', '|',
                'heading', 'quote', 'code', '|',
                'unordered-list', 'ordered-list', '|',
                'link', 'image', 'table', '|',
                'preview', 'side-by-side', 'fullscreen'
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | These settings control security features including HTML sanitization,
    | content filtering, and protection against malicious content.
    |
    */
    'security' => [
        /*
         * Enable HTML sanitization to prevent XSS attacks.
         */
        'sanitize_html' => env('SMARTMD_SANITIZE_HTML', true),

        /*
         * Allowed HTML tags when sanitization is enabled.
         */
        'allowed_html_tags' => [
            'p', 'br', 'strong', 'em', 'code', 'pre',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'ul', 'ol', 'li', 'blockquote',
            'a', 'img', 'table', 'thead', 'tbody', 'tr', 'th', 'td',
            'span', 'div'
        ],

        /*
         * Allowed HTML attributes.
         */
        'allowed_html_attributes' => [
            'href', 'src', 'alt', 'title', 'class', 'id',
            'width', 'height', 'style'
        ],

        /*
         * Content Security Policy settings for embedded content.
         */
        'csp' => [
            'allow_inline_styles' => false,
            'allow_inline_scripts' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Parsing Configuration
    |--------------------------------------------------------------------------
    |
    | These settings control how markdown content is parsed and rendered,
    | including support for extensions and custom features.
    |
    */
    'parsing' => [
        /*
         * Enable mathematical expressions (LaTeX) support.
         */
        'enable_math' => env('SMARTMD_ENABLE_MATH', true),

        /*
         * Enable Mermaid diagram support.
         */
        'enable_mermaid' => env('SMARTMD_ENABLE_MERMAID', true),

        /*
         * Enable syntax highlighting for code blocks.
         */
        'enable_highlight' => env('SMARTMD_ENABLE_HIGHLIGHT', true),

        /*
         * Enable table support.
         */
        'enable_tables' => env('SMARTMD_ENABLE_TABLES', true),

        /*
         * Enable strikethrough text support.
         */
        'enable_strikethrough' => env('SMARTMD_ENABLE_STRIKETHROUGH', true),

        /*
         * Enable automatic link detection.
         */
        'enable_autolinks' => env('SMARTMD_ENABLE_AUTOLINKS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    |
    | These settings control caching and performance optimizations.
    |
    */
    'performance' => [
        /*
         * Enable content caching.
         */
        'enable_cache' => env('SMARTMD_ENABLE_CACHE', false),

        /*
         * Cache TTL in seconds.
         */
        'cache_ttl' => env('SMARTMD_CACHE_TTL', 3600),

        /*
         * Cache store to use (null for default).
         */
        'cache_store' => env('SMARTMD_CACHE_STORE', null),
    ],
];
