#!/bin/bash

# Script de démarrage pour le développement
# Démarre le serveur Angular et configure l'environnement

echo "🚀 Démarrage de l'application ALRCF en mode développement..."

# Vérifier si Node.js est installé
if ! command -v node &> /dev/null; then
    echo "❌ Node.js n'est pas installé. Veuillez l'installer d'abord."
    exit 1
fi

# Vérifier si npm est installé
if ! command -v npm &> /dev/null; then
    echo "❌ npm n'est pas installé. Veuillez l'installer d'abord."
    exit 1
fi

# Installer les dépendances si nécessaire
if [ ! -d "node_modules" ]; then
    echo "📦 Installation des dépendances..."
    npm install
fi

# Vérifier si la base de données est configurée
echo "🔍 Vérification de la base de données..."
if [ ! -f "api/database/installed.flag" ]; then
    echo "📊 Configuration de la base de données..."
    echo "Veuillez exécuter: http://localhost/api/database/install.php"
    echo "Puis créez le fichier api/database/installed.flag"
fi

# Démarrer le serveur Angular
echo "🌐 Démarrage du serveur Angular..."
echo "L'application sera disponible sur: http://localhost:4200"
echo "L'API sera disponible sur: http://localhost/api/"
echo ""
echo "Appuyez sur Ctrl+C pour arrêter le serveur"
echo ""

npm start
