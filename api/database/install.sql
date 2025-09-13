-- Script d'installation simplifié pour ALRCF
-- Exécuter ce script dans votre base de données existante

-- Table des utilisateurs (adhérents et admins)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    firstName VARCHAR(100) NOT NULL,
    lastName VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    postalCode VARCHAR(10),
    role ENUM('admin', 'adherent') NOT NULL DEFAULT 'adherent',
    isActive BOOLEAN DEFAULT TRUE,
    memberNumber VARCHAR(20) UNIQUE,
    joinDate DATE,
    subscriptionStatus ENUM('active', 'expired', 'pending') DEFAULT 'pending',
    lastPaymentDate DATE,
    nextPaymentDate DATE,
    subscriptionAmount DECIMAL(10,2) DEFAULT 50.00,
    emergencyContact VARCHAR(100),
    emergencyPhone VARCHAR(20),
    lastLogin DATETIME,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des actualités
CREATE TABLE IF NOT EXISTS news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    imageUrl VARCHAR(500),
    author VARCHAR(100) NOT NULL,
    publishedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    isPublished BOOLEAN DEFAULT TRUE,
    category ENUM('general', 'event', 'announcement', 'urgent') DEFAULT 'general',
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des événements
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    startDate DATETIME NOT NULL,
    endDate DATETIME,
    location VARCHAR(255) NOT NULL,
    imageUrl VARCHAR(500),
    maxParticipants INT,
    currentParticipants INT DEFAULT 0,
    isPublished BOOLEAN DEFAULT TRUE,
    registrationRequired BOOLEAN DEFAULT FALSE,
    registrationDeadline DATETIME,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des messages de contact
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstName VARCHAR(100) NOT NULL,
    lastName VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied', 'closed') DEFAULT 'new',
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    repliedAt DATETIME,
    reply TEXT
);

-- Table des signalements
CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    adherentId INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category ENUM('incident', 'complaint', 'suggestion', 'other') NOT NULL,
    status ENUM('pending', 'in_progress', 'resolved', 'closed') DEFAULT 'pending',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    adminNotes TEXT,
    resolution TEXT,
    FOREIGN KEY (adherentId) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des cotisations
CREATE TABLE IF NOT EXISTS subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    adherentId INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    paymentDate DATE NOT NULL,
    period VARCHAR(10) NOT NULL,
    status ENUM('paid', 'pending', 'overdue') DEFAULT 'pending',
    paymentMethod ENUM('cash', 'check', 'transfer', 'card') NOT NULL,
    reference VARCHAR(100),
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (adherentId) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des inscriptions aux événements
CREATE TABLE IF NOT EXISTS event_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    eventId INT NOT NULL,
    adherentId INT NOT NULL,
    registeredAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('registered', 'cancelled', 'attended') DEFAULT 'registered',
    FOREIGN KEY (eventId) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (adherentId) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (eventId, adherentId)
);

-- Insertion d'un utilisateur admin par défaut
INSERT IGNORE INTO users (
    email, 
    password, 
    firstName, 
    lastName, 
    role, 
    isActive, 
    memberNumber, 
    joinDate, 
    subscriptionStatus,
    subscriptionAmount
) VALUES (
    'admin@alrcf.fr',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Administrateur',
    'ALRCF',
    'admin',
    TRUE,
    'ADMIN001',
    CURDATE(),
    'active',
    0.00
);

-- Insertion de quelques données d'exemple
INSERT IGNORE INTO news (title, content, author, category) VALUES
('Bienvenue sur notre nouveau site', 'Nous sommes ravis de vous présenter notre nouveau site web moderne et interactif.', 'Administrateur', 'general'),
('Assemblée générale 2024', 'L\'assemblée générale annuelle aura lieu le 15 mars 2024 à 18h00.', 'Administrateur', 'event'),
('Nouvelle adhésion', 'Nous accueillons 15 nouveaux adhérents ce mois-ci !', 'Administrateur', 'announcement');

INSERT IGNORE INTO events (title, description, startDate, location, maxParticipants, registrationRequired) VALUES
('Assemblée générale 2024', 'Assemblée générale annuelle de l\'association', '2024-03-15 18:00:00', 'Salle des fêtes - Mairie', 100, TRUE),
('Formation premiers secours', 'Formation aux gestes de premiers secours', '2024-04-10 09:00:00', 'Centre de formation', 20, TRUE),
('Sortie culturelle', 'Visite du musée d\'art moderne', '2024-05-20 14:00:00', 'Musée d\'art moderne', 30, TRUE);

-- Index pour améliorer les performances
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);
CREATE INDEX IF NOT EXISTS idx_users_memberNumber ON users(memberNumber);
CREATE INDEX IF NOT EXISTS idx_news_published ON news(isPublished, publishedAt);
CREATE INDEX IF NOT EXISTS idx_events_startDate ON events(startDate);
CREATE INDEX IF NOT EXISTS idx_events_published ON events(isPublished);
CREATE INDEX IF NOT EXISTS idx_contact_messages_status ON contact_messages(status);
CREATE INDEX IF NOT EXISTS idx_reports_adherent ON reports(adherentId);
CREATE INDEX IF NOT EXISTS idx_reports_status ON reports(status);
CREATE INDEX IF NOT EXISTS idx_subscriptions_adherent ON subscriptions(adherentId);
CREATE INDEX IF NOT EXISTS idx_subscriptions_period ON subscriptions(period);
