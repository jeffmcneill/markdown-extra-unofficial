<?php
/**
 * Plugin Name: Markdown Extra Unofficial
 * Plugin URI: https://jeffmcneill.com/markdown-extra
 * Description:
 * Version: 1.0.1
 * Author: 
 * Author URI: 
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Requires at least: 5.2
 * Requires PHP: 5.6
 *
 * Copyright (c) 2021 Jeff Mcneill <https://jeffmcneill.com/markdown-extra>
 * All rights reserved.
 * 
 * Based on PHP Markdown Lib
 * Copyright (c) 2004-2021 Michel Fortin <https://michelf.ca/>
 * All rights reserved.
 * 
 * Based on Markdown
 * Copyright (c) 2003-2006 John Gruber
 * <https://daringfireball.net/>
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are 
 * met:
 * 
 * Redistributions of source code must retain the above copyright notice, 
 * this list of conditions and the following disclaimer.
 * 
 * Redistributions in binary form must reproduce the above copyright notice, 
 * this list of conditions and the following disclaimer in the documentation 
 * and/or other materials provided with the distribution.
 * 
 * Neither the name "Markdown" nor the names of its contributors may be 
 * used to endorse or promote products derived from this software without 
 * specific prior written permission.
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( ! defined( 'MARKDOWN_WP_POSTS' ) ) {
	define( 'MARKDOWN_WP_POSTS', true );
}
if ( ! defined( 'MARKDOWN_WP_COMMENTS' ) ) {
	define( 'MARKDOWN_WP_COMMENTS', true );
}

// Include Markdown classes.
require_once __DIR__ . '/Michelf/MarkdownInterface.php';
require_once __DIR__ . '/Michelf/Markdown.php';
require_once __DIR__ . '/Michelf/MarkdownExtra.php';

/**
 *
 */
class Markdown_Extra {

	/**
	 * @var array
	 */
	private $hidden_tags;

	/**
	 * @var array
	 */
	private $placeholders;

	/**
	 * @var object
	 */
	private $parser;

	/**
	 *
	 */
	public function __construct() {
		register_activation_hook( __FILE__, array( __CLASS__, 'activate' ) );
		register_deactivation_hook( __FILE__, array( __CLASS__, 'deactivate' ) );

		$this->parser = new \Michelf\MarkdownExtra();

		$this->hidden_tags = explode(
			' ',
			'<p> </p> <pre> </pre> <ol> </ol> <ul> </ul> <li> </li>'
		);

		$this->placeholders = explode(
			' ',
			 // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_str_rot13
			str_rot13(
				'pEj07ZbbBZ U1kqgh4w4p pre2zmeN6K QTi31t9pre ol0MP1jzJR ' .
				'ML5IjmbRol ulANi1NsGY J7zRLJqPul liA8ctl16T K9nhooUHli'
			)
		);

		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 *
	 */
	public static function activate() {
	}

	/**
	 *
	 */
	public static function deactivate() {
	}

	/**
	 *
	 */
	public function init() {
		if ( defined( 'MARKDOWN_WP_POSTS' ) ) {
			$this->posts_hooks();
		}

		if ( defined( 'MARKDOWN_WP_COMMENTS' ) ) {
			$this->comment_hooks();
		}
	}

	/**
	 * Post content and excerpts
	 *
	 * - Remove WordPress paragraph generator.
	 * - Run Markdown on excerpt, then remove all tags.
	 * - Add paragraph tag around the excerpt, but remove it for the excerpt rss.
	 */
	private function posts_hooks() {
		remove_filter( 'the_content', 'wpautop' );
		remove_filter( 'the_content_rss', 'wpautop' );
		remove_filter( 'the_excerpt', 'wpautop' );

		add_filter( 'the_content', array( $this, 'markdown_post' ), 6 );
		add_filter( 'the_content_rss', array( $this, 'markdown_post' ), 6 );
		add_filter( 'get_the_excerpt', array( $this, 'markdown_post' ), 6 );
		add_filter( 'get_the_excerpt', 'trim', 7 );
		add_filter( 'the_excerpt', array( $this, 'add_p' ) );
		add_filter( 'the_excerpt_rss', array( $this, 'strip_p' ) );

		remove_filter( 'content_save_pre', 'balanceTags', 50 );
		remove_filter( 'excerpt_save_pre', 'balanceTags', 50 );

		add_filter( 'the_content', 'balanceTags', 50 );
		add_filter( 'get_the_excerpt', 'balanceTags', 9 );
	}

	/**
	 * Comments
	 *
	 * - Remove WordPress paragraph generator.
	 * - Remove WordPress auto-link generator.
	 * - Scramble important tags before passing them to the kses filter.
	 * - Run Markdown on excerpt then remove paragraph tags.
	 */
	private function comment_hooks() {
		remove_filter( 'comment_text', 'wpautop', 30 );
		remove_filter( 'comment_text', 'make_clickable' );

		add_filter( 'pre_comment_content', array( $this->parser, 'defaultTransform' ), 6 );
		add_filter( 'pre_comment_content', array( $this, 'hide_tags' ), 8 );
		add_filter( 'pre_comment_content', array( $this, 'show_tags' ), 12 );
		add_filter( 'get_comment_text', array( $this->parser, 'defaultTransform' ), 6 );
		add_filter( 'get_comment_excerpt', array( $this->parser, 'defaultTransform' ), 6 );
		add_filter( 'get_comment_excerpt', array( $this, 'strip_p' ), 7 );
	}

	/**
	 * Add a footnote id prefix to posts when inside a loop.
	 */
	public function markdown_post( $text ) {

		if ( is_feed() || is_singular() ) {
			$this->parser->fn_id_prefix = '';
		} else {
			$this->parser->fn_id_prefix = get_the_ID() . '.';
		}

		return $this->parser->transform( $text );
	}

	/**
	 *
	 */
	public function add_p( $text ) {
		if ( ! preg_match( '{^$|^<(p|ul|ol|dl|pre|blockquote)>}i', $text ) ) {
			$text = '<p>' . $text . '</p>';
			$text = preg_replace( '{\n{2,}}', "</p>\n\n<p>", $text );
		}

		return $text;
	}

	/**
	 *
	 */
	public function strip_p( $text ) {
		return preg_replace( '{</?p>}i', '', $text );
	}

	/**
	 *
	 */
	public function hide_tags( $text ) {
		return str_replace( $this->hidden_tags, $this->placeholders, $text );
	}

	/**
	 *
	 */
	public function show_tags( $text ) {
		return str_replace( $this->placeholders, $this->hidden_tags, $text );
	}
}

$GLOBALS['markdown_extra'] = new Markdown_Extra();
