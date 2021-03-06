<?php
namespace Podlove\Modules\ImportExport;

use Podlove\Model;
use Podlove\Modules\ImportExport\Exporter;

class Importer {

	// path to import file
	private $file;

	// SimpleXML document of import file
	private $xml;

	public function __construct($file) {
		$this->file = $file;
	}

	public function import() {

		$this->xml = simplexml_load_file($this->file);
		$this->xml->registerXPathNamespace('wpe', Exporter::XML_NAMESPACE);

		$this->importEpisodes();
		$this->importOptions();
		$this->importAssets();
		$this->importFeeds();
		$this->importFileTypes();
		$this->importMediaFiles();
		$this->importTemplates();

		do_action('podlove_xml_import', $this->xml);

		wp_redirect(admin_url('admin.php?page=podlove_imexport_migration_handle&status=success'));
		exit;
	}

	private function importEpisodes()
	{
		Model\Episode::delete_all();

		$episodes = $this->xml->xpath('//wpe:episode');
		foreach ($episodes as $episode) {
			$new_episode = new Model\Episode;

			foreach ($episode->children('wpe', true) as $attribute) {
				$new_episode->{$attribute->getName()} = self::escape((string) $attribute);
			}

			if ($new_post_id = $this->getNewPostId($new_episode->post_id)) {
				$new_episode->post_id = $new_post_id;
				$new_episode->save();
			} else {
				// no matching post found
			}
		}
	}

	private function importOptions()
	{
		$wpe_options = $this->xml->xpath('//wpe:options');
		$options = $wpe_options[0]->children('wpe', true);
		foreach ($options as $option) {
			update_option($option->getName(), maybe_unserialize((string) $option));
		}
	}

	private function importAssets() {
		self::importTable($this->xml, 'asset', '\Podlove\Model\EpisodeAsset');
	}

	private function importFeeds() {
		self::importTable($this->xml, 'feed', '\Podlove\Model\Feed');
	}

	private function importFileTypes() {
		self::importTable($this->xml, 'filetype', '\Podlove\Model\FileType');	
	}

	private function importMediaFiles() {
		self::importTable($this->xml, 'mediafile', '\Podlove\Model\MediaFile');
	}

	private function importTemplates() {
		self::importTable($this->xml, 'template', '\Podlove\Model\Template');
	}

	public static function importTable($xml, $item_name, $table_class) {
		$table_class::delete_all();

		$group = $xml->xpath('//wpe:' . $item_name);

		foreach ($group as $item) {
			$new_item = new $table_class;

			foreach ($item->children('wpe', true) as $attribute) {
				$new_item->{$attribute->getName()} = self::escape((string) $attribute);
			}

			$new_item->save();
		}	
	}

	private static function escape($value) {
		global $wpdb;
		$wpdb->escape_by_ref($value);
		return $value;
	}

	/**
	 * Get mapping for post id after post import.
	 *
	 * When importing posts, their IDs might change.
	 * This function maps an existing post id to the new one.
	 * 
	 * @param  int      $old_post_id
	 * @return int|null post_id on success, otherwise null.
	 */
	private function getNewPostId($old_post_id)
	{
		$query_for_post_id = new \WP_Query(array(
			'post_type' => 'podcast',
			'meta_query' => array(
				array(
					'key' => 'import_id',
					'value' => $old_post_id,
					'compare' => '='
				)
			)
		));

		if ($query_for_post_id->have_posts()) {
			$p = $query_for_post_id->next_post();
			return $p->ID;
		} else {
			return null;
		}
	}

}