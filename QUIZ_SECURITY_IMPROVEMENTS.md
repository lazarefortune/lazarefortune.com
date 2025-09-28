# Améliorations de sécurité des Quiz

## 🚨 Problèmes identifiés et corrigés

### 1. **Exposition des bonnes réponses côté client**
**Problème** : Les bonnes réponses (`isCorrect: true`) étaient envoyées au client, permettant la triche.

**Solution** :
- Suppression de `isCorrect` dans l'endpoint `/api/quiz/{id}`
- Création d'un endpoint sécurisé `/api/secure-quiz/start/{id}` pour démarrer les sessions
- Validation et calcul du score uniquement côté serveur

### 2. **Validation côté client uniquement**
**Problème** : Le serveur acceptait n'importe quel score sans validation.

**Solution** :
- Création du service `QuizValidationService` pour valider les réponses côté serveur
- Calcul du score uniquement côté serveur avec validation stricte
- Détection d'activité suspecte (temps de réponse trop courts, patterns suspects)

### 3. **Absence de protection CSRF**
**Problème** : Aucune protection contre les attaques CSRF.

**Solution** :
- Ajout de tokens CSRF pour toutes les soumissions
- Endpoint `/api/csrf-token` pour récupérer les tokens
- Validation CSRF dans `SecureQuizController`

### 4. **Absence de rate limiting**
**Problème** : Aucune protection contre le spam ou les attaques par déni de service.

**Solution** :
- Configuration du rate limiter (5 soumissions par minute)
- Limitation par adresse IP
- Blocage temporaire en cas de dépassement

### 5. **Sessions non sécurisées**
**Problème** : Les sessions n'avaient pas de validation d'expiration ni de vérification d'intégrité.

**Solution** :
- Création du service `QuizSessionManager`
- Tokens de session uniques et sécurisés
- Expiration automatique des sessions (2 heures)
- Validation de l'intégrité des sessions

### 6. **Logique de scoring non sécurisée**
**Problème** : La logique de scoring était basée sur des données client non fiables.

**Solution** :
- Validation stricte des réponses côté serveur
- Calcul du score uniquement côté serveur
- Détection de patterns de triche

## 🛡️ Nouvelles fonctionnalités de sécurité

### Services créés
- `QuizSessionManager` : Gestion sécurisée des sessions
- `QuizValidationService` : Validation et détection d'activité suspecte
- `SecureQuizController` : Endpoints sécurisés pour les quiz

### Endpoints sécurisés
- `POST /api/secure-quiz/start/{id}` : Démarrer une session de quiz
- `POST /api/secure-quiz/submit` : Soumettre les réponses (avec validation)
- `GET /api/secure-quiz/session/{token}` : Récupérer les informations de session
- `GET /api/csrf-token` : Récupérer un token CSRF

### Détection d'activité suspecte
- Temps de réponse trop courts (< 2 secondes par question)
- Scores parfaits avec temps très courts
- Patterns de temps identiques (possible script)
- Réduction automatique du score en cas d'activité suspecte

### Contraintes de temps
- Validation des contraintes de temps côté serveur
- Limite maximale de temps par quiz (1 heure par défaut)
- Tracking précis du temps passé sur chaque question

## 🔧 Configuration requise

### Rate Limiter
Ajouter dans `config/packages/rate_limiter.yaml` :
```yaml
framework:
    rate_limiter:
        quiz_submission_limiter:
            policy: 'token_bucket'
            limit: 5
            interval: '1 minute'
```

### Services
Les services sont automatiquement configurés dans `config/services.yaml`.

## 📱 Adaptations du composant React

### Nouvelles fonctionnalités
- Gestion des sessions sécurisées
- Récupération automatique des tokens CSRF
- Tracking du temps de réponse par question
- Soumission sécurisée des réponses
- Affichage des résultats après validation serveur

### Changements dans l'API
- Plus d'exposition des bonnes réponses côté client
- Validation et scoring uniquement côté serveur
- Gestion des erreurs de sécurité
- Feedback basé sur les données serveur

## 🚀 Utilisation

### Côté client
Le composant React a été adapté pour utiliser automatiquement les nouveaux endpoints sécurisés. Aucun changement requis dans l'utilisation.

### Côté serveur
Les anciens endpoints restent fonctionnels pour la compatibilité, mais il est recommandé d'utiliser les nouveaux endpoints sécurisés.

## 🔍 Monitoring et logs

### Logs de sécurité
- Activité suspecte détectée
- Tentatives de soumission avec données invalides
- Sessions expirées ou invalides

### Métriques
- Temps de réponse par question
- Patterns de soumission
- Taux de détection d'activité suspecte

## ⚠️ Notes importantes

1. **Compatibilité** : Les anciens endpoints restent fonctionnels
2. **Performance** : Les validations côté serveur ajoutent une légère latence
3. **Sécurité** : Toutes les données sensibles sont maintenant protégées
4. **Monitoring** : Surveillez les logs pour détecter les tentatives de triche

