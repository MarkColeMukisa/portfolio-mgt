<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { CheckCircle2, FolderPlus, Plus, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AlertError from '@/components/AlertError.vue';
import ProjectCard from '@/components/portfolio/ProjectCard.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard, home } from '@/routes';
import {
    destroy as destroyProject,
    store as storeProject,
} from '@/routes/projects';
import type { BreadcrumbItem, PortfolioProject } from '@/types';

const props = defineProps<{
    stats: {
        projects: number;
        tags: number;
    };
    projects: PortfolioProject[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];

const page = usePage<{
    flash?: {
        success?: string | null;
    };
}>();

const flashSuccess = computed(() => page.props.flash?.success);
const deletingProjectId = ref<number | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

const form = useForm<{
    title: string;
    description: string;
    tags: string;
    image: File | null;
}>({
    title: '',
    description: '',
    tags: '',
    image: null,
});

const parsedTags = computed(() => parseTags(form.tags));
const formErrors = computed(
    () => Object.values(form.errors).filter(Boolean) as string[],
);

function parseTags(value: string): string[] {
    return value
        .split(',')
        .map((tag) => tag.trim().toLowerCase())
        .filter(
            (tag, index, tags) => tag.length > 0 && tags.indexOf(tag) === index,
        );
}

function handleImageChange(event: Event): void {
    const target = event.target as HTMLInputElement;
    form.image = target.files?.[0] ?? null;
}

function submitProject(): void {
    form.transform((data) => ({
        ...data,
        tags: parseTags(data.tags),
    })).post(storeProject.url(), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset();

            if (fileInput.value) {
                fileInput.value.value = '';
            }
        },
    });
}

