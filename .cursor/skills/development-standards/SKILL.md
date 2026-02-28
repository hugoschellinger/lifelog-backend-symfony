---
name: development-standards
description: Enforces high-level development standards: strict TypeScript (no any), Zod validation, PHP 8.2+ typing, SOLID and composition-over-inheritance, JSDoc/PHPDoc for complex logic (explain why), and explicit error handling (no empty try-catch). Use when writing or reviewing TypeScript, PHP, or when the user mentions type safety, clean code, SOLID, documentation, or error handling.
---

# Development Standards

Respecter les standards de développement de haut niveau dans tout le code produit ou revu.

## Type Safety

- **TypeScript** : mode **strict** ; bannir `any` (préférer `unknown` + narrowing si nécessaire). Typage explicite des paramètres et retours des fonctions publiques.
- **Validation** : **Zod** pour valider les entrées (API, formulaires, env). Schémas réutilisables et inférence de types depuis les schémas.
- **PHP** : **8.2+** avec typage strict : types pour paramètres, retours, propriétés. Utiliser les types union et `readonly` quand pertinent.

```ts
// TypeScript + Zod
const UserSchema = z.object({ name: z.string(), email: z.email() });
type User = z.infer<typeof UserSchema>;
```

```php
// PHP 8.2+
public function __construct(
    private readonly string $id,
    private readonly \DateTimeImmutable $createdAt,
) {}
```

---

## Clean Code

- **SOLID** : une classe/module une responsabilité (S), extension par abstraction pas par modification (O), interfaces stables et implémentations interchangeables (L, I), dépendances injectées (D).
- **Composition > héritage** : privilégier des petits composants/services composés ; héritage uniquement pour vraie relation "is-a" et réutilisation de contrat.

---

## Documentation

- Documenter les **fonctions complexes** avec **JSDoc** (TS/JS) ou **PHPDoc** (PHP).
- Expliquer le **Pourquoi** (intention, contraintes, cas particuliers) plutôt que le **Comment** (le code suffit). Éviter les commentaires redondants du type "incrémente i".

```ts
/**
 * Normalise le montant pour la devise donnée (arrondi banquier, 2 décimales).
 * Requis pour conformité comptable avec la norme ISO 4217.
 */
function normalizeAmount(amount: number, currency: string): number { ... }
```

---

## Error Handling

- **Ne jamais laisser un try-catch vide** : au minimum logger l’erreur et/ou la renvoyer (wrap ou rethrow) avec contexte.
- Gestion **explicite et utile pour le debug** : message clair, code d’erreur si pertinent, conservation de la cause (cause chain). Éviter les messages génériques ("Something went wrong").

```ts
try {
  await sendEmail(payload);
} catch (err) {
  logger.error({ err, payload }, 'Email send failed');
  throw new AppError('EMAIL_SEND_FAILED', { cause: err });
}
```

```php
try {
    $this->processor->handle($command);
} catch (\Throwable $e) {
    $this->logger->error('Command failed', ['command' => $command::class, 'exception' => $e]);
    throw new DomainException('Command execution failed', 0, $e);
}
```

---

## Checklist rapide

- [ ] TypeScript strict, pas de `any` ; Zod pour la validation des entrées
- [ ] PHP 8.2+ avec types stricts sur paramètres, retours et propriétés
- [ ] SOLID respecté ; composition privilégiée à l’héritage
- [ ] JSDoc/PHPDoc sur la logique complexe (focus sur le Pourquoi)
- [ ] Aucun try-catch vide ; erreurs loggées et/ou propagées avec contexte
