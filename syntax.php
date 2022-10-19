<?php
/**
 * Plugin Color: Sets new colors for text and background.
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 */
 
// must be run within DokuWiki
if(!defined('DOKU_INC')) die();
  
/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_color extends DokuWiki_Syntax_Plugin {
 
    function getType(){ return 'formatting'; }
    function getAllowedTypes() { return array('formatting', 'substition', 'disabled'); }   
    function getSort(){ return 158; }
    function connectTo($mode) { $this->Lexer->addEntryPattern('<color.*?>(?=.*?</color>)',$mode,'plugin_color'); }
    function postConnect() { $this->Lexer->addExitPattern('</color>','plugin_color'); }
 
 
    /**
     * Handle the match
     */
    function handle($match, $state, $pos, Doku_Handler $handler){
        switch ($state) {
          case DOKU_LEXER_ENTER :
              $str = substr($match, 6, -1);
              if (false === strpbrk($str,':')){
                 $m = explode('/', $str);
              } else {
                 $m = explode(':', $str);
              }
              $color = $this->_specToCSS('color', $m[0]);
              $background = $this->_specToCSS('background-color',
                                              isset($m[1]) ? $m[1] : null);
              return [$state, [$color, $background]];
 
          case DOKU_LEXER_UNMATCHED :  return array($state, $match);
          case DOKU_LEXER_EXIT :       return array($state, '');
        }
        return array();
    }
 
    /**
     * Create output
     */
    function render($mode, Doku_Renderer $renderer, $data) {
        if($mode == 'xhtml'){
            list($state, $match) = $data;
            switch ($state) {
              case DOKU_LEXER_ENTER :      
                list($color, $background) = $match;
                $renderer->doc .= "<span style='$color $background'>"; 
                break;
 
              case DOKU_LEXER_UNMATCHED :  $renderer->doc .= $renderer->_xmlEntities($match); break;
              case DOKU_LEXER_EXIT :       $renderer->doc .= "</span>"; break;
            }
            return true;
        }
        if($mode == 'odt'){
            list($state, $match) = $data;
            switch ($state) {
              case DOKU_LEXER_ENTER :      
                list($color, $background) = $match;
                if (class_exists('ODTDocument')) {
                    $renderer->_odtSpanOpenUseCSS (NULL, 'style="'.$color.$background.'"');
                }
                break;
 
              case DOKU_LEXER_UNMATCHED :
                $renderer->cdata($match);
                break;

              case DOKU_LEXER_EXIT :
                if (class_exists('ODTDocument')) {
                    $renderer->_odtSpanClose();
                }
                break;
            }
            return true;
        }
        if($mode == 'metadata'){
            list($state, $match) = $data;
            switch ($state) {
              case DOKU_LEXER_UNMATCHED :
                if ($renderer->capture) $renderer->cdata($match);
                break;
            }
            return true;
        }
        return false;
    }
  
    // Build a CSS attribute:value pair.
    function _specToCSS($attrib, $c) {
        $c = trim($c);
        return ((!empty($c) &&
                 $this->_isValid($c)) ? $attrib.':'.$c.';'
                                      : null);
    }

    // validate color value $c
    // this is cut price validation - only to ensure there is nothing harmful
    // just ensure that no illegal characters are included therein
    // leave it to the browsers to ignore a faulty colour specification
    function _isValid($c) {
        return (false === strpbrk($c, '"\'<>&;'));
    }
}
?>
