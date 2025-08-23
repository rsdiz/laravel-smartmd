<?php

namespace NoisyWinds\Smartmd;

use Parsedown;

/**
 * Enhanced Markdown parser extending Parsedown with additional features.
 * 
 * This class extends the base Parsedown parser to support:
 * - Mathematical expressions (LaTeX)
 * - Mermaid diagrams and flowcharts
 * - Enhanced code highlighting
 * - Improved security features
 * 
 * @package NoisyWinds\Smartmd
 * @author noisywinds
 * @since 2.0.0
 */
class Markdown extends Parsedown
{
    /**
     * Initialize the enhanced markdown parser.
     */
    public function __construct()
    {
        // Call parent constructor first
        if (method_exists(parent::class, '__construct')) {
            parent::__construct();
        }
        
        // Initialize our enhanced features
        $this->initializeEnhancedFeatures();
    }

    /**
     * Initialize enhanced parsing features.
     */
    protected function initializeEnhancedFeatures()
    {
        // Ensure parent properties exist, then extend them
        if (!isset($this->BlockTypes)) {
            $this->BlockTypes = [];
        }
        if (!isset($this->InlineTypes)) {
            $this->InlineTypes = [];
        }
        
        // Add our custom block and inline types for math
        $this->BlockTypes['$'] = ['Math'];
        $this->InlineTypes['$'] = ['Math'];
        
        // Update inline marker list to include math delimiter
        if (property_exists($this, 'inlineMarkerList')) {
            if (strpos($this->inlineMarkerList, '$') === false) {
                $this->inlineMarkerList .= '$';
            }
        }
    }

    /**
     * Configure security settings for safe parsing.
     *
     * @param bool $safe Whether to enable safe mode
     * @return $this
     */
    public function setSafeMode($safe = true)
    {
        // Call parent method if it exists
        if (method_exists(parent::class, 'setSafeMode')) {
            parent::setSafeMode($safe);
        }
        
        return $this;
    }

    /**
     * Continue processing a paragraph block.
     *
     * @param array $Line
     * @param array $Block
     * @return array|null
     */
    protected function paragraphContinue($Line, array $Block)
    {
        if (isset($Block['interrupted'])) {
            return null;
        }

        $Block['element']['handler']['argument'] .= "\n" . $Line['text'];

        return $Block;
    }

    /**
     * Process block-level mathematical expressions.
     *
     * @param array $Line
     * @return array|null
     */
    protected function blockMath($Line)
    {
        $marker = $Line['text'][0];
        $openerLength = strspn($Line['text'], $marker);
        
        if ($openerLength < 2) {
            return null;
        }
        
        $infostring = trim(substr($Line['text'], $openerLength), "\t ");
        if (strpos($infostring, '$') !== false) {
            return null;
        }
        
        $Element = [
            'name' => 'p',
            'text' => '',
            'attributes' => ['class' => 'math']
        ];
        
        return [
            'char' => $marker,
            'openerLength' => $openerLength,
            'element' => [
                'name' => 'span',
                'element' => $Element,
            ],
        ];
    }

    /**
     * Continue processing a math block.
     *
     * @param array $Line
     * @param array $Block
     * @return array|null
     */
    protected function blockMathContinue($Line, $Block)
    {
        if (isset($Block['complete'])) {
            return null;
        }
        
        if (isset($Block['interrupted'])) {
            $Block['element']['element']['text'] .= str_repeat("\n", $Block['interrupted']);
            unset($Block['interrupted']);
        }
        
        $len = strspn($Line['text'], $Block['char']);
        if ($len >= $Block['openerLength'] && 
            trim(substr($Line['text'], $len), ' ') === '') {
            $Block['element']['element']['text'] = substr($Block['element']['element']['text'], 1);
            $Block['complete'] = true;
            return $Block;
        }
        
        $Block['element']['element']['text'] .= "\n" . $Line['body'];
        return $Block;
    }

    /**
     * Complete processing of a math block.
     *
     * @param array $Block
     * @return array
     */
    protected function blockMathComplete($Block)
    {
        return $Block;
    }

    /**
     * Process inline mathematical expressions.
     *
     * @param array $Excerpt
     * @return array|null
     */
    protected function inlineMath($Excerpt)
    {
        $marker = $Excerpt['text'][0];
        $escapedMarker = preg_quote($marker, '/');
        
        $pattern = '/^([' . $escapedMarker . ']++)[ ]*+(.+?)[ ]*+(?<![' . $escapedMarker . '])\1(?!' . $escapedMarker . ')/s';
        
        if (preg_match($pattern, $Excerpt['text'], $matches)) {
            $text = $matches[2];
            $text = preg_replace('/[ ]*+\n/', ' ', $text);
            
            return [
                'extent' => strlen($matches[0]),
                'element' => [
                    'name' => 'span',
                    'text' => $text,
                    'attributes' => ['class' => 'math']
                ],
            ];
        }
        
        return null;
    }

