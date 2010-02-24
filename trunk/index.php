<?php

// On est pass� dans l'index
define("TR_ENGINE_INDEX", 1);

// V�rification de la version PHP
// Classe compatible PHP 4
require("engine/core/info.class.php");

// Inclusion du chargeur
require("engine/core/loader.class.php");

// Chargement du syst�me de s�curit�
Core_Loader::classLoader("Core_Secure");
Core_Secure::getInstance(true);

// Chargement du Marker
Core_Loader::classLoader("Exec_Marker");

if (Core_Secure::isDebuggingMode()) {
	Exec_Marker::startTimer("all");
}
Exec_Marker::startTimer("main");

// Chargement de la classe principal
Core_Loader::classLoader("Core_Main");

// Pr�paration du moteur
$TR_ENGINE = new Core_Main();

// Recherche de nouveau composant
if ($TR_ENGINE->newComponentDetected()) {
	// Installtion des nouveaux composants
	$TR_ENGINE->install();
} else {
	// D�marrage du moteur
	$TR_ENGINE->start();
}

if (Core_Secure::isDebuggingMode()) {
	Exec_Marker::stopTimer("all");
	Core_Exception::displayException();
}

?>