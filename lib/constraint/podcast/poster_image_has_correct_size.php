<?php
namespace Podlove\Constraint\Podcast;

use Podlove\Model;
use Podlove\Constraint\Constraint;

class PosterImageHasCorrectSize extends Constraint {

	const SCOPE = 'podcast';
	const SEVERITY = Constraint::SEVERITY_WARNING;

	public function the_title() {
		echo __('Poster image has not the right size.', 'podlove');
	}

	public function the_description() {
		$edit_url = admin_url('admin.php?page=podlove_settings_podcast_handle');
		$podcast = Model\Podcast::get_instance();
		?>
		<p>
			<?php echo sprintf(
				__('The podcast poster image %s does not have the right size.
					It should be at least 300x300 and not be bigger than 1400x1400.
					Keep in mind that the image will be downloaded by mobile clients, too.', 'podlove'),
				'<a href="' . $podcast->cover_image . '">' . $podcast->cover_image . '</a>'
			);
			?>
		</p>
		<p>
			<a href="<?php echo $edit_url ?>">
				<?php echo __('Edit podcast settings.', 'podlove') ?>
			</a>
		</p>
		<?php
	}

	public function isValid() {
		$podcast = Model\Podcast::get_instance();
		list($width, $height) = getimagesize($podcast->cover_image);

		return !$this->isTooSmall($width, $height) && !$this->isTooBig($width, $height);
	}

	private function isTooSmall($width, $height) {
		return $width < 300 || $height < 300;
	}

	private function isTooBig($width, $height) {
		return $width > 1400 || $height > 1400;
	}
}