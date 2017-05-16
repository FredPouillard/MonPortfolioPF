<?php
// fichier qui traite un appel ajax
// affiche le résultat d'un appel au VinFilter
// BDD fr
@include_once ('../fct_conn/config.php');

include_once('../fct_conn/pdo.php');
include_once('../fct_conn/ws_vinfilter.php');
include_once('../fct_conn/fonctions.php');

// variables globales
$langue = "fr";
$pays = "fr";

	// récupération de l'immat
	if(empty($_POST['recupImmat'])) {
		$immat = _IMMAT_DEPART;
	} else {
		$immat = $_POST['recupImmat'];
	}
	
	// récupération du login
	if(empty($_POST['recupLogin'])) {
		$login = _LOGIN_DEPART;
	} else {
		$login = $_POST['recupLogin'];
	}
	
	// récupération du mot de passe
	if(empty($_POST['recupMotDePasse'])) {
		$motDePasse = _MOT_DE_PASSE;
	} else {
		$motDePasse = $_POST['recupMotDePasse'];
	}
	
	// récupération du TypeId et du Ktypnr
	$resWS = getDonneesWS($login, $motDePasse, $immat);	
	
	if(isset($resWS->VEHICULE->CODIF_VIN_PRF)) {
		$vin = $resWS->VEHICULE->CODIF_VIN_PRF;
		
		// recherche du typeId puis du ktypnr
		$resVIN = appelwsVINmodif($vin, _COMPANY, $pays, $langue, _PASSWORD, _USER);
		if(isset($resVIN->TypeId)) {
			$tabTypeId = array();
			$tabKtypnr = array();
			foreach($resVIN->TypeId as $element) {
				$tabTypeId[] = array(
					"Type Id" => $element->TypeId
				);
				$tabKtypnr[] = array(
					"Ktypnr" => appelwsKtypnrFromTypeId($element->TypeId, _COMPANY, _PASSWORD, _USER)->GetVtTcdTypeAndClassByAdcTypeIdResult->TcdTypeId
				);
			}
		} else {
			$tabTypeId = array(
				"Type Id" => ""
			);
			$tabKtypnr = array(
				"Ktypnr" => ""
			);
		}
		
	} else {
		$tabTypeId = array(
			"Type Id" => ""
		);
		$tabKtypnr = array(
			"Ktypnr" => ""
		);
	}
	
	if($immat != "") { // si démarrage de l'outil : pas d'affichage
		if(isset($tabTypeId[0])) {
		// saisie du classId pour affichage du titre et de l'en - tête
			$classId = appelwsKtypnrFromTypeId($tabTypeId[0]['Type Id'], _COMPANY, _PASSWORD, _USER)->GetVtTcdTypeAndClassByAdcTypeIdResult->TcdClassId;
			if($classId != 2) {
			// affichage titre et en - tête BDD France vl
				?><caption>SIV VinFilter Véhicule léger (BDD France)</caption><?
				afficheEnTeteHorizPaysClassId($pays, $classId);
			} else if($classId == 2) {
			// affichage titre et en - tête BDD France pl
				?><caption>SIV VinFilter Poids lourd (BDD France)</caption><?
				afficheEnTeteHorizPaysClassId($pays, $classId);
			}
			afficheDonneesKtypnr($tabTypeId, $classId, $tabKtypnr, $conn, $pays);
		} else {
			echo "<p style='color:red'><strong>Pas de données Ktypnr.</strong></p>";
		}
	}

?>