<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Test de la classe DAO</title>
	<style type="text/css">body {font-family: Arial, Helvetica, sans-serif; font-size: small;}</style>
</head>
<body>

<?php
// Domaine : Services web de l'application de suivi des r�servations de la Maison des Ligues de Lorraine
// Test de la classe DAO
// Auteur : JM CARTRON
// Derni�re mise � jour : 29/9/2015

// connexion du serveur web � la base MySQL
include_once ('DAO.class.php');
$dao = new DAO();


// test de la m�thode getNiveauUtilisateur --------------------------------------------------------
// modifi� par Jim le 24/9/2015
// echo "<h3>Test de getNiveauUtilisateur : </h3>";
// $niveauUtilisateur = $dao->getNiveauUtilisateur('admin', 'admin');
// echo "<p>NiveauUtilisateur de ('admin', 'admin') : <b>" . $niveauUtilisateur . "</b><br>";
// $niveauUtilisateur = $dao->getNiveauUtilisateur('admin', 'adminnnnn');
// echo "NiveauUtilisateur de ('admin', 'adminnnnn') : <b>" . $niveauUtilisateur . "</b><br>";
// $niveauUtilisateur = $dao->getNiveauUtilisateur('guesdonm', 'passe');
// echo "NiveauUtilisateur de ('guesdonm', 'passe') : <b>" . $niveauUtilisateur . "</b></p>";


// test de la m�thode genererUnDigicode -----------------------------------------------------------
// modifi� par Jim le 24/9/2015
echo "<h3>Test de genererUnDigicode : </h3>";
echo "<p>Un digicode aléatoire : <b>" . $dao->genererUnDigicode() . "</b><br>";
echo "Un digicode aléatoire : <b>" . $dao->genererUnDigicode() . "</b><br>";
echo "Un digicode aléatoire : <b>" . $dao->genererUnDigicode() . "</b><p>";


// test de la m�thode creerLesDigicodesManquants --------------------------------------------------
// modifi� par Jim le 24/9/2015
// echo "<h3>Test de creerLesDigicodesManquants : </h3>";
// $dao->creerLesDigicodesManquants();
// echo "<p>Pour ce test, videz auparavant la table <b>mrbs_entry_digicode</b><br>";
// echo " puis vérifiez que la table est reconstruite après exécution du test.</p>";


// test de la m�thode listeReservations -----------------------------------------------------------
// modifi� par Jim le 25/9/2015
// echo "<h3>Test de listeReservations : </h3>";
// $lesReservations = $dao->listeReservations("jim");
// $nbReponses = sizeof($lesReservations);
// echo "<p>Nombre de réservations de 'jim' : " . $nbReponses . "</p>";
// affichage des r�servations
// foreach ($lesReservations as $uneReservation)
// {	echo (utf8_encode($uneReservation->toString()));
// 	echo ('<br>');
// }
// $lesReservations = $dao->listeReservations("zenelsy");
// $nbReponses = sizeof($lesReservations);
// echo "<p>Nombre de réservations de 'zenelsy' : " . $nbReponses . "</p>";
// // affichage des r�servations
// foreach ($lesReservations as $uneReservation)
// {	echo (utf8_encode($uneReservation->toString()));
// echo ('<br>');
// }


// // test de la méthode existeReservation -----------------------------------------------------------
// // modifié par Jim le 25/9/2015
// echo "<h3>Test de existeReservation : </h3>";
// if ($dao->existeReservation("7")) $existe = "oui"; else $existe = "non";
// echo "<p>Existence de la réservation 7 : <b>" . $existe . "</b><br>";
// if ($dao->existeReservation("1")) $existe = "oui"; else $existe = "non";
//  echo "Existence de la réservation 1 : <b>" . $existe . "</b></p>";


// // test de la méthode estLeCreateur ---------------------------------------------------------------
// // modifié par Jim le 25/9/2015
// echo "<h3>Test de estLeCreateur : </h3>";
// if ($dao->estLeCreateur("antoineq", "7")) $estLeCreateur = "oui"; else $estLeCreateur = "non";
// echo "<p>'antoineq' a crée la réservation 7 : <b>" . $estLeCreateur . "</b><br>";
// if ($dao->estLeCreateur("admin", "7")) $estLeCreateur = "oui"; else $estLeCreateur = "non";
// echo "'admin' a crée la réservation 7 : <b>" . $estLeCreateur . "</b></p>";


