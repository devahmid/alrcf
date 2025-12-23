-- Ajouter la colonne videoUrl à la table news
ALTER TABLE news ADD COLUMN IF NOT EXISTS videoUrl VARCHAR(500) NULL AFTER imageUrl;

