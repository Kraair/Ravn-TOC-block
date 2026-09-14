<?php
/**
 * Server-side render van het inhoudsopgave-block.
 *
 * Beschikbare variabelen: $attributes, $content, $block
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id = get_the_ID();

if ( ! $post_id ) {
	return;
}

$desktop_levels = ! empty( $attributes['desktopLevels'] ) ? array_map( 'intval', (array) $attributes['desktopLevels'] ) : array( 2, 3 );
$mobile_levels  = ! empty( $attributes['mobileLevels'] ) ? array_map( 'intval', (array) $attributes['mobileLevels'] ) : array( 2, 3 );
$all_levels     = array_values( array_unique( array_merge( $desktop_levels, $mobile_levels ) ) );

$headings = toc_block_get_headings_for_post( $post_id, $all_levels );

if ( empty( $headings ) ) {
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		printf(
			'<p style="opacity:.6;font-style:italic;">%s</p>',
			esc_html__( 'Inhoudsopgave: er zijn nog geen titels gevonden op deze pagina.', 'toc-block' )
		);
	}
	return;
}

$title           = isset( $attributes['title'] ) ? $attributes['title'] : __( 'Inhoudsopgave', 'toc-block' );
$instance_class  = wp_unique_id( 'toc-block-instance-' );
$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => 'toc-block-nav ' . $instance_class ) );

// Inline stijl per link opbouwen op basis van de gekozen instellingen.
$link_style = '';
if ( ! empty( $attributes['linkColor'] ) ) {
	$link_style .= 'color:' . esc_html( $attributes['linkColor'] ) . ';';
}
if ( ! empty( $attributes['linkFontSize'] ) ) {
	$link_style .= 'font-size:' . (int) $attributes['linkFontSize'] . 'px;';
}
if ( ! empty( $attributes['linkFontWeight'] ) && 'inherit' !== $attributes['linkFontWeight'] ) {
	$link_style .= 'font-weight:' . esc_html( $attributes['linkFontWeight'] ) . ';';
}
$link_style .= 'text-decoration:' . esc_html( ! empty( $attributes['linkTextDecoration'] ) ? $attributes['linkTextDecoration'] : 'none' ) . ';';

$link_hover_color = ! empty( $attributes['linkHoverColor'] ) ? $attributes['linkHoverColor'] : '';
$highlight_attr   = ! empty( $attributes['highlightActive'] ) ? ' data-toc-active-highlight="1"' : '';
?>
<?php if ( $link_hover_color ) : ?>
	<style>.<?php echo esc_html( $instance_class ); ?> .toc-block-item a:hover { color: <?php echo esc_html( $link_hover_color ); ?> !important; }</style>
<?php endif; ?>
<nav <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo $highlight_attr; // phpcs:ignore WordPress.Security.EscapeOutput ?> aria-label="<?php echo esc_attr( $title ? $title : __( 'Inhoudsopgave', 'toc-block' ) ); ?>">
	<?php if ( ! empty( $title ) ) : ?>
		<p class="toc-block-title"><strong><?php echo esc_html( $title ); ?></strong></p>
	<?php endif; ?>
	<ul class="toc-block-list">
		<?php foreach ( $headings as $heading ) :
			$classes = array( 'toc-block-item', 'toc-block-level-' . $heading['level'] );
			if ( ! in_array( $heading['level'], $desktop_levels, true ) ) {
				$classes[] = 'toc-block-hide-desktop';
			}
			if ( ! in_array( $heading['level'], $mobile_levels, true ) ) {
				$classes[] = 'toc-block-hide-mobile';
			}
			?>
			<li class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
				<a href="#<?php echo esc_attr( $heading['id'] ); ?>"<?php echo $link_style ? ' style="' . esc_attr( $link_style ) . '"' : ''; ?>><?php echo esc_html( $heading['text'] ); ?></a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
