<?php
// Service web du projet R�servations M2L
// Ecrit le 29/09/2015 par MrJ

// Ce service web permet à un utilisateur de s'authentifier
// et fournit un flux XML contenant un compte-rendu d'exécution

// Le service web doit recevoir 3 paramètres : nom, mdp, numreservation
// Les param�tres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://<hébergeur>/AnnulerReservation.php?nom=zenelsy&mdp=ab&numreservation=1
// Les paramètres peuvent être passés par la méthode POST (à privilègier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/AnnulerReservation.php

// d�claration des variables globales pour pouvoir les utiliser aussi dans les fonctions
global $doc;		// le document XML à générer

// inclusion de la classe Outils
include_once ('../modele/Outils.class.php');
// inclusion des paramètres de l'application
include_once ('../modele/include.parametres.php');

// cr�e une instance de DOMdocument
$doc = new DOMDocument();

// specifie la version et le type d'encodage
$doc->version = '1.0';
$doc->encoding = 'ISO-8859-1';

// crée un commentaire et l'encode en ISO
$elt_commentaire = $doc->createComment('Service web Connecter - BTS SIO - Lyc�e De La Salle - Rennes');
// place ce commentaire à la racine du document XML
$doc->appendChild($elt_commentaire);

// R�cup�ration des donn�es transmises
// la fonction $_GET r�cup�re une donn�e pass�e en param�tre dans l'URL par la m�thode GET
if ( empty ($_GET ["nom"]) == true)  $nom = "";  else   $nom = $_GET ["nom"];
if ( empty ($_GET ["mdp"]) == true)  $mdp = "";  else   $mdp = $_GET ["mdp"];
if ( empty ($_GET ["numreservation"]) == true)  $numreservation = "";  else   $numreservation = $_GET ["numreservation"];
// si l'URL ne contient pas les données, on regarde si elles ont �t� envoy�es par la m�thode POST
// la fonction $_POST récupère une donnée envoyées par la méthode POST
if ( $nom == "" && $mdp == "" && $numreservation=="")
{	if ( empty ($_POST ["nom"]) == true)  $nom = "";  else   $nom = $_POST ["nom"];
	if ( empty ($_POST ["mdp"]) == true)  $mdp = "";  else   $mdp = $_POST ["mdp"];
	if ( empty ($_POST ["numreservation"]) == true)  $numreservation = "";  else   $numreservation = $_POST ["numreservation"];
}

// Contr�le de la pr�sence des param�tres
if ( $nom == "" || $mdp == "" || $numreservation == "")
{	TraitementAnormal ("Erreur : données incomplètes.");
}
else
{	// connexion du serveur web à la base MySQL ("include_once" peut etre remplac� par "require_once")
include_once ('../modele/DAO.class.php');
$dao = new DAO();
$niveauUtilisateur = $dao->getNiveauUtilisateur($nom, $mdp);

if ( $niveauUtilisateur == "inconnu" )
	TraitementAnormal("Erreur : authentification incorrecte.");
else
{
	//vérification si le numéro de réservation est existant
	$reservationExistante = $dao->existeReservation($numreservation);
	if($reservationExistante == false)
		TraitementAnormal("Erreur : numéro de réservation inexistant.");
	else
	{
		//vérification si le demandeur est bien l'auteur
		$createur = $dao->estLeCreateur($nom, $numreservation);
		if($createur == false)
			TraitementAnormal("Erreur : vous n'êtes pas l'auteur de cette réservation.");
		else 
		{
			$reservation = $dao->getReservation($numreservation);			
				
				//vérification si la date n'est pas passé
				if($reservation -> getStart_time() < time())
				{
					TraitementAnormal("Erreur : cette réservation est déjà passée.");
				}
				//confirmation de la réservation
				else 
				{
					$unUtilisateur = $dao ->  getUtilisateur($nom);
					$outils -> envoyerMail($unUtilisateur -> getEmail(), 'Annulation réservation', 'Votre réservation n°'.$numreservation.' a été annulée ', 'delasalle.sio.esnault.j@gmail.com');
					TraitementNormal("Enregistrement effectué ; vous allez recevoir un mail de confirmation.");
					$dao -> annulerReservation($numreservation);
				}
			
		}
	}
}	
// ferme la connexion à MySQL :
unset($dao);
}
// Mise en forme finale
$doc->formatOutput = true;
// renvoie le contenu XML
echo $doc->saveXML();
// fin du programme
exit;


// fonction de traitement des cas anormaux
function TraitementAnormal($msg)
{	// redéclaration des donn�es globales utilis�es dans la fonction
global $doc;
// crée l'élément 'data' � la racine du document XML
$elt_data = $doc->createElement('data');
$doc->appendChild($elt_data);
// place l'élément 'reponse' juste apr�s l'�l�ment 'data'
$elt_reponse = $doc->createElement('reponse', $msg);
$elt_data->appendChild($elt_reponse);
return;
}


// fonction de traitement des cas normaux
function TraitementNormal()
{	
	// redéclaration des donn�es globales utilis�es dans la fonction
	global $doc;
	// crée l'élément 'data' � la racine du document XML
	$elt_data = $doc->createElement('data');
	$doc->appendChild($elt_data);
	// place l'élément 'reponse' juste après l'�l�ment 'data'
	 $msg = "Enregistrement effectu� ; vous allez recevoir un mail de confirmation";
	$elt_reponse = $doc->createElement('reponse', $msg);
	$elt_data->appendChild($elt_reponse);
	return;
}
?>