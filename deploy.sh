#!/bin/bash

# Script de déploiement pour ALRCF
echo "🚀 Déploiement de l'application ALRCF..."

# Build de l'application Angular
echo "📦 Construction de l'application Angular..."
npm run build

if [ $? -ne 0 ]; then
    echo "❌ Erreur lors de la construction de l'application"
    exit 1
fi

# Vérifier que le dossier dist existe
if [ ! -d "dist/alrcf-association" ]; then
    echo "❌ Le dossier dist/alrcf-association n'existe pas"
    exit 1
fi

echo "✅ Application construite avec succès"

# Instructions de déploiement
echo ""
echo "📋 Instructions de déploiement :"
echo "1. Copiez le contenu du dossier 'dist/alrcf-association/' vers la racine de votre hébergement"
echo "2. Copiez le dossier 'api/' vers la racine de votre hébergement"
echo "3. Copiez le fichier '.htaccess' vers la racine de votre hébergement"
echo "4. Copiez le fichier 'index.html' vers la racine de votre hébergement"
echo ""
echo "📁 Fichiers à déployer :"
echo "   - dist/alrcf-association/* → racine du site"
echo "   - api/ → racine du site"
echo "   - .htaccess → racine du site"
echo "   - index.html → racine du site"
echo ""
echo "🔧 Configuration requise :"
echo "   - PHP 8.0+ avec extensions PDO, OpenSSL, cURL"
echo "   - Base de données MySQL"
echo "   - Module mod_rewrite activé"
echo "   - Configuration email SMTP"
echo ""
echo "✅ Prêt pour le déploiement !"
