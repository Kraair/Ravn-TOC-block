<?php
/**
 * Plugin Name:       Ravn TOC Block
 * Plugin URI:        https://github.com/Kraair/Ravn-TOC-block
 * Description:       Voegt een echt Gutenberg-block toe dat automatisch een inhoudsopgave opbouwt uit de titels van de pagina (core/heading, GenerateBlocks en de meeste andere page builders), met smooth scroll, optionele highlighting van de actieve sectie, en apart instelbaar welke kop-niveaus op desktop en mobiel zichtbaar zijn. Volledig stijlbaar via de standaard block-instellingen.
 * Version:           1.2.0
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            Kraaier Studio
 * Author URI:        https://github.com/Kraair
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       toc-block
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TOC_BLOCK_PATH', plugin_dir_path( __FILE__ ) );
define( 'TOC_BLOCK_URL', plugin_dir_url( __FILE__ ) );
define( 'TOC_BLOCK_VERSION', '1.2.0' );

/**
 * Registreer scripts/stijlen en het block zelf.
 */
add_action( 'init', 'toc_block_register' );
function toc_block_register() {

	wp_register_script(
		'toc-block-editor-script',
		TOC_BLOCK_URL . 'editor.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n' ),
		TOC_BLOCK_VERSION,
		true
	);

	wp_register_script(
		'toc-block-frontend-script',
		TOC_BLOCK_URL . 'frontend.js',
		array(),
		TOC_BLOCK_VERSION,
		true
	);

	wp_register_style(
		'toc-block-style',
		TOC_BLOCK_URL . 'style.css',
		array(),
		TOC_BLOCK_VERSION
	);

	register_block_type( TOC_BLOCK_PATH . 'block.json' );
}

/**
 * Welke block-types tellen als "titel" voor de inhoudsopgave, en hoe haal je
 * daar het kop-niveau en de tekst uit? Uitbreidbaar via het filter hieronder.
 * Dit is alleen een SNELLE route voor bekende blocks; de generieke fallback
 * in toc_block_match_heading() vangt vrijwel alles andere al af.
 *
 *   add_filter( 'toc_block_heading_block_defs', function( $defs ) {
 *       $defs['mijn-plugin/kop'] = array( 'tag_attr' => 'tag', 'text_attr' => 'text' );
 *       return $defs;
 *   } );
 *
 * @return array
 */
function toc_block_get_heading_block_defs() {
	return apply_filters(
		'toc_block_heading_block_defs',
		array(
			'core/heading'            => array(
				'level_attr' => 'level',
				'text_attr'  => 'content',
			),
			// GenerateBlocks v1 "Headline" element.
			'generateblocks/headline' => array(
				'tag_attr'  => 'element',
				'text_attr' => 'content',
			),
			// GenerateBlocks v2 "Text" block (vervangt Headline).
			'generateblocks/text'     => array(
				'tag_attr'  => 'tagName',
				'text_attr' => 'content',
			),
		)
	);
}

/**
 * Bepaal of een geparste block een titel is, en zo ja: geef het niveau (1-6)
 * en de platte tekst terug.
 *
 * Twee lagen:
 * 1) Een expliciete definitie hierboven (snel, voor bekende blocks).
 * 2) Generieke fallback: als de eigen opgeslagen HTML van het block
 *    letterlijk met een <h1>-<h6> tag begint, tellen we het sowieso als
 *    titel — werkt automatisch voor vrijwel elke page builder.
 *
 * @param array $block
 * @param array $defs
 * @return array{level:int,text:string}|null
 */
function toc_block_match_heading( $block, $defs ) {
	$name = isset( $block['blockName'] ) ? $block['blockName'] : '';

	if ( $name && isset( $defs[ $name ] ) ) {
		$def   = $defs[ $name ];
		$level = 0;

		if ( ! empty( $def['level_attr'] ) && isset( $block['attrs'][ $def['level_attr'] ] ) ) {
			$level = (int) $block['attrs'][ $def['level_attr'] ];
		} elseif ( ! empty( $def['tag_attr'] ) && ! empty( $block['attrs'][ $def['tag_attr'] ] ) ) {
			if ( preg_match( '/^h([1-6])$/i', trim( (string) $block['attrs'][ $def['tag_attr'] ] ), $m ) ) {
				$level = (int) $m[1];
			}
		}

		if ( $level >= 1 && $level <= 6 ) {
			$text = '';
			if ( ! empty( $def['text_attr'] ) && ! empty( $block['attrs'][ $def['text_attr'] ] ) ) {
				$text = wp_strip_all_tags( $block['attrs'][ $def['text_attr'] ] );
			} elseif ( ! empty( $block['innerHTML'] ) ) {
				$text = wp_strip_all_tags( $block['innerHTML'] );
			}
			$text = trim( $text );

			if ( '' !== $text ) {
				return array(
					'level' => $level,
					'text'  => $text,
				);
			}
		}
	}

	if ( ! empty( $block['innerHTML'] ) && preg_match( '/^\s*<h([1-6])\b/i', $block['innerHTML'], $m ) ) {
		$level = (int) $m[1];
		$text  = trim( wp_strip_all_tags( $block['innerHTML'] ) );

		if ( '' !== $text ) {
			return array(
				'level' => $level,
				'text'  => $text,
			);
		}
	}

	return null;
}

