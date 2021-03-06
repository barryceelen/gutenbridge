<?php
/**
 * PostTypeColumnSupport
 *
 * @package Gutenbridge
 */

namespace Gutenbridge;

/**
 * PostTypeColumnSupport adds the custom Editor column to Post List
 * Screen in the WP-Admin. The column displays an icon to indicate
 * whether a post was edited or updated in the Block Editor or the
 * Classic Editor.
 */
class PostTypeColumnSupport {

	/**
	 * The parent plugin
	 *
	 * @var Plugin
	 */
	public $container;

	/**
	 * Registers with WordPress to manage the custom Posts Column
	 *
	 * @return void
	 */
	public function register() {
		add_filter( 'manage_posts_columns', [ $this, 'update_post_columns' ], 10000 );
		add_action( 'manage_posts_custom_column', [ $this, 'update_post_column_value' ], 10, 2 );
	}

	/**
	 * Only register if in an admin context.
	 */
	public function can_register() {
		return is_admin();
	}

	/**
	 * Adds Editor column and updates display order
	 *
	 * @param array $columns List of post columns
	 * @return array
	 */
	public function update_post_columns( $columns ) {
		$column_label = $this->get_editor_column_label();
		$title_index  = array_search( $title, $columns, true );

		$editor_column = [
			'editor' => $column_label,
		];

		$columns['editor'] = $column_label;

		$ranks = [
			'cb'        => 1,
			'title'     => 2,
			'editor'    => 3,
			'coauthors' => 4,
		];

		$sorter = function( $a, $b ) use ( $ranks ) {
			$a_rank = ! empty( $ranks[ $a ] ) ? $ranks[ $a ] : 1000;
			$b_rank = ! empty( $ranks[ $b ] ) ? $ranks[ $b ] : 1000;

			if ( $a_rank < $b_rank ) {
				return -1;
			} elseif ( $a_rank > $b_rank ) {
				return 1;
			} else {
				return 0;
			}
		};

		uksort( $columns, $sorter );

		$columns = apply_filters( 'gutenbridge_post_list_columns', $columns );

		return $columns;
	}

	/**
	 * Outputs Post Column based on whether the specified post was created
	 * or updated in CE or block editor.
	 *
	 * @param string $column The column name
	 * @param int    $post_id The current post id
	 * @return void
	 */
	public function update_post_column_value( $column, $post_id ) {
		if ( 'editor' !== $column ) {
			return;
		}

		$column_label = $this->get_editor_column_label();

		// phpcs:disable
		if ( $this->is_block_editor_post( $post_id ) ) {
			$icon  = $this->get_block_editor_column_icon();
			$title = __( BLOCK_EDITOR_LABEL, 'gutenbridge' );
		} else {
			$icon  = $this->get_classic_editor_column_icon();
			$title = __( CLASSIC_EDITOR_LABEL, 'gutenbridge' );
		}
		// phpcs:enable

		?>
			<span
				class="dashicons <?php echo esc_attr( $icon ); ?>"
				title="<?php echo esc_attr( $title ); ?>"
				>
			</span>
		<?php
	}

	/**
	 * Returns the label for the custom column
	 *
	 * @return string
	 */
	public function get_editor_column_label() {
		return __( 'Editor', 'gutenbridge' );
	}

	/**
	 * Returns the icon to indicate that a post was created in the block
	 * editor.
	 *
	 * @return string
	 */
	public function get_block_editor_column_icon() {
		return apply_filters( 'gutenbridge_block_editor_column_icon', 'dashicons-wordpress' );
	}

	/**
	 * Retruns the icon to indicate that a post was created in the classic
	 * editor.
	 *
	 * @return string
	 */
	public function get_classic_editor_column_icon() {
		return apply_filters( 'gutenbridge_classic_editor_column_icon', 'dashicons-backup' );
	}

	/**
	 * Checks if the specified post is a block editor post.
	 *
	 * @param int $post_id The post id.
	 * @return bool
	 */
	public function is_block_editor_post( $post_id ) {
		return $this->container->is_block_editor_post( $post_id );
	}

}
