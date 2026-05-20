/*
 * Color Plugin — toolbar buttons (color picker submenu)
 *
 * Adds a "Color Text" picker to the DokuWiki editor toolbar, with 13 color
 * swatches. Each swatch wraps the current selection in `<color #HEX>...</color>`.
 *
 * Local fork modifications vs upstream (2022-10-19):
 *   - Wrapped in an IIFE with 'use strict' so helper vars (icobase) don't
 *     leak to the global scope.
 *   - `toolbar.push(...)` instead of `toolbar[toolbar.length] = ...`.
 *
 * See http://www.dokuwiki.org/plugin:color for more info.
 */
(function () {
    'use strict';

    if (typeof window.toolbar === 'undefined') return;

    var icobase = '../../plugins/color/images/';

    window.toolbar.push({
        type: 'picker',
        title: 'Color Text',
        icon: icobase + 'toolbar_icon.png',
        list: [
            {
                type: 'format',
                title: 'Gray Colored Text',
                icon: icobase + 'picker_light_gray.png',
                open: '<color #c3c3c3>',
                close: '</color>'
            },
            {
                type: 'format',
                title: 'Rose Highlighted Text',
                icon: icobase + 'picker_rose.png',
                open: '<color #ffaec9>',
                close: '</color>'
            },
            {
                type: 'format',
                title: 'Red Highlighted Text',
                icon: icobase + 'picker_red.png',
                open: '<color #ed1c24>',
                close: '</color>'
            },
            {
                type: 'format',
                title: 'Orange Highlighted Text',
                icon: icobase + 'picker_orange.png',
                open: '<color #ff7f27>',
                close: '</color>'
            },
            {
                type: 'format',
                title: 'Gold Highlighted Text',
                icon: icobase + 'picker_gold.png',
                open: '<color #ffc90e>',
                close: '</color>'
            },
            {
                type: 'format',
                title: 'Yellow Highlighted Text',
                icon: icobase + 'picker_yellow.png',
                open: '<color #fff200>',
                close: '</color>'
            },
            {
                type: 'format',
                title: 'Light Yellow Highlighted Text',
                icon: icobase + 'picker_light_yellow.png',
                open: '<color #efe4B0>',
                close: '</color>'
            },
            {
                type: 'format',
                title: 'Green Highlighted Text',
                icon: icobase + 'picker_green.png',
                open: '<color #22b14c>',
                close: '</color>'
            },
            {
                type: 'format',
                title: 'Lime Highlighted Text',
                icon: icobase + 'picker_lime.png',
                open: '<color #b5e61d>',
                close: '</color>'
            },
            {
                type: 'format',
                title: 'Turquoise Highlighted Text',
                icon: icobase + 'picker_turquoise.png',
                open: '<color #00a2e8>',
                close: '</color>'
            },
            {
                type: 'format',
                title: 'Light Turquoise Highlighted Text',
                icon: icobase + 'picker_light_turquoise.png',
                open: '<color #99d9ea>',
                close: '</color>'
            },
            {
                type: 'format',
                title: 'Blue-Gray Highlighted Text',
                icon: icobase + 'picker_blue_gray.png',
                open: '<color #7092be>',
                close: '</color>'
            },
            {
                type: 'format',
                title: 'Lavender Highlighted Text',
                icon: icobase + 'picker_lavender.png',
                open: '<color #c8bfe7>',
                close: '</color>'
            }
        ]
    });
})();
