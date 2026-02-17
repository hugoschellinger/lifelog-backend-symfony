---
name: performance-rules
description: Applies systematic performance rules for frontend (lazy loading, image optimization, re-render minimization), backend (N+1 avoidance, Redis/SWR caching), and ElysiaJS/Bun (static typing, fast validation). Use when implementing or reviewing frontend/backend code, optimizing React/SwiftUI/PHP/Node apps, or when the user mentions performance, lazy loading, N+1, caching, or Elysia.
---

# Performance Rules

Applique systématiquement ces règles lors du développement ou de la revue de code.

## Frontend

### Lazy loading des composants lourds

- **React** : `React.lazy()` + `Suspense` pour les routes et composants non critiques.
- **SwiftUI** : `LazyVStack` / `LazyHStack` pour listes longues ; charger les vues coûteuses à la demande (onAppear, pagination).

```tsx
// React
const HeavyChart = React.lazy(() => import('./HeavyChart'));
<Suspense fallback={<Spinner />}><HeavyChart /></Suspense>
```

### Optimisation des images

- Privilégier **WebP** (avec fallback JPEG/PNG si besoin).
- Utiliser **srcset** et **sizes** pour le responsive ; **loading="lazy"** pour les images below-the-fold.
- Optionnel : composant `<picture>` avec sources multiples.

```html
<img src="hero.webp" srcset="hero-480.webp 480w, hero-960.webp 960w" sizes="(max-width: 600px) 480px, 960px" loading="lazy" alt="..." />
```

### Réduction des re-renders (React / SwiftUI)

- **React** : `React.memo` sur composants purs recevant des props stables ; `useMemo` / `useCallback` pour éviter recréation d’objets/fonctions entre renders ; éviter state/context trop large.
- **SwiftUI** : Préférer `@State` local et découpage en sous-vues ; éviter de mettre de gros `ObservableObject` à la racine si seule une partie change.

---

## Backend

### Éviter le N+1 (Eloquent / Doctrine)

- **Eloquent** : `with()` / `load()` pour eager load des relations ; vérifier les requêtes générées en dev.
- **Doctrine** : `JOIN FETCH` ou `EntityRepository` avec associations chargées en une requête ; éviter les accès en boucle à des relations non chargées.

```php
// Eloquent
User::with(['posts', 'posts.comments'])->get();

// Doctrine (DQL)
$qb->select('u', 'p')->from(User::class, 'u')->leftJoin('u.posts', 'p')->getQuery()->getResult();
```

### Caching des données peu changeantes

- **Redis** : pour sessions, compteurs, listes de référence (catégories, config). TTL adapté au type de donnée.
- **SWR / stale-while-revalidate** : côté API ou front, pour données lues souvent et mises à jour peu fréquentes ; réduire les appels redondants.

---

## ElysiaJS / Bun

### Exploiter la vitesse du moteur

- **Typage statique** : types TypeScript stricts partout (pas de `any` inutile) pour que Bun optimise au mieux.
- **Schémas de validation ultra-rapides** : utiliser le validateur intégré Elysia (ou schémas compatibles) pour body/query/params ; éviter des libs lourdes si un schéma léger suffit.
- Garder les handlers légers : logique métier dans des modules typés, pas de parsing/validation manuelle redondante.

```ts
// Elysia – typage + validation au plus près du handler
import { Elysia, t } from 'elysia';

app.post('/users', ({ body }) => body, {
  body: t.Object({ name: t.String(), email: t.String() }),
});
```

---

## Checklist rapide

- [ ] Composants lourds en lazy loading (React/SwiftUI)
- [ ] Images en WebP + srcset/sizes + loading="lazy" si pertinent
- [ ] Re-renders minimisés (memo, useMemo, useCallback, découpage SwiftUI)
- [ ] Requêtes backend sans N+1 (eager load / JOIN FETCH)
- [ ] Cache (Redis/SWR) pour données peu changeantes
- [ ] Elysia/Bun : typage strict + validation par schéma intégré