// test de la méthode getReservation --------------------------------------------------------------
// modifié par Jim le 25/9/2015
// echo "<h3>Test de getReservation : </h3>";
// $laReservation = $dao->getReservation("1");
// if ($laReservation) 
// 	echo "<p>La réservation 1 existe : <br>" . utf8_encode($laReservation->toString()) . "</p>";
// else
// 	echo "<p>La réservation 1 n'existe pas !</p>";	
// $laReservation = $dao->getReservation("7");
// if ($laReservation) 
// 	echo "<p>La réservation 7 existe : <br>" . utf8_encode($laReservation->toString()) . "</p>";
// else
// 	echo "<p>La réservation 7 n'existe pas !</p>";	


// // test de la méthode getUtilisateur --------------------------------------------------------------
// // modifié par Jim le 28/9/2015
// echo "<h3>Test de getUtilisateur : </h3>";
// $unUtilisateur = $dao->getUtilisateur("antoineq");
// if ($unUtilisateur)
// 	echo "<p>L'utilisateur admin existe : <br>" . utf8_encode($unUtilisateur->toString()) . "</p>";
// else
// 	echo "<p>L'utilisateur admin n'existe pas !</p>";
// $unUtilisateur = $dao->getUtilisateur("antoineq");
// if ($unUtilisateur)
// 	echo "<p>L'utilisateur antoineq existe : <br>" . utf8_encode($unUtilisateur->toString()) . "</p>";
// else
// 	echo "<p>L'utilisateur antoineq n'existe pas !</p>";


// // test de la méthode confirmerReservation --------------------------------------------------------
// // pour ce test, utiliser une r�servation dont le champ status est mis auparavant � 4 (�tat provisoire)
// // modifi� par Jim le 28/9/2015
// echo "<h3>Test de confirmerReservation : </h3>";
// $laReservation = $dao->getReservation("7");
// if ($laReservation) {
// 	echo "<p>Etat de la réservation 7 avant confirmation : <b>" . $laReservation->getStatus() . "</b><br>";
// 	$dao->confirmerReservation("7");
// 	$laReservation = $dao->getReservation("7");
// 	echo "Etat de la réservation 7 aprés confirmation : <b>" . $laReservation->getStatus() . "</b></p>";
// }
// else
// 	echo "<p>La réservation 7 n'existe pas !</p>";	


// // test de la méthode annulerReservation --------------------------------------------------------
// // pour ce test, utiliser une réservation existante
// // modifié par Jim le 28/9/2015
// echo "<h3>Test de annulerReservation : </h3>";
// $laReservation = $dao->getReservation("6");
// if ($laReservation) {
// 	$dao->annulerReservation("6");
// 	$laReservation = $dao->getReservation("6");
// 	if ($laReservation)
// 		echo "La réservation 6 n'a pas été supprimée !</p>";
// 	else
// 		echo "La réservation 6 a bien été supprimée !</p>";
// }
// else
// 	echo "<p>La réservation 6 n'existe pas !</p>";


// test de la méthode existeUtilisateur -----------------------------------------------------------
// modifi� par Jim le 28/9/2015
// echo "<h3>Test de existeUtilisateur : </h3>";
// if ($dao->existeUtilisateur("admin")) $existe = "oui"; else $existe = "non";
// echo "<p>Existence de l'utilisateur 'admin' : <b>" . $existe . "</b><br>";
// if ($dao->existeUtilisateur("xxxx")) $existe = "oui"; else $existe = "non";
// echo "Existence de l'utilisateur 'xxxx' : <b>" . $existe . "</b></p>";


// // test de la m�thode modifierMdpUser -------------------------------------------------------------
// // modifié par Jim le 28/9/2015
// echo "<h3>Test de modifierMdpUser : </h3>";
// $unUtilisateur = $dao->getUtilisateur("admin");
// if ($unUtilisateur) {
// 	$dao->modifierMdpUser("admin", "passe");
// 	$unUtilisateur = $dao->getUtilisateur("admin");
// 	echo "<p>Nouveau mot de passe de l'utilisateur admin : <b>" . $unUtilisateur->getPassword() . "</b><br>";
	
