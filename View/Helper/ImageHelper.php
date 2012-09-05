<?php
/**
 * ImageHelper
 *
 * @author Florian Kr�mer
 * @copyright 2012 Florian Kr�mer
 * @license MIT
 */
class ImageHelper extends AppHelper {
/**
 * Helpers
 *
 * @var array
 */
	public $helpers = array(
		'Html');

/**
 * Generates an image url based on the image record data and the used gaufrette adapter to store it
 *
 * @param array $image FileStorage array record or whatever else table that matches this helpers needs without the model, we just want the record fields
 * @param string $version Image version string
 * @param array $options HtmlHelper::image(), 2nd arg options array
 * @return string
 */
	public function image($image, $version = null, $options = array()) {
		if (empty($image) || empty($image['id'])) {
			return $this->fallbackImage($options);
		}

		$url = $this->url($image, $version, $options);

		if ($url !== false) {
			return $this->Html->image($url, $options);
		} else {
			return $this->fallbackImage($options);
		}
	}

	public function url($image, $version = null, $options = array()) {
		$hash = Configure::read('Media.imageHashes.' . $image['model'] . '.' . $version);
		if (empty($hash)) {
			throw new InvalidArgumentException(__d('FileStorage', 'No valid version key passed!'));
		}

		$Event = new CakeEvent('FileStorage.ImageHelper.imagePath', $this, array(
				'hash' => $hash,
				'image' => $image,
				'version' => $version,
				'options' => $options));
		CakeEventManager::instance()->dispatch($Event);

		if ($Event->isStopped()) {
			return '/' . $this->normalizePath($Event->data['path']);
		} else {
			return false;
		}
	}

/**
 * Provides a fallback image if the image record is empty
 *
 * @param array $options
 * @return string
 */
	public function fallbackImage($options = array()) {
		if (isset($options['fallback'])) {
			$image = $options['fallback'];
			unset($options['fallback']);
			return $this->Html->image($image, $options);
		}
		return '';
	}

/**
 * Turns the windows \ into / so that the path can be used in an url
 *
 * @param string $path
 * @return string
 */
	public function normalizePath($path) {
		return str_replace('\\', '/', $path);
	}

}