---
name: inertia-vue-development
description: Laravel + Inertia + Vue Best Practices Guide

# Laravel + Inertia + Vue Best Practices Guide

---

## Stack Overview

This guide covers the **idiomatic patterns** for Laravel + Inertia.js v2 + Vue 3. It includes corrections and upgrades over the base skill defaults.

---

## 1. Project Setup

### Install Ziggy (Named Routes)

```bash
composer require tightenco/ziggy
```

**`resources/views/app.blade.php`:**
```html
<!DOCTYPE html>
<html>
<head>
    @routes <!-- Injects all Laravel named routes into JS -->
    @vite(['resources/js/app.js'])
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
```

**`resources/js/app.ts`:**
```js
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { ZiggyVue } from '../../vendor/tightenco/ziggy'

createInertiaApp({
    resolve: name =>
        resolvePageComponent(name, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el)
    },
})
```

---

## 2. Page Components

### Location
```
resources/js/Pages/   ← Always here (capital P)
```

### Structure Rules
- Always have a **single root element**
- Use `<script setup>` (Composition API)
- Define props with `defineProps`

```vue
<!-- resources/js/Pages/Users/Index.vue -->
<script setup>
defineProps({
    users: Array
})
</script>

<template>
    <div> <!--  Single root element — required! -->
        <h1>Users</h1>
        <ul>
            <li v-for="user in users" :key="user.id">
                {{ user.name }}
            </li>
        </ul>
    </div>
</template>
```

---

## 3. Navigation

###  Always Use `<Link>` + Ziggy (Not Hardcoded Strings)

```vue
<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<template>
    <nav>
        <!-- Named routes via Ziggy -->
        <Link :href="route('home')">Home</Link>
        <Link :href="route('users.index')">Users</Link>
        <Link :href="route('users.show', user.id)">{{ user.name }}</Link>
        <Link :href="route('users.edit', user.id)">Edit</Link>

        <!--  Avoid hardcoded strings -->
        <!-- <Link href="/users">Users</Link> -->
    </nav>
</template>
```

### Link with HTTP Method

```vue
<template>
    <!-- POST action (e.g. logout) -->
    <Link :href="route('logout')" method="post" as="button">
        Logout
    </Link>

    <!-- DELETE action -->
    <Link :href="route('users.destroy', user.id)" method="delete" as="button">
        Delete
    </Link>
</template>
```

### Prefetching (Performance)

```vue
<template>
    <!-- Prefetch on hover for faster perceived navigation -->
    <Link :href="route('users.index')" prefetch>
        Users
    </Link>
</template>
```

### Programmatic Navigation

Use `router` only when navigating **without** a form submit (e.g. conditionally, after confirmation, or on events). For form submissions, **let Laravel redirect instead**.

```vue
<script setup>
import { router } from '@inertiajs/vue3'

// Navigate after confirmation
function deleteUser(id) {
    if (confirm('Are you sure?')) {
        router.delete(route('users.destroy', id))
        // Controller then handles redirect()->route('users.index')
    }
}

// Conditional navigation
function handleRole(user) {
    if (user.role === 'admin') {
        router.visit(route('admin.dashboard'))
    } else {
        router.visit(route('dashboard'))
    }
}
</script>
```

**`router` method cheatsheet:**
```js
router.visit(route('users.index'))                    // GET
router.get(route('users.index'))                      // GET explicit
router.post(route('users.store'), { name: 'John' })   // POST with data
router.put(route('users.update', id), data)           // PUT
router.patch(route('users.update', id), data)         // PATCH
router.delete(route('users.destroy', id))             // DELETE
```

---

## 4. Forms

### When to Use What

| Scenario | Use |
|---|---|
| Simple CRUD form | `<Form>` component |
| Needs `transform()` on data | `useForm` composable |
| Needs `reset('field')` on specific fields | `useForm` composable |
| File uploads with progress bar | `<Form>` with `progress` slot prop |
| Multi-step or complex logic | `useForm` composable |

---

## 4.1 Async Queue Workflows (Upload Now, Process Later)

Use this pattern when backend processing can exceed normal request time (for example AI analysis):

1. Submit with `useForm` and return quickly from the backend with an identifier and initial state (for example `pending`).
2. Poll a status endpoint (`GET /resource/{id}/status`) every 2-3 seconds.
3. Render explicit states in UI: `pending`, `processing`, `completed`, `failed`.
4. Stop polling on terminal states and on component unmount.

