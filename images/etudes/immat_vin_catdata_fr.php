<?php
/* fichier immat_vin_catdata_fr
saisie d'une immat et affichage des données BDD fr
-> données catdata
-> données AAA
-> données DATANEO
-> données vinfilter (cliquer pour y accéder)

*/

error_reporting(E_ALL);
error_reporting(E_ALL | E_STRICT | E_PARSE);
ini_set('display_startup_errors','1');
ini_set('display_errors','1');

@include_once ('fct_conn/config.php');

include_once('fct_conn/pdo.php');
include_once('fct_conn/ws_vinfilter.php');
include_once('fct_conn/fonctions.php');

// variables globales
$langue = "fr";
$pays = "fr";

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Immat VinFilter fr</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" href="css/tableaux.css" />
		<script src="js/jquery-3.1.0.min.js"></script>
    </head>
    <body id="bodyImmat">
		<!-- Titre de la page -->
		<p id="titre"><strong>SIV IMMAT / VIN CATDATA FR</strong></p>
		<!-- lien vers la page de menu et logo -->
		<form id="lienmenu" method="link" action="menu.html">
			<input type="submit" value="Retour Menu">
		</form>
		<div id="logo">
			<img src="img/logo-catdata.png" alt="catdata" style="height:30px;">
		</div>
		
		<!-- affichage du VinFitler et accès vers le tableau -->
		<form id="formAfficheVinFilter">
			<!-- <input id="afficheVinFilter" type="button" value="Afficher VinFilter "> -->
			<button id="afficheVinFilter" type="button"> Afficher VinFilter</button>
		</form>
		<script>
			$(document).ready(function() {
				var click = false;
				$("#afficheVinFilter").click(function() {
					if(!click) { // accès au résultat du VinFilter
						// afficher image loader
						$("#divImmatVIN").html("<img src='img/ajax-loader-large.gif' width='30'>");
						$.ajax({
							type: "POST",
							url: "ajax/immat_vinfilter_fr.php",
							data: {recupImmat : $("#txt_immat").val(),
									recupLogin : $("#txt_login").val(),
									recupMotDePasse : $("#txt_mdp").val()
									},
							success: function(data){
								// affichage du tableau VinFilter
								$("#divImmatVIN").html(data);
							}
						});
						$(location).attr('href',"#divImmatVIN");
						$("#afficheVinFilter").html(" Masquer VinFilter");
						click = true;
					} else { // suppression du résultat du VinFilter
						$("#divImmatVIN").empty();
						$(location).attr('href',"#");
						$("#afficheVinFilter").html(" Afficher VinFilter");
						click = false;
					}
				});
			});
		</script>
		
		<form action="immat_vin_catdata_fr.php" method="post" name="form" id="formImmat">
			<div id="labelImmat">
				<p id="haut" name="haut">
					<label for="txt_login" id="lbl_login">Login : </label>
				</p>
				<p id="milieu" name="milieu">
					<label for="txt_mdp" id="lbl_mdp">Mot de Passe : </label>
				</p>
				<p id="bas" name="bas">
				<label for="txt_immat" id="lbl_immat">Immat : </label>
				</p>
			</div>
			<div id="saisieImmat">
				<p id="haut" name="haut">
					<input type="text" id="txt_login" name="txt_login" size="30" value="<? 
						// récupération du login et affichage dans la zone de saisie
						if(empty($_POST['txt_login'])) {
							$login = _LOGIN_DEPART;
						} else {
							$login = $_POST['txt_login'];
						}
						echo $login; ?>" required />
				</p>
				<p id="milieu" name="milieu">
					<input type="password" id="txt_mdp" name="txt_mdp" size="30" value="<? 
						// récupération du mot de passe et affichage dans la zone de saisie
						if(empty($_POST['txt_mdp'])) {
							$motDePasse = _MOT_DE_PASSE;
						} else {
							$motDePasse = $_POST['txt_mdp'];
						}
						echo $motDePasse; ?>" required />
				</p>
				<p id="bas" name="bas">
				<input type="text" id="txt_immat" name="txt_immat" size="30" pattern="[0-9A-Za-z]{1,}" value="<? 
					// récupération de l'immat et affichage dans la zone de saisie
					if(empty($_POST['txt_immat'])) {
						$immat = _IMMAT_DEPART;
					} else {
						$immat = $_POST['txt_immat'];
					}
					echo $immat; ?>" required />
				<input type="submit" id="btn_saisie" name="btn_saisie" value="Envoyer"/>
				</p>
			</div>
		</form>
		
    <?php

	// Appel WS CATDATA
	$resWS = getDonneesWS($login, $motDePasse, $immat);	
	
	// Appel AAA
	$resAAA = getDonneesAAA(_CLIENT_SIRET, _CLIENT_ID, _CLIENT_PASSWORD, $immat);
	
	// appel au webservice DATA NEO
	$resDataNeo = appelDataNeo($immat, _LOGIN_DATA_NEO, _PASSWORD_DATA_NEO, _SIRET_DATA_NEO);
	
	// affichage vertical des tableaux de résultat
	// pour l'affichage des erreurs
	$erreur = false;
	
	?><div id="divImmat">
		<!-- tableau CATDATA -->
		<table><?php
			if($immat != "") { // si démarrage de l'outil : pas d'affichage
				if(isset($resWS->VEHICULE)) {
					?><caption>SIV CATDATA</caption><?php
					afficheEnTeteImmat();
					afficheDonneesImmat($resWS->VEHICULE);
				} else {
					$erreur = true;
				}
			}
		?></table>
		<!-- tableau AAA -->
		<table><?php
			if($immat != "") { // si démarrage de l'outil : pas d'affichage
				if(isset($resAAA['dataAAA'])) {
					?><caption>SIV AAA</caption><?php
					afficheEnTeteImmat();
					afficheDonneesImmat($resAAA['dataAAA']->return);
				} else {
					$erreur = true;
				}
			}
		?></table>
		<!-- tableau DATA NEO -->
		<table><?php
			if($immat != "") { // si démarrage de l'outil : pas d'affichage
				if(isset($resDataNeo->Vehicle)) {
					?><caption>SIV DATANEO</caption><?php
					afficheEnTete();
					AfficheDonnees($resDataNeo->Vehicle);
					// rajout du dernier membre dans un array pour la fonction d'affichage
					$tab = array('ResponseId' => $resDataNeo->ResponseId);
					AfficheDonnees($tab);
				} else {
					echo "<p style='color:red'><strong>Pas d'informations DATA NEO sur ce véhicule IMMAT / VIN = ".$immat."</strong></p>";
				}
			}
		?></table>
	</div>
	<!-- affichage horizontal des résultats -->
	<div id="divImmatKtypnr">
		<!-- tableau de données issues des ktypnr CATDATA et AAA -->
		<table><?php
			if($immat != "") { // si démarrage de l'outil : pas d'affichage
				$entete = false; // pour l'affichage unique de l'en - tête
				$tabClassId = array();
				$classId = 0;
				if(isset($resWS->VEHICULE->LTYPVEH->TYPVEH[0]->IDTYPVEH) && $resWS->VEHICULE->LTYPVEH->TYPVEH[0]->IDTYPVEH != 0) {
					// récupération des données CATDATA
					$tabTypVeh = $resWS->VEHICULE->LTYPVEH->TYPVEH;
					// récupération des ktypnr CATDATA
					$tabTypeId = array();
					$tabKtypnr = array();
					if(count($tabTypVeh) > 1) {
						foreach($tabTypVeh as $element) {
							$tabTypeId[] = array(
								"Type Id" => appelwsTypeId(_COMPANY, _USER, _PASSWORD, substr(($element->IDTYPVEH), 2, -3), substr(($element->IDTYPVEH), 0, 1))->GetVtAdcTypeIdByTcdTypeAndClassResult
							);
							$tabKtypnr[] = array(
								"Ktypnr" => substr(($element->IDTYPVEH), 2, -3)
							);
							$tabClassId[] = substr(($element->IDTYPVEH), 0, 1);
						}
					} else {
						$tabTypeId = array(
							"Type Id" => appelwsTypeId(_COMPANY, _USER, _PASSWORD, substr(($tabTypVeh[0]->IDTYPVEH), 2, -3), substr(($tabTypVeh[0]->IDTYPVEH), 0, 1))->GetVtAdcTypeIdByTcdTypeAndClassResult
						);
						$tabKtypnr = array(
							"Ktypnr" => substr(($tabTypVeh[0]->IDTYPVEH), 2, -3)
						);
						$tabClassId[] = substr(($tabTypVeh[0]->IDTYPVEH), 0, 1);
					}
					// récupération du classId
					$classId = substr(($tabTypVeh[0]->IDTYPVEH), 0, 1);
					if(!$entete) {
						if($classId != 2) {
							?><caption>Données Véhicules légers</caption><?php
						} else if($classId == 2) {
							?><caption>Données Poids lourds</caption><?php
						}
						afficheEnTeteHorizPaysClassId($pays, $classId, "CATDATA");
						$entete = true;
					}
					afficheDonneesTypnrTab($tabTypeId, $tabClassId, $tabKtypnr, $conn, $pays, "CATDATA");
				} else {
					// $erreur = true;
					echo "<p style='color:red'><strong>Pas d'informations ktypnr CATDATA sur ce véhicule IMMAT / VIN = ".$immat."</strong></p>";
				}
				if(isset($resAAA['dataAAA']->return->k_type) && $resAAA['dataAAA']->return->k_type != "") {
					// récupération du ktypnr AAA
					$tabk_type = $resAAA['dataAAA']->return->k_type;
						$tabTypeId = array(
							"Type Id" => appelwsTypeId(_COMPANY, _USER, _PASSWORD, $tabk_type, $classId)->GetVtAdcTypeIdByTcdTypeAndClassResult
						);
						$tabKtypnr = array(
							"Ktypnr" => $tabk_type
						);
					if(!$entete) {
						?><caption>Données Ktypnr</caption><?php
						afficheEnTeteHorizPaysClassId($pays, $classId, "AAA");
						$entete = true;
					}
					afficheDonneesTypnrTab($tabTypeId, $tabClassId, $tabKtypnr, $conn, $pays, "AAA");
				} else {
					// $erreur = true;
					echo "<p style='color:red'><strong>Pas d'informations ktypnr AAA sur ce véhicule IMMAT / VIN = ".$immat."</strong></p>";
				}
			}
		?></table>
	</div>
	<div id="divImmatVIN">
		<!-- tableau VIN -->
		<table><?php
			// affichage du tableau après appel par ajax suite au clic sur bouton Afficher VinFilter
		?></table>
	</div><?php
	
	if($erreur) {
		echo "<p style='color:red'><strong>Pas d'informations sur ce véhicule IMMAT / VIN = ".$immat."</strong></p>";
	}
	
?></body><?
?>