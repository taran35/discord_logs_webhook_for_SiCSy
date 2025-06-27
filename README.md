# 🧩 Système de récupération des logs sur Discord via Webhook pour SiCSy

---

## 📚 Sommaire

- [⚙️ Prérequis](#️-prérequis)
- [🚀 Installation](#-installation)

---

## ⚙️ Prérequis

- ☁️ un cloud [SiCSy](https://github.com/taran35/SiCSy) 
- 📄 un fichier CAcert (Obtenable [ici](https://curl.se/ca))
- 🤖 un webhook discord

## 🚀 Installation

1. télécharger le dossier `discord_logs_webhook_for_SiCSy`

2. le mettre dans le dossier **modules** de votre SiCSy

3. aller sur votre panel administrateur dans l'onglet **Gérer les modules**

4. configurer le module via le formulaire et le sauvegarder

5. ouvrir la page de setup en cliquant sur le bouton **Setup le module / Tester le webhook**

**Si tout se passe bien vous devriez avoir une page indiquant que le module a correctement été configuré et que le webhook fonctionne.**

> Si le Webhook n'est pas fonctionnel: le champ entré dans le paramètre `webhook_url` est incorrect

> Si il y a une erreur avec la configuration: le fichier **cloud_script.js** a du être modifié et empêche la configuration du module
