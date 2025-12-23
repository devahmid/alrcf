-- Script d'ajout de la table announcements pour ALRCF
-- Exécuter ce script dans votre base de données existante
-- Ce script ajoute uniquement la table announcements sans modifier les tables existantes

-- Table des annonces entre voisins
CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userId INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category ENUM('service', 'emploi', 'vente', 'location', 'autre') NOT NULL,
    price DECIMAL(10,2),
    contactPhone VARCHAR(20),
    contactEmail VARCHAR(255),
    imageUrl VARCHAR(500),
    status ENUM('pending', 'approved', 'rejected', 'expired') DEFAULT 'pending',
    isPublic BOOLEAN DEFAULT TRUE,
    approvedBy INT,
    approvedAt DATETIME,
    rejectionReason TEXT,
    expiresAt DATETIME,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (userId) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approvedBy) REFERENCES users(id) ON DELETE SET NULL
);

-- Index pour améliorer les performances
CREATE INDEX IF NOT EXISTS idx_announcements_user ON announcements(userId);
CREATE INDEX IF NOT EXISTS idx_announcements_status ON announcements(status);
CREATE INDEX IF NOT EXISTS idx_announcements_category ON announcements(category);
CREATE INDEX IF NOT EXISTS idx_announcements_public ON announcements(isPublic, status);

