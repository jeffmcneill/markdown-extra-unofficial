**Updated 2024-08-09**

- Copied repository over to [Codeberg which is now the primary source for Markdown Extra Unofficial](https://codeberg.org/jeffmcneill/markdown-extra-unofficial). This Github repository is only for historical purposes.

# Markdown Extra Unofficial WordPress Plugin 

![Markdown Extra Unofficial](https://github.com/jeffmcneill/markdown-extra-unofficial/blob/main/markdown-extra-unofficial.png)

Markdown Extra Unofficial is a WordPress plugin supporting [Markdown Extra](https://michelf.ca/projects/php-markdown/extra/) the parser by Michel Fortin. There are several [markdown plugins for WordPress](https://wordpress.org/plugins/tags/markdown/), however most require that markdown be put in a block or a shortcode. This plugin is meant to be used with the [Classic Editor](https://wordpress.org/plugins/classic-editor/) and parse all text in pages and posts, excerpts, term descriptions, and WooCommerce short descriptions.

<!--more-->

## PHP Markdown Extra Classic

There was originally a [Markdown Extra WordPress Plugin released by Michel Fortin](https://michelf.ca/projects/php-markdown/classic/) but was discontinued in 2013. The [Markdown Extra PHP Parser (PHP Markdown)](https://github.com/michelf/php-markdown) is an active and ongoing project. 

## Updated functionality in PHP Markdown

For years I used the old version of Markdown Extra WordPress plugin. It still worked brilliantly. However, two improvements to PHP Markdown compelled the creation of a new WordPress plugin: the ability to add `{rel="nofollow"}` after an href, and the elimination of extra `<p></p>` inadvertently added to certain markup. Using the latest PHP Markdown parser, all we need is an updated wrapper and some WordPress specific commands, so that it works as a WordPress plugin.

## Markdown Extra extended syntax

Along with the standard Markdown features, Markdown Extra has several enhancements:

- Adding CSS classes to block-level elements `{.class}` and `{#id}`
- Supports markdown inside of html markup `<div markdown="1">`
- Has an improved syntax for simple tables
- Supports definition lists 
- Supports footnotes
- Supports abbreviations
- Supports adding rel="nofollow" to hrefs `{rel="nofollow"}`

See full [Markdown Extra syntax](https://michelf.ca/projects/php-markdown/extra/)

## Download Markdown Extra Unofficial

Markdown Extra Unofficial is available via [Codeberg](https://codeberg.org/jeffmcneill/markdown-extra-unofficial/releases).
