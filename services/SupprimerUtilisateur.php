<?php
// Service web du projet Réservations M2L
// Ecrit le 13/10/2015 par Nicolas Esteve

// Ce service web permet à un administrateur authentifié de supprimer un utilisateur
// et fournit un compte-rendu d'exécution

// Le service web doit être appelé avec 3 paramètres : nomAdmin, mdpAdmin, nomUtilisateur
// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://<hébergeur>/CreerUtilisateur.php?nomAdmin=admin&mdpAdmin=admin&name=jim&level=1&email=jean.michel.cartron@gmail.com
// Les paramètres peuvent être passés par la méthode POST (à privilégier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/CreerUtilisateur.php