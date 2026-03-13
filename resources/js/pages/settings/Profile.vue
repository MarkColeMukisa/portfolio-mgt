<script setup lang="ts">
import { Form, Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { CheckCircle2, Trash2, UploadCloud } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import AlertError from '@/components/AlertError.vue';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useInitials } from '@/composables/useInitials';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/profile';
import {
    destroy as destroyProfilePhoto,
    update as updateProfilePhoto,
} from '@/routes/profile-photo';
import { send } from '@/routes/verification';
import type { BreadcrumbItem, User } from '@/types';

type Props = {
    mustVerifyEmail: boolean;
    status?: string;
    flashSuccess?: string | null;
};

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: edit(),
    },
];

const page = usePage<{
    auth: {
        user: User;
    };
}>();
const { getInitials } = useInitials();
const user = computed(() => page.props.auth.user);
const fileInput = ref<HTMLInputElement | null>(null);

const photoForm = useForm<{
    photo: File | null;
}>({
    photo: null,
});

const photoErrors = computed(
    () => Object.values(photoForm.errors).filter(Boolean) as string[],
);

function handlePhotoChange(event: Event): void {
    const target = event.target as HTMLInputElement;
    photoForm.photo = target.files?.[0] ?? null;
}

function uploadPhoto(): void {
    photoForm.post(updateProfilePhoto.url(), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            photoForm.reset();

            if (fileInput.value) {
                fileInput.value.value = '';
            }
        },
    });
}

function removePhoto(): void {
    photoForm.delete(destroyProfilePhoto.url(), {
        preserveScroll: true,
    });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Profile settings" />

        <h1 class="sr-only">Profile settings</h1>

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <Heading
                    variant="small"
                    title="Profile photo"
                    description="Upload a Cloudinary-managed avatar for your portfolio presence"
                />

                <Alert
                    v-if="props.flashSuccess"
                    class="border-[#86efac] bg-[#f0fdf4] text-[#166534] dark:border-[#166534] dark:bg-[#14532d] dark:text-[#dcfce7]"
                >
                    <CheckCircle2 class="size-4" />
                    <AlertTitle>Updated</AlertTitle>
                    <AlertDescription>{{
                        props.flashSuccess
                    }}</AlertDescription>
                </Alert>

                <div
                    class="grid gap-6 rounded-[28px] border border-[#bbf7d0] bg-white p-6 shadow-[0_24px_80px_-44px_rgba(21,83,45,0.35)] lg:grid-cols-[auto_1fr] dark:border-[#166534] dark:bg-[#052e16]/85"
                >
                    <div class="flex flex-col items-center gap-4">
                        <Avatar
                            class="size-28 rounded-[32px] border border-[#bbf7d0] bg-[#f0fdf4] dark:border-[#166534] dark:bg-[#14532d]"
                        >
                            <AvatarImage
                                v-if="user.avatar"
                                :src="user.avatar"
                                :alt="user.name"
                            />
                            <AvatarFallback
                                class="rounded-[32px] bg-transparent text-2xl text-[#166534] dark:text-[#dcfce7]"
                            >
                                {{ getInitials(user.name) }}
                            </AvatarFallback>
                        </Avatar>
                        <p
                            class="text-xs tracking-[0.22em] text-[#16a34a] uppercase dark:text-[#86efac]"
                        >
                            profile_photos/{{ user.id }}
                        </p>
                    </div>

                    <div class="space-y-5">
                        <div class="flex items-start gap-3">
                            <div
                                class="rounded-2xl bg-[#dcfce7] p-3 text-[#15803d] dark:bg-[#14532d] dark:text-[#86efac]"
                            >
                                <UploadCloud class="size-5" />
                            </div>
                            <div>
                                <h2
                                    class="text-xl font-semibold text-[#14532D] dark:text-white"
                                >
                                    Keep your author card polished
                                </h2>
                                <p
                                    class="mt-2 text-sm leading-7 text-[#166534]/80 dark:text-[#dcfce7]/75"
                                >
                                    Your avatar is uploaded to Cloudinary and
                                    reused anywhere your name appears next to a
                                    project.
                                </p>
                            </div>
                        </div>

                        <AlertError
                            v-if="photoErrors.length"
                            :errors="photoErrors"
                        />

                        <form class="space-y-4" @submit.prevent="uploadPhoto">
                            <div class="grid gap-2">
                                <Label for="photo">Profile photo</Label>
                                <input
                                    id="photo"
                                    ref="fileInput"
                                    type="file"
                                    accept="image/png,image/jpeg,image/webp"
                                    class="h-11 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none file:mr-4 file:border-0 file:bg-transparent file:font-medium file:text-foreground"
                                    @change="handlePhotoChange"
                                />
                                <p
                                    class="text-xs leading-6 text-[#166534]/70 dark:text-[#dcfce7]/70"
                                >
                                    PNG, JPG, or WebP up to 5 MB. Replacing the
                                    photo deletes the previous Cloudinary asset
                                    immediately.
                                </p>
                                <InputError :message="photoForm.errors.photo" />
                            </div>

                            <div class="flex flex-wrap gap-3">
                                <Button
                                    type="submit"
                                    :disabled="photoForm.processing"
                                    class="rounded-full bg-[linear-gradient(135deg,var(--portfolio-green)_0%,var(--portfolio-mint)_60%,var(--portfolio-yellow)_100%)] px-5 text-[#14532D] shadow-[0_16px_32px_-20px_rgba(34,197,94,0.6)] hover:opacity-95"
                                >
                                    {{
                                        photoForm.processing
                                            ? 'Uploading...'
                                            : 'Upload photo'
                                    }}
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    :disabled="
                                        photoForm.processing || !user.avatar
                                    "
                                    class="rounded-full border-[#fca5a5] text-[#b91c1c] hover:bg-[#fef2f2] dark:border-[#7f1d1d] dark:text-[#fecaca] dark:hover:bg-[#3f0d12]"
                                    @click="removePhoto"
                                >
                                    <Trash2 class="mr-2 size-4" />
                                    Delete photo
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>

                <Heading
                    variant="small"
                    title="Profile information"
                    description="Update your name and email address"
                />

                <Form
                    v-bind="ProfileController.update.form()"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            class="mt-1 block w-full"
                            name="name"
                            :default-value="user.name"
                            required
                            autocomplete="name"
                            placeholder="Full name"
                        />
                        <InputError class="mt-2" :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            name="email"
                            :default-value="user.email"
                            required
                            autocomplete="username"
                            placeholder="Email address"
                        />
                        <InputError class="mt-2" :message="errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            Your email address is unverified.
                            <Link
                                :href="send()"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            >
                                Click here to resend the verification email.
                            </Link>
                        </p>

                        <div
                            v-if="status === 'verification-link-sent'"
                            class="mt-2 text-sm font-medium text-green-600"
                        >
                            A new verification link has been sent to your email
                            address.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            :disabled="processing"
                            data-test="update-profile-button"
                            >Save</Button
                        >

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="recentlySuccessful"
                                class="text-sm text-neutral-600"
                            >
                                Saved.
                            </p>
                        </Transition>
                    </div>
                </Form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
