<?php

namespace App\Http\Controllers\Smartmd;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use NoisyWinds\Smartmd\Markdown;
use InvalidArgumentException;

/**
 * Controller for parsing markdown content and rendering demo pages.
 * 
 * This controller provides endpoints for:
 * - Rendering markdown content with the enhanced parser
 * - Displaying demo content with various markdown features
 * - Server-side markdown processing
 * 
 * @package App\Http\Controllers\Smartmd
 * @author noisywinds
 * @since 2.0.0
 */
class ParseController extends Controller
{
    /**
     * Enhanced markdown parser instance.
     */
    protected Markdown $markdown;

    /**
     * Create a new parse controller instance.
     */
    public function __construct()
    {
        $this->markdown = new Markdown();
    }

    /**
     * Display the demo page with sample markdown content.
     *
     * @return View
     */
    public function index(): View
    {
        $demoText = $this->getDemoMarkdownContent();
        $html = $this->markdown->text($demoText);
        
        return view('Smartmd::php-show', ['content' => $html]);
    }

    /**
     * Parse markdown content from request.
     *
     * @param Request $request
     * @return View|Response
     */
    public function parse(Request $request)
    {
        try {
            $content = $request->input('content', '');
            
            if (empty($content)) {
                return response()->json([
                    'error' => 'No content provided',
                    'message' => 'Please provide markdown content to parse'
                ], 400);
            }

            $html = $this->markdown->text($content);

            if ($request->expectsJson()) {
                return response()->json([
                    'html' => $html,
                    'success' => true
                ]);
            }

            return view('Smartmd::php-show', ['content' => $html]);
            
        } catch (InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Invalid input',
                    'message' => $e->getMessage()
                ], 422);
            }
            
            return back()->withErrors(['content' => $e->getMessage()]);
            
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Parsing failed',
                    'message' => 'An error occurred while parsing the markdown content'
                ], 500);
            }
            
            return back()->withErrors(['content' => 'An error occurred while parsing the content']);
        }
    }

    /**
     * Get demo markdown content showcasing various features.
     *
     * @return string
     */
    protected function getDemoMarkdownContent(): string
    {
        return <<<'MARKDOWN'
# Hello world
Hi, my friend. That is Fresh Air markdown editor. Are you using it for the first time? Click the question button or [click there](https://xiaoqingxin.site/p/markdown-guide) - there has a simple documentation for you.

## Some great features

### 1. LaTeX
You can render LaTeX mathematical expressions inline like this
$
y = x+1
$
, or like a block.
$$
\Gamma(z) = \int_0^\infty t^{z-1}e^{-t}dt\,.
$$

$$
\begin{cases}3x + 5y +  z \\
7x - 2y + 4z \\
-6x + 3y + 2z\end{cases}
$$

> Mathematics abused me hundreds of times, I treat mathematics like my first love.

### 2. Code highlighting

```javascript
// get page width
var width = document.body.clientWidth;
```

```python
# Find the odd number sum within 100
i = 0
sum = 0
while i < 100:
    if i % 2 == 1:
        sum += i
    i += 1
print("odd number sum is: " + str(sum))
```

### 3. Flow chart

```
graph LR
A[rock] -- write --> B((article))
A --> C(posts)
B --> D{mountain}
C --> D
```

### 4. Table lists

| Column 1 | Column 2 | Column 3 |
| -------- | -------- | -------- |
| Text     | Text     | Text     |
| Text     | Text     | Text     |

### 5. Emoji faces

Emoji shortcode will be rendered like this:
```
:joy: :laughing: :fire: :dragon_face: :frog:
```
:joy: :laughing: :fire: :dragon_face: :frog:

### 6. Upload image
![Sample Image](https://www.xiaoqingxin.site/images/logo_3.png)

MARKDOWN;
    }
}
