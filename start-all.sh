#!/bin/bash

# Script de démarrage complet pour le développement
# Démarre Angular, PHP et configure l'environnement

echo "🚀 Démarrage complet de l'application ALRCF..."

# Vérifier les prérequis
echo "🔍 Vérification des prérequis..."

if ! command -v node &> /dev/null; then
    echo "❌ Node.js n'est pas installé. Veuillez l'installer d'abord."
    exit 1
fi

if ! command -v php &> /dev/null; then
    echo "❌ PHP n'est pas installé. Veuillez l'installer d'abord."
    exit 1
fi

if ! command -v mysql &> /dev/null; then
    echo "⚠️  MySQL n'est pas installé. La base de données ne sera pas disponible."
fi

echo "✅ Prérequis vérifiés"

# Installer les dépendances si nécessaire
if [ ! -d "node_modules" ]; then
    echo "📦 Installation des dépendances Angular..."
    npm install
fi

# Vérifier la base de données
echo "🔍 Vérification de la base de données..."
if [ ! -f "api/database/installed.flag" ]; then
    echo "📊 Configuration de la base de données requise..."
    echo "Veuillez exécuter: http://localhost:8000/database/install.php"
    echo "Puis créez le fichier api/database/installed.flag"
fi

# Démarrer le serveur PHP en arrière-plan
echo "🌐 Démarrage du serveur PHP..."
cd api
php -S localhost:8000 > /dev/null 2>&1 &
PHP_PID=$!
cd ..

# Attendre que le serveur PHP soit prêt
sleep 2

# Démarrer Angular
echo "🌐 Démarrage du serveur Angular..."
echo ""
echo "📱 Application disponible sur: http://localhost:4200"
echo "🔧 API disponible sur: http://localhost:8000"
echo "📊 Base de données: http://localhost:8000/database/install.php"
echo ""
echo "Appuyez sur Ctrl+C pour arrêter tous les serveurs"
echo ""

# Fonction de nettoyage
cleanup() {
    echo ""
    echo "🛑 Arrêt des serveurs..."
    kill $PHP_PID 2>/dev/null
    echo "✅ Serveurs arrêtés"
    exit 0
}

# Capturer Ctrl+C
trap cleanup SIGINT

# Démarrer Angular
npm start

# Nettoyage en cas d'arrêt normal
cleanup
