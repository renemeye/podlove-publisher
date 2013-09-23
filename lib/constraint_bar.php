<?php 
namespace Podlove;

class ConstraintBar {

	/**
	 * Singleton
	 */
	private $instance;

	static public function instance()
	{
		 if (!isset($instance)) {
		 	$instance = new self;
		 }

		 return $instance;
	}

	final private function __clone(){}

	protected function __construct()
	{
		if ( ! is_super_admin() || ! is_admin_bar_showing() || $this->is_wp_login() )
			return;

		add_action('admin_bar_menu', array($this, 'admin_bar_menu'));
		add_action('all_admin_notices', array($this, 'render_conflicts'));
	}

	private function is_wp_login() {
		return 'wp-login.php' == basename( $_SERVER['SCRIPT_NAME'] );
	}

	public function render_conflicts() {
		?>
		<style type="text/css">
		#podlove-conflicts {
			border: 1px solid #CCC;
			border-left: 3px solid #f55;
			border-radius: 0;
			background: #EEE;
			padding: 0px 15px;
		}

		#podlove-conflicts article {
			border-bottom: 1px solid #CCC;
		}

		#wp-admin-bar-podlove-bar-conflicts.active {
			background-color: #EEEEEE;
		}

		#wp-admin-bar-podlove-bar-conflicts.active .ab-item {
			color: #464646;
			text-shadow: none;
		}
		</style>

		<script type="text/javascript">
		(function($) {
			$(document).ready(function() {
				var $button = $("#wp-admin-bar-podlove-bar-conflicts");
				$button.addClass("active");
				$button.on("click", function() {
					$(this).toggleClass("active");
					$("#podlove-conflicts").toggle();
				});
			});
		}(jQuery))
		</script>

		<section id="podlove-conflicts">
			<h3>Podlove Conflicts</h3>

			<?php foreach ( Model\Violation::find_all_by_where('resolved_at IS NULL') as $violation ): ?>
				<?php $constraint = $violation->getConstraint(); ?>
				<article>
					<header>
						<h4><?php $constraint->the_title(); ?></h4>
					</header>
					<?php $constraint->the_description(); ?>
				</article>
			<?php endforeach; ?>
		</section>
		<?php
	}

	public function admin_bar_menu() {
		global $wp_admin_bar;

		$wp_admin_bar->add_menu( array(
			'id'     => 'podlove-bar-conflicts',
			'parent' => 'top-secondary',
			'title'  => __('Podlove Conflicts', 'podlove')
		) );
	}
}
