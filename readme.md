# lazarefortune.com — V3 clean rebuild

> **V3 clean rebuild en cours**
>
> Cette branche (`v3-clean-rebuild`) repart sur une base applicative neuve.
> L’ancienne base de code métier (Domain, Http, Infrastructure legacy, Studio V2, CRUD admin, premium, badges, quiz, collaboration…) a été retirée.
>
> **Les utilisateurs seront conservés** via un script d’import dédié (à venir).
> **Nouvelle base de données** et nouvelles entités à définir dans les prochains commits.

## Vision V3

- **Symfony** au centre (domaine, sécurité, persistence, jobs)
- **Site public** SEO en Twig / Symfony UX
- **Studio** moderne avec React embarqué (Webpack Encore)
- **YouTube** comme hébergeur vidéo
- **Rôles** : `ROLE_USER`, `ROLE_ADMIN`, `ROLE_SUPER_ADMIN`

## Structure actuelle

```
src/
├── Shared/      # transversal (controllers socle, helpers futurs)
├── Auth/        # utilisateurs, sécurité
├── Video/       # contenu vidéo site
├── Playlist/    # formations / playlists
├── Tag/         # technologies / taxonomie
├── Progress/    # progression utilisateur
├── Comment/     # commentaires
├── Youtube/     # intégration YouTube
└── Studio/      # interface créateur

assets/
├── app.js
├── styles/
└── studio/
    ├── components/
    ├── hooks/
    └── api/
```

## Démarrage local

```bash
composer validate
composer install
npm install
npm run build
php bin/console about
php bin/console debug:router
./vendor/bin/phpunit
symfony server:start   # ou votre stack habituelle
```

## Licensing

Dual-licensed MPL 2.0 + Commercial — voir `LICENSE-MPL.txt` et `LICENSE-COMMERCIAL.txt`.
