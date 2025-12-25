-- Ajouter la table des projets associatifs
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category ENUM('culturel', 'sportif', 'social', 'environnement', 'autre') DEFAULT 'autre',
    status ENUM('planning', 'in_progress', 'completed', 'cancelled') DEFAULT 'planning',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    startDate DATE,
    endDate DATE,
    budget DECIMAL(10,2),
    imageUrl VARCHAR(500),
    createdBy INT NOT NULL,
    assignedTo INT,
    progress INT DEFAULT 0,
    isPublic BOOLEAN DEFAULT TRUE,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (createdBy) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assignedTo) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_category (category),
    INDEX idx_createdBy (createdBy)
);