/**
 * Haal, uitgaande van de RUWE block-structuur van een post, alle titel-blocks
 * op in documentvolgorde, incl. willekeurig genest. Gecached per request.
 *
 * @param int   $post_id
 * @param array $levels
 * @return array<int, array{level:int,text:string,id:string}>
 */
function toc_block_get_headings_for_post( $post_id, $levels ) {
	static $cache = array();

	$cache_key = $post_id . '_' . implode( '-', $levels );
	if ( isset( $cache[ $cache_key ] ) ) {
		return $cache[ $cache_key ];
	}

	$raw_content = get_post_field( 'post_content', $post_id );

	if ( empty( $raw_content ) ) {
		return $cache[ $cache_key ] = array();
	}

	$blocks   = parse_blocks( $raw_content );
	$defs     = toc_block_get_heading_block_defs();
	$headings = array();
	$used_ids = array();

	$walk = function ( $blocks ) use ( &$walk, &$headings, &$used_ids, $levels, $defs ) {
		foreach ( $blocks as $block ) {

			$match = toc_block_match_heading( $block, $defs );

			if ( $match && in_array( $match['level'], $levels, true ) ) {
				$slug = ! empty( $block['attrs']['anchor'] )
					? sanitize_html_class( $block['attrs']['anchor'] )
					: sanitize_title( $match['text'] );

				$base_slug = $slug;
				$i         = 2;
				while ( in_array( $slug, $used_ids, true ) ) {
					$slug = $base_slug . '-' . $i;
					$i++;
				}
				$used_ids[] = $slug;

				$headings[] = array(
					'level' => $match['level'],
					'text'  => $match['text'],
					'id'    => $slug,
				);
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				$walk( $block['innerBlocks'] );
			}
		}
	};

	$walk( $blocks );

	return $cache[ $cache_key ] = $headings;
}

/**
 * Bepaal of we ons "binnen the_content" van de hoofd-post bevinden.
 */
$GLOBALS['toc_block_in_content'] = false;

add_filter(
	'the_content',
	function ( $content ) {
		$GLOBALS['toc_block_in_content'] = true;
		return $content;
	},
	0
);

add_filter(
	'the_content',
	function ( $content ) {
		$GLOBALS['toc_block_in_content'] = false;
		return $content;
	},
	999
);

/**
 * Injecteer een id-attribuut in de gerenderde HTML van elke titel binnen de
 * hoofdcontent, zodat de links van het TOC-block ergens naartoe kunnen scrollen.
 */
add_filter( 'render_block', 'toc_block_add_id_to_heading', 10, 2 );
function toc_block_add_id_to_heading( $block_content, $block ) {

	if ( is_admin() || empty( $GLOBALS['toc_block_in_content'] ) ) {
		return $block_content;
	}

	$defs  = toc_block_get_heading_block_defs();
	$match = toc_block_match_heading( $block, $defs );

	if ( ! $match ) {
		return $block_content;
	}

	$post_id = get_the_ID();
	if ( ! $post_id ) {
		return $block_content;
	}

	static $queues = array();

	if ( ! isset( $queues[ $post_id ] ) ) {
		$queues[ $post_id ] = toc_block_get_headings_for_post( $post_id, array( 1, 2, 3, 4, 5, 6 ) );
	}

	if ( empty( $queues[ $post_id ] ) ) {
		return $block_content;
	}

	$next = array_shift( $queues[ $post_id ] );
	if ( ! $next ) {
		return $block_content;
	}

	if ( preg_match( '/<h[1-6][^>]*\sid=/i', $block_content ) ) {
		return $block_content;
	}

	$block_content = preg_replace(
		'/<(h[1-6])(\s|>)/i',
		'<$1 id="' . esc_attr( $next['id'] ) . '"$2',
		$block_content,
		1
	);

	return $block_content;
}
