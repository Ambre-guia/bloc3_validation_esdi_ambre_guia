-- Structure de la table `utilisateurs`
CREATE TABLE `utilisateurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `prenom` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `mot_de_passe` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `date_inscription` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `role` enum('utilisateur','admin') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'utilisateur',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Ajout d'un utilisateur admin par défaut (mot de passe: admin123)
INSERT INTO `utilisateurs` (`nom`, `prenom`, `email`, `mot_de_passe`, `role`) VALUES
('Admin', 'Admin', 'admin@librairie.com', '$2y$10$XHExJ.xTvxIGP91E0E9ck.JOuajIH7ZBUq0ioWWDbVPKfK82L3kI2', 'admin');