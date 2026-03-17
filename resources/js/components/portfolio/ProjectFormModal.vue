<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { CheckCircle2, PencilLine, Plus } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import AlertError from '@/components/AlertError.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { store as storeProject, update as updateProject } from '@/routes/projects';
import type { PortfolioProject } from '@/types';

const props = defineProps<{
    project?: PortfolioProject | null;
}>();

const open = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);

const form = useForm<{
    title: string;
    description: string;
    tags: string;
    image: File | null;
    _method?: 'patch';
}>({
    title: '',
    description: '',
    tags: '',
    image: null,
});

const isEditing = computed(
    () => props.project !== null && props.project !== undefined,
);
const heading = computed(() => (isEditing.value ? 'Edit project' : 'Create project'));
const description = computed(() =>
    isEditing.value
        ? 'Update the title, description, tags, or replace the screenshot for this card.'
        : 'Add a title, short description, tags, and screenshot to publish a new card.',
);
const submitLabel = computed(() => {
    if (form.processing) {
        return isEditing.value ? 'Saving...' : 'Uploading...';
    }

    return isEditing.value ? 'Save changes' : 'Publish project';
});
const parsedTags = computed(() => parseTags(form.tags));
const formErrors = computed(
    () => Object.values(form.errors).filter(Boolean) as string[],
);

watch(open, (isOpen) => {
    if (isOpen) {
        hydrateForm();

        return;
    }

    resetTransientState();
});

function hydrateForm(): void {
    form.clearErrors();
    form.defaults(defaultValues());
    form.reset();
    form.image = null;

    if (fileInput.value) {
        fileInput.value.value = '';
    }
}

function defaultValues(): {
    title: string;
    description: string;
    tags: string;
    image: null;
} {
    if (props.project) {
        return {
            title: props.project.title,
            description: props.project.description,
            tags: props.project.tags.map((tag) => tag.name).join(', '),
            image: null,
        };
    }

    return {
        title: '',
        description: '',
        tags: '',
        image: null,
    };
}

function resetTransientState(): void {
    form.clearErrors();
    form.reset();
    form.image = null;

    if (fileInput.value) {
        fileInput.value.value = '';
    }
}

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
    const route =
        isEditing.value && props.project
            ? updateProject.url(props.project.id)
            : storeProject.url();

    form.transform((data) => ({
        ...data,
        tags: parseTags(data.tags),
        ...(isEditing.value ? { _method: 'patch' as const } : {}),
    })).post(route, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            open.value = false;
        },
    });
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogTrigger as-child>
            <slot name="trigger">
                <Button
                    class="rounded-full bg-[linear-gradient(135deg,var(--portfolio-green)_0%,var(--portfolio-mint)_60%,var(--portfolio-yellow)_100%)] px-5 text-[#14532D] shadow-[0_16px_32px_-20px_rgba(34,197,94,0.6)] hover:opacity-95"
                >
                    <Plus class="mr-2 size-4" />
                    Create project
                </Button>
            </slot>
        </DialogTrigger>

        <DialogContent
            class="border-[#bbf7d0] bg-white sm:max-w-2xl dark:border-[#166534] dark:bg-[#052e16]"
        >
            <DialogHeader class="space-y-3">
                <DialogTitle class="text-[#14532D] dark:text-white">
                    {{ heading }}
                </DialogTitle>
                <DialogDescription
                    class="text-sm leading-6 text-[#166534]/80 dark:text-[#dcfce7]/75"
                >
                    {{ description }}
                </DialogDescription>
            </DialogHeader>

            <AlertError v-if="formErrors.length" :errors="formErrors" />

            <form class="space-y-5" @submit.prevent="submitProject">
                <div class="grid gap-2">
                    <Label for="project-title">Project title</Label>
                    <Input
                        id="project-title"
                        v-model="form.title"
                        placeholder="Realtime analytics dashboard"
                    />
                    <InputError :message="form.errors.title" />
                </div>

                <div class="grid gap-2">
                    <Label for="project-description">Description</Label>
                    <textarea
                        id="project-description"
                        v-model="form.description"
                        rows="5"
                        placeholder="Explain what the screenshot shows, the problem it solves, and who it serves."
                        class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                    />
                    <InputError :message="form.errors.description" />
                </div>

                <div class="grid gap-2">
                    <Label for="project-tags">Tags</Label>
                    <Input
                        id="project-tags"
                        v-model="form.tags"
                        placeholder="vue, saas, analytics"
                    />
                    <div v-if="parsedTags.length" class="flex flex-wrap gap-2">
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
                    <Label for="project-image">
                        {{ isEditing ? 'Replace screenshot' : 'Screenshot' }}
                    </Label>
                    <input
                        id="project-image"
                        ref="fileInput"
                        type="file"
                        accept="image/png,image/jpeg,image/webp"
                        class="h-11 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none file:mr-4 file:border-0 file:bg-transparent file:font-medium file:text-foreground"
                        @change="handleImageChange"
                    />
                    <p
                        class="text-xs leading-6 text-[#166534]/70 dark:text-[#dcfce7]/70"
                    >
                        {{
                            isEditing
                                ? 'Leave this empty to keep the current screenshot. Upload a new file to replace both the original image and generated thumbnail.'
                                : 'PNG, JPG, or WebP up to 10 MB. Cloudinary stores the original and generates the gallery thumbnail automatically.'
                        }}
                    </p>
                    <div
                        v-if="form.progress"
                        class="overflow-hidden rounded-full bg-[#dcfce7] dark:bg-[#14532d]"
                    >
                        <div
                            class="h-2 bg-[#16a34a] transition-all"
                            :style="{ width: `${form.progress.percentage}%` }"
                        />
                    </div>
                    <p
                        v-if="form.progress"
                        class="text-xs font-medium text-[#166534] dark:text-[#dcfce7]"
                    >
                        Upload {{ form.progress.percentage }}%
                    </p>
                    <div
                        v-if="isEditing && project"
                        class="rounded-2xl border border-[#bbf7d0] bg-[#f0fdf4] p-3 text-sm text-[#166534] dark:border-[#166534] dark:bg-[#14532d] dark:text-[#dcfce7]"
                    >
                        <div class="flex items-center gap-2 font-medium">
                            <CheckCircle2 class="size-4" />
                            Current screenshot stays active until you upload a replacement.
                        </div>
                    </div>
                    <InputError :message="form.errors.image" />
                </div>

                <DialogFooter class="gap-2">
                    <Button type="button" variant="secondary" @click="open = false">
                        Cancel
                    </Button>

                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-full bg-[linear-gradient(135deg,var(--portfolio-green)_0%,var(--portfolio-mint)_60%,var(--portfolio-yellow)_100%)] px-5 text-[#14532D] shadow-[0_16px_32px_-20px_rgba(34,197,94,0.6)] hover:opacity-95"
                    >
                        <component
                            :is="isEditing ? PencilLine : Plus"
                            class="mr-2 size-4"
                        />
                        {{ submitLabel }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
