# Chiffres

Outil web minimaliste pour l’affichage et le suivi du tirage d’un loto.  
Conçu pour un écran dédié (ex. Raspberry Pi) en rafraîchissement automatique, et une interface administrateur pour gérer les numéros tirés.

## Fonctionnalités

- **Vue affichage** (`index.php`, par défaut) : grille des numéros 1–90, case blanche = non tiré, case bleue = tiré. Rafraîchissement automatique toutes les secondes. Aucune authentification. Idéal pour afficher sur un écran ou un Raspberry Pi sans intervention.
- **Vue administrateur** (`index.php?action=admin`) : protégée par un **secret** défini dans `config.php` (`ADMIN_SECRET`). Même grille avec possibilité de cliquer sur un numéro pour changer son état (tiré / non tiré) et un bouton « Réinitialiser » pour remettre tous les numéros à non tiré.
- Stockage **SQLite** (fichier local), pas de MySQL requis.

## Démarrage rapide avec Docker

Prérequis : [Docker](https://docs.docker.com/get-docker/) et [Docker Compose](https://docs.docker.com/compose/install/).

```bash
cd chiffres
cp config.php.exemple config.php   # puis éditer config.php et définir ADMIN_SECRET
docker compose up --build
```

- **Affichage écran** : http://localhost:8080/  
- **Administration** : http://localhost:8080/index.php?action=login — saisir le secret défini dans `config.php`.

Pour arrêter : `docker compose down`. Les données restent dans le volume `chiffres-data`.

## Installation manuelle (PHP + Apache / Raspberry Pi)

- PHP 8.x avec extensions **PDO** et **pdo_sqlite**
- Copier **`config.php.exemple`** en **`config.php`**, définir `ADMIN_SECRET` (et adapter les chemins si besoin).
- Pointer la racine web vers le répertoire **public/** (ou placer le contenu de `public/` à la racine et adapter les chemins).
- Le dossier **data/** à la racine du projet doit être accessible en écriture (création automatique de la base au premier chargement).

Sur un Raspberry Pi : ouvrir la vue affichage en plein écran (navigateur en kiosque) pour un affichage autonome sans manipulation.

## Structure du projet (MVC)

```
chiffres/
├── config.php.exemple    # Modèle de configuration (copier en config.php)
├── config.php            # Configuration réelle (ignoré par git)
├── init_db.php           # Création des tables SQLite
├── inc/
│   └── db.php            # Connexion PDO + chargement init_db
├── src/
│   ├── Controller/
│   │   └── LotoController.php
│   ├── Model/
│   │   └── DrawModel.php
│   ├── View/
│   │   ├── partials/     # header.php, footer.php
│   │   ├── auth/         # login.php
│   │   ├── display.php   # Grille lecture seule (écran)
│   │   └── admin.php     # Grille interactive (admin)
│   └── functions.php     # h(), is_authenticated(), require_admin(), truncate_to()
├── public/               # Racine web (document root)
│   ├── index.php         # Routage : affichage (public) + admin (auth par secret)
│   └── assets/
│       └── style.css
└── data/                 # Base SQLite (créée à l’exécution)
```

**Routage** (`public/index.php`) : paramètre `action` (GET ou POST). Sans authentification : `display` (défaut). Avec authentification : `login`, `login_check`, `logout`, `admin`, `toggle`, `reset`.

## Licence

MIT — voir [LICENSE](LICENSE).