// 	$dao->modifierMdpUser("admin", "admin");
// 	$unUtilisateur = $dao->getUtilisateur("admin");
// 	echo "Nouveau mot de passe de l'utilisateur admin : <b>" . $unUtilisateur->getPassword() . "</b><br>";
	
// 	$niveauUtilisateur = $dao->getNiveauUtilisateur('admin', 'admin');
// 	echo "NiveauUtilisateur de ('admin', 'admin') : <b>" . $niveauUtilisateur . "</b></p>";
// }
// else
// 	echo "<p>L'utilisateur admin n'existe pas !</p>";


// // test de la méthode envoyerMdp ------------------------------------------------------------------
// // modifi� par Jim le 28/9/2015
// echo "<h3>Test de envoyerMdp : </h3>";
// $dao->modifierMdpUser("jim", "passe");
// $ok = $dao->envoyerMdp("jim", "passe");
// if ($ok)
// 	echo "<p>Mail bien envoyé !</p>";
// else
// 	echo "<p>Echec lors de l'envoi du mail !</p>";


// test de la méthode testerDigicodeSalle ---------------------------------------------------------
// modifié par Jim le 28/9/2015
// echo "<h3>Test de testerDigicodeSalle : </h3>";
// $reponse = $dao->testerDigicodeSalle("10", "34214E");
// echo "<p>L'appel de testerDigicodeSalle('10', '34214E') donne : <b>" . $reponse . "</b><br>";


// // test de la m�thode testerDigicodeBatiment ------------------------------------------------------
// // modifi� par Jim le 28/9/2015
echo "<h3>Test de testerDigicodeBatiment : </h3>";
$reponse = $dao->testerDigicodeBatiment("34214E");
echo "<p>L'appel de testerDigicodeBatiment('34214E') donne : <b>" . $reponse . "</b><br>";


// test de la méthode enregistrerUtilisateur ------------------------------------------------------
// modifi� par Jim le 28/9/2015
// echo "<h3>Test de enregistrerUtilisateur : </h3>";
// $ok = $dao->enregistrerUtilisateur("jim1", "1", "passe", "delasalle.sio.esnault.j@gmail.com");
// if ($ok)
// 	echo "<p>Utilisateur bien enregistré !</p>";
// else
// 	echo "<p>Echec lors de l'enregistrement de l'utilisateur !</p>";


// // test de la méthode aPasseDesReservations -------------------------------------------------------
// // pour ce test, choisir un utilisateur avec des r�servations et un autre sans r�servation
// // modifi� par Jim le 28/9/2015
//  echo "<h3>Test de aPasseDesReservations : </h3>";
//  $ok = $dao->aPasseDesReservations("zenelsy");
//  if ($ok)
// echo "<p>zenelsy a bien passé des réservations !<br>";
// else
// echo "<p>zenelsy n'a pas passé de réservations !<br>";
// $ok = $dao->aPasseDesReservations("admin");
// if ($ok)
// echo "admin a bien passé des réservations !</p>";
// else
// echo "admin n'a pas passé de réservations !</p>";


// // test de la m�thode supprimerUtilisateur --------------------------------------------------------
// // modifi� par Jim le 28/9/2015
echo "<h3>Test de supprimerUtilisateur : </h3>";
$ok = $dao->supprimerUtilisateur("jim");
if ($ok)
	echo "<p>Utilisateur 'jim' a bien supprimé !</p>";
else
	echo "<p>Echec lors de la suppression de l'utilisateur 'jim' !</p>";


// // test de la m�thode listeSalles -----------------------------------------------------------------
// // modifi� par Jim le 28/9/2015
// echo "<h3>Test de listeSalles : </h3>";
// $lesSalles = $dao->listeSalles();
// $nbReponses = sizeof($lesSalles);
// echo "<p>Nombre de salles : " . $nbReponses . "</p>";
// // affichage des salles
// foreach ($lesSalles as $uneSalle)
// {	echo (utf8_encode($uneSalle->getRoom_name()));
// 	echo ('<br>');
// }


// ferme la connexion � MySQL :
unset($dao);
?>

</body>
</html>