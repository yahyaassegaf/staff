<script lang="ts" setup>

import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import PasswordInput from '../../../../shared/UI/passwordInput.vue';

const setBodyClass = (action: string) => {
    if (action === "add") {
        document.body.classList.add("bg-white");
    } else {
        document.body.classList.remove("bg-white");
    }
};

onMounted(() => {
    const router = useRouter();

    // Check if the user has visited before
    if (localStorage.getItem("visited") === "true") {
        setBodyClass("add");
    } else {
        setBodyClass("add");
        localStorage.setItem("visited", "true");
    }

    // Listen for route changes to remove class on navigation
    router.beforeEach(() => {
        setBodyClass("remove");
    });

    const handleBeforeUnload = () => {
        setBodyClass("remove");
        localStorage.removeItem("visited");
    };

    window.addEventListener("beforeunload", handleBeforeUnload, {
        passive: true
    });

    return () => {
        window.removeEventListener("beforeunload", handleBeforeUnload);
        setBodyClass("remove");
    };
});
</script>

<template>
    <div class="row authentication authentication-cover-main mx-0">
        <div class="col-xxl-9 col-xl-9">
            <div class="row justify-content-center align-items-center h-100">
                <div class="col-xxl-4 col-xl-5 col-lg-6 col-md-6 col-sm-8 col-12">
                    <div class="card custom-card border-0 shadow-none my-4">
                        <div class="card-body p-5">
                            <div>
                                <h4 class="mb-1 fw-semibold">Reset Password</h4>
                                <p class="mb-4 text-muted fw-normal">Set your new password here.</p>
                            </div>
                            <div class="row gy-3">
                                <div class="col-xl-12">
                                    <label for="reset-password" class="form-label text-default">Current Password</label>
                                    <div class="position-relative">
                                        <PasswordInput initialValue="" name="newpassword" id="newpassword"
                                            placeholder="current password" />
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <label for="reset-newpassword" class="form-label text-default">New Password</label>
                                    <div class="position-relative">
                                        <PasswordInput initialValue="" name="newpassword" id="newpassword"
                                            placeholder="new password" />
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <label for="reset-confirmpassword" class="form-label text-default">Confirm
                                        Password</label>
                                    <div class="position-relative">
                                        <PasswordInput initialValue="" name="newpassword" id="newpassword"
                                            placeholder="confirm password" />
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid mt-3">
                                <router-link to="/dashboards/sales" class="btn btn-primary">Reset Password</router-link>
                            </div>
                            <div class="text-center my-3 authentication-barrier">
                                <span class="op-4 fs-13">OR</span>
                            </div>
                            <div class="d-grid mb-3">
                                <button
                                    class="btn btn-white btn-w-lg border d-flex align-items-center justify-content-center flex-fill mb-3">
                                    <span class="avatar avatar-xs">
                                        <img src="/images/media/apps/google.png" alt="">
                                    </span>
                                    <span class="lh-1 ms-2 fs-13 text-default fw-medium">Signup with Google</span>
                                </button>
                                <button
                                    class="btn btn-white btn-w-lg border d-flex align-items-center justify-content-center flex-fill">
                                    <span class="avatar avatar-xs">
                                        <img src="/images/media/apps/facebook.png" alt="">
                                    </span>
                                    <span class="lh-1 ms-2 fs-13 text-default fw-medium">Signup with Facebook</span>
                                </button>
                            </div>
                            <div class="text-center mt-3 fw-medium">
                                Dont want to reset? <router-link to="/pages/authentication/sign-in/basic"
                                    class="text-primary">login Here</router-link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-xl-3 col-lg-12 d-xl-block d-none px-0">
            <div class="authentication-cover overflow-hidden">
                <div class="authentication-cover-logo">
                    <router-link to="/dashboards/sales">
                        <img src="/images/brand-logos/toggle-logo.png" alt="logo" class="desktop-dark">
                    </router-link>
                </div>
                <div class="authentication-cover-background">
                    <img src="/images/media/backgrounds/9.png" alt="">
                </div>
                <div class="authentication-cover-content">
                    <div class="p-5">
                        <h3 class="fw-semibold lh-base">Welcome to Dashboard</h3>
                        <p class="mb-0 text-muted fw-medium">Manage your website and content with ease using our
                            powerful admin tools.</p>
                    </div>
                    <div>
                        <img src="/images/media/media-72.png" alt="" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


<style scoped>
/* Add your styles here */
</style>
