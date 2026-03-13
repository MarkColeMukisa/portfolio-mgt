<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowRight, Layers3, Sparkles, UploadCloud } from 'lucide-vue-next';
import { computed } from 'vue';
import ProjectCard from '@/components/portfolio/ProjectCard.vue';
import { Button } from '@/components/ui/button';
import { dashboard, home, login, register } from '@/routes';
import type {
    PortfolioProject,
    PortfolioTag,
    ScrollCollection,
    User,
} from '@/types';

const props = withDefaults(
    defineProps<{
        canRegister: boolean;
        heroStats: {
            projects: number;
            builders: number;
            tags: number;
        };
        featuredTags: PortfolioTag[];
        projects: ScrollCollection<PortfolioProject>;
    }>(),
    {
        canRegister: true,
    },
);

const page = usePage<{
    auth: {
        user: User | null;
    };
}>();

const authUser = computed(() => page.props.auth.user);

const statCards = computed(() => [
    {
        label: 'Live projects',
        value: props.heroStats.projects,
        icon: Layers3,
    },
    {
        label: 'Builders',
        value: props.heroStats.builders,
        icon: Sparkles,
    },
    {
        label: 'Cloudinary-ready shots',
        value: props.heroStats.tags,
        icon: UploadCloud,
    },
]);
</script>

