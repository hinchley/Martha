# Martha
Martha is a cut-down variant of [Markdown](http://daringfireball.net/projects/markdown/) written in PHP. The class excludes features in Markdown that I have never used (e.g. nested lists and reference links) and changes the syntax of other features that I rarely remember (e.g. links and images). The source code is super lean, efficient, and easy to extend.

## Usage
The following code snippet demonstrates how to use Martha. The code assumes the text that is to be processed is stored in a file named "martha.txt".

    include "martha.php";

    $input = file_get_contents('martha.txt');
    $martha = new Martha();
    echo $martha->go($input);

## Syntax
The following sections provide an overview of the Martha syntax.

### HTML
Martha will ignore all HTML tags. It won't change the tags, nor will it mess with the content between the tags.

For example, this markup will remain unchanged by Martha:

    <p>An explicit paragraph with a <strong class="strong">strong</strong> tag.</p>

### Entities
All ampersands that are not part of an HTML entity will be encoded.

This text:

    The & used in the &copy; entity will not be encoded.

Will be converted to:

    The &amp; used in the &copy; entity will not be encoded.

### Emphasis (Italics)
Text (on a single line) between two asterisks (stars) will be wrapped in `<em>` tags.

This text:

    This *word* is emphasised.

Will generate:

    <p>This <em>word</em> is emphasised.</p>

### Strong (Bold)
Text (on a single line) between two consecutive asterisks (stars) will be wrapped in `<strong>` tags.

This text:

    This **word** is bold.

Will generate:

    <p>This <strong>word</strong> is bold.</p>

### Headings
Headings are created using hash characters. The level of the heading is determined by the number of hashes.

For example, this text:

    #### This is a Level 4 Heading

Will generate:

    <h4>This is a Level 4 Heading</h4>

### Paragraphs
Text that is immediately followed by another block-level element (code block, list, etc), or is separated from other text by one or more blank lines, will be converted to a paragraph.

This text:

    This is a sentence in a paragraph.
    This is another sentence in the same paragraph.

    This is a new paragraph.

Will be converted to:

    <p>This is a sentence in a paragraph.
    This is another sentence in the same paragraph.</p>
    <p>This is a new paragraph.</p>

And this text:

    # Heading
    Some text.

Will be converted to:

    <h1>Heading</h1>
    <p>Some text.</p>

A `<br />` tag can be inserted into a paragraph by adding two spaces to the end of a line.

For example, if the first sentence in the following paragraph ends with two spaces:

    This is a sentence in a paragraph.  
    This is another sentence in the same paragraph.

The text will be converted to:

    <p>This is a sentence in a paragraph.<br />
    This is another sentence in the same paragraph.</p>

### Lists
Martha supports ordered (numbered) and unordered (bulleted) lists. Unordered lists are specified using hyphens; ordered lists using numbers.

Here are two examples:

    - First element of an unordered list.
    - Second element of the the list.

    1. First element of an ordered list.
    2. Second element of the the list.

The examples generate the following markup:

    <ul>
    <li>First element of an unordered list.</li>
    <li>Second element of the the list.</li>
    </ul>

    <ol>
    <li>First element of an ordered list.</li>
    <li>Second element of the the list.</li>
    </ol>

Martha does not support nested lists (or any block-level nesting within lists), however, nesting of inline elements (e.g. emphasis and strong) is supported.

### Blockquotes
Blockquotes are created using the "greater than" symbol. The symbol should prefix each line of the quotation.

This text:

    > This is a blockquote.
    > It has two lines in a single paragraph.

Will generate:

    <blockquote>
    <p>This is a blockquote.
    It has two lines in a single paragraph.</p>
    </blockquote>

A prefixed line that is otherwise empty will generate a new paragraph.

This text:

    > This is a blockquote.
    >
    > It has two paragraphs.

Will generate:

    <blockquote>
    <p>This is a blockquote.</p>
    <p>It has two paragraphs.</p>
    </blockquote>

Blockquotes can include other elements (both block and inline elements).

This text:

    > - A list item.
    > - *Another* item.

Will generate:

    <blockquote>
    <ul>
    <li>A list item.</li>
    <li><em>Another</em> item.</li>
    </ul>
    </blockquote>

### Code Blocks
Code blocks are created by indenting lines by two spaces. Within a code block, ampersands (&amp;) and angle brackets (&lt; and &gt;) are automatically converted into HTML entities.

This text, if indented two spaces:

    $foo = 'bar & stuff';
    echo $foo;

Will generate:

    <pre>
    $foo = 'bar &amp; stuff';
    echo $foo;
    </pre>

An inline section of code can be created by wrapping text between backticks. As with a code block, the enclosed text will be automatically converted into HTML entities.

This text:

    This is a `<span>` element.

Will generate:

    <p>This is a <code>&lt;span&gt;</code> element.</p>

### Links
In Martha, a link is created by enclosing a URL within quotes within a pair of angled brackets. The link text and a link title can also be specified by including optional second and third values between the brackets.

It's easy to remember: just think of the angled bracket as an arrow pointing to a URL target.

This text:

    A link to <"http://google.com/">.

Will generate:

    <p>A link to <a href="http://google.com/">http://google.com/</a>.</p>

A second term will set the link text:

    A link to <"http://google.com/" "Google">.

And will generate:

    <p>A link to <a href="http://google.com/">Google</a>.</p>

A third term will set the link title:

    A link to <"http://google.com/" "Google" "Google Search Engine">.

And will generate:

    <p>A link to <a href="http://google.com/" title="Google Search Engine">Google</a>.</p>

### Images
In Martha, an image is created by enclosing a source path within quotes within a pair of square brackets. The image alt text and an image title can also be specified by including optional second and third values between the brackets.

It's easy to remember: just think of the square brackets as forming a frame for a photo.

This text:

    ["/images/image.jpg"]

Will generate:

    <img src="/images/image.jpg" />

A second term will set the image alt attribute:

    ["/images/image.jpg" "Photo"]

And will generate:

    <img src="/images/image.jpg" alt="Photo" />

A third term will set the image title:

    ["/images/image.jpg" "Photo" "Photo of me!"]

And will generate:

    <img src="/images/image.jpg" alt="Photo" title="Photo of me!" />