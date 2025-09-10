# Guide de contribution – Inazaoui

Merci de votre intérêt pour contribuer au projet **Inazaoui**, une application Symfony de gestion d'albums et médias. Ce guide présente les règles à suivre pour garantir une contribution claire, structurée et cohérente.

---

## Soumettre un bug ou un problème

Avant d’ouvrir un ticket, vérifiez s’il n’a pas déjà été signalé.

1. Créez un ticket GitHub dans l’onglet "Issues".
2. Fournissez un maximum d’informations :
   - Étapes pour reproduire le bug
   - Comportement attendu
   - Capture d’écran ou logs si utile
3. Spécifiez votre environnement (OS, PHP, Symfony, navigateur...)

---

## Proposer une fonctionnalité

1. Ouvrez une nouvelle *issue* en sélectionnant "Enhancement" dans les Labels.
2. Décrivez :
   - Le problème que vous essayez de résoudre
   - La solution proposée
   - L’impact potentiel

---

## Contribuer au code

### Étapes générales

```bash
# 1. Forkez ce dépôt
# 2. Clonez votre fork
git clone https://github.com/votre-utilisateur/Inazaoui.git
cd Inazaoui

# 3. Créez une branche dédiée à votre modification
git checkout -b feat/nom-fonctionnalite
```

---

## Testez vos modifications

Avant de push votre code, assurez-vous que les tests passent :
```bash 
php bin/phpunit
```

Toute nouvelle fonctionnalité ou correction doit être couverte par des **tests pertinents**.

---

## Conventions de nommage

### Branches Git

Utilisez les préfixes suivants pour nommer vos branches :

- Fonctionnalité : `feat/ajout-commentaires`
- Correction : `fix/erreur-authentification`
- Refactoring : `refactor/optimisation-queries`
- Documentation : `docs/update-readme`
- Maintenance : `chore/maj-dependances`

### Commits

Suivez ces exemples pour vos messages de commit :
- `docs(readme): ajout des instructions de backup`

---

## Validation avant Pull Request

Avant de proposer votre modification, assurez-vous de :

- Respecter les standards **PSR-12**
- Lancer les tests : `composer test`
- Vérifier votre code avec des outils comme `phpstan`
- Ajouter des tests pour toute nouvelle logique
- Mettre à jour la documentation si nécessaire

---

## Ouvrir une pull request


1. Poussez votre branche :
```bash 
git push origin feat/ma-fonctionnalite
```

2. Ouvrez une Pull Request sur GitHub :
- Décrivez les changements effectués
- Liez l’issue concernée (ex: `Closes #12`)
- Attendez une revue de code avant fusion

---

## Contribuer à la documentation

Vous pouvez aussi contribuer à la documentation du projet : README, fichiers Markdown, instructions d'installation ou tout contenu utile à la compréhension du code.

---

## Merci !

Merci de contribuer à ce projet ! Votre aide est précieuse pour l'améliorer et le rendre plus robuste pour tous les utilisateurs.