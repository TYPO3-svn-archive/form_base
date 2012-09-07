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
 * persistence identifier is some resource:// uri probably
 *
 */
class Tx_FormBase_Persistence_YamlPersistenceManager implements Tx_FormBase_Persistence_FormPersistenceManagerInterface, t3lib_Singleton {
					
	/**
	 * The Extbase object manager
	 * 
	 * @var Tx_Extbase_Object_ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @var string
	 */
	protected $savePath;

	/**
	 * @param array $settings
	 */
	public function injectSettings(array $settings) {
		if (isset($settings['yamlPersistenceManager']['savePath'])) {
			$this->savePath = $settings['yamlPersistenceManager']['savePath'];
			if (!is_dir($this->savePath)) {
				Tx_FormBase_Utility_Files::createDirectoryRecursively($this->savePath);
			}
		}
	}

	public function load($persistenceIdentifier) {
		if (!$this->exists($persistenceIdentifier)) {
			throw new Tx_FormBase_Exception_PersistenceManagerException(sprintf('The form identified by "%s" could not be loaded.', $persistenceIdentifier), 1329307034);
		}
		$formPathAndFilename = $this->getFormPathAndFilename($persistenceIdentifier);
		return \Symfony\Component\Yaml\Yaml::parse(file_get_contents($formPathAndFilename));
	}

	public function save($persistenceIdentifier, array $formDefinition) {
		$formPathAndFilename = $this->getFormPathAndFilename($persistenceIdentifier);
		file_put_contents($formPathAndFilename, \Symfony\Component\Yaml\Yaml::dump($formDefinition, 99));
	}

	public function exists($persistenceIdentifier) {
		return is_file($this->getFormPathAndFilename($persistenceIdentifier));
	}

	public function listForms() {
		$forms = array();
		$directoryIterator = $this->objectManager->create('DirectoryIterator',$this->savePath);

		foreach ($directoryIterator as $fileObject) {
			if (!$fileObject->isFile()) {
				continue;
			}
			$fileInfo = pathinfo($fileObject->getFilename());
			if (strtolower($fileInfo['extension']) !== 'yaml') {
				continue;
			}
			$persistenceIdentifier = $fileInfo['filename'];
			$form = $this->load($persistenceIdentifier);
			$forms[] = array(
				'identifier' => $form['identifier'],
				'name' => isset($form['label']) ? $form['label'] : $form['identifier'],
				'persistenceIdentifier' => $persistenceIdentifier
			);
		}
		return $forms;
	}

	/**
	 * Returns the absolute path and filename of the form with the specified $persistenceIdentifier
	 * Note: This (intentionally) does not check whether the file actually exists
	 *
	 * @param string $persistenceIdentifier
	 * @return string the absolute path and filename of the form with the specified $persistenceIdentifier
	 */
	protected function getFormPathAndFilename($persistenceIdentifier) {
		$formFileName = sprintf('%s.yaml', $persistenceIdentifier);
		return Tx_FormBase_Utility_Files::concatenatePaths(array($this->savePath, $formFileName));
	}
}
?>