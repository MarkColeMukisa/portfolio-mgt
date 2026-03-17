<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { CheckCircle2, FolderPlus, PencilLine, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ProjectCard from '@/components/portfolio/ProjectCard.vue';
import ProjectFormModal from '@/components/portfolio/ProjectFormModal.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard, home } from '@/routes';
import { destroy as destroyProject } from '@/routes/projects';
import type { BreadcrumbItem, PortfolioProject } from '@/types';

defineProps<{
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
                        <ProjectFormModal>
                            <template #trigger>
                                <Button
                                    class="rounded-full bg-[#14532D] px-5 text-white shadow-[0_16px_32px_-20px_rgba(21,83,45,0.55)] hover:bg-[#166534]"
                                >
                                    <FolderPlus class="mr-2 size-4" />
                                    Add project
                                </Button>
                            </template>
                        </ProjectFormModal>
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
                                Create and edit projects in modals so the
                                dashboard stays focused on your published work.
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

                    <div
                        class="rounded-[24px] border border-dashed border-[#86efac] bg-[#f0fdf4] p-5 dark:border-[#166534] dark:bg-[#14532d]/40"
                    >
                        <p
                            class="text-sm leading-7 text-[#166534]/80 dark:text-[#dcfce7]/75"
                        >
                            Launch the shared project modal to publish a new
                            screenshot or revise an existing card without
                            leaving this view.
                        </p>

                        <ProjectFormModal>
                            <template #trigger>
                                <Button
                                    class="mt-4 rounded-full bg-[linear-gradient(135deg,var(--portfolio-green)_0%,var(--portfolio-mint)_60%,var(--portfolio-yellow)_100%)] px-5 text-[#14532D] shadow-[0_16px_32px_-20px_rgba(34,197,94,0.6)] hover:opacity-95"
                                >
                                    <FolderPlus class="mr-2 size-4" />
                                    New project
                                </Button>
                            </template>
                        </ProjectFormModal>
                    </div>
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
                        Edit or delete any project you no longer want visible.
                        Replaced screenshots rotate their Cloudinary assets after
                        a successful save.
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
                            <div class="flex flex-col gap-3">
                                <ProjectFormModal
                                    v-if="project.can.update"
                                    :project="project"
                                >
                                    <template #trigger>
                                        <Button
                                            variant="outline"
                                            class="w-full rounded-full border-[#86efac] text-[#166534] hover:bg-[#f0fdf4] dark:border-[#166534] dark:text-[#dcfce7] dark:hover:bg-[#14532d]"
                                        >
                                            <PencilLine class="mr-2 size-4" />
                                            Edit project
                                        </Button>
                                    </template>
                                </ProjectFormModal>

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
                            </div>
                        </template>
                    </ProjectCard>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