<template>
    <Head title="Portfolio Gallery" />

    <div
        class="min-h-screen overflow-hidden bg-[#F0FDF4] text-[#14532D] dark:bg-[#04140b] dark:text-[#dcfce7]"
    >
        <div
            class="pointer-events-none absolute inset-x-0 top-0 h-[34rem] bg-[radial-gradient(circle_at_top_left,rgba(134,239,172,0.7),transparent_45%),radial-gradient(circle_at_top_right,rgba(253,224,71,0.45),transparent_32%)]"
        />
        <div
            class="pointer-events-none absolute top-24 left-1/2 h-[28rem] w-[28rem] -translate-x-1/2 rounded-full bg-[linear-gradient(135deg,var(--portfolio-green)_0%,var(--portfolio-mint)_60%,var(--portfolio-yellow)_100%)] opacity-20 blur-3xl"
        />

        <div class="relative">
            <header
                class="border-b border-[#bbf7d0]/70 bg-white/70 backdrop-blur-xl dark:border-[#166534] dark:bg-[#052e16]/80"
            >
                <div
                    class="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-5 lg:px-8"
                >
                    <div>
                        <p
                            class="text-xs font-semibold tracking-[0.3em] text-[#16a34a] uppercase dark:text-[#86efac]"
                        >
                            Portfolio Mgt
                        </p>
                        <h1
                            class="mt-2 text-lg font-semibold text-[#14532D] dark:text-white"
                        >
                            Screenshot-first portfolios for makers
                        </h1>
                    </div>

                    <nav class="flex items-center gap-3">
                        <Link
                            v-if="authUser"
                            :href="dashboard()"
                            prefetch
                            class="inline-flex items-center gap-2 rounded-full border border-[#16a34a]/20 bg-white px-4 py-2 text-sm font-medium text-[#166534] shadow-sm transition hover:border-[#16a34a]/40 hover:bg-[#f0fdf4] dark:border-[#15803d] dark:bg-[#14532d] dark:text-[#dcfce7]"
                        >
                            Dashboard
                            <ArrowRight class="size-4" />
                        </Link>
                        <template v-else>
                            <Link
                                :href="login()"
                                class="inline-flex items-center rounded-full px-4 py-2 text-sm font-medium text-[#166534] transition hover:bg-[#dcfce7] dark:text-[#dcfce7] dark:hover:bg-[#14532d]"
                            >
                                Log in
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="inline-flex items-center rounded-full bg-[linear-gradient(135deg,var(--portfolio-green)_0%,var(--portfolio-mint)_60%,var(--portfolio-yellow)_100%)] px-5 py-2 text-sm font-semibold text-[#14532D] shadow-[0_16px_32px_-18px_rgba(34,197,94,0.7)] transition hover:scale-[1.01]"
                            >
                                Create account
                            </Link>
                        </template>
                    </nav>
                </div>
            </header>

            <main
                class="mx-auto flex w-full max-w-7xl flex-col gap-14 px-6 py-12 lg:px-8 lg:py-16"
            >
                <section
                    class="grid gap-8 lg:grid-cols-[1.35fr_0.9fr] lg:items-end"
                >
                    <div
                        class="space-y-8 rounded-[36px] border border-[#bbf7d0] bg-white/80 p-8 shadow-[0_32px_120px_-48px_rgba(21,83,45,0.4)] backdrop-blur-sm lg:p-10 dark:border-[#166534] dark:bg-[#052e16]/80"
                    >
                        <div
                            class="inline-flex items-center gap-2 rounded-full bg-[#dcfce7] px-3 py-1 text-xs font-semibold tracking-[0.24em] text-[#15803d] uppercase dark:bg-[#14532d] dark:text-[#86efac]"
                        >
                            Cloudinary managed media
                        </div>

                        <div class="space-y-5">
                            <h2
                                class="max-w-3xl text-4xl font-semibold tracking-tight text-[#14532D] sm:text-5xl lg:text-6xl dark:text-white"
                            >
                                A clean, centered gallery for polished project
                                screenshots.
                            </h2>
                            <p
                                class="max-w-2xl text-base leading-8 text-[#166534]/85 sm:text-lg dark:text-[#dcfce7]/80"
                            >
                                Every upload is delivered from Cloudinary,
                                optimized for the device, and presented in a
                                balanced 16:10 card system with room for the
                                builder, tags, and context.
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <Link
                                v-if="authUser"
                                :href="dashboard()"
                                class="inline-flex items-center gap-2 rounded-full bg-[linear-gradient(135deg,var(--portfolio-green)_0%,var(--portfolio-mint)_60%,var(--portfolio-yellow)_100%)] px-5 py-3 text-sm font-semibold text-[#14532D] shadow-[0_16px_36px_-20px_rgba(34,197,94,0.65)] transition hover:translate-y-[-1px]"
                            >
                                Open creator dashboard
                                <ArrowRight class="size-4" />
                            </Link>
                            <Link
                                v-else
                                :href="login()"
                                class="inline-flex items-center gap-2 rounded-full border border-[#16a34a]/20 bg-white px-5 py-3 text-sm font-semibold text-[#166534] transition hover:border-[#16a34a]/40 hover:bg-[#f0fdf4] dark:border-[#15803d] dark:bg-[#14532d] dark:text-[#dcfce7]"
                            >
                                Start uploading
                            </Link>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-1">
                        <div
                            v-for="item in statCards"
                            :key="item.label"
                            class="rounded-[28px] border border-[#bbf7d0] bg-white/85 p-5 shadow-[0_18px_60px_-36px_rgba(21,83,45,0.45)] backdrop-blur-sm dark:border-[#166534] dark:bg-[#052e16]/82"
                        >
                            <component
                                :is="item.icon"
                                class="size-5 text-[#16a34a] dark:text-[#86efac]"
                            />
                            <p
                                class="mt-6 text-3xl font-semibold text-[#14532D] dark:text-white"
                            >
                                {{ item.value }}
                            </p>
                            <p
                                class="mt-2 text-sm text-[#166534]/80 dark:text-[#dcfce7]/75"
                            >
                                {{ item.label }}
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
                                Popular tags
                            </p>
                            <h2
                                class="mt-2 text-2xl font-semibold text-[#14532D] dark:text-white"
                            >
                                What teams are shipping right now
                            </h2>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <div
                            v-for="tag in featuredTags"
                            :key="tag.id"
                            class="rounded-full border border-[#bbf7d0] bg-white/80 px-4 py-2 text-sm font-medium text-[#166534] shadow-sm dark:border-[#166534] dark:bg-[#052e16]/70 dark:text-[#dcfce7]"
                        >
                            #{{ tag.name }}
                            <span
                                class="ml-2 text-xs text-[#16a34a] dark:text-[#86efac]"
                                >{{ tag.project_count }}</span
                            >
                        </div>
                    </div>
                </section>

                <section class="space-y-6">
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
                    >
                        <div>
                            <p
                                class="text-sm font-semibold tracking-[0.24em] text-[#16a34a] uppercase dark:text-[#86efac]"
                            >
                                Community gallery
                            </p>
                            <h2
                                class="mt-2 text-3xl font-semibold text-[#14532D] dark:text-white"
                            >
                                Responsive project screenshots with room to
                                breathe
                            </h2>
                        </div>
                        <p
                            class="max-w-xl text-sm leading-7 text-[#166534]/80 dark:text-[#dcfce7]/75"
                        >
                            Four columns on desktop, two on tablet, one on
                            mobile, with Cloudinary thumbnails keeping the
                            layout stable while images stream in.
                        </p>
                    </div>

                    <div
                        v-if="projects.data.length === 0"
                        class="rounded-[32px] border border-dashed border-[#86efac] bg-white/70 px-8 py-16 text-center shadow-sm dark:border-[#166534] dark:bg-[#052e16]/65"
                    >
                        <h3
                            class="text-2xl font-semibold text-[#14532D] dark:text-white"
                        >
                            No projects yet
                        </h3>
                        <p
                            class="mx-auto mt-3 max-w-xl text-sm leading-7 text-[#166534]/80 dark:text-[#dcfce7]/75"
                        >
                            The first uploads will appear here as soon as
                            creators publish their screenshots.
                        </p>
                    </div>

                    <div v-else class="space-y-10">
                        <div
                            class="mx-auto grid w-full max-w-[88rem] grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4"
                        >
                            <ProjectCard
                                v-for="project in projects.data"
                                :key="project.id"
                                :project="project"
                            />
                        </div>

                        <div
                            v-if="projects.next_page_url"
                            class="flex justify-center"
                        >
                            <Link
                                :href="
                                    home({
                                        query: {
                                            page: projects.current_page + 1,
                                        },
                                    })
                                "
                                preserve-scroll
                            >
                                <Button
                                    class="rounded-full bg-[linear-gradient(135deg,var(--portfolio-green)_0%,var(--portfolio-mint)_60%,var(--portfolio-yellow)_100%)] px-6 text-[#14532D] shadow-[0_16px_32px_-20px_rgba(34,197,94,0.6)] hover:opacity-95"
                                >
                                    Load more projects
                                </Button>
                            </Link>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>
</template>