    /**
     * Process inline code with enhanced syntax highlighting.
     *
     * @param array $Excerpt
     * @return array|null
     */
    protected function inlineCode($Excerpt)
    {
        $marker = $Excerpt['text'][0];
        $escapedMarker = preg_quote($marker, '/');
        
        $pattern = '/^([' . $escapedMarker . ']++)[ ]*+(.+?)[ ]*+(?<![' . $escapedMarker . '])\1(?!' . $escapedMarker . ')/s';
        
        if (preg_match($pattern, $Excerpt['text'], $matches)) {
            $text = $matches[2];
            $text = preg_replace('/[ ]*+\n/', ' ', $text);

            return [
                'extent' => strlen($matches[0]),
                'element' => [
                    'name' => 'code',
                    'text' => $text,
                    'attributes' => ['class' => 'hljs'],
                ],
            ];
        }
        
        return null;
    }

    /**
     * Process code blocks with enhanced features.
     *
     * @param array $Line
     * @param array|null $Block
     * @return array|null
     */
    protected function blockCode($Line, $Block = null)
    {
        if (isset($Block) && $Block['type'] === 'Paragraph' && !isset($Block['interrupted'])) {
            return null;
        }

        if ($Line['indent'] >= 4) {
            $text = substr($Line['body'], 4);

            return [
                'element' => [
                    'name' => 'pre',
                    'attributes' => ['class' => 'hljs'],
                    'element' => [
                        'name' => 'code',
                        'text' => $text,
                    ],
                ],
            ];
        }
        
        return null;
    }

    /**
     * Process fenced code blocks with language detection and Mermaid support.
     *
     * @param array $Line
     * @return array|null
     */
    protected function blockFencedCode($Line)
    {
        $marker = $Line['text'][0];
        $openerLength = strspn($Line['text'], $marker);

        if ($openerLength < 3) {
            return null;
        }

        $infostring = trim(substr($Line['text'], $openerLength), "\t ");

        if (strpos($infostring, '`') !== false) {
            return null;
        }

        $Element = [
            'name' => 'code',
            'text' => '',
        ];

        if ($infostring !== '') {
            $language = substr($infostring, 0, strcspn($infostring, " \t\n\f\r"));
            $Element['attributes'] = ['class' => "language-{$language}"];
        }

        return [
            'char' => $marker,
            'openerLength' => $openerLength,
            'element' => [
                'name' => 'pre',
                'attributes' => ['class' => 'hljs'],
                'element' => $Element,
            ],
        ];
    }

    /**
     * Continue processing a fenced code block with Mermaid detection.
     *
     * @param array $Line
     * @param array $Block
     * @return array|null
     */
    protected function blockFencedCodeContinue($Line, $Block)
    {
        if (isset($Block['complete'])) {
            return null;
        }

        if (isset($Block['interrupted'])) {
            $Block['element']['element']['text'] .= str_repeat("\n", $Block['interrupted']);
            unset($Block['interrupted']);
        }

        $len = strspn($Line['text'], $Block['char']);
        if ($len >= $Block['openerLength'] && 
            trim(substr($Line['text'], $len), ' ') === '') {
            $Block['element']['element']['text'] = substr($Block['element']['element']['text'], 1);
            $Block['complete'] = true;
            return $Block;
        }

        $Block['element']['element']['text'] .= "\n" . $Line['body'];

        // Detect Mermaid diagrams
        if ($this->isMermaidContent($Line['body'])) {
            $Block['element']['element']['attributes'] = ['class' => 'mermaid'];
            $Block['element']['attributes'] = ['class' => 'flow'];
        }

        return $Block;
    }

    /**
     * Check if content represents a Mermaid diagram.
     *
     * @param string $content
     * @return bool
     */
    protected function isMermaidContent($content)
    {
        $mermaidKeywords = [
            'gantt',
            'sequenceDiagram',
            'classDiagram',
            'stateDiagram',
            'erDiagram',
            'journey',
            'pie',
            'gitgraph',
        ];

        foreach ($mermaidKeywords as $keyword) {
            if ($content === $keyword) {
                return true;
            }
        }

        // Check for graph declarations
        return (bool) preg_match('/^graph\s+(?:TB|BT|RL|LR|TD);?$/', $content);
    }
}