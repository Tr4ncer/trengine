<?php
if (!defined("TR_ENGINE_INDEX")) {
	require("../../engine/core/secure.class.php");
	new Core_Secure();
}

/**
 * Module d'interface entre le site et le client.
 * 
 * @author Sebastien Villemain
 */
class Module_Management_Index extends Module_Model {

	/**
	 * Tableau contenant les boutons de la barre d'outil.
	 *
	 * @var array
	 */
	private static $toolbar = array();
	
	public function display() {
		// Ajout du CSS du template et du fichier javascript par d�faut
		Core_HTML::getInstance()->addCssTemplateFile("module_management.css");
		Core_HTML::getInstance()->addJavascriptFile("management.js");
		
		// Nom de la page administable
		$managePage = Core_Request::getString("manage");
		
		$pageList = self::listManagementPages(); // Liste de pages de configuration
		$moduleList = Libs_Module::listModules(); // Liste des modules
		
		// Pr�paration de la mise en page
		$libsMakeStyle = new Libs_MakeStyle();
		$libsMakeStyle->assign("pageList", $pageList);
		$libsMakeStyle->assign("moduleList", $moduleList);
		
		// Affichage de la page d'administration
		$managementScreen = "module_management_index";
		$pageSelected = "";
		if (!empty($managePage)) { // Affichage d'une page de configuration sp�cial
			$settingPage = Exec_Utils::inMultiArray($managePage, $pageList);
			$moduleSettingPage = Exec_Utils::inMultiArray($managePage, $moduleList);
			
			// Si c'est une page valide
			if ($settingPage || $moduleSettingPage) {
				$pageSelected = $managePage; // Page selectionn�e
				$managementScreen = "module_management_setting"; // Nom du template
				
				// R�cuperation du module
				$moduleClassPage = "";
				$moduleClassName = "";
				if ($settingPage) {
					$moduleClassPage = "Module_Management_" . ucfirst($managePage) . ".setting";
					$moduleClassName = "Module_Management_" . ucfirst($managePage);
				} else {
					$moduleClassPage = $moduleClassName = "Module_" . ucfirst($managePage) . "_Index";
				}
				
				// Si aucune erreur, on lance le module
				if (Core_Loader::classLoader($moduleClassPage)) {
					// Nom de la page courane
					$currentPageName = self::getManagementPageName($pageSelected);
					$libsMakeStyle->assign("currentPageName", $currentPageName);

					// Ajout du repere au fil d'ariane
					if (Core_Main::isFullScreen()) {
						Libs_Breadcrumb::getInstance()->addTrail(
							$currentPageName,
							Core_Html::getLink('?mod=management&manage=' . $pageSelected)
						);
					}

					$ModuleClass = new $moduleClassName();
					$content = "";
					if (Core_Loader::isCallable($moduleClassPage, "setting")) {
						$content = $ModuleClass->setting();
					}
					
					$libsMakeStyle->assign("content", $content);
				}
			}
		}
		$libsMakeStyle->assign("toolbar", self::$toolbar);
		$libsMakeStyle->assign("pageSelected", $pageSelected);
		$libsMakeStyle->display($managementScreen);
	}

	public function install() {
		// Aucune installation n'est permise
	}

	public function uninstall() {
		// Aucune d�sinstallation n'est permise
	}
	
	/**
	 * Retourne un tableau contenant les pages d'administration disponibles.
	 * 
	 * @return array => array("value" => valeur de la page, "name" => nom de la page).
	 */
	public static function &listManagementPages() {
		$page = "";
		$pageList = array();
		$files = Core_CacheBuffer::listNames("modules/management");
		foreach($files as $key => $fileName) {
			// Nettoyage du nom de la page
			$pos = strpos($fileName, ".setting.module");
			// Si c'est une page administrable
			if ($pos !== false && $pos > 0) {
				$page = substr($fileName, 0, $pos);
				// V�rification des droits de l'utilisateur
				if (Core_Access::moderate("management/" . $page . ".setting")) {
					$name = self::getManagementPageName($page);
					$pageList[] = array("value" => $page, "name" => $name);
				}
			}
		}
		return $pageList;
	}

	/**
	 * Retourne le nom litt�rale de la page.
	 * 
	 * @param $page String
	 * @return String
	 */
	private static function &getManagementPageName($page) {
		$name = defined("SETTING_TITLE_" . strtoupper($page)) ? constant("SETTING_TITLE_" . strtoupper($page)) : ucfirst($page);
		return $name;
	}

	/**
	 * Ajout d'un bouton a la barre d'outil.
	 * 
	 * @param String $name
	 * @param String $description
	 * @param String $link
	 */
	private static function addButtonInToolbar($name, $description, $link) {
		self::$toolbar[] = array(
			"name" => $name,
			"description" => $description,
			"link" => $link
		);
	}

	public static function addEditButtonInToolbar($link, $description = "") {
		if (empty($description)) $description = EDIT;
		self::addButtonInToolbar("edit", $description, $link);
	}
	public static function addDeleteButtonInToolbar($link, $description = "") {
		if (empty($description)) $description = DELETE;
		self::addButtonInToolbar("delete", $description, $link);
	}
	public static function addCopyButtonInToolbar($link, $description = "") {
		if (empty($description)) $description = COPY;
		self::addButtonInToolbar("copy", $description, $link);
	}
	public static function addAddButtonInToolbar($link, $description = "") {
		if (empty($description)) $description = ADD;
		self::addButtonInToolbar("add", $description, $link);
	}
}

?>