```vue
<script setup lang="ts">
import { onBeforeUnmount, ref } from 'vue'

const status = ref<'pending' | 'processing' | 'completed' | 'failed'>('pending')
const poller = ref<number | null>(null)

const stopPolling = () => {
    if (poller.value !== null) {
        window.clearInterval(poller.value)
        poller.value = null
    }
}

const startPolling = (id: number) => {
    stopPolling()
    poller.value = window.setInterval(async () => {
        const response = await fetch(route('client.diagnose.status', id), {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        })

        if (!response.ok) {
            return
        }

        const payload = await response.json()
        status.value = payload.status

        if (status.value === 'completed' || status.value === 'failed') {
            stopPolling()
        }
    }, 2000)
}

onBeforeUnmount(() => stopPolling())
</script>
```

---

### `<Form>` Component (Simple CRUD — Recommended)

```vue
<script setup>
import { Form } from '@inertiajs/vue3'
</script>

<template>
    <Form :action="route('users.store')" method="post"
        #default="{ errors, processing, wasSuccessful }">

        <input type="text" name="name" />
        <div v-if="errors.name">{{ errors.name }}</div>

        <input type="email" name="email" />
        <div v-if="errors.email">{{ errors.email }}</div>

        <button type="submit" :disabled="processing">
            {{ processing ? 'Creating...' : 'Create User' }}
        </button>

        <div v-if="wasSuccessful">User created!</div>
    </Form>
</template>
```

### `<Form>` with Auto Reset

```vue
<template>
    <Form
        :action="route('users.store')"
        method="post"
        reset-on-success
        set-defaults-on-success
        #default="{ errors, processing, wasSuccessful }"
    >
        <input type="text" name="name" />
        <div v-if="errors.name">{{ errors.name }}</div>

        <button type="submit" :disabled="processing">Submit</button>
        <div v-if="wasSuccessful">Saved!</div>
    </Form>
</template>
```

**All `<Form>` reset props:**

| Prop | What It Does |
|---|---|
| `reset-on-error` | Clears form data when request fails |
| `reset-on-success` | Clears form after successful submit |
| `set-defaults-on-success` | Updates default values after success |

### Full `<Form>` Slot Props Reference

```vue
<Form :action="route('users.store')" method="post"
    #default="{
        errors,              // Validation errors from Laravel
        hasErrors,           // Boolean — any errors exist?
        processing,          // Boolean — request in flight?
        progress,            // File upload progress percentage
        wasSuccessful,       // True after success
        recentlySuccessful,  // True for a few seconds after success
        isDirty,             // Form data changed from defaults?
        reset,               // Reset to defaults
        submit               // Manually trigger submit
    }"
>
```

---

### `useForm` Composable (Complex Forms)

```vue
<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
    name: '',
    email: '',
    password: '',
})

function submit() {
    form
        .transform(data => ({
            ...data,
            name: data.name.trim(), // Transform before sending
        }))
        .post(route('users.store'), {
            onSuccess: () => form.reset('password'), // Reset specific field
            onError: () => console.log(form.errors),
        })
}

// For update
function update(id) {
    form.put(route('users.update', id), {
        onSuccess: () => router.visit(route('users.index')),
        preserveScroll: true,
    })
}
</script>

<template>
    <form @submit.prevent="submit"> <!-- Always prevent default -->
        <input type="text" v-model="form.name" />
        <div v-if="form.errors.name">{{ form.errors.name }}</div>

        <input type="email" v-model="form.email" />
        <div v-if="form.errors.email">{{ form.errors.email }}</div>

        <input type="password" v-model="form.password" />
        <div v-if="form.errors.password">{{ form.errors.password }}</div>

        <button type="submit" :disabled="form.processing">
            Create User
        </button>
    </form>
</template>
```

---

## 5. Laravel Controller Patterns

### Let Laravel Handle Redirects After Form Actions

```php
// Store
public function store(StoreUserRequest $request)
{
    User::create($request->validated());
    return redirect()->route('users.index')->with('success', 'User created!');
}

//  Update
public function update(UpdateUserRequest $request, User $user)
{
    $user->update($request->validated());
    return redirect()->route('users.index')->with('success', 'User updated!');
}

//  Destroy
public function destroy(User $user)
{
    $user->delete();
    return redirect()->route('users.index')->with('success', 'User deleted!');
}

//  Return back with errors (handled automatically by Inertia)
public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
    ]);
    // Inertia automatically catches validation errors and sends back to form
}
```