function removeProject(project: PortfolioProject): void {
    deletingProjectId.value = project.id;

    router.delete(destroyProject.url(project.id), {
        preserveScroll: true,
        onFinish: () => {
            deletingProjectId.value = null;
        },
    });
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-8 px-4 py-6 md:px-6">
            <section
                class="rounded-[32px] border border-[#bbf7d0] bg-[linear-gradient(135deg,var(--portfolio-green)_0%,var(--portfolio-mint)_60%,var(--portfolio-yellow)_100%)] p-8 text-[#14532D] shadow-[0_32px_90px_-48px_rgba(21,83,45,0.55)]"
            >
                <div
                    class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between"
                >
                    <div class="space-y-3">
                        <p
                            class="text-sm font-semibold tracking-[0.24em] text-[#166534] uppercase"
                        >
                            Creator dashboard
                        </p>
                        <h1
                            class="text-3xl font-semibold tracking-tight sm:text-4xl"
                        >
                            Upload a new portfolio screenshot
                        </h1>
                        <p
                            class="max-w-2xl text-sm leading-7 text-[#166534]/85 sm:text-base"
                        >
                            Add a title, a short description, and
                            comma-separated tags. Images upload straight to
                            Cloudinary, then publish into the public gallery.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <Link
                            :href="home()"
                            class="inline-flex items-center rounded-full border border-white/60 bg-white/70 px-4 py-2 text-sm font-medium text-[#14532D] backdrop-blur-sm transition hover:bg-white"
                        >
                            Preview public gallery
                        </Link>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
                <div
                    class="space-y-6 rounded-[28px] border border-[#bbf7d0] bg-white p-6 shadow-[0_24px_80px_-44px_rgba(21,83,45,0.4)] dark:border-[#166534] dark:bg-[#052e16]/85"
                >
                    <div class="flex items-start gap-3">
                        <div
                            class="rounded-2xl bg-[#dcfce7] p-3 text-[#15803d] dark:bg-[#14532d] dark:text-[#86efac]"
                        >
                            <FolderPlus class="size-5" />
                        </div>
                        <div>
                            <h2
                                class="text-xl font-semibold text-[#14532D] dark:text-white"
                            >
                                Add project
                            </h2>
                            <p
                                class="mt-2 text-sm leading-6 text-[#166534]/80 dark:text-[#dcfce7]/75"
                            >
                                Cards use a 16:10 thumbnail treatment for a
                                balanced Mac-style grid across mobile, tablet,
                                and desktop.
                            </p>
                        </div>
                    </div>

                    <Alert
                        v-if="flashSuccess"
                        class="border-[#86efac] bg-[#f0fdf4] text-[#166534] dark:border-[#166534] dark:bg-[#14532d] dark:text-[#dcfce7]"
                    >
                        <CheckCircle2 class="size-4" />
                        <AlertTitle>Saved</AlertTitle>
                        <AlertDescription>{{ flashSuccess }}</AlertDescription>
                    </Alert>

                    <AlertError v-if="formErrors.length" :errors="formErrors" />

                    <form class="space-y-5" @submit.prevent="submitProject">
                        <div class="grid gap-2">
                            <Label for="title">Project title</Label>
                            <Input
                                id="title"
                                v-model="form.title"
                                placeholder="Realtime analytics dashboard"
                            />
                            <InputError :message="form.errors.title" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="description">Description</Label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="5"
                                placeholder="Explain what the screenshot shows, the problem it solves, and who it serves."
                                class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                            />
                            <InputError :message="form.errors.description" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="tags">Tags</Label>
                            <Input
                                id="tags"
                                v-model="form.tags"
                                placeholder="vue, saas, analytics"
                            />
                            <div
                                v-if="parsedTags.length"
                                class="flex flex-wrap gap-2"
                            >
                                <Badge
                                    v-for="tag in parsedTags"
                                    :key="tag"
                                    class="rounded-full border-0 bg-[#dcfce7] px-3 py-1 text-xs font-medium text-[#166534] dark:bg-[#166534] dark:text-[#dcfce7]"
                                >
                                    #{{ tag }}
                                </Badge>
                            </div>
                            <InputError :message="form.errors.tags" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="image">Screenshot</Label>
                            <input
                                id="image"
                                ref="fileInput"
                                type="file"
                                accept="image/png,image/jpeg,image/webp"
                                class="h-11 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none file:mr-4 file:border-0 file:bg-transparent file:font-medium file:text-foreground"
                                @change="handleImageChange"
                            />
                            <p
                                class="text-xs leading-6 text-[#166534]/70 dark:text-[#dcfce7]/70"
                            >
                                PNG, JPG, or WebP up to 10 MB. Cloudinary stores
                                the original and generates the gallery thumbnail
                                automatically.
                            </p>
                            <InputError :message="form.errors.image" />
                        </div>

                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-full bg-[linear-gradient(135deg,var(--portfolio-green)_0%,var(--portfolio-mint)_60%,var(--portfolio-yellow)_100%)] px-5 text-[#14532D] shadow-[0_16px_32px_-20px_rgba(34,197,94,0.6)] hover:opacity-95"
                        >
                            <Plus class="mr-2 size-4" />
                            {{
                                form.processing
                                    ? 'Uploading...'
                                    : 'Publish project'
                            }}
                        </Button>
                    </form>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div
                        class="rounded-[28px] border border-[#bbf7d0] bg-white p-6 shadow-[0_24px_80px_-44px_rgba(21,83,45,0.35)] dark:border-[#166534] dark:bg-[#052e16]/85"
                    >
                        <p
                            class="text-sm font-semibold tracking-[0.2em] text-[#16a34a] uppercase dark:text-[#86efac]"
                        >
                            Published cards
                        </p>
                        <p
                            class="mt-4 text-4xl font-semibold text-[#14532D] dark:text-white"
                        >
                            {{ stats.projects }}
                        </p>
                        <p
                            class="mt-2 text-sm leading-6 text-[#166534]/80 dark:text-[#dcfce7]/75"
                        >
                            Screenshots currently visible in the public gallery.
                        </p>
                    </div>

                    <div
                        class="rounded-[28px] border border-[#bbf7d0] bg-white p-6 shadow-[0_24px_80px_-44px_rgba(21,83,45,0.35)] dark:border-[#166534] dark:bg-[#052e16]/85"
                    >
                        <p
                            class="text-sm font-semibold tracking-[0.2em] text-[#16a34a] uppercase dark:text-[#86efac]"
                        >
                            Tags in play
                        </p>
                        <p
                            class="mt-4 text-4xl font-semibold text-[#14532D] dark:text-white"
                        >
                            {{ stats.tags }}
                        </p>
                        <p
                            class="mt-2 text-sm leading-6 text-[#166534]/80 dark:text-[#dcfce7]/75"
                        >
                            Total tag assignments across your current portfolio.
                        </p>
                    </div>
                </div>
            </section>

            <section class="space-y-5">
                <div
                    class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
                >
                    <div>
                        <p
                            class="text-sm font-semibold tracking-[0.24em] text-[#16a34a] uppercase dark:text-[#86efac]"
                        >
                            Your work
                        </p>
                        <h2
                            class="mt-2 text-3xl font-semibold text-[#14532D] dark:text-white"
                        >
                            Manage published screenshots
                        </h2>
                    </div>
                    <p
                        class="max-w-xl text-sm leading-7 text-[#166534]/80 dark:text-[#dcfce7]/75"
                    >
                        Delete any project you no longer want visible.
                        Cloudinary assets are removed immediately when you do.
                    </p>
                </div>

                <div
                    v-if="projects.length === 0"
                    class="rounded-[28px] border border-dashed border-[#86efac] bg-white/70 px-8 py-16 text-center dark:border-[#166534] dark:bg-[#052e16]/65"
                >
                    <h3
                        class="text-2xl font-semibold text-[#14532D] dark:text-white"
                    >
                        No projects yet
                    </h3>
                    <p
                        class="mx-auto mt-3 max-w-xl text-sm leading-7 text-[#166534]/80 dark:text-[#dcfce7]/75"
                    >
                        Publish your first screenshot above and it will appear
                        here instantly.
                    </p>
                </div>

                <div v-else class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                    <ProjectCard
                        v-for="project in projects"
                        :key="project.id"
                        :project="project"
                    >
                        <template #action>
                            <Button
                                variant="outline"
                                class="w-full rounded-full border-[#fca5a5] text-[#b91c1c] hover:bg-[#fef2f2] dark:border-[#7f1d1d] dark:text-[#fecaca] dark:hover:bg-[#3f0d12]"
                                :disabled="deletingProjectId === project.id"
                                @click="removeProject(project)"
                            >
                                <Trash2 class="mr-2 size-4" />
                                {{
                                    deletingProjectId === project.id
                                        ? 'Deleting...'
                                        : 'Delete project'
                                }}
                            </Button>
                        </template>
                    </ProjectCard>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
