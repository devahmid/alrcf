#!/bin/bash

# Script de démarrage du serveur PHP pour le développement
# Démarre un serveur PHP local pour l'API

echo "🚀 Démarrage du serveur PHP pour l'API ALRCF..."

# Vérifier si PHP est installé
if ! command -v php &> /dev/null; then
    echo "❌ PHP n'est pas installé. Veuillez l'installer d'abord."
    exit 1
fi

# Vérifier la version de PHP
PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo "📋 Version PHP: $PHP_VERSION"

# Vérifier si l'extension PDO MySQL est disponible
if ! php -m | grep -q pdo_mysql; then
    echo "⚠️  Extension PDO MySQL non trouvée. L'API pourrait ne pas fonctionner correctement."
fi

# Démarrer le serveur PHP
echo "🌐 Démarrage du serveur sur http://localhost:8000"
echo "📁 Dossier de l'API: $(pwd)/api"
echo ""
echo "Appuyez sur Ctrl+C pour arrêter le serveur"
echo ""

cd api
php -S localhost:8000