---

## 6. Inertia v2 Features

### Deferred Props

Load heavy data **after** the initial page render. Always add a loading skeleton.

**Controller:**
```php
use Inertia\Inertia;

public function index()
{
    return Inertia::render('Users/Index', [
        'users' => Inertia::defer(fn () => User::paginate(20)),
    ]);
}
```

**Vue Page — Always handle `undefined` state:**
```vue
<script setup>
defineProps({ users: Array })
</script>

<template>
    <div>
        <h1>Users</h1>

        <!-- Always check for undefined before data loads -->
        <div v-if="!users" class="animate-pulse">
            <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
            <div class="h-4 bg-gray-200 rounded w-1/2"></div>
        </div>

        <ul v-else>
            <li v-for="user in users" :key="user.id">
                {{ user.name }}
            </li>
        </ul>
    </div>
</template>
```

---

### Polling

Auto-refresh data at intervals. Use `only` to avoid reloading the entire page.

```vue
<script setup>
import { usePoll } from '@inertiajs/vue3'

defineProps({ stats: Object })

//  Simple polling — refresh every 5 seconds
usePoll(5000)
</script>
```

```vue
<script setup>
import { usePoll } from '@inertiajs/vue3'

defineProps({ stats: Object })

//  Advanced — partial reload + manual control
const { start, stop } = usePoll(5000, {
    only: ['stats'],    // ← Only reload 'stats' prop, not entire page
    onStart() { console.log('Polling started') },
    onFinish() { console.log('Polling finished') },
}, {
    autoStart: false,   // Start manually
    keepAlive: true,    // Don't throttle when browser tab is inactive
})
</script>

<template>
    <div>
        <div>Active Users: {{ stats.activeUsers }}</div>
        <button @click="start">Start</button>
        <button @click="stop">Stop</button>
    </div>
</template>
```

**`usePoll` options:**

| Option | Default | Description |
|---|---|---|
| `only` | all props | Partial reload — only fetch specific props |
| `autoStart` | `true` | Set `false` to start manually |
| `keepAlive` | `false` | Set `true` to keep polling on inactive tab |

---

### Infinite Scroll with `WhenVisible`

```vue
<script setup>
import { WhenVisible } from '@inertiajs/vue3'

defineProps({ users: Object }) // Paginated resource
</script>

<template>
    <div>
        <div v-for="user in users.data" :key="user.id">
            {{ user.name }}
        </div>

        <!-- Only renders trigger when next page exists -->
        <WhenVisible
            v-if="users.next_page_url"
            data="users"
            :params="{ page: users.current_page + 1 }"
        >
            <template #fallback>
                <div class="animate-pulse">Loading more...</div>
            </template>
        </WhenVisible>
    </div>
</template>
```

---

## 7. Common Pitfalls & Fixes

|  Mistake |  Fix |
|---|---|
| Using `<a href="/users">` | Use `<Link :href="route('users.index')">` |
| Hardcoded URL strings in `<Link>` | Use Ziggy `route()` helper |
| No single root element in template | Wrap in `<div>` |
| No loading state for deferred props | Always check `v-if="!prop"` first |
| `<form>` without `@submit.prevent` | Use `<Form>` component or `@submit.prevent` |
| Polling entire page | Use `only: ['propName']` in `usePoll` |
| Manually navigating after form submit | Let Laravel `redirect()->route()` handle it |
| Using `router.visit()` after every form | Only use for non-form navigations |

---

## 8. Quick Decision Reference

| Situation | Best Approach |
|---|---|
| Nav links, menus | `<Link :href="route(...)">` |
| Logout / destructive button | `<Link method="post/delete" as="button">` |
| Simple CRUD form | `<Form>` component + `reset-on-success` |
| Complex form (transforms, resets) | `useForm` composable |
| Navigate after event/condition | `router.visit(route(...))` |
| Redirect after save/delete | `redirect()->route()` in Laravel controller |
| Heavy data (load after render) | `Inertia::defer()` + loading skeleton |
| Live/real-time data | `usePoll()` with `only: ['prop']` |
| Infinite scroll | `<WhenVisible>` component |
| External URL | Regular `<a href="">` tag |
