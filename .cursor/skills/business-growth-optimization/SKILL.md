---
name: business-growth-optimization
description: Optimizes code for business growth: technical SEO (semantic HTML, H1/H2, alt tags, OpenGraph, JSON-LD Schema.org for products/services), conversion (strategic CTA placement, loading speed to reduce bounce), and analytics readiness (DataLayer without polluting business logic). Use when building or reviewing landing pages, product pages, marketing sites, or when the user mentions SEO, conversion, CTA, OpenGraph, Schema.org, or analytics tracking.
---

# Business Growth Optimization

Optimiser le code pour la croissance business : SEO technique, conversion et analytics.

## SEO Technique

- **Structure HTML sémantique** : un seul **H1** par page (titre principal) ; **H2** pour sections, H3/H4 pour sous-sections. Hiérarchie logique, pas de saut (éviter H1 → H4).
- **Images** : attribut **alt** descriptif et pertinent pour chaque image (accessibilité + SEO). Pas d’alt vide sauf décoratif (`alt=""` avec `role="presentation"` si besoin).
- **OpenGraph** : générer les balises meta pour partage social (`og:title`, `og:description`, `og:image`, `og:url`, `og:type`). Optionnel : `twitter:card`, `twitter:image`.
- **JSON-LD (Schema.org)** : injecter un script `application/ld+json` avec le schéma adapté (Product, Service, Organization, Article, etc.) pour les produits/services. Données structurées pour les rich snippets.

```html
<!-- OpenGraph -->
<meta property="og:title" content="Titre de la page" />
<meta property="og:description" content="Description courte et engageante" />
<meta property="og:image" content="https://example.com/image.jpg" />
<meta property="og:url" content="https://example.com/page" />
<meta property="og:type" content="website" />

<!-- JSON-LD Product -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Nom du produit",
  "description": "Description",
  "image": "https://example.com/product.jpg"
}
</script>
```

---

## Conversion

- **CTA stratégiques** : placer les Call-to-Action aux points de décision (après valeur perçue, avant friction). Visibilité sans être intrusif ; libellé actionnable (« Réserver », « Télécharger le guide »).
- **Vitesse de chargement** : suggérer des optimisations pour réduire le temps de chargement (lazy load, WebP, critical CSS, réduire le blocage du main thread). Objectif : limiter le taux de rebond lié à la lenteur.

---

## Analytics

- **DataLayer** : préparer le terrain pour le tracking (GA4, GTM, etc.) en exposant les événements et données via un **DataLayer** (objet global `dataLayer` ou équivalent) poussé au bon moment (page_view, click_cta, etc.).
- **Séparation des responsabilités** : ne pas mélanger la logique métier avec le tracking. Centraliser les pushes DataLayer dans un module ou un hook dédié ; le code métier émet des « intentions » (ex. « CTA cliqué »), le module analytics traduit en push DataLayer.

```js
// Exemple : push DataLayer depuis un module dédié, pas depuis le composant métier
// Composant : onCtaClick() → analytics.trackCtaClick({ id, label })
// Module analytics : trackCtaClick(data) → window.dataLayer?.push({ event: 'cta_click', ...data })
```

---

## Checklist rapide

- [ ] H1 unique, H2/H3 hiérarchie sémantique ; alt sur toutes les images
- [ ] OpenGraph + JSON-LD (Product/Service/Article) sur les pages concernées
- [ ] CTA placés stratégiquement ; libellés actionnables
- [ ] Optimisations de chargement suggérées (vitesse)
- [ ] DataLayer prêt ; tracking isolé de la logique métier
