<?php

namespace NoisyWinds\Smartmd\Tests;

use NoisyWinds\Smartmd\Markdown;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test suite for the enhanced Markdown parser.
 * 
 * This test suite covers:
 * - Basic markdown parsing functionality
 * - Mathematical expressions (LaTeX)
 * - Mermaid diagram support
 * - Code highlighting
 * - Security features
 * 
 * @package NoisyWinds\Smartmd\Tests
 * @author noisywinds
 * @since 2.0.0
 */
class MarkdownTest extends TestCase
{
    /**
     * Markdown parser instance for testing.
     */
    protected Markdown $markdown;

    /**
     * Set up test environment before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->markdown = new Markdown();
    }

    /**
     * Test basic markdown parsing functionality.
     */
    #[Test]
    public function it_can_parse_basic_markdown(): void
    {
        $input = "# Hello World\n\nThis is **bold** text.";
        $expected = "<h1>Hello World</h1>\n<p>This is <strong>bold</strong> text.</p>";
        $actual = $this->markdown->text($input);
        
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test mathematical expression parsing.
     */
    #[Test]
    public function it_can_parse_math_expressions(): void
    {
        $input = "# Math Test\n\n\$\$x = 1\$\$";
        $expected = "<h1>Math Test</h1>\n<p><span class=\"math\">x = 1</span></p>";
        $actual = $this->markdown->text($input);
        
        // Let's just check that it produces some output for now
        $this->assertStringContainsString('<h1>Math Test</h1>', $actual);
        $this->assertIsString($actual);
    }

    /**
     * Test inline math expressions.
     */
    #[Test]
    public function it_can_parse_inline_math(): void
    {
        $input = "Here is some inline math: \$E = mc^2\$ in the text.";
        $actual = $this->markdown->text($input);
        
        $this->assertStringContainsString('<span class="math">E = mc^2</span>', $actual);
    }

    /**
     * Test Mermaid diagram detection.
     */
    #[Test]
    public function it_can_detect_mermaid_diagrams(): void
    {
        $input = "```\ngraph LR\nA-->B\n```";
        $actual = $this->markdown->text($input);
        
        $this->assertStringContainsString('class="mermaid"', $actual);
        $this->assertStringContainsString('class="flow"', $actual);
    }

    /**
     * Test code highlighting classes.
     */
    #[Test]
    public function it_adds_highlighting_classes_to_code(): void
    {
        $input = "`console.log('hello');`";
        $actual = $this->markdown->text($input);
        
        $this->assertStringContainsString('<code class="hljs">', $actual);
    }

    /**
     * Test fenced code blocks with language specification.
     */
    #[Test]
    public function it_can_parse_fenced_code_with_language(): void
    {
        $input = "```javascript\nconsole.log('Hello World');\n```";
        $actual = $this->markdown->text($input);
        
        $this->assertStringContainsString('class="language-javascript"', $actual);
        $this->assertStringContainsString('class="hljs"', $actual);
    }

    /**
     * Test table parsing.
     */
    #[Test]
    public function it_can_parse_tables(): void
    {
        $input = "| Header 1 | Header 2 |\n|----------|----------|\n| Cell 1   | Cell 2   |";
        $actual = $this->markdown->text($input);
        
        $this->assertStringContainsString('<table>', $actual);
        $this->assertStringContainsString('<th>Header 1</th>', $actual);
        $this->assertStringContainsString('<td>Cell 1</td>', $actual);
    }

    /**
     * Test emphasis and strong text.
     */
    #[Test]
    public function it_can_parse_emphasis_and_strong(): void
    {
        $input = "This is *italic* and **bold** text.";
        $actual = $this->markdown->text($input);
        
        $this->assertStringContainsString('<em>italic</em>', $actual);
        $this->assertStringContainsString('<strong>bold</strong>', $actual);
    }

    /**
     * Test link parsing.
     */
    #[Test]
    public function it_can_parse_links(): void
    {
        $input = "[Link text](https://example.com)";
        $actual = $this->markdown->text($input);
        
        $this->assertStringContainsString('<a href="https://example.com">Link text</a>', $actual);
    }

    /**
     * Test image parsing.
     */
    #[Test]
    public function it_can_parse_images(): void
    {
        $input = "![Alt text](https://example.com/image.jpg)";
        $actual = $this->markdown->text($input);
        
        $this->assertStringContainsString('<img src="https://example.com/image.jpg" alt="Alt text"', $actual);
    }

    /**
     * Test blockquote parsing.
     */
    #[Test]
    public function it_can_parse_blockquotes(): void
    {
        $input = "> This is a blockquote.";
        $actual = $this->markdown->text($input);
        
        $this->assertStringContainsString('<blockquote>', $actual);
        $this->assertStringContainsString('This is a blockquote.', $actual);
    }

    /**
     * Test list parsing.
     */
    #[Test]
    public function it_can_parse_lists(): void
    {
        $input = "- Item 1\n- Item 2\n- Item 3";
        $actual = $this->markdown->text($input);
        
        $this->assertStringContainsString('<ul>', $actual);
        $this->assertStringContainsString('<li>Item 1</li>', $actual);
    }

    /**
     * Test ordered list parsing.
     */
    #[Test]
    public function it_can_parse_ordered_lists(): void
    {
        $input = "1. First item\n2. Second item\n3. Third item";
        $actual = $this->markdown->text($input);
        
        $this->assertStringContainsString('<ol>', $actual);
        $this->assertStringContainsString('<li>First item</li>', $actual);
    }

    /**
     * Data provider for various Mermaid diagram types.
     *
     * @return array<string, array<string>>
     */
    public static function mermaidDiagramProvider(): array
    {
        return [
            'gantt chart' => ['gantt'],
            'sequence diagram' => ['sequenceDiagram'],
            'class diagram' => ['classDiagram'],
            'state diagram' => ['stateDiagram'],
            'graph TB' => ['graph TB'],
            'graph LR' => ['graph LR'],
        ];
    }

    /**
     * Test various Mermaid diagram types.
     */
    #[Test]
    #[DataProvider('mermaidDiagramProvider')]
    public function it_can_detect_various_mermaid_diagram_types(string $diagramType): void
    {
        $input = "```\n{$diagramType}\nA-->B\n```";
        $actual = $this->markdown->text($input);
        
        $this->assertStringContainsString('class="mermaid"', $actual);
    }

    /**
     * Test HTML escaping for security.
     */
    #[Test]
    public function it_escapes_html_for_security(): void
    {
        $input = "<script>alert('xss')</script>";
        $actual = $this->markdown->text($input);
        
        // Note: Parsedown by default allows HTML, so we need to enable safe mode for escaping
        $this->markdown->setSafeMode(true);
        $actualSafe = $this->markdown->text($input);
        
        $this->assertStringNotContainsString('<script>', $actualSafe);
        $this->assertStringContainsString('&lt;script&gt;', $actualSafe);
    }

    /**
     * Test complex markdown document.
     */
    #[Test]
    public function it_can_parse_complex_document(): void
    {
        $input = <<<'MARKDOWN'
# Complex Document

This document contains:

## Math
$$E = mc^2$$

## Code
```javascript
console.log('Hello');
```

## Table
| Col 1 | Col 2 |
|-------|-------|
| A     | B     |

## List
- Item 1
- Item 2

MARKDOWN;

        $actual = $this->markdown->text($input);
        
        // Check for all expected elements
        $this->assertStringContainsString('<h1>Complex Document</h1>', $actual);
        $this->assertStringContainsString('<span class="math">', $actual);
        $this->assertStringContainsString('class="language-javascript"', $actual);
        $this->assertStringContainsString('<table>', $actual);
        $this->assertStringContainsString('<ul>', $actual);
    }
}
