<?php

/*                                                                        *
 * This script is backported from the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


/**
 * Output the configured stylesheets and JavaScript include tags for a given preset
 */
class Tx_FormBase_ViewHelpers_RenderHeadViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * @inject
	 * @var Tx_FormBase_Resource_Publishing_ResourcePublisher
	 */
	protected $resourcePublisher;

	/**
	 * @inject
	 * @var Tx_FormBase_Factory_ArrayFormFactory
	 */
	protected $formBuilderFactory;

	/**
	 * @param string $presetName name of the preset to use
	 * @return string the rendered form head
	 */
	public function render($presetName = 'default') {
		$content = '';
		$presetConfiguration = $this->formBuilderFactory->getPresetConfiguration($presetName);
		$stylesheets = isset($presetConfiguration['stylesheets']) ? $presetConfiguration['stylesheets'] : array();
		foreach ($stylesheets as $stylesheet) {
			$content .= sprintf('<link href="%s" rel="stylesheet">', $this->resolveResourcePath($stylesheet['source']));
		}
		$javaScripts = isset($presetConfiguration['javaScripts']) ? $presetConfiguration['javaScripts'] : array();
		foreach ($javaScripts as $javaScript) {
			$content .= sprintf('<script src="%s"></script>', $this->resolveResourcePath($javaScript['source']));
		}
		return $content;
	}

	/**
	 * @param string $resourcePath
	 * @return string
	 */
	protected function resolveResourcePath($resourcePath) {
		// TODO: This method should be somewhere in the resource manager probably?
		$matches = array();
		preg_match('#resource://([^/]*)/Public/(.*)#', $resourcePath, $matches);
		if ($matches === array()) {
			throw new Tx_FormBase_Core_ViewHelper_Exception('Resource path "' . $resourcePath . '" can\'t be resolved.', 1328543327);
		}
		$package = $matches[1];
		$path = $matches[2];
		return $this->resourcePublisher->getStaticResourcesWebBaseUri() . 'Packages/' . $package . '/' . $path;
	}
}
?>