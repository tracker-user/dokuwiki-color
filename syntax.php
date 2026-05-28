<?php
/**
 * Color Plugin: write text with custom foreground/background colors.
 *
 * Usage in wiki text:
 *   <color red>red text</color>
 *   <color red/yellow>red on yellow</color>
 *   <color rgb(80%,0%,0%):rgb(100%,80%,100%)>...</color>
 *
 * Local fork of https://github.com/hanche/dokuwiki_color_plugin
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Christopher Smith <chris@jalakai.co.uk> (original)
 * @author  Harald Hanche-Olsen <harald.hanche-olsen@ntnu.no> (current upstream maintainer)
 */

if (!defined('DOKU_INC')) die();

use dokuwiki\Extension\SyntaxPlugin;

class syntax_plugin_color extends SyntaxPlugin
{
    /**
     * @return string Syntax type
     */
    public function getType(): string
    {
        return 'formatting';
    }

    /**
     * @return string[] Allowed child syntax types
     */
    public function getAllowedTypes(): array
    {
        return ['formatting', 'substition', 'disabled'];
    }

    /**
     * @return int Parser sort order
     */
    public function getSort(): int
    {
        return 158;
    }

    /**
     * @param string $mode Current parser mode
     */
    public function connectTo($mode): void
    {
        $this->Lexer->addEntryPattern('<color.*?>(?=.*?</color>)', $mode, 'plugin_color');
    }

    public function postConnect(): void
    {
        $this->Lexer->addExitPattern('</color>', 'plugin_color');
    }

    /**
     * Parse a matched token into structured data.
     *
     * @param string       $match   The matched text
     * @param int          $state   Lexer state (DOKU_LEXER_ENTER / UNMATCHED / EXIT)
     * @param int          $pos     Byte offset in source
     * @param Doku_Handler $handler Parser handler
     * @return array|false Parsed data or empty array
     */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        switch ($state) {
            case DOKU_LEXER_ENTER:
                // Strip `<color` and trailing `>` to get the spec string.
                $str = substr($match, 6, -1);

                // The spec can use either `/` or `:` as the fg/bg separator.
                // Colon is required when a color value itself contains a slash
                // (e.g. `rgb(.../alpha)`-style modern CSS).
                if (!str_contains($str, ':')) {
                    $m = explode('/', $str);
                } else {
                    $m = explode(':', $str);
                }

                $color      = $this->specToCSS('color', $m[0]);
                $background = $this->specToCSS('background-color', $m[1] ?? null);
                return [$state, [$color, $background]];

            case DOKU_LEXER_UNMATCHED:
                return [$state, $match];

            case DOKU_LEXER_EXIT:
                return [$state, ''];
        }
        return [];
    }

    /**
     * Render the parsed data to output.
     *
     * @param string        $mode     Output mode ('xhtml', 'odt', 'metadata', …)
     * @param Doku_Renderer $renderer The active renderer
     * @param array         $data     Data returned by handle()
     * @return bool True if mode was handled
     */
    public function render($mode, Doku_Renderer $renderer, $data): bool
    {
        if ($mode === 'xhtml') {
            [$state, $match] = $data;
            switch ($state) {
                case DOKU_LEXER_ENTER:
                    [$color, $background] = $match;
                    // Both pieces are validated by isValidSpec(); attribute
                    // value is single-quoted so we can use double quotes inside
                    // if we ever need to. Empty pieces are dropped by specToCSS.
                    $renderer->doc .= "<span style='$color $background'>";
                    break;

                case DOKU_LEXER_UNMATCHED:
                    $renderer->doc .= hsc($match);
                    break;

                case DOKU_LEXER_EXIT:
                    $renderer->doc .= '</span>';
                    break;
            }
            return true;
        }

        if ($mode === 'odt') {
            [$state, $match] = $data;
            switch ($state) {
                case DOKU_LEXER_ENTER:
                    [$color, $background] = $match;
                    if (class_exists('ODTDocument')) {
                        $renderer->_odtSpanOpenUseCSS(null, 'style="' . $color . $background . '"');
                    }
                    break;

                case DOKU_LEXER_UNMATCHED:
                    $renderer->cdata($match);
                    break;

                case DOKU_LEXER_EXIT:
                    if (class_exists('ODTDocument')) {
                        $renderer->_odtSpanClose();
                    }
                    break;
            }
            return true;
        }

        if ($mode === 'metadata') {
            [$state, $match] = $data;
            if ($state === DOKU_LEXER_UNMATCHED && $renderer->capture) {
                $renderer->cdata($match);
            }
            return true;
        }

        return false;
    }

    /**
     * Build a single CSS `property: value;` pair, or empty string if the value
     * is empty or contains disallowed characters.
     *
     * @param string      $attrib CSS property name ('color' or 'background-color')
     * @param string|null $c      Raw color spec from wiki markup
     * @return string CSS declaration or empty string
     */
    protected function specToCSS(string $attrib, ?string $c): string
    {
        $c = trim((string)$c);
        if ($c === '' || !$this->isValidSpec($c)) {
            return '';
        }
        return $attrib . ':' . $c . ';';
    }

    /**
     * Reject characters that could break out of a single-quoted style attribute
     * or inject additional CSS properties. Leaves color-spec validity to the browser
     * so the plugin stays forward-compatible with new CSS color syntaxes.
     *
     * @param string $c Color spec to validate
     * @return bool True if safe to use inside a style attribute
     */
    protected function isValidSpec(string $c): bool
    {
        return strpbrk($c, '"\'<>&;') === false;
    }
}
