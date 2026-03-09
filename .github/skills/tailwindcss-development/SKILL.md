---
name: tailwindcss-development
description: 'Styles applications using Tailwind CSS v4 utilities. Activates when adding styles, restyling components, working with gradients, spacing, layout, flex, grid, responsive design, dark mode, colors, typography, or borders; or when the user mentions CSS, styling, classes, Tailwind, restyle, hero section, cards, buttons, or any visual/UI changes.'
license: MIT
metadata:
    author: laravel
---

# Tailwind CSS Development

## When to Apply

Activate this skill when:

- Adding styles to components or pages
- Working with responsive design
- Implementing dark mode
- Extracting repeated patterns into components
- Debugging spacing or layout issues

## Documentation

Use `search-docs` for detailed Tailwind CSS v4 patterns and documentation.

## Basic Usage

- Use Tailwind CSS classes to style HTML. Check and follow existing Tailwind conventions in the project before introducing new patterns.
- Offer to extract repeated patterns into components that match the project's conventions (e.g., Blade, JSX, Vue).
- Consider class placement, order, priority, and defaults. Remove redundant classes, add classes to parent or child elements carefully to reduce repetition, and group elements logically.

## Tailwind CSS v4 Specifics

- Always use Tailwind CSS v4 and avoid deprecated utilities.
- `corePlugins` is not supported in Tailwind v4.

### CSS-First Configuration

In Tailwind v4, configuration is CSS-first using the `@theme` directive — no separate `tailwind.config.js` file is needed:

<!-- CSS-First Config -->

```css
@theme {
    --color-brand: oklch(0.72 0.11 178);
}
```

### Import Syntax

In Tailwind v4, import Tailwind with a regular CSS `@import` statement instead of the `@tailwind` directives used in v3:

<!-- v4 Import Syntax -->

```diff
- @tailwind base;
- @tailwind components;
- @tailwind utilities;
+ @import "tailwindcss";
```

### Replaced Utilities

Tailwind v4 removed deprecated utilities. Use the replacements shown below. Opacity values remain numeric.

| Deprecated             | Replacement          |
| ---------------------- | -------------------- |
| bg-opacity-\*          | bg-black/\*          |
| text-opacity-\*        | text-black/\*        |
| border-opacity-\*      | border-black/\*      |
| divide-opacity-\*      | divide-black/\*      |
| ring-opacity-\*        | ring-black/\*        |
| placeholder-opacity-\* | placeholder-black/\* |
| flex-shrink-\*         | shrink-\*            |
| flex-grow-\*           | grow-\*              |
| overflow-ellipsis      | text-ellipsis        |
| decoration-slice       | box-decoration-slice |
| decoration-clone       | box-decoration-clone |

## Spacing

Use `gap` utilities instead of margins for spacing between siblings:

<!-- Gap Utilities -->

```html
<div class="flex gap-8">
    <div>Item 1</div>
    <div>Item 2</div>
</div>
```

## Dark Mode

If existing pages and components support dark mode, new pages and components must support it the same way, typically using the `dark:` variant:

<!-- Dark Mode -->

```html
<div class="bg-white text-gray-900 dark:bg-gray-900 dark:text-white">
    Content adapts to color scheme
</div>
```

## Common Patterns

### Flexbox Layout

<!-- Flexbox Layout -->

```html
<div class="flex items-center justify-between gap-4">
    <div>Left content</div>
    <div>Right content</div>
</div>
```

### Grid Layout

<!-- Grid Layout -->

```html
<div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
    <div>Card 1</div>
    <div>Card 2</div>
    <div>Card 3</div>
</div>
```

### Lucide Icons

Use Lucide for icons in Vue components. Prefer importing from `lucide-vue-next` and size icons with Tailwind width/height utilities. Keep icons aligned with text using flex and `items-center`.

If `lucide-vue-next` is not listed in `package.json`, add it before using icons.

<!-- Lucide Icons -->

```vue
<script setup lang="ts">
import { Leaf } from "lucide-vue-next";
</script>

<template>
    <span class="inline-flex items-center gap-2 text-emerald-600">
        <Leaf class="h-4 w-4" />
        Healthy
    </span>
</template>
```

## Common Pitfalls

- Using deprecated v3 utilities (bg-opacity-_, flex-shrink-_, etc.)
- Using `@tailwind` directives instead of `@import "tailwindcss"`
- Trying to use `tailwind.config.js` instead of CSS `@theme` directive
- Using margins for spacing between siblings instead of gap utilities
- Forgetting to add dark mode variants when the project uses dark mode
- Using inline SVGs when Lucide icons are already available and consistent

## Project Theme Guidelines (PlantGuard AI)

When creating or restyling pages across the PlantGuard application, adhere to the following theme:

### Colors

- **Primary Brand (Green)**: Use `emerald-600` for primary buttons, highlighted text, and active icons (`#059669`).
- **Brand Highlights**: Use `emerald-500` for gradient or softer text highlights (`#10b981`).
- **Backgrounds**: Use `#f8faf9` (or `bg-slate-50`) for off-white page backgrounds to keep a clean, organic feel.
- **Text (Headings)**: Use `slate-900` for primary headings (e.g., `text-slate-900`).
- **Text (Body)**: Use `slate-600` for paragraph and secondary text (e.g., `text-slate-600`).
- **Borders & Soft Backgrounds**: Use `emerald-100/50` to `emerald-200` for badges and soft UI borders.

### Typography

- Add `tracking-tight` to large headings.
- Use `font-extrabold` for large main Hero titles.
- Use `font-sans` globally.

### UI Components

- **Buttons**: Rounded-xl corners (`rounded-xl`), font-semibold, and shadow (e.g. `shadow-md hover:shadow-lg`).
    - _Primary Button_: `bg-emerald-600 hover:bg-emerald-700 text-white`
    - _Secondary Button_: `bg-white border border-slate-200 text-slate-700 hover:bg-slate-50`
- **Badges**: Pill-shaped (`rounded-full`), inline-flex, with soft green backgrounds (`bg-emerald-100/50 text-emerald-700`).
- **Cards/Floating Elements**: Use `shadow-xl`, `bg-white`, `border border-slate-100`, and `rounded-2xl` styling to match the modern application look.
