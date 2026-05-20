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
 * Local modifications vs. upstream (2022-10-19):
 *   1. Added `public` visibility modifiers on every method (PSR-2/PSR-12).
 *   2. Removed the trailing `?>` closing tag (PSR-12).
 *   3. Standardised on `[]` short array syntax throughout.
 *   4. plugin.info.txt date set to 2077-10-19 so the Extension Manager never
 *      offers an Update — see README.md for the rationale.
 *
 * No functional changes. Same parsing, same output.
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Christopher Smith <chris@jalakai.co.uk> (original)
 * @author  Harald Hanche-Olsen <harald.hanche-olsen@ntnu.no> (current upstream maintainer)
 */

if (!defined('DOKU_INC')) die();

class syntax_plugin_color extends DokuWiki_Syntax_Plugin
{
    public function getType()
    {
        return 'formatting';
    }

    public function getAllowedTypes()
    {
        return ['formatting', 'substition', 'disabled'];
    }

    public function getSort()
    {
        return 158;
    }

    public function connectTo($mode)
    {
        $this->Lexer->addEntryPattern('<color.*?>(?=.*?</color>)', $mode, 'plugin_color');
    }

    public function postConnect()
    {
        $this->Lexer->addExitPattern('</color>', 'plugin_color');
    }

    /**
     * Handle the match
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
                if (strpbrk($str, ':') === false) {
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
     * Create output
     */
    public function render($mode, Doku_Renderer $renderer, $data)
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
                    $renderer->doc .= $renderer->_xmlEntities($match);
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
     * is empty / contains disallowed characters.
     */
    protected function specToCSS($attrib, $c)
    {
        $c = trim((string)$c);
        if ($c === '' || !$this->isValidSpec($c)) {
            return '';
        }
        return $attrib . ':' . $c . ';';
    }

    /**
     * Lightweight validation: just reject characters that could break out of
     * the style attribute (quotes, tag brackets, ampersand, semicolon). Leave
     * the actual color-spec validity to the browser — keeps the plugin working
     * with CSS 4 / future color syntaxes.
     */
    protected function isValidSpec($c)
    {
        return strpbrk($c, '"\'<>&;') === false;
    }
}
