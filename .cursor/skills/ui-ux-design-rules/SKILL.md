---
name: ui-ux-design-rules
description: Applies senior UI/UX design rules: consistent spacing (4px/8px system), smooth micro-interactions (Framer Motion for React, native animations for SwiftUI), mobile-first interfaces, and clear feedback (skeletons, success/error states). Use when designing or implementing interfaces, components, or when the user mentions UI, UX, design system, spacing, animations, mobile-first, or loading states.
---

# UI/UX Design Rules

Agir comme un Designer UI/UX Senior : appliquer ces règles pour des interfaces cohérentes, fluides et accessibles.

## Cohérence des espacements

- Adopter un **système d’espacement** basé sur **4px** ou **8px** (multiples : 4, 8, 12, 16, 24, 32, 48, 64).
- Utiliser les mêmes tokens partout (paddings, margins, gaps). Éviter les valeurs arbitraires (ex. `13px`, `17px`).
- **Tailwind** : privilégier `p-4`, `gap-6`, `mt-8`, etc. Ou variables CSS : `--space-1` à `--space-16` mappées sur la base 4/8.

```css
/* Exemple de scale 8px */
--space-1: 4px;   --space-2: 8px;   --space-3: 12px;  --space-4: 16px;
--space-6: 24px;  --space-8: 32px;  --space-12: 48px; --space-16: 64px;
```

---

## Micro-interactions

- **React** : **Framer Motion** pour transitions et animations (hover, focus, entrée/sortie). Durées courtes (200–400 ms), easing cohérent (`easeOut`, `easeInOut`).
- **SwiftUI** : animations natives `.animation(.easeInOut(duration: 0.3), value: ...)`, `withAnimation { }`, transitions `.transition(.opacity.combined(with: .scale))` pour un rendu premium.
- Objectif : donner une sensation **premium** sans surcharger (pas d’animations inutiles).

```tsx
// React – Framer Motion
<motion.button whileHover={{ scale: 1.02 }} whileTap={{ scale: 0.98 }} transition={{ duration: 0.2 }}>
  Action
</motion.button>
```

```swift
// SwiftUI
Button("Action") { ... }
  .animation(.easeInOut(duration: 0.25), value: isPressed)
```

---

## Mobile-First

- Chaque interface **web** doit être **irréprochable sur mobile** : concevoir d’abord pour petit écran, puis étendre (breakpoints `sm`, `md`, `lg`).
- Touch targets ≥ 44px ; textes lisibles sans zoom ; pas de hover-only pour les actions critiques.
- Tester sur viewport étroit et avec DevTools device mode.

---

## Feedback utilisateur

- **États de chargement** : utiliser des **Skeletons** (placeholders animés) plutôt qu’un simple spinner pour les blocs de contenu ; garder le spinner pour les actions ponctuelles (submit).
- **Succès / erreur** : messages **clairs et visibles** (toast, banner ou inline) ; ton neutre et actionnable. Ex. « Enregistrement réussi » / « Erreur : vérifiez votre email et réessayez ».
- Ne jamais laisser l’utilisateur sans retour après une action (submit, navigation asynchrone).

```tsx
// Skeleton (ex. React)
<div className="animate-pulse flex gap-4">
  <div className="h-12 w-12 rounded-full bg-gray-200" />
  <div className="flex-1 space-y-2">
    <div className="h-4 bg-gray-200 rounded w-3/4" />
    <div className="h-4 bg-gray-200 rounded w-1/2" />
  </div>
</div>
```

---

## Checklist rapide

- [ ] Espacements en scale 4px ou 8px (paddings, margins, gaps)
- [ ] Micro-interactions fluides (Framer Motion / animations SwiftUI)
- [ ] Interface mobile-first et testée sur petit écran
- [ ] Skeletons pour chargements de contenu ; messages succès/erreur clairs
