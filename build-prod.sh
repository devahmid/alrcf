#!/bin/bash

# Script de build pour la production
# Compile l'application Angular pour le déploiement

echo "🏗️  Build de l'application ALRCF pour la production..."

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

# Build de production
echo "🔨 Compilation de l'application..."
npm run build

# Vérifier si le build a réussi
if [ $? -eq 0 ]; then
    echo "✅ Build réussi!"
    echo "📁 Fichiers de production dans: dist/alrcf-association/"
    echo ""
    echo "📋 Instructions de déploiement:"
    echo "1. Uploadez le contenu de dist/alrcf-association/ sur votre serveur web"
    echo "2. Uploadez le dossier api/ sur votre serveur"
    echo "3. Configurez la base de données"
    echo "4. Modifiez l'URL de l'API dans src/environments/environment.prod.ts"
    echo ""
    echo "🌐 L'application est prête pour la production!"
else
    echo "❌ Erreur lors du build. Vérifiez les erreurs ci-dessus."
    exit 1
fi
