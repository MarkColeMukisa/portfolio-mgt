<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { useInitials } from '@/composables/useInitials';
import type { PortfolioProject } from '@/types';
import { computed } from 'vue';

const props = defineProps<{
    project: PortfolioProject;
}>();

const { getInitials } = useInitials();
const relativeTimeFormatter = new Intl.RelativeTimeFormat('en', {
    numeric: 'always',
});

const createdAtLabel = computed<string | null>(() => {
    if (!props.project.created_at) {
        return null;
    }

    return formatRelativeTime(props.project.created_at);
});

function formatRelativeTime(value: string): string {
    const createdAt = new Date(value);
    const elapsedSeconds = (createdAt.getTime() - Date.now()) / 1000;

    if (Math.abs(elapsedSeconds) < 60) {
        return 'just now';
    }

    const elapsedMinutes = truncateUnit(elapsedSeconds / 60);
    const elapsedHours = truncateUnit(elapsedSeconds / (60 * 60));
    const elapsedDays = truncateUnit(elapsedSeconds / (60 * 60 * 24));
    const elapsedWeeks = truncateUnit(elapsedSeconds / (60 * 60 * 24 * 7));
    const elapsedMonths = truncateUnit(elapsedSeconds / (60 * 60 * 24 * 30));
    const elapsedYears = truncateUnit(elapsedSeconds / (60 * 60 * 24 * 365));

    if (Math.abs(elapsedMinutes) < 60) {
        return relativeTimeFormatter.format(elapsedMinutes, 'minute');
    }

    if (Math.abs(elapsedHours) < 24) {
        return relativeTimeFormatter.format(elapsedHours, 'hour');
    }

    if (Math.abs(elapsedDays) < 7) {
        return relativeTimeFormatter.format(elapsedDays, 'day');
    }

    if (Math.abs(elapsedWeeks) < 4) {
        return relativeTimeFormatter.format(elapsedWeeks, 'week');
    }

    if (Math.abs(elapsedMonths) < 12) {
        return relativeTimeFormatter.format(elapsedMonths, 'month');
    }

    return relativeTimeFormatter.format(elapsedYears, 'year');
}

function truncateUnit(value: number): number {
    return value < 0 ? Math.ceil(value) : Math.floor(value);
}
</script>

<template>
    <article
        class="group overflow-hidden rounded-[28px] border border-[#bbf7d0] bg-white/90 shadow-[0_24px_80px_-36px_rgba(21,83,45,0.45)] ring-1 ring-white/60 backdrop-blur-sm transition duration-300 hover:-translate-y-1 hover:shadow-[0_32px_100px_-44px_rgba(21,83,45,0.55)] dark:border-[#166534] dark:bg-[#052e16]/85 dark:ring-[#166534]"
    >
        <div
            class="relative aspect-[16/10] overflow-hidden bg-[#dcfce7] dark:bg-[#14532d]"
        >
            <img
                :src="project.thumbnail_url"
                :srcset="`${project.responsive_images.mobile} 720w, ${project.responsive_images.tablet} 960w, ${project.responsive_images.desktop} 1280w`"
                sizes="(max-width: 767px) 100vw, (max-width: 1279px) 50vw, 25vw"
                :alt="project.title"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]"
                loading="lazy"
            />
            <div
                class="pointer-events-none absolute inset-0 bg-gradient-to-t from-[#14532d]/35 via-transparent to-transparent"
            />
        </div>

        <div class="space-y-4 p-5 sm:p-6">
            <div class="flex items-center gap-3">
                <Avatar
                    class="size-10 rounded-2xl border border-[#bbf7d0] bg-[#f0fdf4] dark:border-[#166534] dark:bg-[#14532d]"
                >
                    <AvatarImage
                        v-if="project.author.avatar"
                        :src="project.author.avatar"
                        :alt="project.author.name"
                    />
                    <AvatarFallback
                        class="rounded-2xl bg-transparent text-[#166534] dark:text-[#dcfce7]"
                    >
                        {{ getInitials(project.author.name) }}
                    </AvatarFallback>
                </Avatar>

                <div class="min-w-0">
                    <p
                        class="truncate text-sm font-semibold text-[#14532d] dark:text-[#dcfce7]"
                    >
                        {{ project.author.name }}
                    </p>
                    <p
                        class="text-xs tracking-[0.24em] text-[#16a34a] uppercase dark:text-[#86efac]"
                    >
                        Portfolio highlight
                    </p>
                </div>
            </div>

            <div class="space-y-2">
                <div class="flex items-start justify-between gap-3">
                    <h3
                        class="text-xl font-semibold tracking-tight text-[#14532d] dark:text-white"
                    >
                        {{ project.title }}
                    </h3>
                    <span
                        v-if="createdAtLabel"
                        class="shrink-0 rounded-full bg-[#dcfce7] px-2.5 py-1 text-[11px] font-medium text-[#166534] dark:bg-[#14532d] dark:text-[#86efac]"
                    >
                        {{ createdAtLabel }}
                    </span>
                </div>
                <p
                    class="line-clamp-3 text-sm leading-6 text-[#166534]/85 dark:text-[#dcfce7]/80"
                >
                    {{ project.description_preview }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <Badge
                    v-for="tag in project.tags"
                    :key="tag.id"
                    class="rounded-full border-0 bg-[#dcfce7] px-3 py-1 text-xs font-medium text-[#166534] dark:bg-[#166534] dark:text-[#dcfce7]"
                >
                    #{{ tag.name }}
                </Badge>
            </div>

            <slot name="action" />
        </div>
    </article>
</template>
